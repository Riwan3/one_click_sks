<script setup>
defineProps({
  status: { type: String, required: true },
  label: { type: String, required: true },
});

const STATUS_CLASS = {
  draft: 'bg-slate-500 text-white',
  published: 'bg-emerald-600 text-white',
  archived: 'bg-slate-800 text-white',
};
</script>

<template>
  <span :class="['inline-block rounded-md px-2 py-0.5 text-[11px] font-semibold', STATUS_CLASS[status] ?? STATUS_CLASS.draft]">{{ label }}</span>
</template>
