<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';

const props = defineProps({
    pareto: { type: Array, required: true },
    weekLabel: { type: String, required: true },
});

const chartData = computed(() => ({
    labels: props.pareto.map((item) => item.label),
    datasets: [
        {
            type: 'bar',
            label: 'Performa (%)',
            data: props.pareto.map((item) => item.value),
            backgroundColor: '#4472C4',
            yAxisID: 'y',
            order: 2,
        },
        {
            type: 'line',
            label: 'Kumulatif (%)',
            data: props.pareto.map((item) => item.cumulative),
            borderColor: '#C0504D',
            backgroundColor: '#C0504D',
            tension: 0,
            fill: false,
            yAxisID: 'y2',
            order: 1,
        },
    ],
}));

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 45 } },
        y: { min: 0, max: 100, ticks: { stepSize: 20, font: { size: 10 } }, grid: { color: '#f0f0f0' } },
        y2: { min: 0, max: 105, position: 'right', grid: { display: false }, ticks: { display: false } },
    },
};
</script>

<template>
    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <h6 class="mb-3 text-sm font-bold text-slate-700">Skor per Aspek GMP &mdash; {{ weekLabel }}</h6>
        <div class="h-[320px]">
            <Bar :data="chartData" :options="options" />
        </div>
    </div>
</template>
