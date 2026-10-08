<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { CalendarX, Search, Table2 } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import { formatDate, yearOptions } from '@/utils/moncu';
import { useMoncuFilters } from '@/composables/useMoncuFilters';
import RosterBreadcrumb from '../Partials/RosterBreadcrumb.vue';
import MoncuFilterBar from '../Moncu/Partials/MoncuFilterBar.vue';
import MonitoringSummaryCards from './Partials/MonitoringSummaryCards.vue';
import MonitoringAttention from './Partials/MonitoringAttention.vue';
import MonitoringTable from './Partials/MonitoringTable.vue';
import MonitoringDetailModal from './Partials/MonitoringDetailModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: { type: Object, required: true },
  options: { type: Object, required: true },
  today: { type: String, required: true },
  context: { type: Object, required: true },
  summary: { type: Object, required: true },
  attentionApplicable: { type: Boolean, default: true },
  attention: { type: Array, default: () => [] },
  rows: { type: Array, default: () => [] },
  table: { type: Object, required: true },
});

const BASE_URL = '/one-roster/moncu-monitoring';
const CONTEXT_BADGE = {
  current: { label: 'HARI INI', class: 'bg-emerald-600' },
  historical: { label: 'DATA HISTORIS', class: 'bg-slate-800' },
  future: { label: 'PERIODE MENDATANG', class: 'bg-slate-800' },
};

const { filters: filterForm, apply, reset } = useMoncuFilters(BASE_URL, props.filters);

const currentYear = new Date().getFullYear();
const toOptions = (values) => values.map((value) => ({ value, label: value }));
const filterFields = computed(() => [
  { key: 'year', label: 'Tahun', type: 'select', allLabel: null, options: toOptions(yearOptions(currentYear + 1, currentYear - 4)) },
  { key: 'group', label: 'Group', type: 'select', options: toOptions(props.options.groups) },
  { key: 'jabatan', label: 'Jabatan', type: 'select', options: toOptions(props.options.jabatan), span: 'md:col-span-2' },
  { key: 'status', label: 'Status', type: 'select', options: toOptions(props.options.statuses) },
  { key: 'q', label: 'Cari NRP / Nama', type: 'text', span: 'col-span-2 md:col-span-1' },
]);

const isCurrent = computed(() => props.context.mode === 'current');
const badge = computed(() => CONTEXT_BADGE[props.context.mode]);
const hasFilter = computed(() => `${props.filters.group}${props.filters.jabatan}${props.filters.status}${props.filters.q}` !== '');
const hasNoData = computed(() => props.summary.total === 0 && !hasFilter.value);

const detailNrp = ref(null);

const query = (overrides = {}) => ({ ...props.filters, sort: props.table.sort, dir: props.table.dir, ...overrides });
const statusUrl = (status) => `${BASE_URL}?${new URLSearchParams(Object.entries(query({ status })).filter(([, v]) => v !== '' && v != null))}`;

const handleSort = (key) => {
  const nextDir = props.table.sort === key && props.table.dir === 'asc' ? 'desc' : 'asc';
  router.get(BASE_URL, query({ sort: key, dir: nextDir }), { preserveScroll: true });
};
</script>

<template>
  <Head title="MONCU Monitoring - One-Roster" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-4">
      <RosterBreadcrumb :trail="[{ label: 'MONCU Monitoring' }]" />

      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-800">MONCU Monitoring</h1>
          <p class="text-xs text-slate-500">Gambaran operasional cuti — hanya baca · hari ini {{ formatDate(today) }}</p>
        </div>
        <Link href="/one-roster/moncu" class="flex items-center gap-1.5 self-start rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
          <Table2 class="h-4 w-4" /> MONCU (input cuti)
        </Link>
      </div>

      <MoncuFilterBar :fields="filterFields" :filters="filterForm" @apply="apply" @reset="reset" />

      <div v-if="hasNoData" class="flex flex-col items-center gap-2 rounded-2xl border border-slate-200/80 bg-white py-12 text-sm text-slate-400 shadow-sm">
        <CalendarX class="h-8 w-8" />
        <p>Belum ada data MONCU.</p>
      </div>

      <template v-else>
        <div :class="['flex flex-wrap items-center gap-2 rounded-xl border px-4 py-2 text-xs', isCurrent ? 'border-slate-200 bg-white' : 'border-slate-300 bg-slate-100']">
          <span :class="['rounded px-2 py-0.5 text-[10px] font-bold text-white', badge.class]">{{ badge.label }}</span>
          <strong>{{ context.label }}</strong>
          <span v-if="isCurrent" class="text-slate-500">Status dihitung terhadap hari ini ({{ formatDate(context.today) }}).</span>
          <span v-else>Status Periode per {{ formatDate(context.as_of) }} — <strong>bukan kondisi operasional hari ini</strong> (hari ini {{ formatDate(context.today) }}).</span>
        </div>

        <MonitoringSummaryCards :summary="summary" :is-current="isCurrent" :filter-url="statusUrl" />
        <MonitoringAttention :items="attention" :is-applicable="attentionApplicable" @show-detail="detailNrp = $event" />

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
          <div v-if="!rows.length" class="flex flex-col items-center gap-2 py-12 text-sm text-slate-400">
            <Search class="h-8 w-8" />
            <p>Tidak ada data sesuai filter.</p>
          </div>
          <template v-else>
            <MonitoringTable :rows="rows" :sort="table.sort" :dir="table.dir" :status-label="context.status_label" @sort="handleSort" @show-detail="detailNrp = $event" />
            <div class="flex flex-col items-center justify-between gap-2 p-3 sm:flex-row">
              <p class="text-xs text-slate-500">{{ table.total }} operator · halaman {{ table.page }}/{{ table.pages }}</p>
              <Pagination :current-page="table.page" :last-page="table.pages" :base-url="BASE_URL" :query="query()" />
            </div>
          </template>
        </div>
      </template>
    </div>

    <MonitoringDetailModal :nrp="detailNrp" :year="filters.year" @close="detailNrp = null" />
  </AppLayout>
</template>
