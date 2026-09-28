<script setup>
import { reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ClipboardList, CheckCircle2, Hourglass, XCircle, Percent, Trophy, AlertTriangle, ListTodo } from 'lucide-vue-next';
import ComplianceBar from './Partials/Dashboard/ComplianceBar.vue';
import ComplianceCharts from './Partials/Dashboard/ComplianceCharts.vue';
import ComplianceRankingTable from './Partials/Dashboard/ComplianceRankingTable.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: { type: Object, required: true },
  summary: { type: Object, required: true },
  dailyTrend: { type: Array, default: () => [] },
  perArea: { type: Array, default: () => [] },
  perPengawas: { type: Array, default: () => [] },
  topPengawas: { type: Array, default: () => [] },
  lowestAreas: { type: Array, default: () => [] },
  outstanding: { type: Array, default: () => [] },
  areaOptions: { type: Array, default: () => [] },
  shiftOptions: { type: Array, default: () => [] },
});

const form = reactive({ ...props.filters });

const fieldClass = 'rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';

const kpiCards = computed(() => [
  { label: 'Total Assignment', value: props.summary.total, icon: ClipboardList, bg: 'bg-blue-50', text: 'text-blue-500' },
  { label: 'Compliance Selesai', value: props.summary.selesai, icon: CheckCircle2, bg: 'bg-green-50', text: 'text-emerald-500' },
  { label: 'Compliance Progress', value: props.summary.progress, icon: Hourglass, bg: 'bg-yellow-50', text: 'text-amber-500' },
  { label: 'Belum Dilaksanakan', value: props.summary.belum, icon: XCircle, bg: 'bg-rose-50', text: 'text-red-500' },
]);

const summaryTitle = computed(() => {
  const date = new Date(`${props.filters.tanggal}T00:00:00`).toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });
  return `Ringkasan Hari Ini — ${date}${props.filters.shift ? ` — ${props.filters.shift}` : ''}`;
});

const applyFilters = () => {
  const filled = Object.fromEntries(Object.entries(form).filter(([, v]) => v !== ''));
  router.get('/one-reporting/dashboard', filled, { preserveScroll: true, replace: true });
};
</script>

<template>
  <Head title="Dashboard Compliance - One Click" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="mx-auto max-w-[1400px] space-y-6">
      <div>
        <nav class="mb-4 text-sm text-slate-500">
          <Link href="/" class="text-blue-500">Home</Link> &gt;
          <Link href="/one-reporting/dashboard" class="text-blue-500">Reporting</Link> &gt;
          <span>Dashboard</span>
        </nav>
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h1 class="text-3xl font-extrabold text-slate-800">Reporting Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Monitor compliance assignment harian per area &amp; pengawas</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <input v-model="form.tanggal" type="date" :class="[fieldClass, 'w-40']" @change="applyFilters" />
            <select v-model="form.shift" :class="[fieldClass, 'w-36']" @change="applyFilters">
              <option value="">Semua Shift</option>
              <option v-for="s in shiftOptions" :key="s" :value="s">{{ s }}</option>
            </select>
            <select v-model="form.area" :class="[fieldClass, 'w-44']" @change="applyFilters">
              <option value="">Semua Area</option>
              <option v-for="a in areaOptions" :key="a" :value="a">{{ a }}</option>
            </select>
          </div>
        </div>
      </div>

      <section>
        <div class="mb-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ summaryTitle }}</div>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
          <div v-for="card in kpiCards" :key="card.label" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-3">
              <div :class="['flex h-12 w-12 shrink-0 items-center justify-center rounded-xl', card.bg, card.text]"><component :is="card.icon" class="h-6 w-6" /></div>
              <div>
                <div :class="['text-3xl font-extrabold leading-none', card.text]">{{ card.value }}</div>
                <div class="mt-1 text-xs font-semibold text-slate-500">{{ card.label }}</div>
              </div>
            </div>
          </div>
          <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <div class="flex items-center gap-3">
              <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-500"><Percent class="h-6 w-6" /></div>
              <div>
                <div class="text-3xl font-extrabold leading-none text-violet-500">{{ summary.persentase }}%</div>
                <div class="mt-1 text-xs font-semibold text-slate-500">Persentase Compliance</div>
              </div>
            </div>
            <ComplianceBar :percent="summary.persentase" :show-label="false" class="mt-2" />
          </div>
        </div>
      </section>

      <ComplianceCharts :daily-trend="dailyTrend" :per-area="perArea" :per-pengawas="perPengawas" />

      <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <ComplianceRankingTable title="Top Compliance Pengawas" :icon="Trophy" icon-class="text-amber-500" label-header="Nama Pengawas" :rows="topPengawas" show-assignment />
        <ComplianceRankingTable title="Area Compliance Terendah" :icon="AlertTriangle" icon-class="text-red-500" label-header="Area" :rows="lowestAreas" />
      </div>

      <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-2 border-b border-slate-100 px-5 py-3 text-sm font-bold text-slate-800">
          <span class="flex items-center gap-2"><ListTodo class="h-4 w-4 text-red-500" /> Outstanding Activity</span>
          <span v-if="summary.belum > 0" class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-800">{{ summary.belum }} belum dilaksanakan</span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full min-w-[720px] text-left text-[13px] text-slate-700">
            <thead class="border-b-2 border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-500">
              <tr>
                <th class="py-2 pl-4 pr-2">#</th>
                <th class="px-2 py-2">Shift</th>
                <th class="px-2 py-2">Area</th>
                <th class="px-2 py-2">Unit</th>
                <th class="px-2 py-2">Pengawas</th>
                <th class="px-2 py-2">Aktivitas Belum Dilaksanakan</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-if="outstanding.length === 0">
                <td colspan="6" class="py-10 text-center">
                  <CheckCircle2 class="mx-auto h-8 w-8 text-emerald-500" />
                  <div class="mt-2 text-sm text-slate-500">Semua activity sudah dilaksanakan</div>
                </td>
              </tr>
              <tr v-for="(row, idx) in outstanding" :key="row.id" class="hover:bg-slate-50">
                <td class="py-2.5 pl-4 pr-2 text-slate-400">{{ idx + 1 }}</td>
                <td class="px-2 py-2.5"><span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ row.shift }}</span></td>
                <td class="px-2 py-2.5"><span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ row.area }}</span></td>
                <td class="px-2 py-2.5 text-slate-500">{{ row.unit }}</td>
                <td class="px-2 py-2.5 font-semibold">{{ row.pengawas }}</td>
                <td class="px-2 py-2.5">{{ row.aktivitas }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
