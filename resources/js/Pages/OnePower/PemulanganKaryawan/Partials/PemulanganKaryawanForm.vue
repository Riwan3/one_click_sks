<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search, RefreshCw, FileText } from 'lucide-vue-next';
import SignaturePad from '@/Components/ui/SignaturePad.vue';

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
            const res = await fetch(`/one-power/pembinaan/pemulangan-karyawan/search-employee?keyword=${encodeURIComponent(keyword)}`);
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
        const res = await fetch(`/one-power/pembinaan/pemulangan-karyawan/next-number?tanggal=${props.form.tanggal || ''}`);
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
        <!-- Form Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-blue-700 to-indigo-900 text-white flex items-center justify-between">
            <h2 class="text-base font-bold flex items-center gap-2">
                <FileText class="h-5 w-5" />
                {{ isEdit ? 'Edit Formulir Pemulangan Karyawan' : 'Formulir Pemulangan Karyawan' }}
            </h2>
            <div class="text-xs text-blue-100 font-medium">
                {{ documentSettings.noDokumen || 'PPA-ADRO-F-SHE-29F' }}
            </div>
        </div>

        <form class="p-6 space-y-5" @submit.prevent="onSubmit">
            <!-- Search Karyawan Box -->
            <div class="relative rounded-xl border border-blue-100 bg-blue-50/50 p-4">
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

            <!-- Identitas (Nama, NRP, Jabatan, Dept) -->
            <div class="grid gap-4 sm:grid-cols-4">
                <div class="sm:col-span-2">
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

            <!-- Jabatan, Lokasi, Tanggal, No. Surat -->
            <div class="grid gap-4 sm:grid-cols-3">
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
                        Lokasi <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.lokasi"
                        type="text"
                        placeholder="Contoh: Wara, CSA INGGRIS"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        required
                    >
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.tanggal"
                        type="date"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        required
                    >
                </div>
            </div>

            <!-- No. Surat -->
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Nomor Surat Pemulangan
                </label>
                <div class="flex gap-2">
                    <input
                        v-model="form.no_surat"
                        type="text"
                        placeholder="Contoh: 001/PK/PPA/ADW/PRO/IX/2026"
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

            <!-- Indikasi Fatigue -->
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Indikasi Fatigue <span class="text-red-500">*</span>
                </label>
                <input
                    v-model="form.indikasi_fatigue"
                    type="text"
                    placeholder="Contoh: kurang istirahat, mengantuk saat briefing"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    required
                >
            </div>

            <!-- Switches: Jam Tidur <= 4 Jam & Fatigue Berat -->
            <div class="grid gap-4 sm:grid-cols-2 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input
                        v-model="form.jam_tidur_kurang_4jam"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                    >
                    <div>
                        <div class="text-xs font-bold text-slate-800">Jam Tidur &le; 4 Jam</div>
                        <div class="text-[11px] text-slate-500">Centang jika total jam tidur kurang atau sama dengan 4 jam</div>
                    </div>
                </label>

                <label class="flex items-center gap-2.5 cursor-pointer">
                    <input
                        v-model="form.fatigue_berat"
                        type="checkbox"
                        class="h-4 w-4 rounded border-rose-300 text-rose-600 focus:ring-rose-500"
                    >
                    <div>
                        <div class="text-xs font-bold text-rose-700">Fatigue Berat (Check by Paramedic)</div>
                        <div class="text-[11px] text-slate-500">Wajib paraf Paramedic pada form cetak</div>
                    </div>
                </label>
            </div>

            <!-- Deskripsi Penyebab -->
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Deskripsi (Penyebab Fatigue)
                </label>
                <textarea
                    v-model="form.deskripsi_penyebab"
                    rows="3"
                    placeholder="Jelaskan alasan/penyebab fatigue secara rinci..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                />
            </div>

            <!-- Alamat & Penanggung Jawab -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Alamat Tempat Tinggal (Pengantaran)
                    </label>
                    <textarea
                        v-model="form.alamat_pengantaran"
                        rows="2"
                        placeholder="Alamat lengkap tujuan pemulangan..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Penanggungjawab Pengantaran
                    </label>
                    <input
                        v-model="form.penanggungjawab_pengantaran"
                        type="text"
                        placeholder="Nama Driver / Petugas Pengantar"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                </div>
            </div>

            <!-- Signatures -->
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

            <!-- Submit Button & Back -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                <Link
                    href="/one-power/pembinaan/pemulangan-karyawan"
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
