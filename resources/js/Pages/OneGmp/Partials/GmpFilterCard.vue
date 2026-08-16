<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    filters: { type: Object, required: true },
});

const gmpType = ref(props.filters.gmpType);
const week = ref(props.filters.week);
const tahun = ref(props.filters.tahun);

const submit = () => {
    router.get('/one-gmp', {
        gmp_type: gmpType.value,
        week: week.value,
        tahun: tahun.value,
    }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <select v-model="gmpType" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 outline-none focus:border-emerald-500" @change="submit">
                <option v-for="(label, value) in filters.gmpTypeOptions" :key="value" :value="value">{{ label }}</option>
            </select>

            <select v-model="week" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 outline-none focus:border-emerald-500" @change="submit">
                <option v-for="value in filters.weekOptions" :key="value" :value="value">Week {{ value }}</option>
            </select>

            <select v-model="tahun" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-700 outline-none focus:border-emerald-500" @change="submit">
                <option v-for="year in filters.years" :key="year" :value="year">{{ year }}</option>
            </select>
        </div>
    </div>
</template>
