<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Search, Table2, TriangleAlert } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import { MONTH_OPTIONS, shiftMonth, yearOptions } from '@/utils/moncu';
import { useMoncuFilters } from '@/composables/useMoncuFilters';
import RosterBreadcrumb from '../Partials/RosterBreadcrumb.vue';
import MoncuFilterBar from '../Moncu/Partials/MoncuFilterBar.vue';
import CalendarGrid from './Partials/CalendarGrid.vue';
import CalendarDayModal from './Partials/CalendarDayModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: { type: Object, required: true },
  options: { type: Object, required: true },
  today: { type: String, required: true },
  range: { type: Object, required: true },
  cells: { type: Object, required: true },
  summary: { type: Object, required: true },
  warnings: { type: Array, default: () => [] },
  overlapNrp: { type: Array, default: () => [] },
  state: { type: String, required: true },
  hasParamError: { type: Boolean, default: false },
});

const BASE_URL = '/one-roster/moncu-calendar';
const SUMMARY_CARDS = [
  ['hari_ct', 'Tanggal dengan CT'], ['ct_employee_day', 'Employee-day CT'], ['id_employee_day', 'Employee-day ID'],
  ['ct_unik', 'Employee unik cuti'], ['id_unik', 'Employee unik induksi'], ['plan', 'Total PLAN'],
];
const EMPTY_MESSAGE = {
  empty: 'Belum ada cuti atau induksi pada periode ini.',
  plan_only: 'Belum ada cuti aktual. Terdapat PLAN.',
};

const { filters: filterForm, apply, reset } = useMoncuFilters(BASE_URL, props.filters);

const currentYear = new Date().getFullYear();
const toOptions = (values) => values.map((value) => ({ value, label: value }));
const yearChoices = computed(() => {
  const years = yearOptions(currentYear + 3, currentYear - 5);
  return years.includes(props.filters.year) ? years : [props.filters.year, ...years];
});
const filterFields = computed(() => [
  { key: 'year', label: 'Tahun', type: 'select', allLabel: null, options: toOptions(yearChoices.value) },
  { key: 'month', label: 'Bulan', type: 'select', allLabel: null, options: MONTH_OPTIONS },
  { key: 'group', label: 'Group', type: 'select', options: toOptions(props.options.groups) },
  { key: 'position', label: 'Jabatan', type: 'select', options: toOptions(props.options.positions), span: 'md:col-span-2' },
  { key: 'search', label: 'Cari NRP / Nama', type: 'text', span: 'col-span-2 md:col-span-1' },
]);

const monthTitle = computed(() => MONTH_OPTIONS[props.filters.month - 1].label + ' ' + props.filters.year);
const hasFilter = computed(() => `${props.filters.group}${props.filters.position}${props.filters.search}` !== '');
const emptyMessage = computed(() => (props.state === 'no_employees' ? (hasFilter.value ? 'Tidak ada data sesuai filter.' : 'Belum ada data MONCU.') : EMPTY_MESSAGE[props.state]));

const selectedDate = ref(null);

const url = (overrides = {}) => {
  const query = { ...props.filters, ...overrides };
  return `${BASE_URL}?${new URLSearchParams(Object.entries(query).filter(([, v]) => v !== '' && v != null))}`;
};
const prevUrl = computed(() => url(shiftMonth(props.filters.year, props.filters.month, -1)));
const nextUrl = computed(() => url(shiftMonth(props.filters.year, props.filters.month, 1)));
const thisMonthUrl = computed(() => url({ year: '', month: '' }));
const dayFilters = computed(() => ({ group: props.filters.group, position: props.filters.position, search: props.filters.search }));
</script>

<template>
  <Head title="MONCU Calendar - One-Roster" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-4">
      <RosterBreadcrumb :trail="[{ label: 'MONCU Calendar' }]" />

      <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
          <h1 class="text-xl font-bold tracking-tight text-slate-800">MONCU Calendar</h1>
          <p class="text-xs text-slate-500">Kalender cuti dari tanggal aktual MONCU — hanya baca</p>
        </div>
        <Link href="/one-roster/moncu" class="flex items-center gap-1.5 self-start rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
          <Table2 class="h-4 w-4" /> MONCU (input cuti)
        </Link>
      </div>

      <p v-if="hasParamError" class="rounded-xl bg-amber-50 px-4 py-2 text-xs text-amber-800">Tahun/bulan pada URL tidak valid; ditampilkan bulan berjalan.</p>

      <div class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <Link :href="prevUrl" class="flex items-center gap-1 rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-600 hover:bg-slate-50" title="Bulan sebelumnya"><ChevronLeft class="h-3.5 w-3.5" /> Sebelumnya</Link>
            <strong class="px-2 text-sm text-slate-800">{{ monthTitle }}</strong>
            <Link :href="nextUrl" class="flex items-center gap-1 rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs text-slate-600 hover:bg-slate-50" title="Bulan berikutnya">Berikutnya <ChevronRight class="h-3.5 w-3.5" /></Link>
          </div>
          <Link :href="thisMonthUrl" class="rounded-lg border border-blue-300 px-2.5 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50">Bulan ini</Link>
        </div>
        <MoncuFilterBar :fields="filterFields" :filters="filterForm" @apply="apply" @reset="reset" />
      </div>

      <p v-if="warnings.length" class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-800">
        <TriangleAlert class="mt-0.5 h-4 w-4 shrink-0" />
        <span>{{ warnings[0] }} <span class="text-slate-500">(NRP: {{ overlapNrp.join(', ') }}) Data tidak diubah.</span></span>
      </p>

      <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <p class="mb-2 text-sm font-semibold text-slate-700">{{ monthTitle }}</p>
        <div class="grid grid-cols-2 gap-2 md:grid-cols-3 xl:grid-cols-6">
          <div v-for="[key, label] in SUMMARY_CARDS" :key="key" class="rounded-xl border border-slate-200 p-2 text-center">
            <p class="text-xl font-bold leading-tight text-slate-800">{{ summary[key] }}</p>
            <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ label }}</p>
          </div>
        </div>
      </div>

      <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
        <span><span class="mr-1 rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-semibold text-white">CT</span>= Cuti</span>
        <span><span class="mr-1 rounded bg-amber-400 px-1.5 py-0.5 text-[10px] font-semibold text-slate-900">ID</span>= Induksi</span>
        <span><span class="mr-1 rounded border border-slate-300 bg-slate-50 px-1.5 py-0.5 text-[10px] font-semibold">PLAN</span>= Rencana Cuti (informasi, bukan cuti aktual)</span>
        <span class="text-slate-400">Klik tanggal untuk melihat daftar karyawan.</span>
      </div>

      <p v-if="emptyMessage" class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs text-slate-600">
        <Search v-if="state === 'no_employees'" class="h-4 w-4" /> {{ emptyMessage }}
      </p>

      <CalendarGrid :range="range" :cells="cells" :today="today" @select-date="selectedDate = $event" />
    </div>

    <CalendarDayModal :date="selectedDate" :filters="dayFilters" @close="selectedDate = null" />
  </AppLayout>
</template>
