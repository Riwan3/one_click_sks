<script setup>
import { UNIT_STATUS_CLASS } from '@/utils/fleetBoard';

defineProps({
  unit: { type: Object, required: true }, // { code, status }
});
</script>

<template>
  <span class="inline-flex items-center justify-center gap-0.5">
    {{ unit.code }}
    <span v-if="unit.status" :class="['inline-flex h-3 w-3 items-center justify-center rounded-full text-[6px] font-black leading-none', UNIT_STATUS_CLASS[unit.status] ?? 'bg-slate-500 text-white']">{{ unit.status }}</span>
  </span>
</template>
