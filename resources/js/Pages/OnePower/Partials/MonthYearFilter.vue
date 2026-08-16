<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    filters: { type: Object, required: true },
});

const submit = (event) => {
    router.get('/one-power', {
        bulan: props.filters.bulan,
        tahun: props.filters.tahun,
        ...(event.target.name === 'bulan' ? { bulan: event.target.value } : {}),
        ...(event.target.name === 'tahun' ? { tahun: event.target.value } : {}),
    }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <select
                name="bulan"
                :value="filters.bulan"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 outline-none focus:border-blue-500"
                @change="submit"
            >
                <option v-for="(name, value) in filters.monthNames" :key="value" :value="value">{{ name }}</option>
            </select>

            <select
                name="tahun"
                :value="filters.tahun"
                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 outline-none focus:border-blue-500"
                @change="submit"
            >
                <option v-for="year in filters.years" :key="year" :value="year">{{ year }}</option>
            </select>
        </div>
    </div>
</template>
