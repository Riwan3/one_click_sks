<script setup>
import { X } from 'lucide-vue-next';

defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, required: true },
  wide: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-slate-900/50 p-4" @mousedown.self="emit('close')">
    <div :class="['my-8 w-full rounded-2xl bg-white shadow-xl', wide ? 'max-w-3xl' : 'max-w-md']">
      <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
        <h3 class="text-sm font-bold text-slate-800">{{ title }}</h3>
        <button type="button" class="text-slate-400 hover:text-slate-600" aria-label="Tutup" @click="emit('close')"><X class="h-4 w-4" /></button>
      </div>
      <div class="px-5 py-4"><slot /></div>
      <div v-if="$slots.footer" class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 px-5 py-3"><slot name="footer" /></div>
    </div>
  </div>
</template>
