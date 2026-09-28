<script setup>
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

defineProps({
  page: { type: Number, required: true },
  perPage: { type: Number, required: true },
  total: { type: Number, required: true },
  label: { type: String, default: 'data' },
});

const emit = defineEmits(['change']);
</script>

<template>
  <div class="flex flex-wrap items-center justify-between gap-2">
    <p class="text-xs text-slate-500 dark:text-slate-400">
      Halaman {{ page }} dari {{ Math.max(1, Math.ceil(total / perPage)) }} ({{ total }} {{ label }})
    </p>
    <div class="flex gap-1">
      <button
        type="button"
        :disabled="page <= 1"
        class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700"
        @click="emit('change', page - 1)"
      >
        <ChevronLeft class="h-4 w-4" />
      </button>
      <button
        type="button"
        :disabled="page >= Math.ceil(total / perPage)"
        class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700"
        @click="emit('change', page + 1)"
      >
        <ChevronRight class="h-4 w-4" />
      </button>
    </div>
  </div>
</template>
