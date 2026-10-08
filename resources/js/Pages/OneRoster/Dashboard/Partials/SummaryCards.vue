<script setup>
import { Users, Sun, MoonStar, Plane, PauseCircle, MoreHorizontal } from 'lucide-vue-next';

defineProps({
  total: { type: Object, required: true },
});

const CARDS = [
  { key: 'orang', label: 'Total Orang', icon: Users, tone: 'bg-blue-50 text-blue-500' },
  { key: 'siang', label: 'Siang (D)', icon: Sun, tone: 'bg-amber-100 text-amber-600' },
  { key: 'malam', label: 'Malam (N)', icon: MoonStar, tone: 'bg-indigo-100 text-indigo-600' },
  { key: 'cuti', label: 'Sedang Cuti', icon: Plane, tone: 'bg-purple-100 text-purple-600' },
  { key: 'off', label: 'OFF', icon: PauseCircle, tone: 'bg-red-100 text-red-600' },
  { key: 'lain', label: 'Lainnya', icon: MoreHorizontal, tone: 'bg-slate-200 text-slate-600' },
];
</script>

<template>
  <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
    <div v-for="card in CARDS" :key="card.key" class="flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
      <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl', card.tone]">
        <component :is="card.icon" class="h-5 w-5" />
      </div>
      <div class="min-w-0">
        <p class="text-2xl font-black leading-none text-slate-800">{{ total[card.key] }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ card.label }}</p>
      </div>
    </div>
  </div>
</template>
