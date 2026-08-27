<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  FileText, 
  Calendar, 
  Filter, 
  FileSpreadsheet, 
  Printer, 
  BarChart2, 
  TrendingUp, 
  Users, 
  MapPin,
  CheckCircle2,
  XCircle,
  Clock
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  tab: String,
  filters: Object,
  laporanRows: Array,
  rekapHarian: Array,
  rekapBulanan: Object,
  allAreas: Array,
});

const activeTab = ref(props.tab || 'laporan');

const filterForm = ref({
  tab: activeTab.value,
  tgl_dari: props.filters?.tgl_dari || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
  tgl_sampai: props.filters?.tgl_sampai || new Date().toISOString().split('T')[0],
  area: props.filters?.area || '',
  pengawas: props.filters?.pengawas || '',
  status: props.filters?.status || '',
  bulan: props.filters?.bulan || new Date().getMonth() + 1,
  tahun: props.filters?.tahun || new Date().getFullYear(),
});

const switchTab = (t) => {
  activeTab.value = t;
  filterForm.value.tab = t;
  applyFilter();
};

const applyFilter = () => {
  router.get('/one-reporting/laporan', filterForm.value, { preserveState: true, replace: true });
};

const resetFilter = () => {
  filterForm.value = {
    tab: activeTab.value,
    tgl_dari: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    tgl_sampai: new Date().toISOString().split('T')[0],
    area: '',
    pengawas: '',
    status: '',
    bulan: new Date().getMonth() + 1,
    tahun: new Date().getFullYear(),
  };
  applyFilter();
};

const exportExcel = () => {
  const query = new URLSearchParams(filterForm.value).toString();
  window.location.href = `/one-reporting/laporan/export?${query}`;
};

const printPdf = () => {
  window.print();
};
</script>

<template>
  <Head title="Laporan Compliance - One-Reporting" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 mb-1">
            <span>Operation Compliance Report</span>
            <span>/</span>
            <span class="text-slate-500">Laporan Compliance</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <FileText class="w-6 h-6 text-sky-500" />
            Report &amp; Rekapitulasi Compliance
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Analisis laporan compliance harian, rekap harian, dan tren bulanan</p>
        </div>

        <div class="flex items-center gap-2">
          <button @click="exportExcel" class="px-4 py-2 text-xs font-semibold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm transition flex items-center gap-1.5">
            <FileSpreadsheet class="w-4 h-4" />
            Export Excel (CSV)
          </button>
          <button @click="printPdf" class="px-3 py-2 text-xs font-semibold rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition flex items-center gap-1.5">
            <Printer class="w-4 h-4" />
            Cetak / PDF
          </button>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <button 
          @click="switchTab('laporan')"
          class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2"
          :class="activeTab === 'laporan' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
        >
          <FileText class="w-4 h-4" />
          Detail Laporan
        </button>
        <button 
          @click="switchTab('rekap_harian')"
          class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2"
          :class="activeTab === 'rekap_harian' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
        >
          <Calendar class="w-4 h-4" />
          Rekap Harian
        </button>
        <button 
          @click="switchTab('rekap_bulanan')"
          class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2"
          :class="activeTab === 'rekap_bulanan' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
        >
          <BarChart2 class="w-4 h-4" />
          Rekap Bulanan &amp; Grafik
        </button>
      </div>

      <!-- TAB 1: LAPORAN DETAIL -->
      <div v-if="activeTab === 'laporan'" class="space-y-4">
        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
          <form @submit.prevent="applyFilter" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3 items-end">
            <div>
              <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Dari Tanggal</label>
              <input type="date" v-model="filterForm.tgl_dari" class="w-full px-2.5 py-1.5 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Sampai Tanggal</label>
              <input type="date" v-model="filterForm.tgl_sampai" class="w-full px-2.5 py-1.5 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Area</label>
              <select v-model="filterForm.area" class="w-full px-2.5 py-1.5 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50">
                <option value="">Semua Area</option>
                <option v-for="a in allAreas" :key="a" :value="a">{{ a }}</option>
              </select>
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Pengawas</label>
              <input type="text" v-model="filterForm.pengawas" placeholder="Cari nama..." class="w-full px-2.5 py-1.5 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Status</label>
              <select v-model="filterForm.status" class="w-full px-2.5 py-1.5 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50">
                <option value="">Semua Status</option>
                <option value="belum">Belum Dimulai</option>
                <option value="progress">Progress</option>
                <option value="selesai">Complete</option>
              </select>
            </div>

            <div class="flex items-center gap-2">
              <button type="submit" class="flex-1 py-1.5 px-3 bg-sky-600 text-white font-semibold text-xs rounded-xl shadow-sm hover:bg-sky-700 transition flex items-center justify-center gap-1">
                <Filter class="w-3.5 h-3.5" /> Filter
              </button>
              <button type="button" @click="resetFilter" class="py-1.5 px-2.5 bg-slate-100 text-slate-600 font-semibold text-xs rounded-xl hover:bg-slate-200 transition">
                Reset
              </button>
            </div>
          </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
              <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
                <tr>
                  <th class="px-4 py-3.5 w-10 text-center">#</th>
                  <th class="px-4 py-3.5">Tanggal</th>
                  <th class="px-4 py-3.5">Area &amp; Unit</th>
                  <th class="px-4 py-3.5">Pengawas</th>
                  <th class="px-4 py-3.5">Task Checklist</th>
                  <th class="px-4 py-3.5 text-center">Compliance</th>
                  <th class="px-4 py-3.5 text-center">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-slate-700">
                <tr v-for="(row, idx) in laporanRows" :key="row.id" class="hover:bg-slate-50/60 transition">
                  <td class="px-4 py-3.5 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                  <td class="px-4 py-3.5 font-semibold text-slate-800">{{ row.tanggal }}</td>
                  <td class="px-4 py-3.5">
                    <div class="font-bold text-slate-800">{{ row.area }}</div>
                    <div class="text-[11px] text-slate-500">{{ row.unit }}</div>
                  </td>
                  <td class="px-4 py-3.5 font-medium text-slate-800">{{ row.pengawas }}</td>
                  <td class="px-4 py-3.5">
                    <div class="flex flex-wrap gap-2">
                      <span 
                        v-for="(t, tIdx) in row.tasks" 
                        :key="tIdx"
                        class="px-2 py-0.5 rounded-lg text-[10px] font-semibold border flex items-center gap-1"
                        :class="row.task_done[tIdx] ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-50 text-slate-500 border-slate-200'"
                      >
                        <CheckCircle2 v-if="row.task_done[tIdx]" class="w-3 h-3 text-emerald-600 shrink-0" />
                        <XCircle v-else class="w-3 h-3 text-slate-400 shrink-0" />
                        {{ t }}
                      </span>
                    </div>
                  </td>
                  <td class="px-4 py-3.5 text-center">
                    <span class="font-bold" :class="row.pct === 100 ? 'text-emerald-600' : row.pct > 0 ? 'text-amber-600' : 'text-rose-600'">
                      {{ row.pct }}%
                    </span>
                  </td>
                  <td class="px-4 py-3.5 text-center">
                    <span 
                      class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full"
                      :class="{
                        'bg-emerald-100 text-emerald-800': row.status === 'selesai',
                        'bg-amber-100 text-amber-800': row.status === 'progress',
                        'bg-rose-100 text-rose-800': row.status === 'belum'
                      }"
                    >
                      {{ row.status === 'selesai' ? 'Complete' : row.status === 'progress' ? 'Progress' : 'Belum Dimulai' }}
                    </span>
                  </td>
                </tr>
                <tr v-if="!laporanRows || laporanRows.length === 0">
                  <td colspan="7" class="px-4 py-12 text-center text-slate-400 text-xs">
                    Tidak ada data laporan ditemukan
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- TAB 2: REKAP HARIAN -->
      <div v-if="activeTab === 'rekap_harian'" class="space-y-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
          <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <span class="text-xs font-bold text-slate-700">Ringkasan Rekap Harian Compliance</span>
          </div>
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
              <tr>
                <th class="px-4 py-3 text-center w-12">#</th>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3 text-center">Total Assignment</th>
                <th class="px-4 py-3 text-center">Selesai</th>
                <th class="px-4 py-3 text-center">Progress</th>
                <th class="px-4 py-3 text-center">Belum</th>
                <th class="px-4 py-3 text-center">Rata-rata Compliance %</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="(r, idx) in rekapHarian" :key="r.tanggal" class="hover:bg-slate-50/60 transition">
                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-3 font-bold text-slate-800">{{ r.tanggal }}</td>
                <td class="px-4 py-3 text-center font-bold text-slate-800">{{ r.total }}</td>
                <td class="px-4 py-3 text-center font-semibold text-emerald-600">{{ r.selesai }}</td>
                <td class="px-4 py-3 text-center font-semibold text-amber-600">{{ r.progress }}</td>
                <td class="px-4 py-3 text-center font-semibold text-rose-600">{{ r.belum }}</td>
                <td class="px-4 py-3 text-center">
                  <span class="font-extrabold text-sm text-sky-600">{{ r.pct }}%</span>
                </td>
              </tr>
              <tr v-if="!rekapHarian || rekapHarian.length === 0">
                <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs">
                  Tidak ada rekap harian untuk rentang tanggal ini
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 3: REKAP BULANAN & GRAFIK -->
      <div v-if="activeTab === 'rekap_bulanan'" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Compliance per Supervisor -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
              <Users class="w-4 h-4 text-purple-500" />
              Compliance Per Pengawas (Bulan Ini)
            </h3>
            <div class="space-y-3">
              <div v-for="p in rekapBulanan?.pengawas || []" :key="p.pengawas" class="space-y-1">
                <div class="flex items-center justify-between text-xs font-semibold">
                  <span class="text-slate-800">{{ p.pengawas }}</span>
                  <span class="text-purple-600 font-bold">{{ p.pct }}%</span>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div class="h-full bg-purple-500 rounded-full" :style="{ width: `${p.pct}%` }"></div>
                </div>
              </div>
              <div v-if="!rekapBulanan?.pengawas || rekapBulanan.pengawas.length === 0" class="text-center py-6 text-xs text-slate-400">
                Belum ada data
              </div>
            </div>
          </div>

          <!-- Compliance per Area -->
          <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
              <MapPin class="w-4 h-4 text-emerald-500" />
              Compliance Per Area (Bulan Ini)
            </h3>
            <div class="space-y-3">
              <div v-for="a in rekapBulanan?.area || []" :key="a.area" class="space-y-1">
                <div class="flex items-center justify-between text-xs font-semibold">
                  <span class="text-slate-800">{{ a.area }}</span>
                  <span class="text-emerald-600 font-bold">{{ a.pct }}%</span>
                </div>
                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                  <div class="h-full bg-emerald-500 rounded-full" :style="{ width: `${a.pct}%` }"></div>
                </div>
              </div>
              <div v-if="!rekapBulanan?.area || rekapBulanan.area.length === 0" class="text-center py-6 text-xs text-slate-400">
                Belum ada data
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
