<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Chart } from 'chart.js';

const props = defineProps({
  series: { type: Object, required: true }, // { 'Minggu 1 (1-7)': { shift1, shift2, lainnya } }
});

const SHIFT1_COLOR = '#006699';
const SHIFT2_COLOR = '#f59e0b';
const OTHER_COLOR = '#9ca3af';

const canvasRef = ref(null);
let chart = null;

const values = (key) => Object.values(props.series).map((week) => week[key]);

const chartData = computed(() => {
  const datasets = [
    { label: 'Shift 1 (07:00-18:30)', data: values('shift1'), backgroundColor: SHIFT1_COLOR, borderRadius: 4 },
    { label: 'Shift 2 (19:00-06:30)', data: values('shift2'), backgroundColor: SHIFT2_COLOR, borderRadius: 4 },
  ];
  if (values('lainnya').some((count) => count > 0)) {
    datasets.push({ label: 'Di Luar Jam Shift', data: values('lainnya'), backgroundColor: OTHER_COLOR, borderRadius: 4 });
  }

  return { labels: Object.keys(props.series), datasets };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'bottom', labels: { usePointStyle: true, font: { size: 12 } } },
    tooltip: { mode: 'index', intersect: false },
  },
  scales: {
    x: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 } } },
    y: { stacked: true, beginAtZero: true, ticks: { precision: 0, font: { size: 11 } }, grid: { color: '#f0f0f0' } },
  },
};

onMounted(() => {
  chart = new Chart(canvasRef.value, { type: 'bar', data: chartData.value, options: chartOptions });
});

watch(chartData, (data) => {
  if (!chart) return;
  chart.data = data;
  chart.update();
});

onBeforeUnmount(() => chart?.destroy());
</script>

<template>
  <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:p-5">
    <h2 class="mb-3 text-sm font-bold text-slate-800">Diagram Login Per Minggu (Shift 1 vs Shift 2)</h2>
    <div class="relative h-[350px]">
      <canvas ref="canvasRef" />
    </div>
  </div>
</template>
