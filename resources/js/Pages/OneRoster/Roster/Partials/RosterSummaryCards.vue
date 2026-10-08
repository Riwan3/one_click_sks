<script setup>
import { CalendarRange, FilePen, CheckCircle2, Archive } from 'lucide-vue-next';

defineProps({
  summary: { type: Object, required: true },
});

const CARDS = [
  { key: 'total', label: 'Total Roster', icon: CalendarRange, tone: 'bg-blue-50 text-blue-600' },
  { key: 'draft', label: 'Draft', icon: FilePen, tone: 'bg-slate-100 text-slate-600' },
  { key: 'published', label: 'Published', icon: CheckCircle2, tone: 'bg-emerald-50 text-emerald-600' },
  { key: 'archived', label: 'Archived', icon: Archive, tone: 'bg-rose-50 text-rose-700' },
];
</script>

<template>
  <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
    <div v-for="card in CARDS" :key="card.key" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div :class="['flex h-11 w-11 shrink-0 items-center justify-center rounded-xl', card.tone]"><component :is="card.icon" class="h-5 w-5" /></div>
      <div>
        <div class="text-2xl font-bold leading-tight text-slate-800">{{ summary[card.key] }}</div>
        <div class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">{{ card.label }}</div>
      </div>
    </div>
  </div>
</template>
