<script setup>
import { ref, watch } from 'vue';
import { CalendarCheck } from '@lucide/vue';

const props = defineProps({
    row: { type: Object, default: null },
    open: { type: Boolean, default: false },
    form: { type: Object, required: true },
    codeColumns: { type: Array, required: true },
});

const emit = defineEmits(['close', 'submit']);

const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const employeeQuery = ref('');
const employeeResults = ref([]);
const showResults = ref(false);
let searchTimer = null;

watch(() => props.row, (row) => {
    employeeQuery.value = row ? `${row.nrp} - ${row.nama}` : '';
}, { immediate: true });

watch(employeeQuery, (keyword) => {
    clearTimeout(searchTimer);
    if (! keyword || keyword.length < 2) { employeeResults.value = []; return; }
    searchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/personal-data/attendance/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showResults.value = true;
    }, 300);
});

const pickEmployee = (employee) => {
    props.form.employee_id = employee.id;
    employeeQuery.value = `${employee.nrp} - ${employee.name}`;
    showResults.value = false;
};

const handleSubmit = () => emit('submit');
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-blue-500 px-6 py-4 text-white">
                <h5 class="flex items-center gap-2 text-base font-bold"><CalendarCheck class="h-4 w-4" /> {{ row ? 'Edit' : 'Tambah' }} Roster Absensi</h5>
                <button type="button" class="text-2xl leading-none text-white/80 hover:text-white" @click="emit('close')">&times;</button>
            </div>
            <form class="max-h-[80vh] overflow-y-auto" @submit.prevent="handleSubmit">
                <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-4">
                    <div class="relative sm:col-span-4">
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Karyawan</label>
                        <input v-model="employeeQuery" type="text" autocomplete="off" placeholder="Cari NRP atau nama..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500" @focus="showResults = true">
                        <div v-if="showResults && employeeResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                            <button v-for="employee in employeeResults" :key="employee.id" type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50" @click="pickEmployee(employee)">
                                <span class="font-semibold text-slate-700">{{ employee.nrp }}</span>
                                <span class="text-slate-500"> &ndash; {{ employee.name }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Bulan</label>
                        <select v-model.number="form.bulan" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                            <option v-for="(name, idx) in months" :key="idx" :value="idx + 1">{{ name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Tahun</label>
                        <input v-model.number="form.tahun" type="number" min="2000" max="2100" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Dept</label>
                        <input v-model="form.dept" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">ATR %</label>
                        <input v-model.number="form.atr_percentage" type="number" step="0.01" min="0" max="100" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>

                    <div class="sm:col-span-4">
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Kode Kehadiran</label>
                        <div class="grid grid-cols-5 gap-2 sm:grid-cols-8">
                            <div v-for="code in codeColumns" :key="code">
                                <label class="mb-1 block text-center text-[0.65rem] font-bold uppercase text-slate-400">{{ code }}</label>
                                <input v-model.number="form[code]" type="number" min="0" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-center text-sm outline-none focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-100 px-6 py-4">
                    <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="emit('close')">Batal</button>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</button>
                </div>
            </form>
        </div>
    </div>
</template>
