<script setup>
import { ref, reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ClipboardList, CheckCircle2, Hourglass, XCircle, Search, X } from 'lucide-vue-next';
import { COMPLIANCE_STATUS } from '@/utils/odrCompliance';
import DailyComplianceTable from './Partials/Daily/DailyComplianceTable.vue';
import AssignmentDetailModal from './Partials/Daily/AssignmentDetailModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: { type: Object, required: true },
  kpi: { type: Object, required: true },
  rows: { type: Array, default: () => [] },
  areaOptions: { type: Array, default: () => [] },
  shiftOptions: { type: Array, default: () => [] },
  aktivitasDocs: { type: Object, default: () => ({}) },
  canManage: { type: Boolean, default: false },
});

const BASE_URL = '/one-reporting/daily';

const form = reactive({ tanggal: props.filters.tanggal, shift: props.filters.shift, area: props.filters.area, status: props.filters.status });
const detailAssignment = ref(null);

const assignmentUrl = computed(() => {
  const query = new URLSearchParams({ tanggal: props.filters.tanggal, ...(props.filters.shift ? { shift: props.filters.shift } : {}) });
  return `${BASE_URL}/assignment?${query}`;
});

const kpiCards = computed(() => [
  { label: 'Total Assignment', value: props.kpi.total, icon: ClipboardList, bg: 'bg-blue-50', iconColor: 'text-blue-500', valueColor: 'text-slate-800' },
  { label: 'Complete', value: props.kpi.selesai, icon: CheckCircle2, bg: 'bg-green-50', iconColor: 'text-emerald-500', valueColor: 'text-emerald-500' },
  { label: 'Progress', value: props.kpi.progress, icon: Hourglass, bg: 'bg-yellow-50', iconColor: 'text-amber-500', valueColor: 'text-amber-500' },
  { label: 'Belum Dimulai', value: props.kpi.belum, icon: XCircle, bg: 'bg-rose-50', iconColor: 'text-red-500', valueColor: 'text-red-500' },
]);

const fieldClass = 'rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
const labelClass = 'mb-1 block text-[11px] font-bold text-slate-500';

const handleFilter = () => {
  const filled = Object.fromEntries(Object.entries(form).filter(([, v]) => v !== ''));
  router.get(BASE_URL, filled, { preserveScroll: true });
};
</script>

<template>
  <Head title="Daily Compliance - One Click" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="mx-auto max-w-[1600px] space-y-4">
      <div class="flex flex-wrap items-start justify-between gap-2">
        <div>
          <nav class="mb-1 text-xs text-slate-500">
            <Link href="/one-reporting/dashboard" class="text-blue-500">Dashboard</Link> / Operation Compliance Report / <span class="text-slate-400">Daily Compliance</span>
          </nav>
          <h1 class="text-xl font-bold text-slate-800">Monitoring Daily Compliance</h1>
          <p class="text-[13px] text-slate-500">Checklist task compliance per pengawas</p>
        </div>
        <Link v-if="canManage" :href="assignmentUrl" class="inline-flex items-center gap-1 rounded-lg border border-slate-400 px-3 py-1.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">
          <ClipboardList class="h-4 w-4" />Kelola Assignment
        </Link>
      </div>

      <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
        <div v-for="card in kpiCards" :key="card.label" class="flex items-center gap-3 rounded-xl bg-white px-4 py-3.5 shadow-sm">
          <div :class="['flex h-9 w-9 shrink-0 items-center justify-center rounded-lg', card.bg]"><component :is="card.icon" :class="['h-5 w-5', card.iconColor]" /></div>
          <div>
            <div :class="['text-2xl font-extrabold leading-none', card.valueColor]">{{ card.value }}</div>
            <div class="text-xs font-semibold text-slate-500">{{ card.label }}</div>
          </div>
        </div>
      </div>

      <form class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3" @submit.prevent="handleFilter">
        <div>
          <label :class="labelClass">TANGGAL</label>
          <input v-model="form.tanggal" type="date" :class="[fieldClass, 'w-40']" />
        </div>
        <div>
          <label :class="labelClass">SHIFT</label>
          <select v-model="form.shift" :class="[fieldClass, 'w-32']">
            <option value="">Semua Shift</option>
            <option v-for="s in shiftOptions" :key="s" :value="s">{{ s }}</option>
          </select>
        </div>
        <div>
          <label :class="labelClass">AREA</label>
          <select v-model="form.area" :class="[fieldClass, 'w-40']">
            <option value="">Semua Area</option>
            <option v-for="a in areaOptions" :key="a" :value="a">{{ a }}</option>
          </select>
        </div>
        <div>
          <label :class="labelClass">STATUS</label>
          <select v-model="form.status" :class="[fieldClass, 'w-40']">
            <option value="">Semua Status</option>
            <option v-for="value in ['belum', 'progress', 'selesai']" :key="value" :value="value">{{ COMPLIANCE_STATUS[value].label }}</option>
          </select>
        </div>
        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700"><Search class="h-3.5 w-3.5" />Filter</button>
        <Link :href="BASE_URL" class="inline-flex items-center gap-1 rounded-lg border border-slate-400 px-3 py-1.5 text-sm font-semibold text-slate-600 hover:bg-white"><X class="h-3.5 w-3.5" />Reset</Link>
        <div class="ml-auto flex items-center gap-3 text-xs text-slate-500">
          <span v-for="value in ['belum', 'progress', 'selesai']" :key="value" class="inline-flex items-center gap-1">
            <span :class="['h-2.5 w-2.5 rounded-full', COMPLIANCE_STATUS[value].dot]" />{{ COMPLIANCE_STATUS[value].label }}
          </span>
        </div>
      </form>

      <DailyComplianceTable
        :rows="rows"
        :tanggal="filters.tanggal"
        :assignment-url="assignmentUrl"
        :can-manage="canManage"
        @detail-requested="detailAssignment = $event"
      />
    </div>

    <AssignmentDetailModal v-if="detailAssignment" :assignment="detailAssignment" :aktivitas-docs="aktivitasDocs" @closed="detailAssignment = null" />
  </AppLayout>
</template>
