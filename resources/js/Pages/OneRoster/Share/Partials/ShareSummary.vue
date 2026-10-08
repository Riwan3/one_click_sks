<script setup>
import { computed } from 'vue';

const props = defineProps({
  days: { type: Array, required: true },
});

const CHIPS = [
  { code: 'd', label: 'Day (Siang)', badge: 'D', box: 'border-emerald-200 bg-emerald-50/70', text: 'text-emerald-700', value: 'text-emerald-900', pill: 'bg-emerald-200/80 text-emerald-800' },
  { code: 'n', label: 'Night (Malam)', badge: 'N', box: 'border-blue-200 bg-blue-50/70', text: 'text-blue-700', value: 'text-blue-900', pill: 'bg-blue-200/80 text-blue-800' },
  { code: 'ct', label: 'Cuti (CT)', badge: 'CT', box: 'border-amber-200 bg-amber-50/70', text: 'text-amber-700', value: 'text-amber-900', pill: 'bg-amber-200/80 text-amber-800' },
  { code: 'off', label: 'Off / Libur', badge: 'OFF', box: 'border-rose-200 bg-rose-50/70', text: 'text-rose-700', value: 'text-rose-900', pill: 'bg-rose-200/80 text-rose-800' },
];

const chips = computed(() => CHIPS.map((chip) => ({
  ...chip,
  total: props.days.filter((day) => day.kode.toLowerCase() === chip.code).length,
})));
</script>

<template>
  <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 print:hidden">
    <div v-for="chip in chips" :key="chip.code" :class="['flex items-center justify-between rounded-xl border p-2.5', chip.box]">
      <div>
        <div :class="['text-[10px] font-bold uppercase', chip.text]">{{ chip.label }}</div>
        <div :class="['text-base font-black', chip.value]">{{ chip.total }} <span class="text-[11px] font-normal">Hari</span></div>
      </div>
      <span :class="['rounded-md px-2 py-0.5 text-xs font-extrabold', chip.pill]">{{ chip.badge }}</span>
    </div>
  </div>
</template>
