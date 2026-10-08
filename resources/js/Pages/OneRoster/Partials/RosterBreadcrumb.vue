<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  trail: { type: Array, default: () => [] }, // [{ label, href? }] — item terakhir = halaman aktif
});
</script>

<template>
  <nav aria-label="breadcrumb" class="mb-2 flex flex-wrap items-center gap-1 text-xs text-slate-500">
    <Link href="/portal" class="text-blue-600 underline">Home</Link>
    <span>/</span>
    <span>One Power</span>
    <span>/</span>
    <span>Roster V2</span>
    <template v-for="(item, i) in trail" :key="i">
      <span>/</span>
      <Link v-if="item.href && i < trail.length - 1" :href="item.href" class="text-blue-600 underline">{{ item.label }}</Link>
      <span v-else class="text-slate-700">{{ item.label }}</span>
    </template>
  </nav>
</template>
