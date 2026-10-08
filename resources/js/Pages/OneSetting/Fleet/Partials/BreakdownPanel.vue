<script setup>
import { Undo2 } from 'lucide-vue-next';
import { PARKING_CLASS, PARKING_LABEL } from '@/utils/fleet';
import SidePanel from './SidePanel.vue';

defineProps({
  units: { type: Array, default: () => [] }, // [{ code, type, parking_code }]
});

const emit = defineEmits(['return']);
</script>

<template>
  <SidePanel title="Unit Breakdown / Service" :items="units" tone="rose" empty-text="Tidak ada unit breakdown.">
    <template #default="{ items }">
      <li v-for="unit in items" :key="unit.code" class="flex items-center gap-1.5 py-1.5 text-xs">
        <span class="flex-1 font-bold text-slate-700">{{ unit.code }}<span v-if="unit.type === 'loader'" class="ml-1 text-[10px] font-semibold text-slate-400">digger</span></span>
        <span v-if="unit.parking_code" :class="['rounded px-1.5 py-0.5 text-[10px] font-black', PARKING_CLASS[unit.parking_code]]" :title="PARKING_LABEL[unit.parking_code]">{{ unit.parking_code }}</span>
        <button type="button" class="flex items-center gap-0.5 rounded border border-emerald-600 px-2 py-0.5 text-[10px] font-semibold text-emerald-700 hover:bg-emerald-50" @click="emit('return', unit)"><Undo2 class="h-3 w-3" /> Kembalikan ke Ready</button>
      </li>
    </template>
  </SidePanel>
</template>
