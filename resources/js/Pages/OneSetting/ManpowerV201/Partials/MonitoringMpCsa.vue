<script setup>
import { computed } from 'vue';
import { MapPin } from 'lucide-vue-next';
import { balanceColumns, countFilled, countUnits } from '@/utils/manpowerV201';
import MonitoringMpGroup from './MonitoringMpGroup.vue';

const props = defineProps({
  csa: { type: Object, required: true },
  codes: { type: Object, required: true },
});

// Gaya per CSA (kata kunci pada nama): Monte Suport hijau, Gibson maroon, Monte Baru biru, lainnya slate.
const CSA_STYLES = [
  { match: ['SUPORT', 'SUPPORT'], title: 'from-green-700 to-green-900', unit: 'bg-green-900/85 text-white', spare: 'bg-green-200/70 text-green-900' },
  { match: ['GIBSON'], title: 'from-rose-900 to-red-900', unit: 'bg-red-900/85 text-white', spare: 'bg-red-200/70 text-red-900' },
  { match: ['MONTE BARU'], title: 'from-blue-700 to-blue-900', unit: 'bg-blue-900/80 text-white', spare: 'bg-blue-200/70 text-blue-900' },
];
const DEFAULT_STYLE = { title: 'from-slate-600 to-slate-800', unit: 'bg-slate-700/85 text-white', spare: 'bg-slate-200/80 text-slate-700' };

const style = computed(() => {
  const name = props.csa.name.toUpperCase();

  return CSA_STYLES.find((s) => s.match.some((keyword) => name.includes(keyword))) ?? DEFAULT_STYLE;
});

const columns = computed(() => Object.values(balanceColumns(props.csa.categories)).filter((list) => list.length));
</script>

<template>
  <div class="mb-5 overflow-hidden rounded-2xl bg-white shadow-md">
    <div :class="['flex items-center justify-between bg-gradient-to-br px-4 py-2.5 text-sm font-black uppercase tracking-wider text-white', style.title]">
      <span class="flex items-center gap-2"><MapPin class="h-4 w-4" /> {{ csa.name }}</span>
      <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-[11px]">{{ countFilled(csa) }} / {{ countUnits(csa) }} unit</span>
    </div>
    <div :class="['grid gap-3 p-3', columns.length === 2 ? 'lg:grid-cols-2' : '']">
      <div v-for="(categories, index) in columns" :key="index">
        <MonitoringMpGroup v-for="cat in categories" :key="cat.id" :category="cat" :tone="style" :codes="codes" />
      </div>
    </div>
  </div>
</template>
