<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FileText, Calendar, BarChart3 } from 'lucide-vue-next';
import ReportTab from './Partials/Laporan/ReportTab.vue';
import DailyRecapTab from './Partials/Laporan/DailyRecapTab.vue';
import MonthlyRecapTab from './Partials/Laporan/MonthlyRecapTab.vue';

defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: { type: Object, required: true },
  reportRows: { type: Array, default: () => [] },
  dailyRecap: { type: Array, default: () => [] },
  monthlyRecap: { type: Object, default: null },
  areaOptions: { type: Array, default: () => [] },
});

const TABS = [
  { key: 'laporan', label: 'Laporan', icon: FileText },
  { key: 'rekap_harian', label: 'Rekap Harian', icon: Calendar },
  { key: 'rekap_bulanan', label: 'Rekap Bulanan', icon: BarChart3 },
];
</script>

<template>
  <Head title="Report Compliance - One Click" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="mx-auto max-w-[1400px] space-y-4">
      <div>
        <nav class="mb-1 text-xs text-slate-500 print:hidden">
          <Link href="/one-reporting/dashboard" class="text-blue-500">Dashboard</Link> / Operation Compliance Report / <span class="text-slate-400">Report Compliance</span>
        </nav>
        <h1 class="text-xl font-bold text-slate-800">Report Compliance</h1>
        <p class="text-[13px] text-slate-500">Laporan, rekap harian &amp; tren bulanan compliance</p>
      </div>

      <nav class="flex flex-wrap gap-1.5 print:hidden">
        <Link
          v-for="tab in TABS"
          :key="tab.key"
          :href="`/one-reporting/laporan?tab=${tab.key}`"
          :class="[
            'inline-flex items-center gap-1 rounded-full border-[1.5px] px-4 py-1.5 text-sm font-semibold transition',
            filters.tab === tab.key ? 'border-blue-500 bg-blue-500 text-white' : 'border-slate-200 text-slate-600 hover:bg-slate-100',
          ]"
        >
          <component :is="tab.icon" class="h-4 w-4" />{{ tab.label }}
        </Link>
      </nav>

      <ReportTab v-if="filters.tab === 'laporan'" :filters="filters" :rows="reportRows" :area-options="areaOptions" />
      <DailyRecapTab v-else-if="filters.tab === 'rekap_harian'" :filters="filters" :rows="dailyRecap" :area-options="areaOptions" />
      <MonthlyRecapTab v-else-if="monthlyRecap" :filters="filters" :recap="monthlyRecap" />
    </div>
  </AppLayout>
</template>
