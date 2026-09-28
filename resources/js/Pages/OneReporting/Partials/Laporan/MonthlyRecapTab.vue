<script setup>
import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Line, Bar, Doughnut } from 'vue-chartjs';
import { Search, BarChart3, UserSquare, Map as MapIcon } from 'lucide-vue-next';

const props = defineProps({
  filters: { type: Object, required: true },
  recap: { type: Object, required: true },
});

const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const AREA_COLORS = ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316'];
const currentYear = new Date().getFullYear();
const YEARS = [0, 1, 2, 3].map((offset) => currentYear - offset);

const form = reactive({ bulan: props.filters.bulan, tahun: props.filters.tahun });

const periodLabel = computed(() => `${MONTHS[props.filters.bulan - 1]} ${props.filters.tahun}`);
const percentAxis = { min: 0, max: 100, ticks: { callback: (v) => `${v}%` }, grid: { color: '#f1f5f9' } };

const charts = computed(() => [
  {
    key: 'harian',
    title: 'Compliance Harian',
    icon: BarChart3,
    iconClass: 'text-blue-500',
    span: 'lg:col-span-2',
    height: 'h-[260px]',
    component: Line,
    series: props.recap.harian,
    emptyText: 'Belum ada data untuk bulan ini',
    data: {
      labels: props.recap.harian.map((d) => d.label),
      datasets: [{
        label: 'Compliance %', data: props.recap.harian.map((d) => d.pct), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)',
        borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: '#3b82f6', fill: true, tension: 0.3,
      }],
    },
    options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: percentAxis, x: { grid: { display: false }, title: { display: true, text: 'Hari ke-', font: { size: 11 } } } } },
  },
  {
    key: 'pengawas',
    title: 'Compliance Per Pengawas',
    icon: UserSquare,
    iconClass: 'text-violet-500',
    span: '',
    height: 'h-[320px]',
    component: Bar,
    series: props.recap.pengawas,
    emptyText: 'Belum ada data',
    data: {
      labels: props.recap.pengawas.map((d) => d.label),
      datasets: [{ label: 'Compliance %', data: props.recap.pengawas.map((d) => d.pct), backgroundColor: 'rgba(139,92,246,0.75)', borderRadius: 6 }],
    },
    options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: percentAxis, y: { grid: { display: false } } } },
  },
  {
    key: 'area',
    title: 'Compliance Per Area',
    icon: MapIcon,
    iconClass: 'text-emerald-500',
    span: '',
    height: 'h-[320px]',
    component: Doughnut,
    series: props.recap.area,
    emptyText: 'Belum ada data',
    data: {
      labels: props.recap.area.map((d) => d.label),
      datasets: [{ data: props.recap.area.map((d) => d.pct), backgroundColor: AREA_COLORS, borderWidth: 2, borderColor: '#fff' }],
    },
    options: {
      maintainAspectRatio: false,
      cutout: '60%',
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12, padding: 10 } },
        tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${ctx.raw}%` } },
      },
    },
  },
]);

const handleShow = () => router.get('/one-reporting/laporan', { tab: 'rekap_bulanan', ...form }, { preserveScroll: true });
</script>

<template>
  <div class="space-y-3">
    <form class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3.5 print:hidden" @submit.prevent="handleShow">
      <div>
        <label class="mb-1 block text-[11px] font-bold text-slate-500">BULAN</label>
        <select v-model.number="form.bulan" class="w-40 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm">
          <option v-for="(name, idx) in MONTHS" :key="name" :value="idx + 1">{{ name }}</option>
        </select>
      </div>
      <div>
        <label class="mb-1 block text-[11px] font-bold text-slate-500">TAHUN</label>
        <select v-model.number="form.tahun" class="w-28 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm">
          <option v-for="y in YEARS" :key="y" :value="y">{{ y }}</option>
        </select>
      </div>
      <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700"><Search class="h-3.5 w-3.5" />Tampilkan</button>
    </form>

    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
      <div v-for="chart in charts" :key="chart.key" :class="['rounded-2xl bg-white shadow-sm', chart.span]">
        <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-3 text-sm font-bold text-slate-800">
          <component :is="chart.icon" :class="['h-4 w-4', chart.iconClass]" />{{ chart.title }} — {{ periodLabel }}
        </div>
        <div class="p-3">
          <div v-if="chart.series.length === 0" class="py-6 text-center text-sm text-slate-500">{{ chart.emptyText }}</div>
          <div v-else :class="chart.height"><component :is="chart.component" :data="chart.data" :options="chart.options" /></div>
        </div>
      </div>
    </div>
  </div>
</template>
