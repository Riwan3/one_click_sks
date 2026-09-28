<script setup>
import { computed } from 'vue';
import { Line } from 'vue-chartjs';

const props = defineProps({
  weeks: { type: Array, default: () => [] },
  series: { type: Object, required: true },
});

/** Urutan, label & warna kartu sama seperti Trend GMP v1. */
const TYPE_CARDS = [
  { key: 'MFOB', title: 'OB', color: '#f59e0b' },
  { key: 'MFCO', title: 'COAL', color: '#10b981' },
  { key: 'MHR', title: 'HRM', color: '#8b5cf6' },
  { key: 'DEWATERING', title: 'DEWATERING', color: '#3b82f6' },
  { key: 'DISPOSAL', title: 'DISPOSAL', color: '#ef4444' },
];

/** Grid 6 kolom di layar sedang: 2 kartu pertama lebar 3, sisanya lebar 2 (2 + 3 kartu); 5 sejajar di layar lebar. */
const CARD_SPANS = ['sm:col-span-3', 'sm:col-span-3', 'sm:col-span-2', 'sm:col-span-2', 'sm:col-span-2'];

/** Nilai week terakhir (kronologis) yang punya data. */
const latestOf = (key) => {
  const week = [...props.weeks].reverse().find((w) => props.series[key]?.[w] !== undefined);
  return week ? props.series[key][week] : 0;
};

/** Titik per week (week tanpa data = 0, sama seperti v1). */
const pointsOf = (key) => props.weeks.map((w) => props.series[key]?.[w] ?? 0);

const lineData = (key, color, fillColor) => ({
  labels: props.weeks,
  datasets: [{
    label: key === 'FULL' ? 'Akumulasi GMP (%)' : `${key} (%)`,
    data: pointsOf(key),
    borderColor: color,
    backgroundColor: fillColor,
    borderWidth: 2,
    fill: true,
    tension: 0.4,
    pointRadius: key === 'FULL' ? 4 : 2,
    pointBackgroundColor: color,
  }],
});

const fullOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true, max: 100 }, x: { ticks: { font: { size: 10 } } } },
};

const smallOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { y: { beginAtZero: true, max: 100, ticks: { font: { size: 9 } } }, x: { display: false } },
};

const typeCards = computed(() => TYPE_CARDS.map((card, idx) => ({
  ...card,
  span: CARD_SPANS[idx],
  latest: latestOf(card.key),
  data: lineData(card.key, card.color, `${card.color}1A`),
})));

const format = (value) => `${Number(value).toFixed(2)}%`;
</script>

<template>
  <div class="space-y-4">
    <div class="rounded-2xl bg-gradient-to-br from-blue-900 to-blue-500 p-5 text-center text-white shadow-sm">
      <h4 class="mb-1 text-lg font-bold">TOTAL AKUMULASI GMP (FULL)</h4>
      <div class="mb-3 text-4xl font-bold md:text-5xl">{{ format(latestOf('FULL')) }}</div>
      <div class="h-[300px] rounded-xl bg-white p-4">
        <p v-if="weeks.length === 0" class="flex h-full items-center justify-center text-sm text-slate-400">Belum ada data trend GMP pada tahun ini.</p>
        <Line v-else :data="lineData('FULL', '#1e3a8a', 'rgba(59, 130, 246, 0.1)')" :options="fullOptions" />
      </div>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-6 xl:grid-cols-5">
      <div
        v-for="card in typeCards"
        :key="card.key"
        :class="['flex flex-col rounded-xl border-b-4 bg-white p-3 text-center shadow-sm xl:col-span-1', card.span]"
        :style="{ borderBottomColor: card.color }"
      >
        <h6 class="mb-1 text-sm font-bold text-slate-500">{{ card.title }}</h6>
        <h3 class="mb-3 text-2xl font-bold" :style="{ color: card.color }">{{ format(card.latest) }}</h3>
        <div class="mt-auto h-[120px]"><Line :data="card.data" :options="smallOptions" /></div>
      </div>
    </div>
  </div>
</template>
