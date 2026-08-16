<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

const props = defineProps({
    lokasi: { type: Array, required: true },
});

const colorFor = (value) => {
    if (value >= 80) return '#16a34a';
    if (value >= 60) return '#f59e0b';
    return '#dc2626';
};

const chartData = computed(() => ({
    labels: props.lokasi.map((item) => item.label),
    datasets: [{
        data: props.lokasi.map((item) => item.value),
        backgroundColor: props.lokasi.map((item) => colorFor(item.value)),
        borderRadius: 6,
    }],
}));

const options = {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { min: 0, max: 100, grid: { color: '#f0f0f0' }, ticks: { font: { size: 10 } } },
        y: { ticks: { font: { size: 10 } } },
    },
};
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <h6 class="mb-3 text-sm font-bold text-slate-700">Skor per Lokasi</h6>
        <div class="h-[280px]">
            <Bar :data="chartData" :options="options" />
        </div>
    </div>
</template>
