<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Search, X, Printer } from 'lucide-vue-next';
import { averagePercentColor, formatLongDate, formatSlashDate } from '@/utils/odrCompliance';

const props = defineProps({
  filters: { type: Object, required: true },
  rows: { type: Array, default: () => [] },
  areaOptions: { type: Array, default: () => [] },
});

const BASE_URL = '/one-reporting/laporan';

const COUNT_COLUMNS = [
  { key: 'total', label: 'Total Assignment', badge: 'bg-blue-50 text-blue-700' },
  { key: 'selesai', label: 'Complete', badge: 'bg-green-100 text-green-800' },
  { key: 'progress', label: 'Progress', badge: 'bg-yellow-100 text-yellow-800' },
  { key: 'belum', label: 'Belum Dilaksanakan', badge: 'bg-red-100 text-red-800' },
];

const form = reactive({ tgl_dari: props.filters.tgl_dari, tgl_sampai: props.filters.tgl_sampai, area: props.filters.area });

const fieldClass = 'rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100';
const labelClass = 'mb-1 block text-[11px] font-bold text-slate-500';

const printPage = () => window.print();

const handleFilter = () => {
  const filled = Object.fromEntries(Object.entries(form).filter(([, v]) => v !== ''));
  router.get(BASE_URL, { tab: 'rekap_harian', ...filled }, { preserveScroll: true });
};
</script>

<template>
  <div class="space-y-3">
    <form class="flex flex-wrap items-end gap-2 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3.5 print:hidden" @submit.prevent="handleFilter">
      <div><label :class="labelClass">DARI TANGGAL</label><input v-model="form.tgl_dari" type="date" :class="[fieldClass, 'w-40']" /></div>
      <div><label :class="labelClass">SAMPAI TANGGAL</label><input v-model="form.tgl_sampai" type="date" :class="[fieldClass, 'w-40']" /></div>
      <div>
        <label :class="labelClass">AREA</label>
        <select v-model="form.area" :class="[fieldClass, 'w-40']">
          <option value="">Semua Area</option>
          <option v-for="a in areaOptions" :key="a" :value="a">{{ a }}</option>
        </select>
      </div>
      <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700"><Search class="h-3.5 w-3.5" />Filter</button>
      <Link :href="`${BASE_URL}?tab=rekap_harian`" title="Reset" class="inline-flex items-center rounded-lg border border-slate-400 px-2.5 py-1.5 text-slate-600 hover:bg-white"><X class="h-4 w-4" /></Link>
      <button type="button" class="ml-auto inline-flex items-center gap-1 rounded-lg border border-slate-400 px-3 py-1.5 text-sm text-slate-600 hover:bg-white" @click="printPage"><Printer class="h-4 w-4" />PDF / Print</button>
    </form>

    <div class="overflow-hidden rounded-2xl bg-white shadow-md print:shadow-none">
      <div class="border-b border-slate-100 bg-slate-50 px-4 py-2 text-[13px] font-bold text-slate-600">
        Rekap Harian · {{ formatSlashDate(filters.tgl_dari) }} – {{ formatSlashDate(filters.tgl_sampai) }}
      </div>
      <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-[13px] text-slate-700">
          <thead class="whitespace-nowrap border-b-2 border-slate-100 text-xs font-bold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="py-2 pl-4 pr-2">#</th>
              <th class="px-2 py-2">Tanggal</th>
              <th v-for="col in COUNT_COLUMNS" :key="col.key" class="px-2 py-2 text-center">{{ col.label }}</th>
              <th class="min-w-[120px] py-2 pl-2 pr-4 text-center">Compliance %</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="rows.length === 0">
              <td colspan="7" class="py-10 text-center text-sm text-slate-500">Tidak ada data untuk rentang tanggal ini</td>
            </tr>
            <tr v-for="(row, idx) in rows" :key="row.tanggal" class="hover:bg-slate-50">
              <td class="py-2.5 pl-4 pr-2 text-slate-400">{{ idx + 1 }}</td>
              <td class="px-2 py-2.5 font-semibold">{{ formatLongDate(row.tanggal) }}</td>
              <td v-for="col in COUNT_COLUMNS" :key="col.key" class="px-2 py-2.5 text-center">
                <span :class="['rounded px-3 py-1 text-sm font-bold', col.badge]">{{ row[col.key] }}</span>
              </td>
              <td class="py-2.5 pl-2 pr-4 text-center">
                <div class="font-bold" :style="{ color: averagePercentColor(row.pct) }">{{ row.pct }}%</div>
                <div class="mx-auto mt-1 h-1.5 w-[90px] overflow-hidden rounded bg-slate-100">
                  <div class="h-full rounded" :style="{ width: `${row.pct}%`, background: averagePercentColor(row.pct) }" />
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
