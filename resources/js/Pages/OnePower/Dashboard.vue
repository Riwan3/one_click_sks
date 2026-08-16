<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Line, Bar } from 'vue-chartjs';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import DashboardTabs from './Partials/DashboardTabs.vue';
import MonthYearFilter from './Partials/MonthYearFilter.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
    dailyTrend: { type: Array, required: true },
    yearlyTrend: { type: Array, required: true },
    topOperators: { type: Array, required: true },
});

const CHART_COLOR = '#006699';

const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { color: '#f0f0f0' }, ticks: { font: { size: 10 } } },
        y: { grid: { color: '#f0f0f0' }, ticks: { font: { size: 10 } } },
    },
};

const trendChartData = computed(() => ({
    labels: props.dailyTrend.map((point) => point.label),
    datasets: [{
        data: props.dailyTrend.map((point) => point.value),
        borderColor: CHART_COLOR,
        backgroundColor: 'rgba(0,102,153,0.05)',
        borderWidth: 2,
        tension: 0.3,
        fill: true,
        pointRadius: 4,
    }],
}));

const yearlyChartData = computed(() => ({
    labels: props.yearlyTrend.map((point) => point.label),
    datasets: [{
        data: props.yearlyTrend.map((point) => point.value),
        backgroundColor: CHART_COLOR,
        borderRadius: 5,
        barThickness: 20,
    }],
}));

const topOperatorsChartData = computed(() => ({
    labels: props.topOperators.map((point) => point.label),
    datasets: [{
        data: props.topOperators.map((point) => point.value),
        backgroundColor: CHART_COLOR,
        borderRadius: 4,
        barThickness: 20,
    }],
}));

const topOperatorsOptions = {
    ...baseOptions,
    indexAxis: 'y',
};

const activeTab = ref('hm');

const comingSoonLabel = computed(() => (activeTab.value === 'kehadiran' ? 'Kehadiran' : 'Deviasi'));
</script>

<template>
    <Head title="Manpower Dashboard - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <nav class="mb-2 text-sm text-slate-500">
            <Link href="/portal" class="text-blue-500 no-underline hover:underline">Home</Link>
            <span class="mx-1.5">&gt;</span>
            <Link href="/one-power" class="text-blue-500 no-underline hover:underline">Manpower</Link>
            <span class="mx-1.5">&gt;</span>
            <span>Dashboard</span>
        </nav>

        <div class="mb-5">
            <h1 class="mb-1 text-2xl font-extrabold text-slate-800">Manpower Dashboard</h1>
            <p class="m-0 text-sm text-slate-500">Ringkasan data tenaga kerja dan kinerja</p>
        </div>

        <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <DashboardTabs :active-tab="activeTab" @tab-selected="activeTab = $event" />
            <MonthYearFilter :filters="filters" />
        </div>

        <template v-if="activeTab === 'hm'">
            <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <StatCard label="Total HM (Bulan Ini)" :value="stats.totalHm.toLocaleString('id-ID')" color="primary" />
                <StatCard label="Rata-rata HM" :value="stats.avgHm.toLocaleString('id-ID')" color="warning" />
                <StatCard label="Operator Aktif" :value="stats.activeOperators.toLocaleString('id-ID')" color="success" />
                <StatCard label="Total Data" :value="stats.totalRecords.toLocaleString('id-ID')" color="danger" />
            </div>

            <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h6 class="mb-3 text-sm font-bold text-slate-700">Tren Hour Meter Harian</h6>
                    <div class="h-[250px]">
                        <Line :data="trendChartData" :options="baseOptions" />
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h6 class="mb-3 text-sm font-bold text-slate-700">Tren HM Bulanan (Tahun {{ filters.tahun }})</h6>
                    <div class="h-[250px]">
                        <Bar :data="yearlyChartData" :options="baseOptions" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h6 class="mb-3 text-sm font-bold text-slate-700">10 Operator dengan HM Tertinggi</h6>
                <div class="h-[300px]">
                    <Bar :data="topOperatorsChartData" :options="topOperatorsOptions" />
                </div>
            </div>
        </template>

        <div v-else class="rounded-xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800">
            {{ comingSoonLabel }} belum tersedia — masih dalam tahap pengembangan.
        </div>
    </AppLayout>
</template>
