<script setup>
import { computed } from 'vue';
import { MIN_HAULER_ROWS } from '@/utils/fleetBoard';
import FleetUnitCells from './FleetUnitCells.vue';

const props = defineProps({
  title: { type: String, required: true },
  titleBackground: { type: String, required: true },
  subTitle: { type: String, default: '' },
  subBackground: { type: String, default: '#00b0f0' },
  units: { type: Array, default: () => [] }, // [{ code, status }]
  countBackground: { type: String, default: '#ffff00' },
  countColor: { type: String, default: '#000' },
});

const rowCount = computed(() => Math.max(MIN_HAULER_ROWS, props.units.length));
const countStyle = computed(() => (props.units.length ? { background: props.countBackground, color: props.countColor } : { background: '#fff', color: '#000' }));
</script>

<template>
  <section class="border-b-2 border-[#222]">
    <div class="flex min-h-[34px] items-center justify-center px-1 text-center text-[10px] font-black leading-tight text-white" :style="{ background: titleBackground }">{{ title }}</div>
    <div v-if="subTitle" class="py-0.5 text-center text-[9px] font-black text-black" :style="{ background: subBackground }">{{ subTitle }}</div>
    <div class="grid grid-cols-[1fr_26px] gap-px bg-[#777]">
      <FleetUnitCells v-for="row in rowCount" :key="row" :unit="units[row - 1] ?? null" />
    </div>
    <div class="py-0.5 text-center text-[10px] font-black" :style="countStyle">{{ units.length }} Unit</div>
  </section>
</template>
