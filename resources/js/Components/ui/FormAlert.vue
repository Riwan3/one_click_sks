<script setup>
defineProps({
  message: { type: String, default: '' },
  tone: { type: String, default: 'danger' }, // danger | success | info | warning
});

const TONE_CLASS = {
  danger: 'border-red-200 bg-red-50 text-red-700',
  success: 'border-emerald-200 bg-emerald-50 text-emerald-700',
  info: 'border-sky-200 bg-sky-50 text-sky-700',
  warning: 'border-amber-200 bg-amber-50 text-amber-800',
};
</script>

<template>
  <div v-if="message" :class="['rounded-lg border px-3 py-2 text-xs', TONE_CLASS[tone]]">{{ message }}</div>
</template>
