<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search, ListFilter, RotateCcw } from '@lucide/vue';

const props = defineProps({
    filters: { type: Object, required: true },
});

const search = ref(props.filters.search);
const tahapan = ref(props.filters.tahapan || 'Semua Tahapan');

const applyFilter = () => {
    router.get('/one-academy', {
        search: search.value,
        tahapan: tahapan.value,
    }, { preserveScroll: true, preserveState: true });
};

const resetFilter = () => {
    search.value = '';
    tahapan.value = 'Semua Tahapan';
    router.get('/one-academy', {}, { preserveScroll: true });
};
</script>

<template>
    <form class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm lg:flex-row lg:items-center" @submit.prevent="applyFilter">
        <div class="relative flex-1">
            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input
                v-model="search"
                type="text"
                placeholder="Cari type unit, program, atau pengaju..."
                class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
            >
        </div>

        <select v-model="tahapan" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            <option v-for="option in filters.tahapanOptions" :key="option" :value="option">{{ option }}</option>
        </select>

        <div class="flex gap-2">
            <button type="submit" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">
                <ListFilter class="h-4 w-4" /> Filter
            </button>
            <button type="button" class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-500 hover:bg-slate-50" @click="resetFilter">
                <RotateCcw class="h-4 w-4" /> Reset
            </button>
        </div>
    </form>
</template>
