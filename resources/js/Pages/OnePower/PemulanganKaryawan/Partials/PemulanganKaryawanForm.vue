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
        const response = await fetch(`/one-power/pembinaan/pemulangan-karyawan/search-employee?keyword=${encodeURIComponent(keyword)}`);
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
                <label class="mb-1 block text-sm font-semibold text-slate-700">Lokasi</label>
                <input v-model="form.lokasi" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal <span class="text-red-500">*</span></label>
                <input v-model="form.tanggal" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal }}</p>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Indikasi Fatigue</label>
                <textarea v-model="form.indikasi_fatigue" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input v-model="form.jam_tidur_kurang_4jam" type="checkbox" class="h-4 w-4 rounded border-slate-300">
                <label class="text-sm text-slate-700">Jam tidur kurang dari 4 jam</label>
            </div>
            <div class="flex items-center gap-2">
                <input v-model="form.fatigue_berat" type="checkbox" class="h-4 w-4 rounded border-slate-300">
                <label class="text-sm text-slate-700">Fatigue berat</label>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Deskripsi Penyebab</label>
                <textarea v-model="form.deskripsi_penyebab" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Alamat Pengantaran</label>
                <textarea v-model="form.alamat_pengantaran" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Penanggung Jawab Pengantaran</label>
                <input v-model="form.penanggungjawab_pengantaran" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanda Tangan Karyawan</label>
                <SignaturePad v-model="form.ttd_karyawan" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanda Tangan Pengawas</label>
                <SignaturePad v-model="form.ttd_pengawas" />
            </div>
        </div>

        <button type="submit" :disabled="form.processing" class="mt-6 w-full rounded-lg bg-blue-500 py-2.5 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60 sm:w-auto sm:px-8">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
        </button>
    </form>
</template>
