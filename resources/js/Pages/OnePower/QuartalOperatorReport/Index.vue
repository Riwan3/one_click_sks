<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Trophy, CloudDownload, RefreshCw, Medal } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import QorDprImportModal from './Partials/QorDprImportModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    isAdmin: { type: Boolean, required: true },
    filters: { type: Object, required: true },
    ranking: { type: Array, required: true },
});

const quartalInput = ref(props.filters.quartal);
const yearInput = ref(props.filters.tahun);

const submitFilter = () => {
    router.get('/one-power/quartal-operator-report', { quartal: quartalInput.value, tahun: yearInput.value }, { preserveScroll: true, preserveState: true });
};

const importModalOpen = ref(false);
const importForm = useForm({ file: null });

const submitImport = () => {
    importForm.post('/one-power/quartal-operator-report/import', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            importModalOpen.value = false;
            importForm.reset();
        },
    });
};

const recalculateForm = useForm({});

const handleRecalculate = () => {
    if (! confirm(`Hitung ulang ranking BCM untuk Kuartal ${quartalInput.value} Tahun ${yearInput.value}?`)) return;

    recalculateForm.transform((data) => ({ ...data, quartal: quartalInput.value, tahun: yearInput.value }))
        .post('/one-power/quartal-operator-report/recalculate', { preserveScroll: true });
};

const rankBadgeClass = (rank) => ({
    1: 'bg-amber-400 text-amber-950',
    2: 'bg-slate-300 text-slate-800',
    3: 'bg-orange-300 text-orange-950',
}[rank] ?? 'bg-slate-100 text-slate-500');

const rowHighlightClass = (rank) => (rank <= 3 ? 'bg-amber-50/60' : '');
</script>

<template>
    <Head title="Quartal Operator Report - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800"><Trophy class="h-5 w-5 text-amber-500" /> Quartal Operator Report</h1>
                <p class="m-0 text-sm text-slate-500">Ranking produksi BCM (bank cubic meter) operator per kuartal, dari data DPR (Daily Production Report).</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-700" @click="importModalOpen = true">
                    <CloudDownload class="h-4 w-4" /> Import DPR
                </button>
                <button
                    v-if="isAdmin"
                    type="button"
                    :disabled="recalculateForm.processing"
                    class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60"
                    @click="handleRecalculate"
                >
                    <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': recalculateForm.processing }" /> Recalculate
                </button>
            </div>
        </div>

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
            <table class="w-full min-w-[800px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Rank</th>
                        <th class="px-4 py-3 font-semibold">NRP / Nama</th>
                        <th class="px-4 py-3 font-semibold">Unit / Kode Unit</th>
                        <th class="px-4 py-3 font-semibold">Loader</th>
                        <th class="px-4 py-3 font-semibold text-right">Total Rit</th>
                        <th class="px-4 py-3 font-semibold text-right text-amber-600">Total BCM</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="ranking.length === 0">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                            Belum ada ranking untuk kuartal ini. Import data DPR lalu klik Recalculate.
                        </td>
                    </tr>
                    <tr v-for="row in ranking" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60" :class="rowHighlightClass(row.ranking)">
                        <td class="px-4 py-3 align-middle">
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-extrabold" :class="rankBadgeClass(row.ranking)">
                                <Medal v-if="row.ranking <= 3" class="h-3.5 w-3.5" />
                                <template v-else>{{ row.ranking }}</template>
                            </span>
                        </td>
                        <td class="px-4 py-3 align-middle">
                            <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                            <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                        </td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">
                            <div>{{ row.unit ?? '-' }}</div>
                            <div class="text-slate-400">{{ row.kodeUnit ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.loader ?? '-' }}</td>
                        <td class="px-4 py-3 align-middle text-right text-xs text-slate-600">{{ row.ritTotal.toLocaleString('id-ID', { maximumFractionDigits: 1 }) }}</td>
                        <td class="px-4 py-3 align-middle text-right">
                            <span class="text-base font-bold text-amber-600">{{ row.bcmTotal.toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <QorDprImportModal :open="importModalOpen" :form="importForm" @close="importModalOpen = false" @submit="submitImport" />
    </AppLayout>
</template>
