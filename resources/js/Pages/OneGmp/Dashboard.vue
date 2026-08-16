<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import GmpFilterCard from './Partials/GmpFilterCard.vue';
import ParetoChart from './Partials/ParetoChart.vue';
import LokasiChart from './Partials/LokasiChart.vue';
import LocationDetailGrid from './Partials/LocationDetailGrid.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    stats: { type: Object, required: true },
    pareto: { type: Array, required: true },
    lokasi: { type: Array, required: true },
    lokasiDetail: { type: Array, required: true },
});

const avgColor = (value) => {
    if (value >= 80) return 'success';
    if (value >= 60) return 'warning';
    return 'danger';
};
</script>

<template>
    <Head title="One-GMP - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <GmpFilterCard :filters="filters" />

        <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <StatCard label="Total Penilaian" :value="stats.totalPenilaian.toLocaleString('id-ID')" color="success" />
            <StatCard label="Rata-rata Skor" :value="stats.avgScore" :color="avgColor(stats.avgScore)" />
            <StatCard label="Skor Tertinggi" :value="stats.maxScore" color="warning" />
            <StatCard label="Skor Terendah" :value="stats.minScore" color="danger" />
        </div>

        <div class="mb-4 grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="xl:col-span-7">
                <ParetoChart :pareto="pareto" :week-label="filters.weekLabel" />
            </div>
            <div class="xl:col-span-5">
                <LokasiChart :lokasi="lokasi" />
            </div>
        </div>

        <LocationDetailGrid :lokasi-detail="lokasiDetail" />
    </AppLayout>
</template>
