<script setup>
import { computed } from 'vue';
import { Doughnut, Line } from 'vue-chartjs';
import { PieChart, BarChart3, TrendingUp } from 'lucide-vue-next';

const props = defineProps({
  summary: { type: Object, required: true },
  dailyCompletions: { type: Array, default: () => [] },
});

const doughnutOptions = {
  maintainAspectRatio: false,
  cutout: '72%',
  plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, usePointStyle: true } } },
};

const lineOptions = {
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } }, grid: { color: '#f0f0f0' } },
    x: { grid: { display: false }, ticks: { font: { size: 9 } } },
  },
};

const completionData = computed(() => ({
  labels: ['Selesai', 'Pending'],
  datasets: [{ data: [props.summary.completed, props.summary.pending], backgroundColor: ['#22c55e', '#e2e8f0'], borderWidth: 0 }],
}));

const passFailData = computed(() => ({
  labels: ['Lulus', 'Tidak Lulus'],
  datasets: [{ data: [props.summary.passed, props.summary.failed], backgroundColor: ['#14b8a6', '#f43f5e'], borderWidth: 0 }],
}));

const trendData = computed(() => ({
  labels: props.dailyCompletions.map((d) => d.tgl),
  datasets: [{
    label: 'Penyelesaian',
    data: props.dailyCompletions.map((d) => d.cnt),
    borderColor: '#b91c1c',
    backgroundColor: 'rgba(185,28,28,.08)',
    borderWidth: 2,
    tension: 0.3,
    fill: true,
    pointBackgroundColor: '#b91c1c',
    pointRadius: 4,
  }],
}));

const charts = computed(() => [
  { key: 'completion', title: 'Completion Rate', icon: PieChart, component: Doughnut, data: completionData.value, options: doughnutOptions },
  { key: 'pass-fail', title: 'Pass vs Fail', icon: BarChart3, component: Doughnut, data: passFailData.value, options: doughnutOptions },
  { key: 'trend', title: 'Trend Penyelesaian (30 Hari)', icon: TrendingUp, component: Line, data: trendData.value, options: lineOptions },
]);
</script>

<template>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
    <div v-for="chart in charts" :key="chart.key" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
      <h6 class="mb-3 flex items-center gap-2 text-xs font-bold text-slate-800">
        <component :is="chart.icon" class="h-4 w-4 text-rose-600" />
        {{ chart.title }}
      </h6>
      <div class="h-[200px]">
        <component :is="chart.component" :data="chart.data" :options="chart.options" />
      </div>
    </div>
  </div>
</template>
