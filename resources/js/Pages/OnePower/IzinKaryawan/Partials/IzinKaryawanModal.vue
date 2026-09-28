<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, Search } from '@lucide/vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    isMedic: { type: Boolean, default: false },
    isAdminOrManpower: { type: Boolean, default: false },
    currentUserEmployee: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close']);

const form = useForm({
    jenis_izin: props.isMedic ? 'sakit' : 'izin',
    no_surat: '',
    nama: '',
    nrp: '',
    jabatan: '',
    departemen: '',
    alasan: '',
    tujuan: '',
    alamat: '',
    tgl_berangkat: '',
    tgl_kembali: '',
    no_telepon: '',
    keluhan: '',
    hasil_td: '',
    hasil_rr: '',
    hasil_t: '',
    hasil_p: '',
    hasil_spo: '',
    diagnosa: '',
    terapi: '',
    keterangan_status: 1,
    rujuk_ke: '',
    rumah_sakit_rujukan: '',
    dokter_rujukan: '',
    dipulangkan_tidur: false,
    file_bukti: null,
});

const employeeResults = ref([]);
const isSearching = ref(false);
let searchTimer = null;

const fetchNextSurat = async (jenis) => {
    try {
        const res = await fetch(`/one-power/deviasi-operator/izin-karyawan/next-surat-number?jenis_izin=${encodeURIComponent(jenis)}`);
        if (res.ok) {
            const data = await res.json();
            form.no_surat = data.no_surat || '';
        }
    } catch (e) {
        console.error('Failed to fetch next surat:', e);
    }
};

const handleNrpInput = (e) => {
    if (!props.isAdminOrManpower) return;
    const keyword = e.target.value.trim();
    clearTimeout(searchTimer);
    if (keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    isSearching.value = true;
    searchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/deviasi-operator/izin-karyawan/search-employee?keyword=${encodeURIComponent(keyword)}`);
            if (res.ok) {
                employeeResults.value = await res.json();
            }
        } finally {
            isSearching.value = false;
        }
    }, 250);
};

const selectEmployee = (emp) => {
    form.nrp = emp.nrp || '';
    form.nama = emp.nama || '';
    form.jabatan = emp.jabatan || '';
    form.departemen = emp.dept || emp.departemen || '';
    employeeResults.value = [];
};

const handleFileChange = (e) => {
    const file = e.target.files?.[0];
    form.file_bukti = file || null;
};

const onJenisChange = () => {
    fetchNextSurat(form.jenis_izin);
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        form.reset();
        form.clearErrors();
        form.jenis_izin = props.isMedic ? 'sakit' : 'izin';
        form.keterangan_status = 1;
        employeeResults.value = [];

        if (!props.isAdminOrManpower && props.currentUserEmployee) {
            form.nrp = props.currentUserEmployee.nrp || '';
            form.nama = props.currentUserEmployee.nama || '';
            form.jabatan = props.currentUserEmployee.jabatan || '';
            form.departemen = props.currentUserEmployee.departemen || '';
        }

        fetchNextSurat(form.jenis_izin);
    }
});

const submit = () => {
    form.post('/one-power/deviasi-operator/izin-karyawan', {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-xs">
        <div class="relative w-full max-w-3xl rounded-2xl bg-white shadow-2xl transition-all max-h-[90vh] flex flex-col">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-6 py-4 rounded-t-2xl">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Tambah Pengajuan</h3>
                    <p class="text-xs text-slate-500">Isi formulir pengajuan izin, sakit, atau emergency karyawan</p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-200 hover:text-slate-600 transition"
                    @click="emit('close')"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="overflow-y-auto p-6 space-y-4 flex-1">
                <!-- Jenis Pengajuan -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jenis Pengajuan <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.jenis_izin"
                        class="w-full rounded-lg border border-blue-400 bg-blue-50/30 px-3 py-2 text-sm font-semibold text-slate-800 outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-500"
                        :disabled="isMedic"
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">No Surat (Otomatis)</label>
                        <input
                            v-model="form.no_surat"
                            type="text"
                            readonly
                            class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-mono text-slate-600 outline-none cursor-not-allowed"
                            placeholder="Nomor otomatis..."
                        >
                    </div>

                    <div class="relative">
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            NRP <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.nrp"
                            type="text"
                            :readonly="!isAdminOrManpower"
                            :class="[
                                'w-full rounded-lg border px-3 py-2 text-sm outline-none',
                                !isAdminOrManpower ? 'bg-slate-100 border-slate-200 cursor-not-allowed text-slate-600' : 'bg-white border-slate-300 focus:border-blue-500'
                            ]"
                            placeholder="Ketik NRP atau Nama..."
                            autocomplete="off"
                            @input="handleNrpInput"
                        >
                        <!-- Autocomplete Dropdown for Admin/Manpower -->
                        <div
                            v-if="isAdminOrManpower && employeeResults.length > 0"
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
                            :readonly="!isAdminOrManpower"
                            :class="[
                                'w-full rounded-lg border px-3 py-2 text-sm outline-none',
                                !isAdminOrManpower ? 'bg-slate-100 border-slate-200 cursor-not-allowed text-slate-600' : 'bg-white border-slate-300 focus:border-blue-500'
                            ]"
                            placeholder="Nama otomatis..."
                        >
                        <p v-if="form.errors.nama" class="mt-1 text-xs text-red-600">{{ form.errors.nama }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan</label>
                        <input
                            v-model="form.jabatan"
                            type="text"
                            readonly
                            class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-600 outline-none cursor-not-allowed"
                            placeholder="Jabatan otomatis..."
                        >
                    </div>
                </div>

                <!-- Departemen -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Departemen</label>
                    <input
                        v-model="form.departemen"
                        type="text"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        placeholder="Isi departemen..."
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
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                            <p v-if="form.errors.tgl_berangkat" class="mt-1 text-xs text-red-600">{{ form.errors.tgl_berangkat }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sampai</label>
                            <input
                                v-model="form.tgl_kembali"
                                type="date"
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
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            placeholder="Contoh: 08123456789"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keluhan</label>
                        <textarea
                            v-model="form.keluhan"
                            rows="2"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            placeholder="Deskripsikan keluhan sakit..."
                        ></textarea>
                    </div>

                    <!-- Detail Pemeriksaan (Card) -->
                    <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Detail Pemeriksaan (Jika Ada)</span>
                            <span class="rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-bold text-slate-600 uppercase">Opsional</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-0.5">TD (mmHg)</label>
                                <input
                                    v-model="form.hasil_td"
                                    type="text"
                                    placeholder="120/80"
                                    class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs outline-none focus:border-blue-500"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-0.5">RR (/mnt)</label>
                                <input
                                    v-model="form.hasil_rr"
                                    type="text"
                                    placeholder="20"
                                    class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs outline-none focus:border-blue-500"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-0.5">T (&deg;C)</label>
                                <input
                                    v-model="form.hasil_t"
                                    type="text"
                                    placeholder="36.5"
                                    class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs outline-none focus:border-blue-500"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-0.5">P (/mnt)</label>
                                <input
                                    v-model="form.hasil_p"
                                    type="text"
                                    placeholder="80"
                                    class="w-full rounded border border-slate-300 bg-white px-2 py-1 text-xs outline-none focus:border-blue-500"
                                >
                            </div>
                            <div>
                                <label class="block text-[11px] text-slate-500 mb-0.5">SPO2 (%)</label>
                                <input
                                    v-model="form.hasil_spo"
                                    type="text"
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
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Terapi</label>
                            <input
                                v-model="form.terapi"
                                type="text"
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
                                <input v-model="form.keterangan_status" type="radio" :value="1" class="text-blue-600 focus:ring-blue-500">
                                <span>Bisa Bekerja</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                                <input v-model="form.keterangan_status" type="radio" :value="2" class="text-blue-600 focus:ring-blue-500">
                                <span>Observasi (1 jam)</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                                <input v-model="form.keterangan_status" type="radio" :value="3" class="text-blue-600 focus:ring-blue-500">
                                <span>Dipulangkan</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                                <input v-model="form.keterangan_status" type="radio" :value="4" class="text-blue-600 focus:ring-blue-500">
                                <span>Diistirahatkan di Rumah</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                                <input v-model="form.keterangan_status" type="radio" :value="5" class="text-blue-600 focus:ring-blue-500">
                                <span>Opname</span>
                            </label>
                        </div>
                    </div>

                    <!-- Rujukan Fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Rujuk Ke / RS</label>
                            <input
                                v-model="form.rumah_sakit_rujukan"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                                placeholder="Nama Rumah Sakit..."
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Dokter</label>
                            <input
                                v-model="form.dokter_rujukan"
                                type="text"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-blue-500"
                                placeholder="Nama Dokter..."
                            >
                        </div>
                    </div>
                </div>

                <!-- Unggah Bukti -->
                <div class="pt-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Unggah Bukti (File/Gambar) <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="file"
                        accept="image/*,.pdf,.doc,.docx"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs text-slate-600 outline-none file:mr-3 file:rounded file:border-0 file:bg-blue-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                        @change="handleFileChange"
                    >
                    <p class="mt-1 text-[11px] text-slate-400">Max 10MB. Format: JPG, PNG, PDF, DOCX</p>
                    <p v-if="form.errors.file_bukti" class="mt-1 text-xs text-red-600">{{ form.errors.file_bukti }}</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50/70 px-6 py-3.5 rounded-b-2xl">
                <button
                    type="button"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 transition"
                    @click="emit('close')"
                >
                    Batal
                </button>
                <button
                    type="button"
                    :disabled="form.processing"
                    class="rounded-lg bg-blue-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-blue-700 active:scale-95 disabled:opacity-60 transition"
                    @click="submit"
                >
                    {{ form.processing ? 'Menyimpan...' : 'SIMPAN PENGAJUAN' }}
                </button>
            </div>
        </div>
    </div>
</template>
