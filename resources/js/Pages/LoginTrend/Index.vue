<script setup>
import { computed, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, ChartColumn } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import TrendChart from './Partials/TrendChart.vue';
import UniqueUsersTable from './Partials/UniqueUsersTable.vue';
import NeverLoggedInTable from './Partials/NeverLoggedInTable.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  filters: { type: Object, required: true }, // { bulan, tahun, years }
  monthNames: { type: Object, required: true },
  report: { type: Object, required: true }, // { series, totals, uniqueUserCount, uniqueUsers }
  never: { type: Object, required: true },
});

const form = reactive({ bulan: props.filters.bulan, tahun: props.filters.tahun });

const periodLabel = computed(() => `${props.monthNames[props.filters.bulan]} ${props.filters.tahun}`);

const summary = computed(() => [
  { label: 'Total Login', value: props.report.totals.total, dot: '' },
  { label: 'User Unik', value: props.report.uniqueUserCount, dot: '' },
  { label: 'Shift 1', value: props.report.totals.shift1, dot: 'bg-[#006699]' },
  { label: 'Shift 2', value: props.report.totals.shift2, dot: 'bg-[#f59e0b]' },
]);

const handleFilter = () => router.get('/login-trend', { bulan: form.bulan, tahun: form.tahun }, { preserveScroll: true });
</script>

<template>
  <Head title="Trend Login - One Click" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="[]" hide-sidebar back-url="/portal">
    <div class="mx-auto max-w-6xl space-y-5">
      <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div>
          <h1 class="flex items-center gap-2 text-xl font-bold tracking-tight text-slate-800"><ChartColumn class="h-5 w-5 text-emerald-600" /> Trend Login</h1>
          <p class="text-xs text-slate-500">Login semua user per bulan, dikelompokkan per Shift 1 &amp; Shift 2 — {{ periodLabel }}</p>
        </div>
        <form class="flex flex-wrap items-center gap-2" @submit.prevent="handleFilter">
          <Link href="/portal" class="flex items-center gap-1.5 rounded-lg border-2 border-blue-500 px-3 py-1.5 text-xs font-bold text-blue-500 transition hover:bg-blue-50">
            <ArrowLeft class="h-3.5 w-3.5" /> Kembali ke Home
          </Link>
          <select v-model.number="form.bulan" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold" @change="handleFilter">
            <option v-for="(name, number) in monthNames" :key="number" :value="Number(number)">{{ name }}</option>
          </select>
          <select v-model.number="form.tahun" class="rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold" @change="handleFilter">
            <option v-for="year in filters.years" :key="year" :value="year">{{ year }}</option>
          </select>
        </form>
      </div>

      <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
        <div v-for="item in summary" :key="item.label" class="rounded-2xl border border-slate-200/80 bg-white p-3 text-center shadow-sm">
          <p class="mb-1 flex items-center justify-center gap-1.5 text-xs text-slate-500">
            <span v-if="item.dot" :class="['h-2 w-2 rounded-full', item.dot]" /> {{ item.label }}
          </p>
          <p class="text-xl font-black text-slate-800">{{ item.value }}</p>
        </div>
      </div>

      <TrendChart :series="report.series" />
      <UniqueUsersTable :users="report.uniqueUsers" :period-label="periodLabel" />
      <NeverLoggedInTable :never="never" :filters="filters" />
    </div>
  </AppLayout>
</template>
