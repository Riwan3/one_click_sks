<script setup>
import { dayHeadClass, monthAbbr } from '@/utils/rosterMatrix';

defineProps({
  days: { type: Array, required: true },
});
</script>

<template>
  <th
    v-for="(day, index) in days" :key="day.date" :title="day.date"
    :class="['sticky top-0 z-[3] min-w-[38px] border border-slate-300 px-1 py-0.5 text-center text-[11px] font-semibold leading-tight', dayHeadClass(day, index)]"
  >
    {{ String(day.d).padStart(2, '0') }}<br /><small>{{ day.label }}</small>
    <template v-if="day.ctx !== ''"><br /><small class="font-bold tracking-wide">{{ monthAbbr(day.mon) }}</small></template>
  </th>
</template>
