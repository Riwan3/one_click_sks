<script setup>
import { Bar } from 'vue-chartjs';

defineProps({
    lokasiDetail: { type: Array, required: true },
});

const chartDataFor = (location) => ({
    labels: location.aspects.map((aspect) => aspect.label),
    datasets: [{
        data: location.aspects.map((aspect) => aspect.value),
        backgroundColor: '#4472C4',
        borderRadius: 4,
    }],
});

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { display: false },
        y: { min: 0, max: 100, ticks: { display: false }, grid: { display: false } },
    },
};
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <h6 class="mb-3 text-sm font-bold text-slate-700">Detail Skor per Lokasi</h6>
        <div class="flex snap-x gap-3 overflow-x-auto pb-2">
            <div
                v-for="location in lokasiDetail"
                :key="location.label"
                class="w-[160px] shrink-0 snap-start rounded-lg border border-slate-100 bg-slate-50 p-3"
            >
                <div class="mb-2 truncate text-xs font-bold text-slate-600" :title="location.label">{{ location.label }}</div>
                <div class="h-[120px]">
                    <Bar :data="chartDataFor(location)" :options="options" />
                </div>
            </div>
        </div>
    </div>
</template>
