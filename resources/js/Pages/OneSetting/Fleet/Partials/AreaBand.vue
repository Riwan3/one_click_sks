<script setup>
defineProps({
  name: { type: String, default: null }, // null = cluster tanpa area
});
</script>

<template>
  <div class="rounded-lg bg-slate-900 px-4 py-1.5 text-center text-sm font-black tracking-widest text-white">
    {{ name ? `AREA ${name.toUpperCase()}` : 'TANPA AREA' }}
  </div>
</template>
