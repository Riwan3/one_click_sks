<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { CalendarDays } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    items: { type: Array, required: true },
});

const quartalInput = ref(props.filters.quartal);
const yearInput = ref(props.filters.tahun);

const submitFilter = () => {
    router.get('/one-power/quartal-operator-report', { quartal: quartalInput.value, tahun: yearInput.value }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <Head title="Quartal Operator Report - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800"><CalendarDays class="h-5 w-5 text-blue-500" /> Quartal Operator Report</h1>
        <p class="mb-5 text-sm text-slate-500">Rata-rata performa operator per kuartal (dari data MOR bulanan).</p>

        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <select v-model.number="quartalInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option :value="1">Kuartal 1 (Jan-Mar)</option>
                <option :value="2">Kuartal 2 (Apr-Jun)</option>
                <option :value="3">Kuartal 3 (Jul-Sep)</option>
                <option :value="4">Kuartal 4 (Okt-Des)</option>
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
                        <th class="px-4 py-3 font-semibold">NRP / Nama</th>
                        <th class="px-4 py-3 font-semibold">Rata-rata ATR</th>
                        <th class="px-4 py-3 font-semibold">Rata-rata Timesheet</th>
                        <th class="px-4 py-3 font-semibold">Rata-rata HM Ratio</th>
                        <th class="px-4 py-3 font-semibold text-blue-600">Rata-rata Total Poin</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="items.length === 0"><td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada data untuk kuartal ini.</td></tr>
                    <tr v-for="row in items" :key="row.employeeId" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                        <td class="px-4 py-3 align-middle">
                            <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                            <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                        </td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.avgAtr }}%</td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.avgTimesheet }}%</td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.avgHmRatio }}%</td>
                        <td class="px-4 py-3 align-middle"><span class="text-base font-bold text-blue-600">{{ row.avgTotalPoin }}</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
