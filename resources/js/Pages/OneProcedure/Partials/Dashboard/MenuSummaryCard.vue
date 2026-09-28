<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowRight } from 'lucide-vue-next';

defineProps({
  title: { type: String, required: true },
  description: { type: String, required: true },
  href: { type: String, required: true },
  icon: { type: [Object, Function], required: true },
  rows: { type: Array, default: () => [] },
  emptyText: { type: String, default: 'Belum ada data' },
});
</script>

<template>
  <div class="flex flex-col rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <Link :href="href" class="group flex items-start gap-3 border-b border-slate-100 p-4">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
        <component :is="icon" class="h-5 w-5" />
      </div>
      <div class="min-w-0 flex-1">
        <h3 class="flex items-center justify-between text-sm font-bold text-slate-800 group-hover:text-rose-600">
          {{ title }}
          <ArrowRight class="h-4 w-4 opacity-0 transition-opacity group-hover:opacity-100" />
        </h3>
        <p class="text-xs text-slate-500">{{ description }}</p>
      </div>
    </Link>

    <ul class="flex-1 divide-y divide-slate-100 px-4 py-2 text-xs">
      <li v-if="rows.length === 0" class="py-3 text-center text-slate-400">{{ emptyText }}</li>
      <li v-for="row in rows" :key="row.label">
        <component :is="row.href ? Link : 'div'" :href="row.href" class="flex items-center justify-between gap-2 py-2 text-slate-600 hover:text-rose-600">
          <span class="truncate">{{ row.label }}</span>
          <span class="font-bold text-slate-800">{{ row.value }}</span>
        </component>
      </li>
    </ul>
  </div>
</template>
