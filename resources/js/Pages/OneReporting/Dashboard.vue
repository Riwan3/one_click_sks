<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  ClipboardCheck, 
  CheckCircle2, 
  Clock, 
  AlertCircle, 
  TrendingUp, 
  Filter, 
  Calendar, 
  Layers, 
  Award, 
  AlertTriangle,
  FileSpreadsheet
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  filters: Object,
  metrics: Object,
  chartHarian: Array,
  areaStats: Array,
  topPengawas: Array,
  areaLow: Array,
  outstanding: Array,
  allAreas: Array,
});

const filterForm = ref({
  tanggal: props.filters?.tanggal || new Date().toISOString().split('T')[0],
  shift: props.filters?.shift || '',
  area: props.filters?.area || '',
});

const applyFilter = () => {
  router.get('/one-reporting/dashboard', filterForm.value, { preserveState: true, replace: true });
};

const resetFilter = () => {
  filterForm.value = {
    tanggal: new Date().toISOString().split('T')[0],
    shift: '',
    area: '',
  };
  applyFilter();
};
</script>

<template>
  <Head title="Dashboard Compliance - One-Reporting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 mb-1">
            <span>Operation Compliance Report</span>
            <span>/</span>
            <span class="text-slate-500">Dashboard</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <ClipboardCheck class="w-6 h-6 text-sky-500" />
            Dashboard Compliance
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Monitoring tingkat kepatuhan aktivitas operasional harian &amp; tren lapangan</p>
        </div>

        <div class="flex items-center gap-2">
          <a href="/one-reporting/daily" class="px-4 py-2 text-xs font-semibold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center gap-2">
            <ClipboardCheck class="w-4 h-4" />
            Input Daily Compliance
          </a>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <form @submit.prevent="applyFilter" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Tanggal</label>
            <div class="relative">
              <input type="date" v-model="filterForm.tanggal" class="w-full pl-9 pr-3 py-2 text-xs font-medium border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-slate-50/50" />
              <Calendar class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Shift</label>
            <select v-model="filterForm.shift" class="w-full px-3 py-2 text-xs font-medium border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-slate-50/50">
              <option value="">Semua Shift</option>
              <option value="Shift 1">Shift 1</option>
              <option value="Shift 2">Shift 2</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1">Area</label>
            <select v-model="filterForm.area" class="w-full px-3 py-2 text-xs font-medium border border-slate-300 rounded-xl focus:ring-2 focus:ring-sky-500 focus:border-sky-500 bg-slate-50/50">
              <option value="">Semua Area</option>
              <option v-for="a in allAreas" :key="a" :value="a">{{ a }}</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <button type="submit" class="flex-1 py-2 px-4 bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5">
              <Filter class="w-3.5 h-3.5" />
              Filter
            </button>
            <button type="button" @click="resetFilter" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition">
              Reset
            </button>
          </div>
        </form>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
            <Layers class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Assignment</p>
            <p class="text-xl font-black text-slate-800">{{ metrics.totalAssignment }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <CheckCircle2 class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Selesai (Complete)</p>
            <p class="text-xl font-black text-emerald-600">{{ metrics.totalSelesai }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
            <Clock class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Progress</p>
            <p class="text-xl font-black text-amber-600">{{ metrics.totalProgress }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
            <AlertCircle class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Belum Dimulai</p>
            <p class="text-xl font-black text-rose-600">{{ metrics.totalBelum }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3 col-span-2 lg:col-span-1">
          <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
            <TrendingUp class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Compliance Rate</p>
            <p class="text-xl font-black text-indigo-600">{{ metrics.persentase }}%</p>
          </div>
        </div>
      </div>

      <!-- Charts & Tables Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- 7-Day Trend Chart -->
        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <TrendingUp class="w-4 h-4 text-sky-500" />
                Tren Compliance Harian (7 Hari Terakhir)
              </h2>
              <span class="text-xs text-slate-400 font-medium">Auto-updated</span>
            </div>

            <!-- Custom Visual Bars -->
            <div class="h-48 flex items-end justify-between gap-3 pt-6 pb-2 px-2">
              <div v-for="item in chartHarian" :key="item.tgl" class="flex-1 flex flex-col items-center gap-2 h-full justify-end group">
                <div class="text-[10px] font-bold text-slate-600 opacity-0 group-hover:opacity-100 transition-opacity">
                  {{ item.total > 0 ? Math.round((item.selesai / item.total) * 100) : 0 }}%
                </div>
                <div class="w-full bg-slate-100 rounded-t-lg relative overflow-hidden flex flex-col justify-end" style="height: 120px;">
                  <div 
                    class="w-full bg-gradient-to-t from-sky-600 to-sky-400 rounded-t-lg transition-all duration-500"
                    :style="{ height: `${item.total > 0 ? (item.selesai / item.total) * 100 : 0}%` }"
                  ></div>
                </div>
                <span class="text-[11px] font-semibold text-slate-500">{{ item.tgl }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Compliance Per Area -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col">
          <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <Layers class="w-4 h-4 text-indigo-500" />
            Compliance Per Area
          </h2>
          <div class="space-y-3 flex-1 overflow-y-auto max-h-56 pr-1">
            <div v-for="st in areaStats" :key="st.area" class="space-y-1">
              <div class="flex items-center justify-between text-xs font-semibold">
                <span class="text-slate-700">{{ st.area }}</span>
                <span class="text-slate-500">{{ st.total > 0 ? Math.round((st.selesai / st.total) * 100) : 0 }}% ({{ st.selesai }}/{{ st.total }})</span>
              </div>
              <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                <div 
                  class="h-full bg-indigo-500 rounded-full transition-all duration-300"
                  :style="{ width: `${st.total > 0 ? (st.selesai / st.total) * 100 : 0}%` }"
                ></div>
              </div>
            </div>
            <div v-if="!areaStats || areaStats.length === 0" class="text-center py-6 text-xs text-slate-400">
              Belum ada data area untuk tanggal ini
            </div>
          </div>
        </div>
      </div>

      <!-- Ranking Tables -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Supervisor -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
              <Award class="w-4 h-4 text-emerald-500" />
              Top Compliance Pengawas
            </h3>
          </div>
          <div class="divide-y divide-slate-100">
            <div v-for="(p, idx) in topPengawas" :key="p.pengawas" class="p-3.5 flex items-center justify-between hover:bg-slate-50/60 transition">
              <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded-lg text-[11px] font-bold flex items-center justify-center" :class="idx === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600'">
                  #{{ idx + 1 }}
                </span>
                <span class="text-xs font-bold text-slate-800">{{ p.pengawas }}</span>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-xs font-semibold text-slate-500">{{ p.selesai }}/{{ p.assignment }} task</span>
                <span class="px-2.5 py-1 text-[11px] font-black rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                  {{ p.persen }}%
                </span>
              </div>
            </div>
            <div v-if="!topPengawas || topPengawas.length === 0" class="p-6 text-center text-xs text-slate-400">
              Belum ada data pengawas
            </div>
          </div>
        </div>

        <!-- Area Lowest -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
              <AlertTriangle class="w-4 h-4 text-rose-500" />
              Area Compliance Terendah
            </h3>
          </div>
          <div class="divide-y divide-slate-100">
            <div v-for="a in areaLow" :key="a.area" class="p-3.5 flex items-center justify-between hover:bg-slate-50/60 transition">
              <span class="text-xs font-bold text-slate-800">{{ a.area }}</span>
              <div class="flex items-center gap-3">
                <span class="text-xs font-semibold text-slate-500">{{ a.selesai }}/{{ a.total }} task</span>
                <span class="px-2.5 py-1 text-[11px] font-black rounded-lg bg-rose-50 text-rose-700 border border-rose-200">
                  {{ a.persen }}%
                </span>
              </div>
            </div>
            <div v-if="!areaLow || areaLow.length === 0" class="p-6 text-center text-xs text-slate-400">
              Belum ada data area
            </div>
          </div>
        </div>
      </div>

      <!-- Outstanding Tasks List -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
          <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <Clock class="w-4 h-4 text-amber-500" />
            Aktivitas Belum Dilaksanakan (Outstanding)
          </h3>
          <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700">
            {{ outstanding.length }} Outstanding
          </span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
              <tr>
                <th class="px-4 py-3">Area</th>
                <th class="px-4 py-3">Unit</th>
                <th class="px-4 py-3">Pengawas</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="o in outstanding" :key="o.id" class="hover:bg-slate-50/60 transition">
                <td class="px-4 py-3 font-semibold text-slate-800">{{ o.area }}</td>
                <td class="px-4 py-3 text-slate-500">{{ o.unit }}</td>
                <td class="px-4 py-3 font-medium">{{ o.pengawas }}</td>
                <td class="px-4 py-3">
                  <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full bg-rose-100 text-rose-700">
                    Belum Dimulai
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <a href="/one-reporting/daily" class="text-sky-600 hover:text-sky-800 font-bold hover:underline">
                    Proses &rarr;
                  </a>
                </td>
              </tr>
              <tr v-if="!outstanding || outstanding.length === 0">
                <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                  Semua aktivitas untuk tanggal ini sudah diproses/selesai! 🎉
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
