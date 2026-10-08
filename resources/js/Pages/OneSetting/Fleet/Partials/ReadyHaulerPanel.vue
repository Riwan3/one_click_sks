<script setup>
import { Wrench } from 'lucide-vue-next';
import { PARKING_CLASS, PARKING_LABEL } from '@/utils/fleet';
import SidePanel from './SidePanel.vue';

defineProps({
  title: { type: String, required: true },
  haulers: { type: Array, default: () => [] },
  loaderCodes: { type: Array, default: () => [] }, // loader pada setting hari itu
});

const emit = defineEmits(['assign', 'breakdown']);

const handleAssign = (event, hauler) => {
  if (event.target.value) emit('assign', hauler, event.target.value);
  event.target.value = '';
};
</script>

<template>
  <SidePanel :title="title" :items="haulers" tone="sky" empty-text="Tidak ada hauler ready.">
    <template #default="{ items }">
      <li v-for="hauler in items" :key="hauler.code" class="flex items-center gap-1.5 py-1.5 text-xs">
        <span class="flex-1 font-bold text-slate-700">{{ hauler.code }}</span>
        <span v-if="hauler.parking_code" :class="['rounded px-1.5 py-0.5 text-[10px] font-black', PARKING_CLASS[hauler.parking_code]]" :title="PARKING_LABEL[hauler.parking_code]">{{ hauler.parking_code }}</span>
        <select :disabled="!loaderCodes.length" class="w-20 rounded border border-slate-200 px-1 py-0.5 text-[10px] text-slate-600" aria-label="Pasang ke loader" @change="handleAssign($event, hauler)">
          <option value="">{{ loaderCodes.length ? 'Pasang ke' : 'Tak ada loader' }}</option>
          <option v-for="code in loaderCodes" :key="code" :value="code">{{ code }}</option>
        </select>
        <button type="button" class="text-slate-400 hover:text-rose-600" title="Breakdown / Service" aria-label="Breakdown / Service" @click="emit('breakdown', hauler)"><Wrench class="h-3.5 w-3.5" /></button>
      </li>
    </template>
  </SidePanel>
</template>
