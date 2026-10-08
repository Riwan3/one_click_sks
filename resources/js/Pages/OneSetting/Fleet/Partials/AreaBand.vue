<script setup>
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineProps({
  area: { type: Object, required: true }, // { name (null = tanpa area), is_editable, clusters }
});

const emit = defineEmits(['add-cluster', 'edit', 'remove']);
</script>

<template>
  <div class="flex flex-wrap items-center gap-2 rounded-lg bg-slate-900 px-4 py-1.5 text-white">
    <h2 class="flex-1 text-center text-sm font-black tracking-widest">{{ area.name ? `AREA ${area.name.toUpperCase()}` : 'TANPA AREA' }}</h2>
    <div v-if="area.is_editable" class="flex items-center gap-1">
      <button type="button" class="flex items-center gap-1 rounded bg-blue-600 px-2 py-0.5 text-[11px] font-semibold hover:bg-blue-700" @click="emit('add-cluster', area)"><Plus class="h-3 w-3" /> Tambah cluster</button>
      <button type="button" class="rounded p-1 hover:bg-slate-700" aria-label="Ubah area" @click="emit('edit', area)"><Pencil class="h-3.5 w-3.5" /></button>
      <button type="button" class="rounded p-1 hover:bg-red-600" aria-label="Hapus area" @click="emit('remove', area)"><Trash2 class="h-3.5 w-3.5" /></button>
    </div>
  </div>
</template>
