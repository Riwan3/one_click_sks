<script setup>
import { router } from '@inertiajs/vue3';
import { RefreshCw, ZoomIn } from 'lucide-vue-next';

const props = defineProps({
  filters: { type: Object, required: true },
  rosters: { type: Array, required: true },
  statusText: { type: String, required: true },
  isFailed: { type: Boolean, default: false },
});

const DAY_OPTIONS = [14, 25, 31, 62];

const handleVisit = (changes) => {
  router.get('/one-roster/analisis-harian', { ...props.filters, ...changes }, { preserveScroll: true });
};

const handleToday = () => {
  router.get('/one-roster/analisis-harian', { roster: props.filters.roster, days: props.filters.days }, { preserveScroll: true });
};
</script>

<template>
  <div class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
    <div>
      <label class="mb-1 block text-xs font-semibold text-slate-600">Roster</label>
      <select :value="filters.roster" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm" @change="handleVisit({ roster: Number($event.target.value) })">
        <option :value="0">Semua roster ({{ rosters.length }})</option>
        <option v-for="roster in rosters" :key="roster.id" :value="roster.id">{{ roster.roster_group }} ({{ roster.period_start.slice(0, 4) }})</option>
      </select>
    </div>
    <div>
      <label class="mb-1 block text-xs font-semibold text-slate-600">Mulai tanggal</label>
      <div class="flex gap-1">
        <input type="date" :value="filters.start" class="rounded-lg border border-slate-300 px-2 py-1 text-sm" @change="handleVisit({ start: $event.target.value })" />
        <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50" @click="handleToday">Hari ini</button>
      </div>
    </div>
    <div>
      <label class="mb-1 block text-xs font-semibold text-slate-600">Rentang</label>
      <select :value="filters.days" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm" @change="handleVisit({ days: Number($event.target.value) })">
        <option v-for="days in DAY_OPTIONS" :key="days" :value="days">{{ days }} hari</option>
      </select>
    </div>
    <div class="space-y-1 text-xs text-slate-500 lg:ml-auto">
      <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
        <span class="flex items-center gap-1"><span class="inline-block h-2.5 w-2.5 rounded-full bg-[#F59E0B]"></span> Siang</span>
        <span class="flex items-center gap-1"><span class="inline-block h-2.5 w-2.5 rounded-full bg-[#4F46E5]"></span> Malam</span>
        <span>garis putus-putus = target section</span>
      </div>
      <div :class="['flex items-center gap-1', isFailed ? 'text-red-600' : 'text-emerald-600']"><RefreshCw class="h-3 w-3" /> {{ statusText }}</div>
      <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
        <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded bg-[#F8B4B4]"></span> di bawah target</span>
        <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded bg-[#FFE58A]"></span> pas</span>
        <span class="flex items-center gap-1"><span class="inline-block h-3 w-3 rounded bg-[#A8E0B8]"></span> di atas target</span>
      </div>
      <div class="flex items-center gap-1"><ZoomIn class="h-3 w-3" /> Seret di grafik untuk memperbesar · Ctrl + roda mouse = zoom · klik dua kali = reset</div>
    </div>
  </div>
</template>
