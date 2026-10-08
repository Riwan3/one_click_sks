<script setup>
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';

defineProps({
  cluster: { type: Object, required: true }, // { name, area, is_editable, loaders }
});

const emit = defineEmits(['edit', 'remove', 'add-loader']);
</script>

<template>
  <section class="min-w-0 space-y-2 rounded-xl border border-slate-200/80 bg-slate-50 p-2">
    <div class="flex flex-wrap items-center gap-2 rounded-lg bg-slate-700 px-3 py-1.5 text-white">
      <h3 class="text-xs font-extrabold tracking-wide">
        {{ cluster.name }}
        <span class="font-semibold text-slate-300">· {{ cluster.loaders.length }} loader</span>
      </h3>
      <div v-if="cluster.is_editable" class="ml-auto flex items-center gap-1">
        <button type="button" class="flex items-center gap-1 rounded bg-blue-600 px-2 py-0.5 text-[11px] font-semibold hover:bg-blue-700" @click="emit('add-loader', cluster)"><Plus class="h-3 w-3" /> Tambah loader</button>
        <button type="button" class="rounded p-1 hover:bg-slate-600" aria-label="Ubah cluster" @click="emit('edit', cluster)"><Pencil class="h-3.5 w-3.5" /></button>
        <button type="button" class="rounded p-1 hover:bg-red-600" aria-label="Hapus cluster" @click="emit('remove', cluster)"><Trash2 class="h-3.5 w-3.5" /></button>
      </div>
    </div>

    <p v-if="!cluster.loaders.length" class="rounded-lg border border-dashed border-slate-300 px-3 py-4 text-center text-xs italic text-slate-400">Cluster belum punya loader.</p>
    <div v-else class="grid grid-cols-[repeat(auto-fill,minmax(max(8.5rem,calc(20%-0.5rem)),1fr))] gap-2">
      <slot />
    </div>
  </section>
</template>
