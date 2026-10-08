<script setup>
import { computed } from 'vue';

const props = defineProps({
  submitLabel: { type: String, default: 'Simpan' },
  processing: { type: Boolean, default: false },
  tone: { type: String, default: 'blue' }, // blue | green | red
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['cancel']);

const TONE_CLASS = { blue: 'bg-blue-600 hover:bg-blue-700', green: 'bg-emerald-600 hover:bg-emerald-700', red: 'bg-red-600 hover:bg-red-700' };
const submitClass = computed(() => TONE_CLASS[props.tone]);
</script>

<template>
  <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 px-5 py-3">
    <slot />
    <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50" @click="emit('cancel')">Batal</button>
    <button type="submit" :disabled="processing || disabled" :class="['rounded-lg px-3 py-1.5 text-sm font-semibold text-white disabled:opacity-60', submitClass]">
      {{ processing ? 'Memproses...' : submitLabel }}
    </button>
  </div>
</template>
