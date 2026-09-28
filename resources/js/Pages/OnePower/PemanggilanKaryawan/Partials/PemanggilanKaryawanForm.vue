<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search, Coffee, RefreshCw, FileText, Upload } from 'lucide-vue-next';
import SignaturePad from '@/Components/ui/SignaturePad.vue';
import PhotoUploadField from '@/Components/ui/PhotoUploadField.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
    isEdit: { type: Boolean, default: false },
    documentSettings: { type: Object, default: () => ({}) },
});

// Employee Search
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
            const res = await fetch(`/one-power/pembinaan/pemanggilan-karyawan/search-employee?keyword=${encodeURIComponent(keyword)}`);
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
    showEmployeeResults.value = false;
    employeeQuery.value = '';
};

// TTD Pemanggilan Search
const ttdResults = ref([]);
const showTtdResults = ref(false);
let ttdTimer = null;

const onTtdInput = (keyword) => {
    clearTimeout(ttdTimer);
    if (! keyword || keyword.length < 2) {
        ttdResults.value = [];
        return;
    }
    ttdTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/pembinaan/pemanggilan-karyawan/search-employee?keyword=${encodeURIComponent(keyword)}`);
            ttdResults.value = await res.json();
            showTtdResults.value = true;
        } catch (e) {
            console.error(e);
        }
    }, 250);
};

const pickTtd = (emp) => {
    props.form.ttd_pemanggilan = emp.name;
    showTtdResults.value = false;
};

// Manpower Search
const manpowerResults = ref([]);
const showManpowerResults = ref(false);
let manpowerTimer = null;

const onManpowerInput = (keyword) => {
    clearTimeout(manpowerTimer);
    if (! keyword || keyword.length < 2) {
        manpowerResults.value = [];
        return;
    }
    manpowerTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/pembinaan/pemanggilan-karyawan/search-employee?keyword=${encodeURIComponent(keyword)}`);
            manpowerResults.value = await res.json();
            showManpowerResults.value = true;
        } catch (e) {
            console.error(e);
        }
    }, 250);
};

const pickManpower = (emp) => {
    props.form.nama_manpower = `${emp.name} - ${emp.jabatan_ss6 || 'Manpower'}`;
    showManpowerResults.value = false;
};

// Fatigue Checkbox
const isFatigueChecked = ref(!!props.form.fatigue_periode);
const onFatigueToggle = () => {
    if (isFatigueChecked.value) {
        props.form.fatigue_periode = 'bulan';
    } else {
        props.form.fatigue_periode = null;
    }
};

// Regenerate No Surat
const fetchNextNoSurat = async () => {
    try {
        const res = await fetch(`/one-power/pembinaan/pemanggilan-karyawan/next-number?tanggal=${props.form.tanggal_pemanggilan || ''}`);
        const data = await res.json();
        if (data.nomor_surat) {
            props.form.no_surat = data.nomor_surat;
        }
    } catch (e) {
        console.error(e);
    }
};

const handleFileChange = (field, event) => {
    const file = event.target.files[0];
    if (file) {
        props.form[field] = file;
    }
};
</script>

<template>
    <div class="max-w-4xl rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <!-- Form Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-red-700 to-red-900 text-white flex items-center justify-between">
            <h2 class="text-base font-bold flex items-center gap-2">
                <FileText class="h-5 w-5" />
                {{ isEdit ? 'Edit Form Pemanggilan Karyawan' : 'Form Pemanggilan Karyawan' }}
            </h2>
            <div class="text-xs text-red-100 font-medium">
                {{ documentSettings.noDokumen || 'PPA-ADRO-F-OPR-01' }}
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
                            <span class="text-xs text-slate-500 block">{{ emp.jabatan_ss6 || '-' }}</span>
                        </button>
                    </div>
                </div>
                <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>
            </div>

            <!-- Identitas Karyawan (Nama, NRP, Jabatan) -->
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
                        Jabatan
                    </label>
                    <input
                        v-model="form.jabatan"
                        type="text"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm uppercase outline-none focus:border-blue-500"
                    >
                    <p v-if="form.errors.jabatan" class="mt-1 text-xs text-red-600">{{ form.errors.jabatan }}</p>
                </div>
            </div>

            <!-- No. Surat -->
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Nomor Surat Pemanggilan
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

            <!-- Alasan Pemanggilan -->
            <div>
                <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Alasan Pemanggilan <span class="text-red-500">*</span>
                </label>
                <textarea
                    v-model="form.alasan_pemanggilan"
                    rows="3"
                    placeholder="Tuliskan alasan pemanggilan secara jelas..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    required
                />
                <p v-if="form.errors.alasan_pemanggilan" class="mt-1 text-xs text-red-600">{{ form.errors.alasan_pemanggilan }}</p>
            </div>

            <!-- Fatigue Flag & Periode -->
            <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-3.5">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        v-model="isFatigueChecked"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                        @change="onFatigueToggle"
                    >
                    <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                        <Coffee class="h-4 w-4 text-amber-600" />
                        Dipanggil karena sering fatigue (berulang)
                    </span>
                </label>

                <div v-if="isFatigueChecked" class="mt-3 pl-6">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Periode Fatigue
                    </label>
                    <select
                        v-model="form.fatigue_periode"
                        class="w-full sm:w-72 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm outline-none focus:border-blue-500"
                    >
                        <option value="bulan">Sering fatigue dalam Sebulan</option>
                        <option value="tahun">Sering fatigue dalam Setahun</option>
                    </select>
                </div>
            </div>

            <!-- Tempat, Jam, Tanggal -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tempat
                    </label>
                    <input
                        v-model="form.tempat"
                        type="text"
                        placeholder="Contoh: CSA Inggris"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                    <p v-if="form.errors.tempat" class="mt-1 text-xs text-red-600">{{ form.errors.tempat }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Jam Pemanggilan
                    </label>
                    <input
                        v-model="form.jam_pemanggilan"
                        type="time"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                    <p v-if="form.errors.jam_pemanggilan" class="mt-1 text-xs text-red-600">{{ form.errors.jam_pemanggilan }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tanggal Pemanggilan <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.tanggal_pemanggilan"
                        type="date"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        required
                    >
                    <p v-if="form.errors.tanggal_pemanggilan" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal_pemanggilan }}</p>
                </div>
            </div>

            <!-- TTD Pemanggilan & Manpower -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="relative">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        TTD Pemanggilan (Nama Penanda Tangan)
                    </label>
                    <input
                        v-model="form.ttd_pemanggilan"
                        type="text"
                        placeholder="Ketik nama penanda tangan..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        autocomplete="off"
                        @input="onTtdInput($event.target.value)"
                        @focus="showTtdResults = true"
                    >
                    <div
                        v-if="showTtdResults && ttdResults.length"
                        class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl"
                    >
                        <button
                            v-for="emp in ttdResults"
                            :key="emp.id"
                            type="button"
                            class="block w-full border-b border-slate-50 px-3 py-2 text-left text-sm hover:bg-slate-50"
                            @click="pickTtd(emp)"
                        >
                            <span class="font-semibold text-slate-800">{{ emp.name }}</span>
                            <span class="text-xs text-slate-400 block">{{ emp.jabatan_ss6 }}</span>
                        </button>
                    </div>
                </div>

                <div class="relative">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Manpower (Jabatan / Dept)
                    </label>
                    <input
                        v-model="form.nama_manpower"
                        type="text"
                        placeholder="Contoh: CHAIRUL HADI - GROUP LEADER MANPOWER"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        autocomplete="off"
                        @input="onManpowerInput($event.target.value)"
                        @focus="showManpowerResults = true"
                    >
                    <div
                        v-if="showManpowerResults && manpowerResults.length"
                        class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl"
                    >
                        <button
                            v-for="emp in manpowerResults"
                            :key="emp.id"
                            type="button"
                            class="block w-full border-b border-slate-50 px-3 py-2 text-left text-sm hover:bg-slate-50"
                            @click="pickManpower(emp)"
                        >
                            <span class="font-semibold text-slate-800">{{ emp.name }}</span>
                            <span class="text-xs text-slate-400 block">{{ emp.jabatan_ss6 }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Upload Foto Dokumen Pemanggilan -->
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <PhotoUploadField
                    v-model="form.foto"
                    :existing-photo="form.existing_foto"
                    :hapus-foto="form.hapus_foto"
                    :error="form.errors?.foto"
                    label="Foto Bukti Dokumen Pemanggilan Karyawan"
                    @update:hapus-foto="form.hapus_foto = $event"
                />
            </div>

            <!-- Upload Eviden -->
            <div class="grid gap-4 sm:grid-cols-2 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Eviden 1 (File Upload)
                    </label>
                    <input
                        type="file"
                        accept="image/*,.pdf"
                        class="w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                        @change="handleFileChange('eviden_1', $event)"
                    >
                    <p class="mt-1 text-[11px] text-slate-400">JPG, PNG, PDF (Maks. 5MB)</p>
                    <div v-if="form.eviden1" class="mt-1.5">
                        <a :href="form.eviden1" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 hover:underline">
                            <FileText class="h-3.5 w-3.5" /> Lihat Eviden 1 Lama
                        </a>
                    </div>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Eviden 2 (File Upload)
                    </label>
                    <input
                        type="file"
                        accept="image/*,.pdf"
                        class="w-full text-xs text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                        @change="handleFileChange('eviden_2', $event)"
                    >
                    <p class="mt-1 text-[11px] text-slate-400">JPG, PNG, PDF (Maks. 5MB)</p>
                    <div v-if="form.eviden2" class="mt-1.5">
                        <a :href="form.eviden2" target="_blank" class="inline-flex items-center gap-1 text-xs font-bold text-sky-600 hover:underline">
                            <FileText class="h-3.5 w-3.5" /> Lihat Eviden 2 Lama
                        </a>
                    </div>
                </div>
            </div>

            <!-- Signature Pads -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <SignaturePad
                        v-model="form.ttd_pemanggil"
                        label="Tanda Tangan Pembuat Panggilan"
                    />
                </div>
                <div>
                    <SignaturePad
                        v-model="form.ttd_karyawan"
                        label="Tanda Tangan Karyawan (yang dipanggil)"
                    />
                </div>
            </div>

            <!-- Submit Button & Back -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                <Link
                    href="/one-power/pembinaan/pemanggilan-karyawan"
                    class="rounded-xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors"
                >
                    Kembali
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex items-center gap-1.5 rounded-xl bg-red-600 px-6 py-2 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition-colors disabled:opacity-50"
                >
                    {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Simpan Data') }}
                </button>
            </div>
        </form>
    </div>
</template>
