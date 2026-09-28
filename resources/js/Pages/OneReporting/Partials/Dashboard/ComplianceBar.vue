<script setup>
import { computed } from 'vue';

const props = defineProps({
  percent: { type: Number, required: true },
  showLabel: { type: Boolean, default: true },
});

/** Warna sama seperti v1: ≥80 hijau, ≥50 kuning, di bawahnya merah. */
const tone = computed(() => {
  if (props.percent >= 80) return { text: 'text-emerald-500', bar: 'from-emerald-500 to-emerald-600' };
  if (props.percent >= 50) return { text: 'text-amber-500', bar: 'from-amber-500 to-amber-600' };
  return { text: 'text-red-500', bar: 'from-red-500 to-red-600' };
});
</script>

<template>
  <div>
    <span v-if="showLabel" :class="['font-bold', tone.text]">{{ percent }}%</span>
    <div class="mt-1 h-1.5 overflow-hidden rounded bg-slate-100">
      <div :class="['h-full rounded bg-gradient-to-r', tone.bar]" :style="{ width: `${Math.min(percent, 100)}%` }" />
    </div>
  </div>
</template>
