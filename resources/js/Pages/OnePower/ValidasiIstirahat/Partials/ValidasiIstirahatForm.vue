<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search, RefreshCw, FileText } from 'lucide-vue-next';
import SignaturePad from '@/Components/ui/SignaturePad.vue';
import PhotoUploadField from '@/Components/ui/PhotoUploadField.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
    isEdit: { type: Boolean, default: false },
    documentSettings: { type: Object, default: () => ({}) },
});

const employeeResults = ref([]);
const showEmployeeResults = ref(false);
const employeeQuery = ref('');
let employeeTimer = null;

watch(employeeQuery, (keyword) => {
    clearTimeout(employeeTimer);
    if (! keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    employeeTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/pembinaan/validasi-istirahat/search-employee?keyword=${encodeURIComponent(keyword)}`);
            employeeResults.value = await res.json();
            showEmployeeResults.value = true;
        } catch (e) {
            console.error(e);
        }
    }, 250);
});

const pickEmployee = (emp) => {
    props.form.employee_id = emp.id;
    props.form.nrp = emp.nrp;
    props.form.nama = emp.name;
    props.form.jabatan = emp.jabatan_ss6 ?? '';
    props.form.departemen = emp.unit ?? '';
    showEmployeeResults.value = false;
    employeeQuery.value = '';
};

const fetchNextNoSurat = async () => {
    try {
        const res = await fetch(`/one-power/pembinaan/validasi-istirahat/next-number?tanggal=${props.form.tanggal_validasi || ''}`);
        const data = await res.json();
        if (data.nomor_surat) {
            props.form.no_surat = data.nomor_surat;
            props.form.doc_rank = data.doc_rank;
        }
    } catch (e) {
        console.error(e);
    }
};
</script>

<template>
    <div class="max-w-4xl rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-blue-700 to-indigo-900 text-white flex items-center justify-between">
            <h2 class="text-base font-bold flex items-center gap-2">
                <FileText class="h-5 w-5" />
                {{ isEdit ? 'Edit Formulir Validasi Istirahat' : 'Formulir Validasi Istirahat Karyawan' }}
            </h2>
            <div class="text-xs text-blue-100 font-medium">
                {{ documentSettings.noDokumen || 'PPA-ADRO-F-SHE-29G' }}
            </div>
        </div>

        <form class="p-6 space-y-6" @submit.prevent="onSubmit">
            <!-- SECTION 1: IDENTITAS KARYAWAN -->
            <div>
                <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-1">
                    IDENTITAS KARYAWAN
                </h3>

                <!-- Search Karyawan Box -->
                <div class="relative mb-4 rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Cari Karyawan (NRP / Nama)
                    </label>
                    <div class="relative">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="employeeQuery"
                            type="text"
                            placeholder="Ketik NRP atau nama untuk mencari data karyawan..."
                            class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                            autocomplete="off"
                            @focus="showEmployeeResults = true"
                        >
                        <div
                            v-if="showEmployeeResults && employeeResults.length"
                            class="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl"
                        >
                            <button
                                v-for="emp in employeeResults"
                                :key="emp.id"
                                type="button"
                                class="block w-full border-b border-slate-50 px-3 py-2 text-left text-sm hover:bg-blue-50/70 transition-colors"
                                @click="pickEmployee(emp)"
                            >
                                <span class="font-bold text-slate-800">{{ emp.nrp }}</span> &ndash;
                                <span class="font-semibold text-slate-700 uppercase">{{ emp.name }}</span>
                                <span class="text-xs text-slate-500 block">{{ emp.jabatan_ss6 || '-' }} ({{ emp.unit || '-' }})</span>
                            </button>
                        </div>
                    </div>
                    <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Nama Karyawan <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.nama"
                            type="text"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm uppercase outline-none focus:border-blue-500"
                            required
                        >
                        <p v-if="form.errors.nama" class="mt-1 text-xs text-red-600">{{ form.errors.nama }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                            NRP <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.nrp"
                            type="text"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            required
                        >
                        <p v-if="form.errors.nrp" class="mt-1 text-xs text-red-600">{{ form.errors.nrp }}</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Departemen
                        </label>
                        <input
                            v-model="form.departemen"
                            type="text"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm uppercase outline-none focus:border-blue-500"
                        >
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 mt-4">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Jabatan
                        </label>
                        <input
                            v-model="form.jabatan"
                            type="text"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm uppercase outline-none focus:border-blue-500"
                        >
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Tanggal Validasi <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.tanggal_validasi"
                            type="date"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            required
                        >
                    </div>
                </div>

                <!-- No. Surat -->
                <div class="mt-4">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Nomor Surat Validasi
                    </label>
                    <div class="flex gap-2">
                        <input
                            v-model="form.no_surat"
                            type="text"
                            placeholder="Contoh: 001/VI/PPA/ADW/PRO/IX/2026"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 outline-none focus:border-blue-500"
                        >
                        <button
                            type="button"
                            class="flex items-center gap-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition-colors"
                            title="Generate Nomor Berikutnya"
                            @click="fetchNextNoSurat"
                        >
                            <RefreshCw class="h-3.5 w-3.5" /> Auto
                        </button>
                    </div>
                    <p v-if="form.errors.no_surat" class="mt-1 text-xs text-red-600">{{ form.errors.no_surat }}</p>
                </div>
            </div>

            <!-- SECTION 2: PERTANYAAN & JAWABAN -->
            <div>
                <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-1">
                    PERTANYAAN &amp; JAWABAN
                </h3>

                <div class="space-y-4">
                    <!-- Q1 -->
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">
                            1. Mengapa jam tidur anda &lt; 6 jam ?
                        </label>
                        <textarea
                            v-model="form.alasan_kurang_tidur"
                            rows="2"
                            placeholder="Jelaskan alasan jam tidur kurang dari 6 jam..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        />
                    </div>

                    <!-- Q2 -->
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">
                            2. (Khusus tidur &lt; 6 Jam, berulang) Mengapa anda melakukan jam tidur &lt; 6 jam secara berulang ?
                        </label>
                        <textarea
                            v-model="form.alasan_kurang_tidur_berulang"
                            rows="2"
                            placeholder="Jelaskan alasan tidur berulang kali kurang dari 6 jam..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        />
                    </div>

                    <!-- Q3 -->
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700">
                            3. Apakah anda mengalami kendala seperti gangguan tidur ? Jika iya, Jelaskan bagaimana kendala / gangguan tersebut !
                        </label>
                        <textarea
                            v-model="form.kendala_tidur"
                            rows="2"
                            placeholder="Jelaskan gangguan atau kendala tidur jika ada..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        />
                    </div>

                    <!-- Q4: Tinggal Di -->
                    <div class="grid gap-4 sm:grid-cols-2 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-slate-700">
                                4. Apakah anda tinggal di mess atau non mess ?
                            </label>
                            <select
                                v-model="form.tinggal_di"
                                class="w-full sm:w-60 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                                <option value="Non Mess">Non Mess</option>
                                <option value="Mess">Mess</option>
                            </select>
                        </div>

                        <div v-if="form.tinggal_di === 'Mess'" class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-slate-700">Nama Mess</label>
                            <input
                                v-model="form.nama_mess"
                                type="text"
                                placeholder="Contoh: Mess A-04"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                        </div>

                        <div v-if="form.tinggal_di === 'Non Mess'" class="sm:col-span-2">
                            <label class="mb-1 block text-xs font-semibold text-slate-700">Alamat Non Mess &amp; Kedekatan Keramaian</label>
                            <textarea
                                v-model="form.alamat_non_mess"
                                rows="2"
                                placeholder="Sebutkan alamat tempat tinggal dan kondisi keramaian..."
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                            />
                        </div>
                    </div>

                    <!-- Q5, Q6, Q7 -->
                    <div class="grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">
                                5. Status Sekarang
                            </label>
                            <select
                                v-model="form.status_perkawinan"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                                <option value="Single">Single</option>
                                <option value="Menikah">Menikah</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">
                                6. Jumlah anak serumah
                            </label>
                            <input
                                v-model="form.jumlah_anak_serumah"
                                type="text"
                                placeholder="Contoh: 2 orang"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">
                                7. Anak usia &lt; 2 thn
                            </label>
                            <input
                                v-model="form.anak_usia_kurang_2th"
                                type="text"
                                placeholder="Contoh: 1 tahun"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                        </div>
                    </div>

                    <!-- Q8 & Q9 -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">
                                8. Pekerjaan sampingan ? Sebutkan.
                            </label>
                            <textarea
                                v-model="form.pekerjaan_sampingan"
                                rows="2"
                                placeholder="Contoh: Tidak ada / Usaha dagang..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700">
                                9. Keterangan
                            </label>
                            <textarea
                                v-model="form.keterangan"
                                rows="2"
                                placeholder="Catatan atau keterangan tambahan..."
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION: UPLOAD FOTO DOKUMEN -->
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <PhotoUploadField
                    v-model="form.foto"
                    :existing-photo="form.existing_foto"
                    :hapus-foto="form.hapus_foto"
                    :error="form.errors?.foto"
                    label="Foto Bukti Dokumen Validasi Istirahat"
                    @update:hapus-foto="form.hapus_foto = $event"
                />
            </div>

            <!-- SECTION 3: INFORMASI PENGAWAS & TTD -->
            <div>
                <h3 class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-800 border-b border-slate-100 pb-1">
                    PENGAWAS &amp; TANDA TANGAN
                </h3>

                <div class="mb-4">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Nama Pengawas <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.nama_pengawas"
                        type="text"
                        placeholder="Nama Pengawas yang memvalidasi..."
                        class="w-full sm:w-80 rounded-lg border border-slate-200 px-3 py-2 text-sm uppercase outline-none focus:border-blue-500"
                        required
                    >
                    <p v-if="form.errors.nama_pengawas" class="mt-1 text-xs text-red-600">{{ form.errors.nama_pengawas }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <SignaturePad
                            v-model="form.ttd_karyawan"
                            label="Tanda Tangan Karyawan"
                        />
                    </div>
                    <div>
                        <SignaturePad
                            v-model="form.ttd_pengawas"
                            label="Tanda Tangan Pengawas"
                        />
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                <Link
                    href="/one-power/pembinaan/validasi-istirahat"
                    class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors"
                >
                    Kembali
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-6 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                    {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Simpan Data') }}
                </button>
            </div>
        </form>
    </div>
</template>
