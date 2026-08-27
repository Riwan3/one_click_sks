<script setup>
import { ref, watch } from 'vue';
import { BarChart } from '@lucide/vue';

const props = defineProps({
    row: { type: Object, default: null },
    open: { type: Boolean, default: false },
    form: { type: Object, required: true },
});

const emit = defineEmits(['close', 'submit']);

const employeeQuery = ref('');
const employeeResults = ref([]);
const showResults = ref(false);
let searchTimer = null;

watch(() => props.row, (row) => {
    employeeQuery.value = row ? `${row.nrp} - ${row.nama}` : '';
}, { immediate: true });

watch(employeeQuery, (keyword) => {
    clearTimeout(searchTimer);
    if (! keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/mor/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showResults.value = true;
    }, 300);
});

const pickEmployee = (employee) => {
    props.form.employee_id = employee.id;
    props.form.posisi = employee.jabatan_ss6 ?? '';
    employeeQuery.value = `${employee.nrp} - ${employee.name}`;
    showResults.value = false;
};

const handleSubmit = () => emit('submit');
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="sticky top-0 z-10 flex items-center justify-between bg-blue-500 px-6 py-4 text-white">
                <h5 class="flex items-center gap-2 text-base font-bold">
                    <BarChart class="h-4 w-4" /> {{ row ? 'Edit' : 'Tambah' }} Data MOR
                </h5>
                <button type="button" class="text-2xl leading-none text-white/80 hover:text-white" @click="emit('close')">&times;</button>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-3">
                    <div class="relative sm:col-span-3">
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
                        <input v-model.number="form.bulan" type="number" min="1" max="12" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Tahun</label>
                        <input v-model.number="form.tahun" type="number" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Posisi</label>
                        <input v-model="form.posisi" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>

                    <div class="sm:col-span-3 mt-2 border-t border-slate-100 pt-3">
                        <h4 class="text-xs font-bold uppercase text-slate-500">ATR</h4>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">ATR %</label>
                        <input v-model.number="form.atr_persen" type="number" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">ATR Point</label>
                        <input v-model.number="form.atr_point" type="number" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Work Day</label>
                        <input v-model.number="form.work_day" type="number" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Sakit</label>
                        <input v-model.number="form.sakit" type="number" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Ijin</label>
                        <input v-model.number="form.ijin" type="number" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Alpa</label>
                        <input v-model.number="form.alpa" type="number" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>

                    <div class="sm:col-span-3 mt-2 border-t border-slate-100 pt-3">
                        <h4 class="text-xs font-bold uppercase text-slate-500">Timesheet & HM</h4>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Timesheet %</label>
                        <input v-model.number="form.timesheet_persen" type="number" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">HM Ratio %</label>
                        <input v-model.number="form.hm_ratio" type="number" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">HM Jam</label>
                        <input v-model.number="form.hm_jam" type="number" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>

                    <div class="sm:col-span-3 mt-2 border-t border-slate-100 pt-3">
                        <h4 class="text-xs font-bold uppercase text-slate-500">Fuel & Pembinaan</h4>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Fuel %</label>
                        <input v-model.number="form.fuel_persen" type="number" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Jumlah Pembinaan</label>
                        <input v-model.number="form.pembinaan" type="number" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Poin Pembinaan</label>
                        <input v-model.number="form.pembinaan_poin" type="number" step="0.01" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="sticky bottom-0 flex justify-end gap-2 border-t border-slate-100 bg-white px-6 py-4">
                    <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="emit('close')">Batal</button>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60">
                        {{ form.processing ? 'Menyimpan...' : (row ? 'Update' : 'Simpan') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
