<script setup>
import { X } from 'lucide-vue-next';

defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, required: true },
  size: { type: String, default: 'md' }, // sm | md | lg | xl
});

const emit = defineEmits(['close']);

const SIZE_CLASS = { sm: 'max-w-sm', md: 'max-w-md', lg: 'max-w-2xl', xl: 'max-w-5xl' };
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="fixed inset-0 z-[60] flex items-start justify-center overflow-y-auto bg-slate-900/50 p-4" @mousedown.self="emit('close')">
      <div :class="['my-8 w-full rounded-2xl bg-white shadow-xl', SIZE_CLASS[size]]">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
          <h3 class="text-sm font-bold text-slate-800">{{ title }}</h3>
          <button type="button" class="text-slate-400 hover:text-slate-600" aria-label="Tutup" @click="emit('close')"><X class="h-4 w-4" /></button>
        </div>
        <slot />
      </div>
    </div>
  </Teleport>
</template>
