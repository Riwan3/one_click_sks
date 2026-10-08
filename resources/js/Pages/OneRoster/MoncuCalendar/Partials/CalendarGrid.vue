<script setup>
import { computed } from 'vue';
import { WEEKDAYS_ID, dateRange } from '@/utils/moncu';

const props = defineProps({
  range: { type: Object, required: true }, // { first, last, start, end }
  cells: { type: Object, required: true }, // { 'YYYY-MM-DD': { ct, id, plan } }
  today: { type: String, required: true },
});

const emit = defineEmits(['select-date']);

const days = computed(() => dateRange(props.range.start, props.range.end));

const isInMonth = (date) => date >= props.range.first && date <= props.range.last;

const badges = (date) => {
  const cell = props.cells[date];
  return [
    { label: 'CT', count: cell.ct, class: 'bg-blue-600 text-white' },
    { label: 'ID', count: cell.id, class: 'bg-amber-400 text-slate-900' },
    { label: 'PLAN', count: cell.plan, class: 'border border-slate-300 bg-slate-50 text-slate-700' },
  ].filter((badge) => badge.count > 0);
};

const cellClass = (date) => [
  'h-16 cursor-pointer overflow-hidden rounded-lg border p-1 text-left align-top sm:h-24 sm:rounded-xl sm:p-1.5',
  date === props.today ? 'border-blue-500 bg-blue-50' : 'border-slate-200 bg-white',
  isInMonth(date) ? '' : '!bg-slate-50 text-slate-400',
  'hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-200',
];
</script>

<template>
  <div class="overflow-x-auto rounded-2xl border border-slate-200/80 bg-white p-2 shadow-sm">
    <div class="grid min-w-[500px] grid-cols-7 gap-1 sm:gap-1.5">
      <div v-for="weekday in WEEKDAYS_ID" :key="weekday" class="py-1 text-center text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ weekday }}</div>
      <button v-for="date in days" :key="date" type="button" :class="cellClass(date)" :aria-label="date" @click="emit('select-date', date)">
        <div class="mb-1 flex items-center gap-1 text-xs font-semibold">
          {{ Number(date.slice(8)) }}
          <span v-if="date === today" class="rounded bg-blue-600 px-1 text-[8px] font-semibold uppercase text-white">Today</span>
        </div>
        <span v-for="badge in badges(date)" :key="badge.label" :class="['mb-0.5 block w-fit rounded px-1.5 text-[10px] font-semibold', badge.class, isInMonth(date) ? '' : 'opacity-70']">{{ badge.label }} {{ badge.count }}</span>
      </button>
    </div>
  </div>
</template>
