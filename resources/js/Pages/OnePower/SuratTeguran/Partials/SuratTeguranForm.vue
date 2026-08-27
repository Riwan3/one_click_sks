<script setup>
import { ref, watch } from 'vue';
import SignaturePad from '@/Components/ui/SignaturePad.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
});

const employeeResults = ref([]);
const showResults = ref(false);
const employeeQuery = ref(props.form.nama);
let searchTimer = null;

watch(employeeQuery, (keyword) => {
    clearTimeout(searchTimer);
    if (! keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/surat-teguran/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showResults.value = true;
    }, 300);
});

const pickEmployee = (employee) => {
    props.form.employee_id = employee.id;
    props.form.nrp = employee.nrp;
    props.form.nama = employee.name;
    props.form.jabatan = employee.jabatan_ss6 ?? '';
    props.form.departemen = employee.unit ?? '';
    employeeQuery.value = employee.name;
    showResults.value = false;
};

const jenisOptions = [
    { key: 'jenis_absen', label: 'Absen tanpa keterangan' },
    { key: 'jenis_perilaku', label: 'Perilaku tidak sesuai' },
    { key: 'jenis_ceroboh', label: 'Ceroboh dalam bekerja' },
    { key: 'jenis_hasil_kerja', label: 'Hasil kerja tidak sesuai standar' },
    { key: 'jenis_menolak_perintah', label: 'Menolak perintah atasan' },
    { key: 'jenis_pelanggaran_peraturan', label: 'Pelanggaran peraturan perusahaan' },
];
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Karyawan <span class="text-red-500">*</span></label>
                <input
                    v-model="employeeQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari NRP atau nama karyawan..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showResults = true"
                >
                <div v-if="showResults && employeeResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button
                        v-for="employee in employeeResults"
                        :key="employee.id"
                        type="button"
                        class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="pickEmployee(employee)"
                    >
                        <span class="font-semibold text-slate-700">{{ employee.nrp }}</span>
                        <span class="text-slate-500"> &ndash; {{ employee.name }}</span>
                    </button>
                </div>
                <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jabatan</label>
                <input v-model="form.jabatan" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Departemen</label>
                <input v-model="form.departemen" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Kejadian <span class="text-red-500">*</span></label>
                <input v-model="form.tanggal_kejadian" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tanggal_kejadian" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal_kejadian }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tempat Kejadian</label>
                <input v-model="form.tempat_kejadian" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jam Kejadian</label>
                <input v-model="form.kejadian_jam" type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Lokasi Kejadian</label>
                <input v-model="form.kejadian_lokasi" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Jenis Pelanggaran</label>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label v-for="opt in jenisOptions" :key="opt.key" class="flex items-center gap-2 text-sm text-slate-600">
                        <input v-model="form[opt.key]" type="checkbox" class="h-4 w-4 rounded border-slate-300">
                        {{ opt.label }}
                    </label>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Penjelasan Singkat</label>
                <textarea v-model="form.penjelasan_singkat" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Dikeluarkan Di</label>
                <input v-model="form.dikeluarkan_di" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Dikeluarkan <span class="text-red-500">*</span></label>
                <input v-model="form.dikeluarkan_tanggal" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.dikeluarkan_tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.dikeluarkan_tanggal }}</p>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Catatan</label>
                <textarea v-model="form.catatan" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <SignaturePad v-model="form.ttd_karyawan" label="TTD Karyawan" />
            <SignaturePad v-model="form.ttd_atasan" label="TTD Atasan" />
            <SignaturePad v-model="form.ttd_atasan_atasan" label="TTD Atasan dari Atasan" />
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <a href="/one-power/pembinaan/surat-teguran" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60"
            >
                {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
            </button>
        </div>
    </form>
</template>
