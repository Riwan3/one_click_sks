<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import DataLabelsPlugin from 'chartjs-plugin-datalabels';
import { BarChart3, BarChartBig } from 'lucide-vue-next';
import { formatGmpScore } from '@/utils/gmpScore';

const props = defineProps({
  pareto: { type: Array, default: () => [] },
  typeLabel: { type: String, required: true },
  week: { type: String, default: '' },
});

const values = computed(() => props.pareto.map((p) => p.value));

/** Akumulasi = running sum / total × 100 (sama seperti chart v1). */
const cumulative = computed(() => {
  const total = values.value.reduce((a, b) => a + b, 0);
  let running = 0;
  return values.value.map((v) => {
    running += v;
    return total > 0 ? Number(((running / total) * 100).toFixed(2)) : 0;
  });
});

const chartData = computed(() => ({
  labels: props.pareto.map((p) => p.label),
  datasets: [
    { type: 'bar', label: 'Performa (%)', data: values.value, backgroundColor: '#4472C4', borderRadius: 0, yAxisID: 'y', order: 2 },
    { type: 'line', label: 'Akumulasi', data: cumulative.value, borderColor: '#C0504D', borderWidth: 2, pointBackgroundColor: '#C0504D', yAxisID: 'y2', tension: 0, fill: false, order: 1 },
  ],
}));

const options = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  animation: false,
  scales: {
    y: { beginAtZero: true, max: 100, ticks: { font: { size: 10 }, stepSize: 20 }, grid: { color: '#f0f0f0' } },
    y2: { position: 'right', beginAtZero: true, max: 105, ticks: { font: { size: 9 } }, grid: { display: false } },
    x: { ticks: { font: { size: 9 }, maxRotation: 45, minRotation: 45, autoSkip: false }, grid: { display: false } },
  },
  plugins: {
    title: { display: true, text: `GMP ${props.typeLabel} - ${props.week}`, font: { size: 13, weight: 'bold' } },
    legend: { display: false },
    tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${c.raw}` } },
    datalabels: {
      display: (ctx) => ctx.datasetIndex === 0 && ctx.dataset.data[ctx.dataIndex] > 10,
      color: '#fff',
      font: { weight: 'bold', size: 10 },
      anchor: 'center',
      align: 'center',
      rotation: -90,
      formatter: (value) => formatGmpScore(value),
    },
  },
}));
</script>

<template>
  <div class="h-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <h6 class="mb-3 flex items-center gap-1 text-xs font-bold uppercase text-slate-800">
      <BarChart3 class="h-4 w-4 text-green-600" />Skor per Aspek GMP — {{ typeLabel }}
    </h6>
    <div v-if="pareto.length === 0" class="py-12 text-center text-slate-400">
      <BarChartBig class="mx-auto mb-1 h-10 w-10 opacity-25" />Belum ada data GMP.
    </div>
    <div v-else class="h-[320px]"><Bar :data="chartData" :options="options" :plugins="[DataLabelsPlugin]" /></div>
  </div>
</template>
