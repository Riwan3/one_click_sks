<script setup>
import { reactive, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Search, X, FileSpreadsheet, Printer, CheckCircle2, Circle } from 'lucide-vue-next';
import { COMPLIANCE_STATUS, complianceStatusOf, rowPercentColor, formatSlashDate } from '@/utils/odrCompliance';

const props = defineProps({
  filters: { type: Object, required: true },
  rows: { type: Array, default: () => [] },
  areaOptions: { type: Array, default: () => [] },
});

const BASE_URL = '/one-reporting/laporan';

const form = reactive({ tgl_dari: props.filters.tgl_dari, tgl_sampai: props.filters.tgl_sampai, area: props.filters.area, pengawas: props.filters.pengawas, status: props.filters.status });

const taskColumnCount = computed(() => Math.max(1, ...props.rows.map((r) => r.tasks.length)));

const exportUrl = computed(() => `${BASE_URL}/export?${new URLSearchParams(Object.fromEntries(Object.entries(form).filter(([, v]) => v !== '')))}`);

const fieldClass = 'w-full rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
const labelClass = 'mb-1 block text-[11px] font-bold text-slate-500';

const truncate = (text) => (text.length > 18 ? `${text.slice(0, 18)}…` : text);

const printPage = () => window.print();

const handleFilter = () => {
  const filled = Object.fromEntries(Object.entries(form).filter(([, v]) => v !== ''));
  router.get(BASE_URL, { tab: 'laporan', ...filled }, { preserveScroll: true });
};
</script>

<template>
  <div class="space-y-3">
    <form class="grid grid-cols-2 items-end gap-2 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3.5 print:hidden md:grid-cols-6" @submit.prevent="handleFilter">
      <div><label :class="labelClass">DARI TANGGAL</label><input v-model="form.tgl_dari" type="date" :class="fieldClass" /></div>
      <div><label :class="labelClass">SAMPAI TANGGAL</label><input v-model="form.tgl_sampai" type="date" :class="fieldClass" /></div>
      <div>
        <label :class="labelClass">AREA</label>
        <select v-model="form.area" :class="fieldClass">
          <option value="">Semua Area</option>
          <option v-for="a in areaOptions" :key="a" :value="a">{{ a }}</option>
        </select>
      </div>
      <div><label :class="labelClass">PENGAWAS</label><input v-model="form.pengawas" type="text" placeholder="Cari pengawas..." :class="fieldClass" /></div>
      <div>
        <label :class="labelClass">STATUS</label>
        <select v-model="form.status" :class="fieldClass">
          <option value="">Semua Status</option>
          <option v-for="value in ['belum', 'progress', 'selesai']" :key="value" :value="value">{{ COMPLIANCE_STATUS[value].label }}</option>
        </select>
      </div>
      <div class="col-span-2 flex gap-2 md:col-span-1">
        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700"><Search class="h-3.5 w-3.5" />Filter</button>
        <Link :href="`${BASE_URL}?tab=laporan`" title="Reset" class="inline-flex items-center rounded-lg border border-slate-400 px-2.5 py-1.5 text-slate-600 hover:bg-white"><X class="h-4 w-4" /></Link>
      </div>
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-md print:shadow-none">
      <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50 px-4 py-2 print:hidden">
        <span class="text-[13px] font-bold text-slate-600">
          {{ formatSlashDate(filters.tgl_dari) }} – {{ formatSlashDate(filters.tgl_sampai) }} · <span class="text-red-600">{{ rows.length }}</span> data
        </span>
        <div class="flex gap-2">
          <a :href="exportUrl" class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-green-700"><FileSpreadsheet class="h-4 w-4" />Export Excel</a>
          <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-slate-400 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100" @click="printPage"><Printer class="h-4 w-4" />PDF / Print</button>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-[13px] text-slate-700">
          <thead class="whitespace-nowrap border-b-2 border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="py-2 pl-4 pr-2">#</th>
              <th class="px-2 py-2">Tanggal</th>
              <th class="px-2 py-2">Area</th>
              <th class="px-2 py-2">Unit</th>
              <th class="px-2 py-2">Pengawas</th>
              <th v-for="ti in taskColumnCount" :key="ti" class="min-w-[90px] px-2 py-2 text-center">Task {{ ti }}</th>
              <th class="min-w-[100px] px-2 py-2 text-center">Compliance %</th>
              <th class="py-2 pl-2 pr-4 text-center">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="rows.length === 0">
              <td :colspan="7 + taskColumnCount" class="py-10 text-center text-sm text-slate-500">Tidak ada data untuk filter yang dipilih</td>
            </tr>
            <tr v-for="(row, idx) in rows" :key="row.id" class="hover:bg-slate-50">
              <td class="py-2.5 pl-4 pr-2 text-slate-400">{{ idx + 1 }}</td>
              <td class="whitespace-nowrap px-2 py-2.5">{{ formatSlashDate(row.tanggal) }}</td>
              <td class="px-2 py-2.5"><span class="rounded bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700">{{ row.area }}</span></td>
              <td class="px-2 py-2.5 text-slate-500">{{ row.unit }}</td>
              <td class="px-2 py-2.5 font-semibold">{{ row.pengawas }}</td>
              <td v-for="ti in taskColumnCount" :key="ti" class="px-2 py-2.5 text-center">
                <div v-if="row.tasks[ti - 1] !== undefined" class="flex flex-col items-center gap-1" :title="row.tasks[ti - 1]">
                  <component :is="row.done[ti - 1] ? CheckCircle2 : Circle" :class="['h-[18px] w-[18px]', row.done[ti - 1] ? 'text-emerald-500' : 'text-slate-300']" />
                  <span class="max-w-[80px] text-[10px] leading-tight text-slate-400">{{ truncate(row.tasks[ti - 1]) }}</span>
                </div>
                <span v-else class="text-slate-400">—</span>
              </td>
              <td class="px-2 py-2.5 text-center">
                <div class="text-sm font-bold" :style="{ color: rowPercentColor(row.report_pct) }">{{ row.report_pct }}%</div>
                <div class="mx-auto mt-1 h-1.5 w-[70px] overflow-hidden rounded bg-slate-100">
                  <div class="h-full rounded" :style="{ width: `${row.report_pct}%`, background: rowPercentColor(row.report_pct) }" />
                </div>
                <div class="mt-0.5 text-[11px] text-slate-400">{{ row.compliance.done }}/{{ row.compliance.total }} task</div>
              </td>
              <td class="py-2.5 pl-2 pr-4 text-center">
                <span :class="['whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold', complianceStatusOf(row.status).badge]">{{ complianceStatusOf(row.status).label }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
