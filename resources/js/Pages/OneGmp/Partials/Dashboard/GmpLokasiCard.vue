<script setup>
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { MapPin } from 'lucide-vue-next';
import { gmpScoreColor } from '@/utils/gmpScore';

const props = defineProps({
  lokasi: { type: Array, default: () => [] },
  typeLabel: { type: String, required: true },
});

const chartData = computed(() => ({
  labels: props.lokasi.map((l) => l.lokasi),
  datasets: [{
    label: 'Rata-rata Skor',
    data: props.lokasi.map((l) => l.avg_score),
    backgroundColor: props.lokasi.map((l) => gmpScoreColor(l.avg_score)),
    borderRadius: 6,
  }],
}));

const options = {
  indexAxis: 'y',
  responsive: true,
  maintainAspectRatio: false,
  scales: { x: { beginAtZero: true, max: 100 } },
  plugins: {
    legend: { display: false },
    datalabels: { display: false },
    tooltip: { callbacks: { label: (ctx) => `${ctx.parsed.x}` } },
  },
};
</script>

<template>
  <div class="h-full rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <h6 class="mb-3 flex items-center gap-1 text-xs font-bold uppercase text-slate-800">
      <MapPin class="h-4 w-4 text-blue-600" />Skor per Lokasi — {{ typeLabel }}
    </h6>
    <div v-if="lokasi.length === 0" class="py-12 text-center text-slate-400">
      <MapPin class="mx-auto mb-1 h-10 w-10 opacity-25" />Belum ada data.
    </div>
    <div v-else class="h-[280px]"><Bar :data="chartData" :options="options" /></div>
  </div>
</template>
