<script setup>
import { ref, watch } from 'vue';
import { Gauge } from '@lucide/vue';

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
        const response = await fetch(`/one-power/personal-data/hm/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showResults.value = true;
    }, 300);
});

const pickEmployee = (employee) => {
    props.form.employee_id = employee.id;
    props.form.dept = employee.unit ?? '';
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
                    <Gauge class="h-4 w-4" /> {{ row ? 'Edit' : 'Tambah' }} Data HM
                </h5>
                <button type="button" class="text-2xl leading-none text-white/80 hover:text-white" @click="emit('close')">&times;</button>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Tanggal</label>
                        <input v-model="form.tanggal" type="date" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                        <p v-if="form.errors.tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Shift</label>
                        <select v-model="form.shift" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                            <option value="DS">DS (Day Shift)</option>
                            <option value="NS">NS (Night Shift)</option>
                        </select>
                        <p v-if="form.errors.shift" class="mt-1 text-xs text-red-600">{{ form.errors.shift }}</p>
                    </div>

                    <div class="relative sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Karyawan</label>
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
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Unit</label>
                        <input v-model="form.unit" type="text" required placeholder="mis. DT123" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                        <p v-if="form.errors.unit" class="mt-1 text-xs text-red-600">{{ form.errors.unit }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Dept</label>
                        <input v-model="form.dept" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">HM Start</label>
                        <input v-model.number="form.hm_start" type="number" step="0.1" min="0" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                        <p v-if="form.errors.hm_start" class="mt-1 text-xs text-red-600">{{ form.errors.hm_start }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">HM Stop</label>
                        <input v-model.number="form.hm_stop" type="number" step="0.1" min="0" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                        <p v-if="form.errors.hm_stop" class="mt-1 text-xs text-red-600">{{ form.errors.hm_stop }}</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Activity</label>
                        <input v-model="form.activity" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Parking</label>
                        <input v-model="form.parking" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="sticky bottom-0 flex justify-end gap-2 border-t border-slate-100 bg-white px-6 py-4">
                    <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="emit('close')">Batal</button>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60">
                        {{ form.processing ? 'Menyimpan...' : (row ? 'Update Data HM' : 'Simpan Data HM') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
