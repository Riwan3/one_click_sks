<script setup>
import { SHIFT_CELL_CLASS } from '@/utils/rosterV2';

const LEGEND = [
  ['D', 'Day Shift (D)'], ['N', 'Night Shift (N)'], ['OFF', 'Off / Libur (OFF)'], ['CT', 'Cuti (CT)'], ['ID', 'Induksi (ID)'],
  ['IDS', 'IDS (Permit)'], ['MCU', 'MCU'], ['FU', 'FU'], ['H', 'H'], ['TR', 'Training (TR)'],
];
</script>

<template>
  <div class="mt-3 flex flex-wrap gap-3 text-xs print:hidden">
    <div v-for="[kode, label] in LEGEND" :key="kode" class="flex items-center gap-1">
      <span :class="['inline-block h-4 w-4 rounded border border-slate-300', SHIFT_CELL_CLASS[kode]]"></span> {{ label }}
    </div>
  </div>
</template>
