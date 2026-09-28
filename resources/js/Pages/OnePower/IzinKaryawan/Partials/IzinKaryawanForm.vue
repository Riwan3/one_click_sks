<script setup>
import { ref, watch, onMounted } from 'vue';
import { FileText, Download } from '@lucide/vue';

const props = defineProps({
    form: { type: Object, required: true },
    isEdit: { type: Boolean, default: false },
    existingBuktiUrl: { type: String, default: null },
    existingBuktiFileName: { type: String, default: null },
    status: { type: String, default: 'Awaiting Approval' },
    isMedic: { type: Boolean, default: false },
    isAdminOrManpower: { type: Boolean, default: false },
    currentUserEmployee: { type: Object, default: () => ({}) },
    onSubmit: { type: Function, required: true },
});

const employeeResults = ref([]);
let searchTimer = null;

const fetchNextSurat = async (jenis) => {
    if (props.isEdit) return;
    try {
        const res = await fetch(`/one-power/deviasi-operator/izin-karyawan/next-surat-number?jenis_izin=${encodeURIComponent(jenis)}`);
        if (res.ok) {
            const data = await res.json();
            props.form.no_surat = data.no_surat || '';
        }
    } catch (e) {
        console.error('Failed to fetch next surat:', e);
    }
};

const handleNrpInput = (e) => {
    if (props.isEdit || !props.isAdminOrManpower) return;
    const keyword = e.target.value.trim();
    clearTimeout(searchTimer);
    if (keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/deviasi-operator/izin-karyawan/search-employee?keyword=${encodeURIComponent(keyword)}`);
            if (res.ok) {
                employeeResults.value = await res.json();
            }
        } catch (err) {
            console.error(err);
        }
    }, 250);
};

const selectEmployee = (emp) => {
    props.form.nrp = emp.nrp || '';
    props.form.nama = emp.nama || '';
    props.form.jabatan = emp.jabatan || '';
    props.form.departemen = emp.dept || emp.departemen || '';
    employeeResults.value = [];
};

const handleFileChange = (e) => {
    const file = e.target.files?.[0];
    props.form.file_bukti = file || null;
};

const onJenisChange = () => {
    fetchNextSurat(props.form.jenis_izin);
};

onMounted(() => {
    if (!props.isEdit) {
        if (!props.isAdminOrManpower && props.currentUserEmployee) {
            if (!props.form.nrp) props.form.nrp = props.currentUserEmployee.nrp || '';
            if (!props.form.nama) props.form.nama = props.currentUserEmployee.nama || '';
            if (!props.form.jabatan) props.form.jabatan = props.currentUserEmployee.jabatan || '';
            if (!props.form.departemen) props.form.departemen = props.currentUserEmployee.departemen || '';
        }
        if (!props.form.no_surat) {
            fetchNextSurat(props.form.jenis_izin);
        }
    }
});

const isImageFile = (url) => {
    if (!url) return false;
    return /\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i.test(url);
};

const isPdfFile = (url) => {
    if (!url) return false;
    return /\.pdf(\?.*)?$/i.test(url);
};

const isReadOnly = props.isEdit && props.status !== 'Awaiting Approval';
</script>

<template>
    <form class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs" @submit.prevent="onSubmit">
        <div class="space-y-4">
            <!-- Jenis Pengajuan -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Jenis Pengajuan <span class="text-red-500">*</span>
                </label>
                <select
                    v-model="form.jenis_izin"
                    :disabled="isEdit || isMedic"
                    :class="[
                        'w-full rounded-lg border px-3 py-2 text-sm font-semibold outline-none',
                        isEdit ? 'bg-slate-100 border-slate-200 text-slate-700 cursor-not-allowed' : 'border-blue-400 bg-blue-50/30 text-slate-800 focus:border-blue-600'
                    ]"
                    @change="onJenisChange"
                >
                    <option v-if="isMedic" value="sakit">SAKIT</option>
                    <template v-else>
                        <option value="izin">IZIN</option>
                        <option value="sakit">SAKIT</option>
                        <option value="emergency">EMERGENCY</option>
                    </template>
                </select>
                <p v-if="form.errors.jenis_izin" class="mt-1 text-xs text-red-600">{{ form.errors.jenis_izin }}</p>
            </div>

            <!-- No Surat & NRP -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No Surat</label>
                    <input
                        v-model="form.no_surat"
                        type="text"
                        readonly
                        class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-mono text-slate-600 outline-none cursor-not-allowed"
                        placeholder="Nomor otomatis..."
                    >
                    <p class="mt-0.5 text-[11px] text-slate-400">Tidak dapat diubah</p>
                </div>

                <div class="relative">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        NRP <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.nrp"
                        type="text"
                        :readonly="isReadOnly || (!isAdminOrManpower && !isEdit)"
                        :class="[
                            'w-full rounded-lg border px-3 py-2 text-sm outline-none',
                            (isReadOnly || (!isAdminOrManpower && !isEdit)) ? 'bg-slate-100 border-slate-200 cursor-not-allowed text-slate-600' : 'bg-white border-slate-300 focus:border-blue-500'
                        ]"
                        placeholder="Ketik NRP atau Nama..."
                        autocomplete="off"
                        @input="handleNrpInput"
                    >
                    <!-- Autocomplete Dropdown for Admin/Manpower on create -->
                    <div
                        v-if="!isEdit && isAdminOrManpower && employeeResults.length > 0"
                        class="absolute z-20 left-0 right-0 mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg"
                    >
                        <button
                            v-for="emp in employeeResults"
                            :key="emp.nrp"
                            type="button"
                            class="w-full px-3 py-2 text-left text-xs hover:bg-blue-50 border-b border-slate-100 last:border-0 flex items-center justify-between"
                            @click="selectEmployee(emp)"
                        >
                            <span class="font-bold text-slate-800">{{ emp.nrp }} - {{ emp.nama }}</span>
                            <span class="text-slate-500 text-[11px]">{{ emp.jabatan || emp.dept }}</span>
                        </button>
                    </div>
                    <p v-if="form.errors.nrp" class="mt-1 text-xs text-red-600">{{ form.errors.nrp }}</p>
                </div>
            </div>

            <!-- Nama & Jabatan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.nama"
                        type="text"
                        :readonly="isReadOnly"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        placeholder="Nama karyawan..."
                    >
                    <p v-if="form.errors.nama" class="mt-1 text-xs text-red-600">{{ form.errors.nama }}</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan</label>
                    <input
                        v-model="form.jabatan"
                        type="text"
                        :readonly="isReadOnly"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        placeholder="Jabatan..."
                    >
                </div>
            </div>

            <!-- Departemen -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Departemen</label>
                <input
                    v-model="form.departemen"
                    type="text"
                    :readonly="isReadOnly"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    placeholder="Isi departemen manual..."
                >
            </div>

            <hr class="border-slate-200 my-2">

            <!-- SECTION IZIN & EMERGENCY -->
            <div v-if="form.jenis_izin === 'izin' || form.jenis_izin === 'emergency'" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Alasan <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        v-model="form.alasan"
                        rows="2"
                        :readonly="isReadOnly"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        placeholder="Sebutkan alasan atau keperluan..."
                    ></textarea>
                    <p v-if="form.errors.alasan" class="mt-1 text-xs text-red-600">{{ form.errors.alasan }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Dari <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.tgl_berangkat"
                            type="date"
                            :readonly="isReadOnly"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        >
                        <p v-if="form.errors.tgl_berangkat" class="mt-1 text-xs text-red-600">{{ form.errors.tgl_berangkat }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Sampai</label>
                        <input
                            v-model="form.tgl_kembali"
                            type="date"
                            :readonly="isReadOnly"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        >
                        <p v-if="form.errors.tgl_kembali" class="mt-1 text-xs text-red-600">{{ form.errors.tgl_kembali }}</p>
                    </div>
                </div>
            </div>

            <!-- SECTION SAKIT -->
            <div v-if="form.jenis_izin === 'sakit'" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">No Telepon</label>
                    <input
                        v-model="form.no_telepon"
                        type="tel"
                        :readonly="isReadOnly"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        placeholder="Contoh: 08123456789"
                    >
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keluhan</label>
                    <textarea
                        v-model="form.keluhan"
                        rows="2"
                        :readonly="isReadOnly"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        placeholder="Deskripsikan keluhan sakit..."
                    ></textarea>
                </div>

                <!-- Detail Pemeriksaan (Card) -->
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700">Hasil Pemeriksaan</span>
                        <span class="rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600 uppercase">Opsional</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">TD (mmHg)</label>
                            <input
                                v-model="form.hasil_td"
                                type="text"
                                :readonly="isReadOnly"
                                placeholder="120/80"
                                class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs outline-none focus:border-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">RR (/mnt)</label>
                            <input
                                v-model="form.hasil_rr"
                                type="text"
                                :readonly="isReadOnly"
                                placeholder="20"
                                class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs outline-none focus:border-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">T (&deg;C)</label>
                            <input
                                v-model="form.hasil_t"
                                type="text"
                                :readonly="isReadOnly"
                                placeholder="36.5"
                                class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs outline-none focus:border-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">P (/mnt)</label>
                            <input
                                v-model="form.hasil_p"
                                type="text"
                                :readonly="isReadOnly"
                                placeholder="80"
                                class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs outline-none focus:border-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-500 mb-0.5">SPO2 (%)</label>
                            <input
                                v-model="form.hasil_spo"
                                type="text"
                                :readonly="isReadOnly"
                                placeholder="98"
                                class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs outline-none focus:border-blue-500"
                            >
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Diagnosa</label>
                        <input
                            v-model="form.diagnosa"
                            type="text"
                            :readonly="isReadOnly"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Terapi</label>
                        <input
                            v-model="form.terapi"
                            type="text"
                            :readonly="isReadOnly"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        >
                    </div>
                </div>

                <!-- Keterangan Status (Radio Options 1-5) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Keterangan Status <span class="text-red-500">*</span>
                    </label>
                    <div class="flex flex-wrap gap-3">
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                            <input v-model="form.keterangan_status" type="radio" :value="1" :disabled="isReadOnly" class="text-blue-600 focus:ring-blue-500">
                            <span>1. Dapat Bekerja Kembali</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                            <input v-model="form.keterangan_status" type="radio" :value="2" :disabled="isReadOnly" class="text-blue-600 focus:ring-blue-500">
                            <span>2. Istirahat 1 jam (Observasi)</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                            <input v-model="form.keterangan_status" type="radio" :value="3" :disabled="isReadOnly" class="text-blue-600 focus:ring-blue-500">
                            <span>3. Istirahat di Rumah (Dipulangkan)</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                            <input v-model="form.keterangan_status" type="radio" :value="4" :disabled="isReadOnly" class="text-blue-600 focus:ring-blue-500">
                            <span>4. Diistirahatkan di Rumah</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                            <input v-model="form.keterangan_status" type="radio" :value="5" :disabled="isReadOnly" class="text-blue-600 focus:ring-blue-500">
                            <span>5. Opname</span>
                        </label>
                    </div>
                </div>

                <!-- Rujukan Fields & Checkbox Dipulangkan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rujuk Ke</label>
                        <input
                            v-model="form.rujuk_ke"
                            type="text"
                            :readonly="isReadOnly"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        >
                    </div>
                    <div class="flex items-center gap-2 pt-5">
                        <input
                            id="form_dipulangkan_tidur"
                            v-model="form.dipulangkan_tidur"
                            type="checkbox"
                            :disabled="isReadOnly"
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >
                        <label for="form_dipulangkan_tidur" class="text-xs font-semibold text-slate-700 cursor-pointer">
                            Dipulangkan (tidur &lt; 4 jam)
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Rumah Sakit Rujukan</label>
                        <input
                            v-model="form.rumah_sakit_rujukan"
                            type="text"
                            :readonly="isReadOnly"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            placeholder="Rumah sakit rujukan..."
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Dokter Rujukan</label>
                        <input
                            v-model="form.dokter_rujukan"
                            type="text"
                            :readonly="isReadOnly"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            placeholder="Dokter rujukan..."
                        >
                    </div>
                </div>
            </div>

            <!-- BUKTI (Preview & Upload) -->
            <div class="pt-2">
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Bukti (File/Gambar) <span v-if="!isEdit" class="text-red-500">*</span>
                </label>

                <!-- Existing file preview (V1 style) -->
                <div v-if="existingBuktiUrl" class="mb-3 space-y-2">
                    <div v-if="isImageFile(existingBuktiUrl)" class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2 max-w-md">
                        <img
                            :src="existingBuktiUrl"
                            alt="Bukti"
                            class="max-h-72 w-full object-contain rounded-lg"
                        >
                    </div>
                    <div v-else-if="isPdfFile(existingBuktiUrl)" class="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2">
                        <iframe :src="existingBuktiUrl" class="w-full h-80 rounded border-0"></iframe>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-600">File: <strong class="text-slate-800">{{ existingBuktiFileName || 'Bukti Lampiran' }}</strong></span>
                        <a
                            :href="existingBuktiUrl"
                            target="_blank"
                            class="inline-flex items-center gap-1 rounded bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-600 hover:bg-blue-100"
                        >
                            <Download class="h-3.5 w-3.5" /> Download / Buka
                        </a>
                    </div>
                </div>
                <div v-else-if="isEdit" class="mb-2 text-xs text-amber-600 bg-amber-50 p-2 rounded-lg border border-amber-200">
                    Belum ada file bukti yang diunggah.
                </div>

                <div v-if="!isReadOnly">
                    <input
                        type="file"
                        accept="image/*,.pdf,.doc,.docx"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-600 outline-none file:mr-3 file:rounded file:border-0 file:bg-blue-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                        @change="handleFileChange"
                    >
                    <p class="mt-1 text-[11px] text-slate-400">
                        {{ isEdit ? 'Unggah untuk mengganti file bukti saat ini. ' : '' }}Max 10MB. Format: JPG, PNG, PDF, DOCX
                    </p>
                    <p v-if="form.errors.file_bukti" class="mt-1 text-xs text-red-600">{{ form.errors.file_bukti }}</p>
                </div>
            </div>
        </div>

        <!-- FORM ACTIONS -->
        <div class="mt-6 flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
            <a
                href="/one-power/deviasi-operator/izin-karyawan"
                class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
            >
                Kembali
            </a>
            <button
                v-if="!isReadOnly"
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-blue-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-blue-700 active:scale-95 disabled:opacity-60 transition"
            >
                {{ form.processing ? 'Menyimpan...' : (isEdit ? 'SIMPAN PERUBAHAN' : 'SIMPAN PENGAJUAN') }}
            </button>
        </div>
    </form>
</template>
