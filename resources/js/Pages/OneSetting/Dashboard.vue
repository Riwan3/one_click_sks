<script setup>
import { ref, computed } from 'vue';
import { confirmDialog } from '@/utils/confirm';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  Sliders,
  Users,
  Truck,
  Database,
  ArrowRight,
  ShieldCheck,
  History,
  RefreshCw,
  Trash2,
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  stats: Object,
  fleetImportHistory: { type: Array, default: () => [] },
  manpowerImportHistory: { type: Array, default: () => [] },
  historyMonth: { type: String, default: () => new Date().toISOString().slice(0, 7) },
});

const activeHistoryTab = ref('fleet');
const historyMonthInput = ref(props.historyMonth);

const applyHistoryMonth = () => {
  router.get('/one-setting/dashboard', { history_month: historyMonthInput.value }, { preserveState: true, preserveScroll: true });
};

const HISTORY_TABS = [
  { key: 'fleet', label: 'Riwayat Import Fleet' },
  { key: 'manpower', label: 'Riwayat Import Setting Manpower' },
];

const activeRows = computed(() => (activeHistoryTab.value === 'fleet' ? props.fleetImportHistory : props.manpowerImportHistory));
const activeUnitLabel = computed(() => (activeHistoryTab.value === 'fleet' ? 'Jumlah Unit' : 'Jumlah Data'));
const activeEmptyMessage = computed(() => (
  activeHistoryTab.value === 'fleet'
    ? 'Belum ada riwayat import alokasi fleet'
    : 'Belum ada riwayat import setting manpower'
));

// Fleet pakai kode shift angka (1/2), Manpower pakai kode huruf (D/N) — satu fungsi label
// yang menangani keduanya biar tabelnya bisa dipakai bareng.
const shiftLabel = (shift) => {
  if (shift === 'N' || Number(shift) === 2) return 'Shift 2 (Malam)';
  return 'Shift 1 (Siang)';
};

const isNightShift = (shift) => shift === 'N' || Number(shift) === 2;

const formatLastImport = (value) => {
  if (!value) return '-';
  return new Date(value.replace(' ', 'T')).toLocaleString('id-ID', {
    dateStyle: 'medium',
    timeStyle: 'short',
  });
};

const refreshHistory = () => {
  router.reload({ only: ['fleetImportHistory', 'manpowerImportHistory'] });
};

const deleteFleetBatch = async (tanggal, shift) => {
  if (await confirmDialog(`Hapus semua data alokasi fleet tanggal ${tanggal} shift ${shift}? Tindakan ini tidak bisa dibatalkan.`)) {
    router.post('/one-setting/fleet/delete', { date: tanggal, shift }, { preserveScroll: true });
  }
};

const deleteManpowerBatch = async (tanggal, shift) => {
  if (await confirmDialog(`Hapus semua data roster manpower tanggal ${tanggal} shift ${shift}? Tindakan ini tidak bisa dibatalkan.`)) {
    router.post('/one-setting/roster/clear', { date: tanggal, shift }, { preserveScroll: true });
  }
};

const deleteActiveBatch = (tanggal, shift) => {
  if (activeHistoryTab.value === 'fleet') deleteFleetBatch(tanggal, shift);
  else deleteManpowerBatch(tanggal, shift);
};
</script>

<template>
  <Head title="Dashboard Setting - One-Setting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 mb-1">
            <span>Operation Settings</span>
            <span>/</span>
            <span class="text-slate-500">Dashboard</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <Sliders class="w-6 h-6 text-sky-500" />
            Dashboard Setting Operasional
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Konfigurasi alokasi manpower, unit fleet, dan monitoring settingan lapangan</p>
        </div>
      </div>

      <!-- Quick Metrics -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
            <Users class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Roster Manpower</p>
            <p class="text-xl font-black text-slate-800">{{ stats?.totalManpowerRoster || 0 }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
            <Truck class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Master Fleet Unit</p>
            <p class="text-xl font-black text-indigo-600">{{ stats?.totalFleetUnits || 0 }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <Database class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Alokasi Fleet</p>
            <p class="text-xl font-black text-emerald-600">{{ stats?.totalFleetAllocations || 0 }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
            <ShieldCheck class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Karyawan</p>
            <p class="text-xl font-black text-amber-600">{{ stats?.totalEmployees || 0 }}</p>
          </div>
        </div>
      </div>

      <!-- Quick Action Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="/one-setting/manpower" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-sky-300 hover:shadow-md transition">
          <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <Users class="w-5 h-5" />
          </div>
          <h3 class="font-bold text-slate-800 text-sm mb-1 group-hover:text-sky-600 flex items-center justify-between">
            Input Setting Manpower
            <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          </h3>
          <p class="text-xs text-slate-500">Setting Manpower Langsung Pada Tanggal &amp; Shift yang dituju diambil dari One-Roster</p>
        </a>

        <a href="/one-setting/fleet" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-indigo-300 hover:shadow-md transition">
          <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <Truck class="w-5 h-5" />
          </div>
          <h3 class="font-bold text-slate-800 text-sm mb-1 group-hover:text-indigo-600 flex items-center justify-between">
            Input Setting Fleet
            <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          </h3>
          <p class="text-xs text-slate-500">Import alokasi fleet alat berat &amp; kelola master unit staff / non-staff.</p>
        </a>

        <a href="/one-setting/setting-board" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-emerald-300 hover:shadow-md transition">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <Database class="w-5 h-5" />
          </div>
          <h3 class="font-bold text-slate-800 text-sm mb-1 group-hover:text-emerald-600 flex items-center justify-between">
            Setting Board
            <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          </h3>
          <p class="text-xs text-slate-500">Papan HD manpower &amp; fleet per site, tanggal, dan shift.</p>
        </a>
      </div>

      <!-- Riwayat Import (Fleet / Setting Manpower) -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-2">
            <button
              v-for="tab in HISTORY_TABS"
              :key="tab.key"
              type="button"
              @click="activeHistoryTab = tab.key"
              :class="[
                'px-3 py-1.5 text-xs font-bold rounded-full transition flex items-center gap-1.5',
                activeHistoryTab === tab.key ? 'bg-sky-600 text-white shadow-sm' : 'bg-white border border-slate-300 text-slate-600 hover:bg-slate-50',
              ]"
            >
              <History class="w-3.5 h-3.5" /> {{ tab.label }}
            </button>
          </div>
          <div class="flex items-center gap-2 shrink-0">
            <input
              type="month"
              v-model="historyMonthInput"
              @change="applyHistoryMonth"
              class="px-2.5 py-1.5 text-xs font-semibold border border-slate-300 rounded-xl bg-white"
            >
            <button type="button" @click="refreshHistory" class="px-3 py-1.5 text-xs font-bold rounded-full bg-white border border-slate-300 text-slate-600 hover:bg-slate-50 transition flex items-center gap-1.5">
              <RefreshCw class="w-3.5 h-3.5" /> Refresh
            </button>
          </div>
        </div>

        <!-- Dibatasi ~10 baris kelihatan, sisanya scroll di dalam (header ikut nempel) -->
        <div class="overflow-x-auto">
          <div class="max-h-[420px] overflow-y-auto">
            <table class="w-full text-left text-xs">
              <thead class="sticky top-0 z-10 bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
                <tr>
                  <th class="px-4 py-3">Tanggal</th>
                  <th class="px-4 py-3">Shift</th>
                  <th class="px-4 py-3">{{ activeUnitLabel }}</th>
                  <th class="px-4 py-3">Terakhir Diimport</th>
                  <th class="px-4 py-3 text-right w-24">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="row in activeRows" :key="`${row.tanggal}-${row.shift}`" class="hover:bg-slate-50/60 transition">
                  <td class="px-4 py-3 font-bold text-slate-800">{{ row.tanggal }}</td>
                  <td class="px-4 py-3">
                    <span :class="[
                      'px-2 py-0.5 text-[10px] font-bold rounded-full',
                      isNightShift(row.shift) ? 'bg-slate-800 text-white' : 'bg-amber-100 text-amber-800',
                    ]">
                      {{ shiftLabel(row.shift) }}
                    </span>
                  </td>
                  <td class="px-4 py-3 font-extrabold text-indigo-600">{{ row.jumlah_unit }}</td>
                  <td class="px-4 py-3 font-medium text-slate-600">{{ formatLastImport(row.last_import) }}</td>
                  <td class="px-4 py-3 text-right">
                    <button type="button" @click="deleteActiveBatch(row.tanggal, row.shift)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </td>
                </tr>
                <tr v-if="!activeRows || activeRows.length === 0">
                  <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                    {{ activeEmptyMessage }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
