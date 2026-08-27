<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  Sliders, 
  Users, 
  Truck, 
  Database, 
  Calendar, 
  Filter, 
  ArrowRight,
  ShieldCheck,
  Activity
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: Object,
  stats: Object,
  recentRoster: Array,
});

const filterDate = ref(props.filters?.date || new Date().toISOString().split('T')[0]);
const filterShift = ref(props.filters?.shift || 'D');

const applyFilter = () => {
  router.get('/one-setting/dashboard', { date: filterDate.value, shift: filterShift.value }, { preserveState: true });
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

        <div class="flex items-center gap-2">
          <a href="/one-setting/settingan" class="px-4 py-2 text-xs font-semibold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center gap-2">
            <Sliders class="w-4 h-4" />
            Kelola Settingan
          </a>
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
        <a href="/one-setting/settingan?sub=manpower" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-sky-300 hover:shadow-md transition">
          <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <Users class="w-5 h-5" />
          </div>
          <h3 class="font-bold text-slate-800 text-sm mb-1 group-hover:text-sky-600 flex items-center justify-between">
            Input Setting Manpower
            <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          </h3>
          <p class="text-xs text-slate-500">Sinkronkan spreadsheet Google Sheets alokasi operator &amp; roster harian.</p>
        </a>

        <a href="/one-setting/settingan?sub=fleet" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-indigo-300 hover:shadow-md transition">
          <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <Truck class="w-5 h-5" />
          </div>
          <h3 class="font-bold text-slate-800 text-sm mb-1 group-hover:text-indigo-600 flex items-center justify-between">
            Input Setting Fleet
            <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          </h3>
          <p class="text-xs text-slate-500">Import alokasi fleet alat berat &amp; kelola master unit staff / non-staff.</p>
        </a>

        <a href="/one-setting/settingan?sub=roster" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-emerald-300 hover:shadow-md transition">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <Database class="w-5 h-5" />
          </div>
          <h3 class="font-bold text-slate-800 text-sm mb-1 group-hover:text-emerald-600 flex items-center justify-between">
            Data Alokasi Manpower
            <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          </h3>
          <p class="text-xs text-slate-500">Lihat tabel alokasi, deteksi bentrok unit/NRP, dan update manual.</p>
        </a>
      </div>

      <!-- Recent Roster Snapshot -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <Activity class="w-4 h-4 text-sky-500" />
            Snapshot Data Roster Terkini
          </h3>
          <div class="flex items-center gap-2">
            <input type="date" v-model="filterDate" @change="applyFilter" class="px-2.5 py-1 text-xs border border-slate-300 rounded-xl bg-white" />
            <select v-model="filterShift" @change="applyFilter" class="px-2.5 py-1 text-xs border border-slate-300 rounded-xl bg-white">
              <option value="D">Shift 1 (Siang)</option>
              <option value="N">Shift 2 (Malam)</option>
            </select>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
              <tr>
                <th class="px-4 py-3 text-center w-12">#</th>
                <th class="px-4 py-3">NRP</th>
                <th class="px-4 py-3">Unit Alokasi</th>
                <th class="px-4 py-3">Shift</th>
                <th class="px-4 py-3">Tipe CSA</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="(r, idx) in recentRoster" :key="r.id" class="hover:bg-slate-50/60 transition">
                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-3 font-bold text-slate-800">{{ r.nrp }}</td>
                <td class="px-4 py-3 font-extrabold text-sky-600">{{ r.unit }}</td>
                <td class="px-4 py-3 font-medium">{{ r.kode === 'D' ? 'Shift 1 (Siang)' : 'Shift 2 (Malam)' }}</td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 text-[10px] font-semibold rounded-md bg-purple-50 text-purple-700 border border-purple-200">
                    {{ r.csa_type || 'CSA GENERAL' }}
                  </span>
                </td>
              </tr>
              <tr v-if="!recentRoster || recentRoster.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                  Belum ada data roster untuk tanggal &amp; shift ini
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
