<script setup>
import { computed } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import { BarChart3, Map, UserSquare } from 'lucide-vue-next';

const props = defineProps({
  dailyTrend: { type: Array, default: () => [] },
  perArea: { type: Array, default: () => [] },
  perPengawas: { type: Array, default: () => [] },
});

const SELESAI_COLOR = 'rgba(16,185,129,0.8)';
const PENGAWAS_COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316', '#ec4899', '#6366f1'];
const legend = { position: 'bottom', labels: { font: { size: 11 } } };
const countAxis = { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f1f5f9' } };

const selesaiVsTotal = (rows, labelKey, totalAlpha, radius) => ({
  labels: rows.map((r) => r[labelKey]),
  datasets: [
    { label: 'Selesai', data: rows.map((r) => r.selesai), backgroundColor: SELESAI_COLOR, borderRadius: radius },
    { label: 'Total', data: rows.map((r) => r.total), backgroundColor: `rgba(59,130,246,${totalAlpha})`, borderRadius: radius },
  ],
});

const charts = computed(() => [
  {
    key: 'harian',
    title: 'Compliance Harian (7 Hari Terakhir)',
    icon: BarChart3,
    iconClass: 'text-blue-500',
    span: 'xl:col-span-5',
    component: Bar,
    data: selesaiVsTotal(props.dailyTrend, 'tgl', 0.25, 6),
    options: { maintainAspectRatio: false, plugins: { legend }, scales: { y: countAxis, x: { grid: { display: false } } } },
  },
  {
    key: 'area',
    title: 'Compliance Per Area',
    icon: Map,
    iconClass: 'text-emerald-500',
    span: 'md:col-span-1 xl:col-span-4',
    component: Bar,
    data: selesaiVsTotal(props.perArea, 'label', 0.2, 4),
    options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend }, scales: { x: countAxis, y: { grid: { display: false } } } },
  },
  {
    key: 'pengawas',
    title: 'Compliance Per Pengawas',
    icon: UserSquare,
    iconClass: 'text-violet-500',
    span: 'md:col-span-1 xl:col-span-3',
    component: Doughnut,
    data: {
      labels: props.perPengawas.map((r) => r.label),
      datasets: [{ data: props.perPengawas.map((r) => r.selesai), backgroundColor: PENGAWAS_COLORS, borderWidth: 2, borderColor: '#fff' }],
    },
    options: { maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 12, padding: 8 } } } },
  },
]);
</script>

<template>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12">
    <div v-for="chart in charts" :key="chart.key" :class="['rounded-2xl border border-slate-200 bg-white shadow-sm md:col-span-2', chart.span]">
      <div class="flex items-center gap-2 border-b border-slate-100 px-5 py-3 text-sm font-bold text-slate-800">
        <component :is="chart.icon" :class="['h-4 w-4', chart.iconClass]" /> {{ chart.title }}
      </div>
      <div class="h-[260px] p-3">
        <component :is="chart.component" :data="chart.data" :options="chart.options" />
      </div>
    </div>
  </div>
</template>
