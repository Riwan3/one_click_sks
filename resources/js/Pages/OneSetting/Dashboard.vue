<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  Sliders,
  Users,
  Truck,
  Database,
  ArrowRight,
  ShieldCheck,
} from 'lucide-vue-next';
import BoardViewsChart from './Partials/BoardViewsChart.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  stats: Object,
  viewStats: { type: Object, required: true },
  month: { type: String, default: () => new Date().toISOString().slice(0, 7) },
});

const monthInput = ref(props.month);

// Jam & shift berjalan untuk banner (shift 1 = 06:00-17:59, shift 2 = sisanya)
const DAY_SHIFT_START_HOUR = 6;
const NIGHT_SHIFT_START_HOUR = 18;
const actualTime = ref('00:00:00');
const actualDateStr = ref('');
const isDayShift = ref(true);
let clockTimer = null;

const updateClock = () => {
  const now = new Date();
  actualTime.value = now.toTimeString().split(' ')[0];
  actualDateStr.value = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }).toUpperCase();
  isDayShift.value = now.getHours() >= DAY_SHIFT_START_HOUR && now.getHours() < NIGHT_SHIFT_START_HOUR;
};

onMounted(() => {
  updateClock();
  clockTimer = setInterval(updateClock, 1000);
});

onUnmounted(() => {
  if (clockTimer) clearInterval(clockTimer);
});

const applyMonth = () => {
  router.get('/one-setting/dashboard', { month: monthInput.value }, { preserveState: true, preserveScroll: true });
};

const refreshViews = () => {
  router.reload({ only: ['viewStats'] });
};
</script>

<template>
  <Head title="Dashboard Setting - One-Setting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 text-white p-6 rounded-2xl shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 rounded-2xl bg-sky-500/20 backdrop-blur border border-sky-400/30 flex items-center justify-center shrink-0">
            <Sliders class="w-6 h-6 text-sky-400" />
          </div>
          <div>
            <div class="flex items-center gap-2 text-[11px] font-semibold text-sky-300/80 mb-0.5">
              <span>Operation Settings</span>
              <span>/</span>
              <span class="text-slate-300">Dashboard</span>
            </div>
            <h1 class="text-xl font-black tracking-wide">Dashboard Setting Operasional</h1>
            <p class="text-xs text-sky-300/80">Konfigurasi alokasi manpower, unit fleet, dan monitoring settingan lapangan</p>
          </div>
        </div>

        <div class="flex items-center gap-6 bg-white/10 backdrop-blur-md px-5 py-2.5 rounded-2xl border border-white/10">
          <div class="text-right">
            <span class="text-xs font-black text-amber-400 tracking-wider flex items-center gap-1.5 justify-end">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
              {{ isDayShift ? 'SHIFT 1 (SIANG)' : 'SHIFT 2 (MALAM)' }}
            </span>
            <p class="text-[10px] text-slate-300 font-medium">{{ actualDateStr }}</p>
          </div>
          <div class="border-l border-white/20 pl-4 font-mono font-black text-xl text-sky-300">
            {{ actualTime }}
          </div>
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

      <!-- Grafik: setting manpower / fleet dibuka pengguna non-admin -->
      <BoardViewsChart v-model:month="monthInput" :stats="viewStats" @change-month="applyMonth" @refresh="refreshViews" />
    </div>
  </AppLayout>
</template>
