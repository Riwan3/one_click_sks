<script setup>
import { Users, CheckCircle2, Trophy, XCircle, Hourglass, RotateCcw } from 'lucide-vue-next';

defineProps({
  summary: { type: Object, required: true },
});

const TONES = {
  blue: { border: 'border-t-blue-600', text: 'text-blue-700', bg: 'bg-blue-100' },
  green: { border: 'border-t-green-600', text: 'text-green-700', bg: 'bg-green-100' },
  teal: { border: 'border-t-teal-600', text: 'text-teal-600', bg: 'bg-teal-100' },
  rose: { border: 'border-t-rose-700', text: 'text-rose-700', bg: 'bg-rose-100' },
  amber: { border: 'border-t-amber-700', text: 'text-amber-800', bg: 'bg-amber-100' },
  violet: { border: 'border-t-violet-600', text: 'text-violet-700', bg: 'bg-violet-100' },
};

const cards = [
  { key: 'total_assigned', label: 'Assigned', icon: Users, tone: TONES.blue },
  { key: 'completed', label: 'Selesai', icon: CheckCircle2, tone: TONES.green },
  { key: 'passed', label: 'Lulus', icon: Trophy, tone: TONES.teal },
  { key: 'failed', label: 'Tidak Lulus', icon: XCircle, tone: TONES.rose },
  { key: 'pending', label: 'Pending', icon: Hourglass, tone: TONES.amber },
  { key: 'retest', label: 'Retest', icon: RotateCcw, tone: TONES.violet },
];
</script>

<template>
  <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
    <div
      v-for="card in cards"
      :key="card.key"
      :class="['rounded-2xl border border-t-[3px] border-slate-200/80 bg-white p-3 text-center shadow-sm', card.tone.border]"
    >
      <div :class="['mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full', card.tone.bg]">
        <component :is="card.icon" :class="['h-5 w-5', card.tone.text]" />
      </div>
      <div :class="['text-2xl font-black leading-none', card.tone.text]">{{ summary[card.key] ?? 0 }}</div>
      <div class="mt-1 text-[11px] font-semibold text-slate-400">{{ card.label }}</div>
    </div>
  </div>
</template>
