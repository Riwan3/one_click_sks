<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Chart } from 'chart.js';
import { Eye, RefreshCw } from 'lucide-vue-next';

const props = defineProps({
  stats: { type: Object, required: true }, // { days: [{ date, manpower, fleet }], totals: { manpower, fleet, users } }
  month: { type: String, required: true }, // YYYY-MM
});

const emit = defineEmits(['update:month', 'change-month', 'refresh']);

const MANPOWER_COLOR = '#0ea5e9';
const FLEET_COLOR = '#6366f1';

const canvasRef = ref(null);
let chart = null;

const summary = computed(() => [
  { label: 'Dibuka Setting Manpower', value: props.stats.totals.manpower, tone: 'text-sky-600' },
  { label: 'Dibuka Setting Fleet', value: props.stats.totals.fleet, tone: 'text-indigo-600' },
  { label: 'Pengguna Non-Admin', value: props.stats.totals.users, tone: 'text-slate-800' },
]);

const chartData = computed(() => ({
  labels: props.stats.days.map((day) => Number(day.date.slice(8))),
  datasets: [
    { label: 'Setting Manpower', data: props.stats.days.map((day) => day.manpower), backgroundColor: MANPOWER_COLOR, borderRadius: 3 },
    { label: 'Setting Fleet', data: props.stats.days.map((day) => day.fleet), backgroundColor: FLEET_COLOR, borderRadius: 3 },
  ],
}));

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
  scales: {
    x: { stacked: true, grid: { display: false }, title: { display: true, text: 'Tanggal', font: { size: 10 } } },
    y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
  },
};

const handleMonthChange = (event) => {
  emit('update:month', event.target.value);
  emit('change-month');
};

onMounted(() => {
  chart = new Chart(canvasRef.value, { type: 'bar', data: chartData.value, options: chartOptions });
});

watch(chartData, (data) => {
  if (!chart) return;
  chart.data = data;
  chart.update();
});

onBeforeUnmount(() => chart?.destroy());
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-4 sm:flex-row sm:items-center">
      <div>
        <h2 class="flex items-center gap-2 text-sm font-bold text-slate-800"><Eye class="h-4 w-4 text-sky-500" /> Setting Manpower &amp; Fleet Dibuka Pengguna</h2>
        <p class="mt-0.5 text-[11px] text-slate-500">Jumlah papan setting yang dibuka di Setting Board oleh pengguna selain admin, per hari.</p>
      </div>
      <div class="flex shrink-0 items-center gap-2">
        <input :value="month" type="month" class="rounded-xl border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold" @change="handleMonthChange">
        <button type="button" class="flex items-center gap-1.5 rounded-full border border-slate-300 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-slate-50" @click="emit('refresh')">
          <RefreshCw class="h-3.5 w-3.5" /> Refresh
        </button>
      </div>
    </div>

    <div class="grid grid-cols-3 divide-x divide-slate-100 border-b border-slate-100">
      <div v-for="item in summary" :key="item.label" class="px-4 py-3 text-center">
        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ item.label }}</p>
        <p :class="['text-xl font-black', item.tone]">{{ item.value }}</p>
      </div>
    </div>

    <div class="h-72 p-4">
      <canvas ref="canvasRef" />
    </div>
  </div>
</template>
