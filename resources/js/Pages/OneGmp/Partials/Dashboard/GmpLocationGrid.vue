<script setup>
import { Bar } from 'vue-chartjs';
import DataLabelsPlugin from 'chartjs-plugin-datalabels';
import { LayoutGrid } from 'lucide-vue-next';

defineProps({
  charts: { type: Array, default: () => [] },
  typeLabel: { type: String, required: true },
});

const chartData = (chart) => ({
  labels: chart.labels,
  datasets: [{ data: chart.data, backgroundColor: chart.colors, borderRadius: 0 }],
});

const options = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    datalabels: {
      display: (ctx) => ctx.dataset.data[ctx.dataIndex] > 10,
      color: '#fff',
      font: { weight: 'bold', size: 9 },
      rotation: -90,
      formatter: (value) => Math.round(value),
    },
  },
  scales: {
    y: { beginAtZero: true, max: 100, ticks: { stepSize: 20, font: { size: 9 } }, grid: { color: '#f0f0f0' } },
    x: { ticks: { maxRotation: 45, minRotation: 45, autoSkip: false, font: { size: 8 } }, grid: { display: false } },
  },
};
</script>

<template>
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <h6 class="mb-3 flex items-center gap-1 text-xs font-bold uppercase text-slate-800">
      <LayoutGrid class="h-4 w-4 text-amber-500" />Skor per Aspek tiap Lokasi — {{ typeLabel }}
    </h6>
    <div v-if="charts.length === 0" class="py-12 text-center text-slate-400">
      <LayoutGrid class="mx-auto mb-1 h-10 w-10 opacity-25" />Belum ada data lokasi.
    </div>
    <!-- Lebar kartu tetap: bila lokasi > 3, geser ke samping (sama seperti v1) -->
    <div v-else class="flex snap-x gap-3 overflow-x-auto pb-2.5">
      <div v-for="chart in charts" :key="chart.title" class="w-[340px] max-w-[85vw] shrink-0 snap-start rounded-2xl border border-slate-200 bg-white p-3">
        <div class="mb-2 text-xs font-bold text-slate-900">{{ chart.title }}</div>
        <div class="h-[230px]"><Bar :data="chartData(chart)" :options="options" :plugins="[DataLabelsPlugin]" /></div>
      </div>
    </div>
  </div>
</template>
