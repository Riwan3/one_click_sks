<script setup>
import { computed, ref, watch } from 'vue';
import { ArrowLeftRight, ChevronLeft, ChevronRight, RotateCcw, Target, ZoomIn, ZoomOut } from 'lucide-vue-next';
import { useAnalysisChart } from '@/composables/useAnalysisChart';
import { averageText, targetCellClass } from '@/utils/rosterAnalysis';
import { chartDateLabel, dayOfMonth, dayShort, isWeekend } from '@/utils/rosterV2';

const props = defineProps({
  chart: { type: Object, required: true },
  dates: { type: Array, required: true },
  canSetTarget: { type: Boolean, default: false },
});

const emit = defineEmits(['set-target']);

const canvas = ref(null);
const labels = computed(() => props.dates.map(chartDateLabel));
const summary = computed(() => averageText(props.chart));
const tableRows = computed(() => [
  { key: 'siang', label: 'Siang', values: props.chart.siang, target: props.chart.tSiang, isManual: props.chart.mSiang },
  { key: 'malam', label: 'Malam', values: props.chart.malam, target: props.chart.tMalam, isManual: props.chart.mMalam },
]);

const { isFit, isFull, refresh, reset, toggleFit, pan, zoomIn, zoomOut } = useAnalysisChart(canvas, () => ({ chart: props.chart, labels: labels.value }));

const targetLabel = (row) => {
  if (row.target === null) return '(target: -)';
  const source = props.chart.sid === undefined ? '' : (row.isManual ? ' · manual' : ' · roster');
  return `(target: ${row.target}${source})`;
};

watch(() => props.chart, refresh, { deep: true });
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-2.5">
      <span class="text-sm font-semibold text-slate-800">{{ chart.title }}</span>
      <span class="inline-flex gap-0.5">
        <button v-if="canSetTarget" type="button" title="Atur target Siang / Malam section ini" class="flex items-center gap-1 rounded border border-blue-500 px-2 py-0.5 text-xs text-blue-600 hover:bg-blue-50" @click="emit('set-target', chart)">
          <Target class="h-3.5 w-3.5" /> Target
        </button>
        <button type="button" title="Geser ke kiri" class="rounded border border-slate-300 px-1.5 py-0.5 text-slate-600 hover:bg-slate-50" @click="pan(-1)"><ChevronLeft class="h-3.5 w-3.5" /></button>
        <button type="button" title="Geser ke kanan" class="rounded border border-slate-300 px-1.5 py-0.5 text-slate-600 hover:bg-slate-50" @click="pan(1)"><ChevronRight class="h-3.5 w-3.5" /></button>
        <button type="button" title="Perbesar" class="rounded border border-slate-300 px-1.5 py-0.5 text-slate-600 hover:bg-slate-50" @click="zoomIn"><ZoomIn class="h-3.5 w-3.5" /></button>
        <button type="button" title="Perkecil" class="rounded border border-slate-300 px-1.5 py-0.5 text-slate-600 hover:bg-slate-50" @click="zoomOut"><ZoomOut class="h-3.5 w-3.5" /></button>
        <button type="button" title="Rapatkan sumbu Y ke data (garis tidak datar)" :class="['rounded border px-1.5 py-0.5', isFit ? 'border-slate-800 bg-slate-800 text-white' : 'border-slate-300 text-slate-600 hover:bg-slate-50']" @click="toggleFit"><ArrowLeftRight class="h-3.5 w-3.5 rotate-90" /></button>
        <button type="button" title="Kembali ke tampilan awal" :disabled="isFull && !isFit" class="rounded border border-slate-300 px-1.5 py-0.5 text-slate-600 hover:bg-slate-50 disabled:opacity-40" @click="reset"><RotateCcw class="h-3.5 w-3.5" /></button>
      </span>
      <span class="text-xs text-slate-500">rata-rata Siang <strong>{{ summary.siang }}</strong> · Malam <strong>{{ summary.malam }}</strong>{{ summary.targetText }}</span>
    </div>

    <div class="relative h-[260px] px-3.5 pb-2 pt-3"><canvas ref="canvas" class="cursor-crosshair"></canvas></div>

    <div class="overflow-x-auto border-t border-slate-100">
      <table class="w-max min-w-full border-separate border-spacing-0 text-xs">
        <thead>
          <tr class="bg-slate-50 text-slate-600">
            <th class="sticky left-0 z-[1] min-w-[150px] border-b border-r border-slate-100 bg-slate-50 px-2 py-1 text-left font-semibold">Jumlah orang</th>
            <th v-for="date in dates" :key="date" :title="date" :class="['min-w-[38px] border-b border-r border-slate-100 px-1.5 py-1 font-semibold', isWeekend(date) ? 'text-red-600' : '']">
              {{ dayOfMonth(date) }}<small class="block text-[10px] font-medium">{{ dayShort(date) }}</small>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in tableRows" :key="row.key">
            <td class="sticky left-0 z-[1] whitespace-nowrap border-b border-r border-slate-100 bg-white px-2 py-1 text-left font-semibold">
              {{ row.label }} <small class="font-medium text-slate-500">{{ targetLabel(row) }}</small>
            </td>
            <td v-for="(value, index) in row.values" :key="index" :class="['border-b border-r border-slate-100 px-1.5 py-1 text-center', targetCellClass(value, row.target)]">{{ value }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
