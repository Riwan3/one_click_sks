<script setup>
import { computed, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import { BASE_URL } from '@/utils/manpowerV201';
import FleetMonitoringTable from './Partials/FleetMonitoringTable.vue';
import ManpowerShell from './Partials/ManpowerShell.vue';
import MonitoringPager from './Partials/MonitoringPager.vue';
import MonitoringTable from './Partials/MonitoringTable.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  ready: { type: Boolean, default: true },
  loadError: { type: String, default: null },
  filters: { type: Object, required: true }, // { type, month, status }
  monitoring: { type: Object, default: null },
});

const MONITORING_URL = `${BASE_URL}/monitoring`;
const TYPES = [
  { key: 'manpower', label: 'Setting Manpower' },
  { key: 'fleet', label: 'Setting Fleet' },
];

const MONITORING_TYPES = {
  manpower: {
    table: MonitoringTable,
    subtitle: 'Monitoring status, kelengkapan, dan hari yang belum disetting (per tanggal dan shift).',
    statusOptions: { final: 'Final', draft: 'Draft', belum: 'Belum disetting', mendatang: 'Mendatang' },
    chips: [
      { key: 'belum', label: 'Belum disetting', class: 'bg-red-600 text-white' },
      { key: 'draft', label: 'Draft', class: 'bg-slate-500 text-white' },
      { key: 'final', label: 'Final', class: 'bg-emerald-600 text-white' },
      { key: 'mendatang', label: 'Mendatang', class: 'border border-slate-300 bg-slate-50 text-slate-600' },
    ],
    note: 'Satu halaman memuat 7 tanggal; angka ringkasan di atas adalah total satu bulan. Hanya tanggal yang ada isinya di roster published atau sudah punya setting yang ditampilkan. "Belum disetting" hanya untuk hari ini dan besok; tanggal setelahnya ditandai Mendatang, dan hari yang sudah lewat tanpa setting tidak ditampilkan.',
  },
  fleet: {
    table: FleetMonitoringTable,
    subtitle: 'Monitoring setting fleet harian: loader, hauler terpasang, loader tanpa hauler, dan hauler dobel (per tanggal dan shift).',
    statusOptions: { terisi: 'Terisi', kosong: 'Ada loader kosong', dobel: 'Hauler dobel', belum: 'Belum disetting' },
    chips: [
      { key: 'belum', label: 'Belum disetting', class: 'bg-red-600 text-white' },
      { key: 'dobel', label: 'Hauler dobel', class: 'bg-amber-500 text-slate-900' },
      { key: 'kosong', label: 'Ada loader kosong', class: 'bg-slate-500 text-white' },
      { key: 'terisi', label: 'Terisi', class: 'bg-emerald-600 text-white' },
    ],
    note: 'Satu halaman memuat 7 tanggal; angka ringkasan di atas adalah total satu bulan. Hanya tanggal yang sudah punya setting fleet yang ditampilkan. "Belum disetting" hanya untuk hari ini dan besok. Loader kosong dan hauler dobel dihitung dari loader grup utama (tanpa ALL AREA dan BREAKDOWN / SERVICE).',
  },
};

const config = computed(() => MONITORING_TYPES[props.filters.type]);

const form = reactive({ type: props.filters.type, month: props.filters.month, status: props.filters.status });

const handleSubmit = () => router.get(MONITORING_URL, { ...form });
const typeHref = (type) => `${MONITORING_URL}?${new URLSearchParams({ type, month: props.filters.month })}`;
const statusHref = (status) => `${MONITORING_URL}?${new URLSearchParams({ type: props.filters.type, month: props.filters.month, ...(status && { status }) })}`;

const currentRange = computed(() => {
  const [, from, to] = props.monitoring.pages[props.monitoring.page - 1];

  return `${from}–${to}`;
});
</script>

<template>
  <ManpowerShell
    :user="user"
    :notif-counts="notifCounts"
    :menu="menu"
    :ready="ready"
    :load-error="loadError"
    :subtitle="config.subtitle"
  >
    <nav class="flex flex-wrap gap-1" aria-label="Jenis monitoring">
      <Link
        v-for="item in TYPES"
        :key="item.key"
        :href="typeHref(item.key)"
        :class="['rounded-lg px-3 py-1.5 text-sm font-semibold', item.key === filters.type ? 'bg-blue-600 text-white' : 'text-blue-600 hover:bg-blue-50']"
      >
        {{ item.label }}
      </Link>
    </nav>

    <template v-if="monitoring">
      <form class="grid grid-cols-2 items-end gap-2 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-[14rem_14rem_auto]" @submit.prevent="handleSubmit">
        <FormInput v-model="form.month" type="month" label="BULAN" required />
        <FormSelect v-model="form.status" label="STATUS">
          <option value="">Semua</option>
          <option v-for="(label, key) in config.statusOptions" :key="key" :value="key">{{ label }}</option>
        </FormSelect>
        <button type="submit" class="col-span-2 flex items-center justify-center gap-1.5 rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900 md:col-span-1"><Search class="h-4 w-4" /> Tampilkan</button>
      </form>

      <div class="flex flex-wrap gap-2">
        <Link v-for="chip in config.chips" :key="chip.key" :href="statusHref(chip.key)" :class="['rounded-full px-3 py-1 text-xs font-bold', chip.class]">{{ chip.label }} {{ monitoring.counts[chip.key] }}</Link>
        <Link v-if="filters.status" :href="statusHref('')" class="rounded-full bg-slate-800 px-3 py-1 text-xs font-bold text-white">Tampilkan semua</Link>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs text-slate-500">Tanggal</span>
        <MonitoringPager :pages="monitoring.pages" :page="monitoring.page" :filters="filters" />
      </div>

      <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <component :is="config.table" :rows="monitoring.rows" :empty-text="`Tidak ada data pada tanggal ${currentRange} untuk status ini.`" />
      </div>

      <MonitoringPager :pages="monitoring.pages" :page="monitoring.page" :filters="filters" />

      <p class="text-xs text-slate-500">{{ config.note }}</p>
    </template>
  </ManpowerShell>
</template>
