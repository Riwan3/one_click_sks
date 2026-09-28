<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  Gauge,
  Calendar,
  Layers,
  ArrowRight,
  Filter,
  RotateCcw,
  Activity,
  BarChart3,
  TrendingUp,
  AlertTriangle,
  OctagonAlert,
  CheckCircle2,
  ShieldCheck,
  FolderOpen,
  Clock,
  CheckCheck,
  ClipboardX,
  Target,
  MapPin,
  FileSpreadsheet,
  Info
} from 'lucide-vue-next';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler,
  BarController,
  LineController
} from 'chart.js';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  Title,
  Tooltip,
  Legend,
  Filler,
  BarController,
  LineController
);

const props = defineProps({
  user: { type: Object, required: true },
  notifCounts: { type: Object, default: () => ({}) },
  menu: { type: Array, required: true },
  filters: { type: Object, required: true },
  options: { type: Object, required: true },
  cards: { type: Object, required: true },
  kpi: { type: Object, required: true },
  messages: { type: Array, default: () => [] },
  trend: { type: Object, required: true },
  aspek: { type: Object, required: true },
  lokasi: { type: Object, required: true },
  pareto: { type: Object, required: true },
});

// ==================== STATE & FILTER ====================
const state = ref({
  type: props.filters.type || 'MHR',
  year: props.filters.year || '',
  week: props.filters.week || '',
  aspekArea: props.filters.aspek_area || props.filters.type || 'MHR',
});

const applyFilters = (custom = {}) => {
  const query = {
    type: state.value.type,
    year: state.value.year || undefined,
    week: state.value.week || undefined,
    aspek_area: state.value.aspekArea || undefined,
    ...custom,
  };

  router.get('/one-gmp/monitoring', query, {
    preserveState: true,
    preserveScroll: true,
  });
};

const handleTypeChange = () => {
  state.value.aspekArea = state.value.type;
  state.value.week = '';
  applyFilters();
};

const handleYearChange = () => {
  state.value.week = '';
  applyFilters();
};

const handleWeekChange = () => {
  applyFilters();
};

const handleAspekAreaChange = () => {
  applyFilters();
};

// ==================== CHART CONSTANTS & HELPERS ====================
const TYPE_COLORS = {
  MHR: '#059669',
  MFOB: '#eab308',
  MFCO: '#8b5cf6',
  DISPOSAL: '#3730a3',
  DEWATERING: '#0284c7',
};

const ASPECT_COLORS = {
  Teknis: '#ea580c',
  Keselamatan: '#0f766e',
  Optimalisasi: '#4472c4',
  Lingkungan: '#10b981',
};

const scoreColor = (v) => {
  if (v == null) return '#cbd5e1';
  if (v >= 85) return '#059669';
  if (v >= 70) return '#eab308';
  if (v >= 50) return '#ea580c';
  return '#dc2626';
};

// ==================== CANVAS REFS & INSTANCES ====================
const trendCanvas = ref(null);
const paretoCanvas = ref(null);
const aspekCanvas = ref(null);
const lokasiCanvas = ref(null);

let trendChartInstance = null;
let paretoChartInstance = null;
let aspekChartInstance = null;
let lokasiChartInstance = null;

const destroyCharts = () => {
  if (trendChartInstance) { trendChartInstance.destroy(); trendChartInstance = null; }
  if (paretoChartInstance) { paretoChartInstance.destroy(); paretoChartInstance = null; }
  if (aspekChartInstance) { aspekChartInstance.destroy(); aspekChartInstance = null; }
  if (lokasiChartInstance) { lokasiChartInstance.destroy(); lokasiChartInstance = null; }
};

const renderTrendChart = () => {
  if (!trendCanvas.value) return;
  if (trendChartInstance) trendChartInstance.destroy();

  const tr = props.trend || { labels: [], full: [], byType: {} };
  const labels = tr.labels || [];
  if (labels.length === 0) return;

  const datasets = [
    {
      label: 'AKUMULASI (Semua Area)',
      data: tr.full || [],
      borderColor: '#0f172a',
      backgroundColor: 'rgba(15, 23, 42, 0.08)',
      borderWidth: 3,
      tension: 0.35,
      fill: true,
      spanGaps: true,
      pointRadius: 4,
      pointHoverRadius: 6,
    }
  ];

  Object.keys(tr.byType || {}).forEach((tt) => {
    datasets.push({
      label: tt,
      data: tr.byType[tt] || [],
      borderColor: TYPE_COLORS[tt] || '#94a3b8',
      backgroundColor: 'transparent',
      borderWidth: 2,
      tension: 0.35,
      fill: false,
      spanGaps: true,
      pointRadius: 2.5,
    });
  });

  trendChartInstance = new ChartJS(trendCanvas.value, {
    type: 'line',
    data: { labels, datasets },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          ticks: { callback: (v) => `${v}%`, font: { size: 11, weight: 'bold' } },
          grid: { color: '#f1f5f9' },
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 10, weight: 'bold' }, color: '#64748b' },
        }
      },
      plugins: {
        legend: {
          position: 'bottom',
          labels: { usePointStyle: true, boxWidth: 8, font: { size: 11, weight: 'bold' } },
        },
        tooltip: {
          callbacks: {
            label: (c) => ` ${c.dataset.label}: ${c.parsed.y != null ? `${c.parsed.y}%` : '-'}`
          }
        }
      }
    }
  });
};

const renderParetoChart = () => {
  if (!paretoCanvas.value) return;
  if (paretoChartInstance) paretoChartInstance.destroy();

  const par = props.pareto || { labels: [], counts: [], cumulative: [] };
  const labels = par.labels || [];
  if (labels.length === 0) return;

  paretoChartInstance = new ChartJS(paretoCanvas.value, {
    data: {
      labels,
      datasets: [
        {
          type: 'bar',
          label: 'Jumlah Temuan',
          data: par.counts || [],
          backgroundColor: '#059669',
          borderRadius: 6,
          maxBarThickness: 42,
          yAxisID: 'y',
          order: 2,
        },
        {
          type: 'line',
          label: '% Kumulatif',
          data: par.cumulative || [],
          borderColor: '#dc2626',
          backgroundColor: '#dc2626',
          borderWidth: 2.5,
          tension: 0.25,
          pointRadius: 3.5,
          pointBackgroundColor: '#dc2626',
          yAxisID: 'y1',
          order: 1,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      scales: {
        y: {
          beginAtZero: true,
          position: 'left',
          title: { display: true, text: 'Jumlah Temuan', font: { size: 11, weight: 'bold' } },
          ticks: { precision: 0, font: { size: 11 } },
          grid: { color: '#f1f5f9' },
        },
        y1: {
          beginAtZero: true,
          max: 100,
          position: 'right',
          grid: { drawOnChartArea: false },
          title: { display: true, text: '% Kumulatif', font: { size: 11, weight: 'bold' } },
          ticks: { callback: (v) => `${v}%`, font: { size: 11 } },
        },
        x: {
          ticks: { autoSkip: false, maxRotation: 45, minRotation: 0, font: { size: 10, weight: 'semibold' } },
          grid: { display: false },
        }
      },
      plugins: {
        legend: {
          position: 'bottom',
          labels: { usePointStyle: true, boxWidth: 8, font: { size: 11, weight: 'bold' } },
        },
        tooltip: {
          callbacks: {
            label: (c) => c.dataset.type === 'line' ? ` Kumulatif: ${c.parsed.y}%` : ` ${c.parsed.y} temuan`
          }
        }
      }
    }
  });
};

const renderAspekChart = () => {
  if (!aspekCanvas.value) return;
  if (aspekChartInstance) aspekChartInstance.destroy();

  const asp = props.aspek || { labels: [], values: [] };
  const labels = asp.labels || [];
  if (labels.length === 0) return;

  aspekChartInstance = new ChartJS(aspekCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Skor Aspek (%)',
          data: asp.values || [],
          backgroundColor: labels.map((l) => ASPECT_COLORS[l] || '#4472c4'),
          borderRadius: 8,
          maxBarThickness: 54,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          ticks: { callback: (v) => `${v}%`, font: { size: 11 } },
          grid: { color: '#f1f5f9' },
        },
        x: {
          grid: { display: false },
          ticks: { font: { size: 11, weight: 'bold' } },
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (c) => ` Skor: ${c.parsed.y}%`
          }
        }
      }
    }
  });
};

const renderLokasiChart = () => {
  if (!lokasiCanvas.value) return;
  if (lokasiChartInstance) lokasiChartInstance.destroy();

  const lok = props.lokasi || { labels: [], values: [] };
  const labels = lok.labels || [];
  if (labels.length === 0) return;

  lokasiChartInstance = new ChartJS(lokasiCanvas.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Skor (%)',
          data: lok.values || [],
          backgroundColor: (lok.values || []).map(scoreColor),
          borderRadius: 6,
          maxBarThickness: 24,
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        x: {
          beginAtZero: true,
          max: 100,
          ticks: { callback: (v) => `${v}%`, font: { size: 11 } },
          grid: { color: '#f1f5f9' },
        },
        y: {
          grid: { display: false },
          ticks: { font: { size: 11, weight: 'semibold' } },
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (c) => ` Skor: ${c.parsed.x}%`
          }
        }
      }
    }
  });
};

const renderAllCharts = () => {
  nextTick(() => {
    renderTrendChart();
    renderParetoChart();
    renderAspekChart();
    renderLokasiChart();
  });
};

onMounted(() => {
  renderAllCharts();
});

watch(
  () => [props.trend, props.pareto, props.aspek, props.lokasi],
  () => {
    renderAllCharts();
  },
  { deep: true }
);

onBeforeUnmount(() => {
  destroyCharts();
});

// Dynamic height calculation for location chart
const dynamicLokasiHeight = computed(() => {
  const count = props.lokasi?.labels?.length || 0;
  return Math.max(300, count * 36) + 'px';
});
</script>

<template>
  <AppLayout :user="user" :notifCounts="notifCounts" :menu="menu">
    <Head title="Monitoring GMP - One-GMP" />

    <div class="space-y-6 pb-12">
      <!-- HERO HEADER -->
      <div
        class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-950 via-slate-900 to-teal-950 p-6 sm:p-8 text-white shadow-xl border border-emerald-800/40"
      >
        <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none" />
        <div class="absolute -left-16 -bottom-16 h-64 w-64 rounded-full bg-teal-500/10 blur-3xl pointer-events-none" />

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="space-y-2">
            <div class="inline-flex items-center gap-2 rounded-full bg-emerald-500/20 px-3 py-1 text-xs font-semibold text-emerald-300 border border-emerald-500/30">
              <Gauge class="h-3.5 w-3.5" />
              <span>Good Mining Practice &bull; Monitoring Evaluasi V2</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-3">
              Dashboard Monitoring GMP
              <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/30 text-emerald-200 border border-emerald-400/30 font-bold uppercase tracking-wider">
                v2
              </span>
            </h1>
            <p class="text-xs sm:text-sm text-emerald-100/80 max-w-2xl leading-relaxed">
              Monitoring menyeluruh capaian Good Mining Practice: analisis trend akumulasi, evaluasi skor per aspek, dan performa inspeksi per lokasi operasional.
            </p>
          </div>

          <div class="flex items-center gap-2.5">
            <button
              type="button"
              @click="applyFilters()"
              class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2.5 text-xs font-bold shadow-lg shadow-black/20 transition hover:-translate-y-0.5"
            >
              <RotateCcw class="h-4 w-4" />
              <span>Segarkan Data</span>
            </button>
          </div>
        </div>
      </div>

      <!-- FILTER PANEL -->
      <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-12 gap-3 items-end">
          <div class="lg:col-span-4 col-span-2">
            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">
              Tipe GMP (Area)
            </label>
            <select
              v-model="state.type"
              @change="handleTypeChange"
              class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
            >
              <option v-for="t in options.types" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>

          <div class="lg:col-span-3 col-span-1">
            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">
              Tahun
            </label>
            <select
              v-model="state.year"
              @change="handleYearChange"
              class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
            >
              <option value="">Semua Tahun</option>
              <option v-for="y in options.years" :key="y" :value="String(y)">{{ y }}</option>
            </select>
          </div>

          <div class="lg:col-span-3 col-span-1">
            <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">
              Week
            </label>
            <select
              v-model="state.week"
              @change="handleWeekChange"
              class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
            >
              <option value="">Semua Week</option>
              <option v-for="w in options.weeks" :key="w" :value="w">{{ w }}</option>
            </select>
          </div>

          <div class="lg:col-span-2 col-span-2">
            <button
              type="button"
              @click="applyFilters()"
              class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white p-2.5 text-xs font-bold shadow-sm transition"
            >
              <Filter class="h-4 w-4" />
              <span>Terapkan</span>
            </button>
          </div>
        </div>
      </div>

      <!-- 4 STATS CARDS -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Penilaian -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm flex items-center gap-4">
          <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-700 shrink-0">
            <FileSpreadsheet class="h-6 w-6" />
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black text-slate-800 leading-none">{{ cards.total_penilaian }}</div>
            <div class="text-xs font-bold uppercase tracking-wide text-slate-400 mt-1">Total Penilaian</div>
          </div>
        </div>

        <!-- Rata-rata Skor -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm flex items-center gap-4">
          <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shrink-0">
            <TrendingUp class="h-6 w-6" />
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black text-slate-800 leading-none">{{ cards.avg_skor }}%</div>
            <div class="text-xs font-bold uppercase tracking-wide text-slate-400 mt-1">Rata-rata Skor</div>
          </div>
        </div>

        <!-- Jumlah Lokasi -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm flex items-center gap-4">
          <div class="h-12 w-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
            <MapPin class="h-6 w-6" />
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black text-slate-800 leading-none">{{ cards.jumlah_lokasi }}</div>
            <div class="text-xs font-bold uppercase tracking-wide text-slate-400 mt-1">Jumlah Lokasi</div>
          </div>
        </div>

        <!-- Lokasi Critical (<50) -->
        <div class="rounded-2xl border border-rose-200 bg-rose-50/40 p-5 shadow-sm flex items-center gap-4">
          <div class="h-12 w-12 rounded-2xl bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
            <OctagonAlert class="h-6 w-6" />
          </div>
          <div>
            <div class="text-2xl sm:text-3xl font-black text-rose-700 leading-none">{{ cards.critical_lokasi }}</div>
            <div class="text-xs font-bold uppercase tracking-wide text-rose-600 mt-1">Lokasi Critical (&lt;50)</div>
          </div>
        </div>
      </div>

      <!-- KPI PENGENDALIAN SECTION -->
      <div class="space-y-3">
        <div class="flex items-center gap-2">
          <ShieldCheck class="h-5 w-5 text-emerald-600" />
          <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider">KPI Pengendalian Operasional</h2>
        </div>

        <!-- Messages / Alerts -->
        <div v-if="messages && messages.length > 0" class="flex flex-col gap-2">
          <div
            v-for="(m, idx) in messages"
            :key="idx"
            :class="[
              'rounded-xl px-4 py-3 text-xs font-bold flex items-center gap-2 border',
              m.level === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-900' : '',
              m.level === 'danger' ? 'bg-rose-50 border-rose-200 text-rose-900' : '',
              m.level === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : '',
              m.level === 'info' ? 'bg-blue-50 border-blue-200 text-blue-900' : '',
            ]"
          >
            <AlertTriangle v-if="m.level === 'warning'" class="h-4 w-4 shrink-0 text-amber-600" />
            <OctagonAlert v-else-if="m.level === 'danger'" class="h-4 w-4 shrink-0 text-rose-600" />
            <CheckCircle2 v-else-if="m.level === 'success'" class="h-4 w-4 shrink-0 text-emerald-600" />
            <Info v-else class="h-4 w-4 shrink-0 text-blue-600" />
            <span>{{ m.text }}</span>
          </div>
        </div>

        <!-- 8 KPI Cards Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
          <!-- Nilai GMP Aktual -->
          <div class="rounded-2xl border-t-4 border-t-emerald-600 border border-slate-200/80 bg-white p-3.5 shadow-sm">
            <div class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase text-slate-400 mb-1">
              <Gauge class="h-3.5 w-3.5" />
              <span>Aktual</span>
            </div>
            <div :class="['text-xl font-black leading-none', (kpi.aktual ?? 0) >= (kpi.target ?? 95) ? 'text-emerald-600' : 'text-rose-600']">
              {{ kpi.aktual }}%
            </div>
            <div class="text-[10px] text-slate-400 font-semibold mt-1">periode berjalan</div>
          </div>

          <!-- Target GMP -->
          <div class="rounded-2xl border-t-4 border-t-emerald-600 border border-slate-200/80 bg-white p-3.5 shadow-sm">
            <div class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase text-slate-400 mb-1">
              <Target class="h-3.5 w-3.5" />
              <span>Target</span>
            </div>
            <div class="text-xl font-black text-emerald-600 leading-none">&ge;{{ kpi.target }}%</div>
            <div class="text-[10px] text-slate-400 font-semibold mt-1">zona hijau</div>
          </div>

          <!-- Area di Bawah Target -->
          <div class="rounded-2xl border-t-4 border-t-rose-600 border border-slate-200/80 bg-white p-3.5 shadow-sm">
            <div class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase text-slate-400 mb-1">
              <AlertTriangle class="h-3.5 w-3.5" />
              <span>Below</span>
            </div>
            <div :class="['text-xl font-black leading-none', (kpi.below_target ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600']">
              {{ kpi.below_target }}
            </div>
            <div class="text-[10px] text-slate-400 font-semibold mt-1">lokasi &lt; target</div>
          </div>

          <!-- Temuan Open -->
          <div class="rounded-2xl border-t-4 border-t-blue-600 border border-slate-200/80 bg-white p-3.5 shadow-sm">
            <div class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase text-slate-400 mb-1">
              <FolderOpen class="h-3.5 w-3.5" />
              <span>Open</span>
            </div>
            <div class="text-xl font-black text-blue-600 leading-none">{{ kpi.temuan_open }}</div>
            <div class="text-[10px] text-slate-400 font-semibold mt-1">area {{ state.type }}</div>
          </div>

          <!-- Temuan Overdue -->
          <div class="rounded-2xl border-t-4 border-t-rose-600 border border-slate-200/80 bg-white p-3.5 shadow-sm">
            <div class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase text-slate-400 mb-1">
              <Clock class="h-3.5 w-3.5" />
              <span>Overdue</span>
            </div>
            <div :class="['text-xl font-black leading-none', (kpi.temuan_overdue ?? 0) > 0 ? 'text-rose-700' : 'text-slate-700']">
              {{ kpi.temuan_overdue }}
            </div>
            <div class="text-[10px] text-slate-400 font-semibold mt-1">lewat deadline</div>
          </div>

          <!-- Closure Rate -->
          <div class="rounded-2xl border-t-4 border-t-emerald-600 border border-slate-200/80 bg-white p-3.5 shadow-sm">
            <div class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase text-slate-400 mb-1">
              <CheckCheck class="h-3.5 w-3.5" />
              <span>Closure</span>
            </div>
            <div class="text-xl font-black text-emerald-600 leading-none">{{ kpi.closure_rate }}%</div>
            <div class="text-[10px] text-slate-400 font-semibold mt-1">tuntas ditutup</div>
          </div>

          <!-- Penilaian Belum Dilakukan -->
          <div class="rounded-2xl border-t-4 border-t-amber-600 border border-slate-200/80 bg-white p-3.5 shadow-sm">
            <div class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase text-slate-400 mb-1">
              <ClipboardX class="h-3.5 w-3.5" />
              <span>Belum</span>
            </div>
            <div :class="['text-xl font-black leading-none', (kpi.belum_dinilai ?? 0) > 0 ? 'text-amber-600' : 'text-emerald-600']">
              {{ kpi.belum_dinilai }}
            </div>
            <div class="text-[10px] text-slate-400 font-semibold mt-1 truncate" :title="kpi.target_week">
              {{ kpi.target_week || 'inspeksi' }}
            </div>
          </div>

          <!-- Peningkatan vs Minggu Lalu -->
          <div class="rounded-2xl border-t-4 border-t-indigo-600 border border-slate-200/80 bg-white p-3.5 shadow-sm">
            <div class="flex items-center gap-1.5 text-[10px] font-extrabold uppercase text-slate-400 mb-1">
              <TrendingUp class="h-3.5 w-3.5" />
              <span>Trend W-o-W</span>
            </div>
            <div
              :class="[
                'text-xl font-black leading-none',
                kpi.improvement == null ? 'text-slate-400' : kpi.improvement > 0 ? 'text-emerald-600' : kpi.improvement < 0 ? 'text-rose-600' : 'text-slate-600'
              ]"
            >
              {{ kpi.improvement != null ? (kpi.improvement > 0 ? `+${kpi.improvement}` : kpi.improvement) : '-' }}
            </div>
            <div class="text-[10px] text-slate-400 font-semibold mt-1 truncate" :title="kpi.prev_week ? `vs ${kpi.prev_week}` : 'vs minggu lalu'">
              {{ kpi.prev_week ? `vs ${kpi.prev_week}` : 'vs minggu lalu' }}
            </div>
          </div>
        </div>
      </div>

      <!-- CHART 1: TREND AKUMULASI GMP (ALL AREAS) -->
      <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-4 sm:px-6">
          <div class="flex items-center gap-2">
            <Activity class="h-5 w-5 text-emerald-600" />
            <div>
              <h3 class="text-sm font-black text-slate-800">Trend Akumulasi GMP (Semua Area)</h3>
              <p class="text-[11px] text-slate-400">Rata-rata persentase skor inspeksi per week</p>
            </div>
          </div>
        </div>
        <div class="p-4 sm:p-6">
          <div class="relative h-[340px] w-full">
            <div v-if="!trend.labels || trend.labels.length === 0" class="flex h-full items-center justify-center text-slate-400 text-xs font-semibold">
              Belum ada data trend untuk periode ini.
            </div>
            <canvas v-else ref="trendCanvas" />
          </div>
        </div>
      </div>

      <!-- CHART 2: PARETO KETIDAKSESUAIAN GMP -->
      <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-4 sm:px-6 flex-wrap gap-2">
          <div class="flex items-center gap-2">
            <BarChart3 class="h-5 w-5 text-emerald-600" />
            <div>
              <h3 class="text-sm font-black text-slate-800">Pareto Ketidaksesuaian GMP</h3>
              <p class="text-[11px] text-slate-400">Fokus masalah GMP terbesar (jumlah temuan ketidaksesuaian standar) &bull; semua area</p>
            </div>
          </div>

          <!-- Pareto Meta Badges -->
          <div class="flex items-center gap-2 flex-wrap text-xs">
            <div v-if="pareto.top3 && pareto.top3.length > 0" class="flex items-center gap-1.5 flex-wrap">
              <span class="text-[10px] font-extrabold uppercase text-slate-400">Top Masalah:</span>
              <span
                v-for="(t, idx) in pareto.top3"
                :key="idx"
                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold"
              >
                <span>{{ idx + 1 }}. {{ t.label }}</span>
                <span class="px-1.5 py-0.2 rounded-full bg-rose-600 text-white text-[10px]">{{ t.count }}</span>
              </span>
            </div>

            <div v-if="pareto.top_area" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-extrabold">
              <MapPin class="h-3.5 w-3.5 text-emerald-600" />
              <span>Area Terbanyak: {{ pareto.top_area.area }} ({{ pareto.top_area.count }} temuan)</span>
            </div>
          </div>
        </div>

        <div class="p-4 sm:p-6">
          <div class="relative h-[360px] w-full">
            <div v-if="!pareto.labels || pareto.labels.length === 0" class="flex h-full items-center justify-center text-slate-400 text-xs font-semibold">
              Belum ada data temuan untuk analisis Pareto.
            </div>
            <canvas v-else ref="paretoCanvas" />
          </div>
        </div>
      </div>

      <!-- ROW: CHART 3 (ASPEK) & CHART 4 (LOKASI) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Skor per Aspek GMP -->
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="flex items-center justify-between border-b border-slate-100 p-4 sm:px-6">
            <div class="flex items-center gap-2">
              <Layers class="h-5 w-5 text-emerald-600" />
              <div>
                <h3 class="text-sm font-black text-slate-800">Skor per Aspek GMP</h3>
                <p class="text-[11px] text-slate-400">Pencapaian berdasarkan 4 aspek operasional</p>
              </div>
            </div>

            <select
              v-model="state.aspekArea"
              @change="handleAspekAreaChange"
              class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
            >
              <option v-for="t in options.types" :key="t" :value="t">{{ t }}</option>
            </select>
          </div>

          <div class="p-4 sm:p-6 flex-1 flex items-center justify-center">
            <div class="relative h-[320px] w-full">
              <div v-if="!aspek.labels || aspek.labels.length === 0" class="flex h-full items-center justify-center text-slate-400 text-xs font-semibold">
                Belum ada data aspek untuk area terpilih.
              </div>
              <canvas v-else ref="aspekCanvas" />
            </div>
          </div>
        </div>

        <!-- Skor per Lokasi -->
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden flex flex-col">
          <div class="flex items-center justify-between border-b border-slate-100 p-4 sm:px-6">
            <div class="flex items-center gap-2">
              <MapPin class="h-5 w-5 text-emerald-600" />
              <div>
                <h3 class="text-sm font-black text-slate-800">Skor per Lokasi</h3>
                <p class="text-[11px] text-slate-400">Peringkat performa lokasi di area {{ state.type }}</p>
              </div>
            </div>

            <span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-extrabold text-emerald-700 border border-emerald-200">
              {{ state.type }}
            </span>
          </div>

          <div class="p-4 sm:p-6 flex-1 overflow-y-auto max-h-[370px]">
            <div class="relative w-full" :style="{ height: dynamicLokasiHeight }">
              <div v-if="!lokasi.labels || lokasi.labels.length === 0" class="flex h-full items-center justify-center text-slate-400 text-xs font-semibold">
                Belum ada data lokasi untuk area terpilih.
              </div>
              <canvas v-else ref="lokasiCanvas" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
