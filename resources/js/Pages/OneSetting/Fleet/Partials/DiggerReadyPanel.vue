<script setup>
import { Plus, Wrench } from 'lucide-vue-next';
import SidePanel from './SidePanel.vue';

defineProps({
  loaders: { type: Array, default: () => [] }, // master loader aktif yang belum ada di setting hari itu
});

const emit = defineEmits(['add', 'breakdown']);
</script>

<template>
  <SidePanel title="Unit Digger Ready" :items="loaders" tone="slate" empty-text="Semua digger sudah dipakai.">
    <template #default="{ items }">
      <li v-for="loader in items" :key="loader.code" class="flex items-center gap-1.5 py-1.5 text-xs">
        <span class="flex-1">
          <span class="font-bold text-slate-700">{{ loader.code }}</span>
          <span v-if="loader.category" class="block text-[10px] text-slate-400">{{ loader.category }}</span>
        </span>
        <button type="button" class="flex items-center gap-0.5 rounded bg-blue-600 px-2 py-0.5 text-[10px] font-semibold text-white hover:bg-blue-700" @click="emit('add', loader)"><Plus class="h-3 w-3" /> Tambah</button>
        <button type="button" class="text-slate-400 hover:text-rose-600" title="Breakdown / Service" aria-label="Breakdown / Service" @click="emit('breakdown', loader)"><Wrench class="h-3.5 w-3.5" /></button>
      </li>
    </template>
  </SidePanel>
</template>
