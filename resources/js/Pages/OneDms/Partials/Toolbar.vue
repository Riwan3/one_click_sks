<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Calendar, Plus, Printer } from '@lucide/vue';

const props = defineProps({
    filters: { type: Object, required: true },
});

const emit = defineEmits(['add']);

const tanggal = ref(props.filters.tanggal);
const shift = ref(props.filters.shift);

const applyFilter = () => {
    router.get('/one-dms/formulir', { tanggal: tanggal.value, shift: shift.value }, { preserveScroll: true, preserveState: true });
};

const handlePrint = () => {
    const params = new URLSearchParams({ tanggal: tanggal.value, shift: shift.value });
    window.open(`/one-dms/formulir/print?${params.toString()}`, '_blank');
};
</script>

<template>
    <div class="no-print mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="mb-1 text-xl font-extrabold text-slate-800">Formulir Driving Monitoring System</h1>
            <p class="m-0 text-sm text-slate-500">Data akan ditarik dari DMS</p>
        </div>

        <form class="flex flex-wrap items-center gap-2" @submit.prevent="applyFilter">
            <div class="relative">
                <Calendar class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="tanggal" type="date" class="rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm outline-none focus:border-[#1e5e3f]">
            </div>
            <select v-model="shift" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                <option v-for="(label, value) in filters.shiftOptions" :key="value" :value="value">{{ label }}</option>
            </select>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Tampilkan</button>

            <button type="button" class="flex items-center gap-1.5 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-600" @click="emit('add')">
                <Plus class="h-4 w-4" /> Tambah Data
            </button>
            <button type="button" class="flex items-center gap-1.5 rounded-lg bg-red-500 px-4 py-2 text-sm font-bold text-white hover:bg-red-600" @click="handlePrint">
                <Printer class="h-4 w-4" /> Cetak
            </button>
        </form>
    </div>
</template>
