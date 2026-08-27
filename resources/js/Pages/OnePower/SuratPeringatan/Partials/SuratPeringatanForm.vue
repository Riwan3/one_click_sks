<script setup>
import { ref, watch } from 'vue';
import SignaturePad from '@/Components/ui/SignaturePad.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
});

const employeeResults = ref([]);
const showEmployeeResults = ref(false);
const employeeQuery = ref(props.form.nama ?? '');
let employeeSearchTimer = null;

const pengawasResults = ref([]);
const showPengawasResults = ref(false);
const pengawasQuery = ref(props.form.pengawasName ?? '');
let pengawasSearchTimer = null;

const activeSp = ref(null);

watch(employeeQuery, (keyword) => {
    clearTimeout(employeeSearchTimer);
    if (! keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    employeeSearchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/surat-peringatan/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showEmployeeResults.value = true;
    }, 300);
});

watch(pengawasQuery, (keyword) => {
    clearTimeout(pengawasSearchTimer);
    if (! keyword || keyword.length < 2) {
        pengawasResults.value = [];
        return;
    }
    pengawasSearchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/surat-peringatan/search-employee?keyword=${encodeURIComponent(keyword)}`);
        pengawasResults.value = await response.json();
        showPengawasResults.value = true;
    }, 300);
});

const checkActiveSp = async () => {
    if (! props.form.employee_id || ! props.form.dikeluarkan_tanggal) {
        activeSp.value = null;
        return;
    }
    const params = new URLSearchParams({ employee_id: props.form.employee_id, tanggal: props.form.dikeluarkan_tanggal });
    const response = await fetch(`/one-power/pembinaan/surat-peringatan/check-active?${params}`);
    const data = await response.json();
    activeSp.value = data && data.id !== props.form.id ? data : null;
};

const pickEmployee = (employee) => {
    props.form.employee_id = employee.id;
    employeeQuery.value = employee.name;
    showEmployeeResults.value = false;
    checkActiveSp();
};

const pickPengawas = (employee) => {
    props.form.pengawas_id = employee.id;
    pengawasQuery.value = employee.name;
    showPengawasResults.value = false;
};

watch(() => props.form.dikeluarkan_tanggal, checkActiveSp);

const jenisOptions = ['Peringatan Pertama', 'Peringatan Kedua', 'Peringatan Terakhir'];

const pelanggaranOptions = [
    { key: 'pelanggaran_absen_terlambat', label: 'Absen / terlambat' },
    { key: 'pelanggaran_perilaku', label: 'Perilaku tidak sesuai' },
    { key: 'pelanggaran_ceroboh_lalai', label: 'Ceroboh / lalai' },
    { key: 'pelanggaran_hasil_kerja', label: 'Hasil kerja tidak sesuai standar' },
    { key: 'pelanggaran_menolak_perintah', label: 'Menolak perintah atasan' },
    { key: 'pelanggaran_peraturan', label: 'Pelanggaran peraturan perusahaan' },
];
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <div v-if="activeSp" class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Karyawan ini masih memiliki <strong>{{ activeSp.jenisPembinaan }}</strong> yang aktif (No. {{ activeSp.nomorSp }}). Tingkat SP baru harus lebih tinggi.
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Karyawan <span class="text-red-500">*</span></label>
                <input
                    v-model="employeeQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari NRP atau nama karyawan..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showEmployeeResults = true"
                >
                <div v-if="showEmployeeResults && employeeResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
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
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jenis Pembinaan <span class="text-red-500">*</span></label>
                <select v-model="form.jenis_pembinaan" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500" @change="checkActiveSp">
                    <option v-for="opt in jenisOptions" :key="opt" :value="opt">{{ opt }}</option>
                </select>
                <p v-if="form.errors.jenis_pembinaan" class="mt-1 text-xs text-red-600">{{ form.errors.jenis_pembinaan }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Kode Pelanggaran</label>
                <input v-model="form.kode_pelanggaran" type="text" placeholder="Contoh: A.1.1" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Kejadian <span class="text-red-500">*</span></label>
                <input v-model="form.tanggal_kejadian" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tanggal_kejadian" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal_kejadian }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jam Kejadian</label>
                <input v-model="form.jam_kejadian" type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tempat Kejadian</label>
                <input v-model="form.tempat_kejadian" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Jenis Pelanggaran</label>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label v-for="opt in pelanggaranOptions" :key="opt.key" class="flex items-center gap-2 text-sm text-slate-600">
                        <input v-model="form[opt.key]" type="checkbox" class="h-4 w-4 rounded border-slate-300">
                        {{ opt.label }}
                    </label>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Detail Pelanggaran</label>
                <textarea v-model="form.detail_pelanggaran" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <div class="relative">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Pengawas</label>
                <input
                    v-model="pengawasQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari nama pengawas..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showPengawasResults = true"
                >
                <div v-if="showPengawasResults && pengawasResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button
                        v-for="employee in pengawasResults"
                        :key="employee.id"
                        type="button"
                        class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="pickPengawas(employee)"
                    >
                        <span class="font-semibold text-slate-700">{{ employee.nrp }}</span>
                        <span class="text-slate-500"> &ndash; {{ employee.name }}</span>
                    </button>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Koreksi Alpa</label>
                <input v-model="form.koreksi_alpa" type="text" placeholder="Jika ada..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Peringatan Sebelumnya</label>
                <select v-model="form.peringatan_sebelumnya_status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option value="Tidak ada">Tidak ada</option>
                    <option value="Ada">Ada</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Masa Berlaku Sanksi (bulan)</label>
                <input v-model.number="form.sanksi_masa_berlaku" type="number" min="1" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div v-if="form.peringatan_sebelumnya_status === 'Ada'" class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jenis Peringatan Sebelumnya</label>
                <input v-model="form.peringatan_sebelumnya_jenis" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div v-if="form.peringatan_sebelumnya_status === 'Ada'" class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Perihal Peringatan Sebelumnya</label>
                <textarea v-model="form.peringatan_sebelumnya_mengenai" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Dikeluarkan Di</label>
                <input v-model="form.dikeluarkan_di" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Dikeluarkan <span class="text-red-500">*</span></label>
                <input v-model="form.dikeluarkan_tanggal" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500" @change="checkActiveSp">
                <p v-if="form.errors.dikeluarkan_tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.dikeluarkan_tanggal }}</p>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Catatan Karyawan</label>
                <textarea v-model="form.catatan_karyawan" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <SignaturePad v-model="form.ttd_karyawan" label="TTD Karyawan" />
            <SignaturePad v-model="form.ttd_atasan" label="TTD Atasan" />
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <a href="/one-power/pembinaan/surat-peringatan" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
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
