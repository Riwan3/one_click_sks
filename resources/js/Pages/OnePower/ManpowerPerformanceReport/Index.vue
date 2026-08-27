<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { BarChart2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    items: { type: Array, required: true },
});

const monthInput = ref(props.filters.bulan);
const yearInput = ref(props.filters.tahun);

const submitFilter = () => {
    router.get('/one-power/personal-data/mpr', { bulan: monthInput.value, tahun: yearInput.value }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <Head title="Manpower Performance Report - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800"><BarChart2 class="h-5 w-5 text-blue-500" /> Manpower Performance Report</h1>
        <p class="mb-5 text-sm text-slate-500">Rekap performa operator berdasarkan data MOR bulanan.</p>

        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <select v-model.number="monthInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option v-for="(name, num) in filters.monthNames" :key="num" :value="Number(num)">{{ name }}</option>
            </select>
            <select v-model.number="yearInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option v-for="y in filters.years" :key="y" :value="y">{{ y }}</option>
            </select>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[700px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Ranking</th>
                        <th class="px-4 py-3 font-semibold">NRP / Nama</th>
                        <th class="px-4 py-3 font-semibold">ATR</th>
                        <th class="px-4 py-3 font-semibold">Timesheet</th>
                        <th class="px-4 py-3 font-semibold">HM Ratio</th>
                        <th class="px-4 py-3 font-semibold text-blue-600">Total Poin</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="items.length === 0"><td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada data untuk periode ini.</td></tr>
                    <tr v-for="row in items" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                        <td class="px-4 py-3 align-middle"><span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">{{ row.ranking ?? '-' }}</span></td>
                        <td class="px-4 py-3 align-middle">
                            <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                            <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                        </td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.atrPersen.toFixed(1) }}%</td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.timesheetPersen.toFixed(1) }}%</td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.hmRatio.toFixed(1) }}%</td>
                        <td class="px-4 py-3 align-middle"><span class="text-base font-bold text-blue-600">{{ row.totalPoin.toFixed(1) }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
