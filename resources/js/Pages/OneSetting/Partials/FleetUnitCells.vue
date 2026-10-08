<script setup>
import { computed } from 'vue';
import { STATUS_STYLE } from '@/utils/fleetBoard';

const props = defineProps({
  unit: { type: Object, default: null }, // { code, status }; null = sel kosong
});

const statusStyle = computed(() => STATUS_STYLE[props.unit?.status] ?? { background: '#fff' });
</script>

<template>
  <div class="flex h-[18px] items-center justify-center bg-white text-[10px] font-black text-black">{{ unit?.code }}</div>
  <div class="flex h-[18px] items-center justify-center text-[9px] font-black" :style="statusStyle">{{ unit?.status }}</div>
</template>
