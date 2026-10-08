<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  summary: { type: Object, required: true },
  isCurrent: { type: Boolean, default: true },
  filterUrl: { type: Function, required: true }, // (status) => url
});

// Data historis / mendatang bukan kondisi hari ini -> label netral "per acuan".
const LABELS = {
  current: { cuti: 'Sedang Cuti', induksi: 'Sedang Induksi', akan: 'Akan Cuti', belum: 'Belum Ada AWAL', selesai: 'Cuti Selesai' },
  reference: { cuti: 'Cuti (per acuan)', induksi: 'Induksi (per acuan)', akan: 'Akan Cuti (per acuan)', belum: 'Belum Ada AWAL (per acuan)', selesai: 'Selesai (per acuan)' },
};

const cards = computed(() => {
  const label = LABELS[props.isCurrent ? 'current' : 'reference'];
  return [
    { label: 'Total Operator MONCU', value: props.summary.total },
    { label: label.cuti, value: props.summary.cuti, status: 'CUTI' },
    { label: label.induksi, value: props.summary.induksi, status: 'INDUKSI' },
    { label: label.akan, value: props.summary.akan_cuti, status: 'AKAN CUTI' },
    { label: label.belum, value: props.summary.belum_ada_awal, status: 'BELUM ADA AWAL' },
    { label: 'Memiliki PLAN', value: props.summary.memiliki_plan },
    { label: label.selesai, value: props.summary.selesai, status: 'SELESAI' },
  ];
});

const cardClass = 'block h-full rounded-xl border border-slate-200 bg-white px-3 py-3 shadow-sm';
</script>

<template>
  <div class="grid grid-cols-2 gap-2 md:grid-cols-4 xl:grid-cols-7">
    <component
      :is="card.status ? Link : 'div'"
      v-for="card in cards"
      :key="card.label"
      v-bind="card.status ? { href: filterUrl(card.status), title: `Filter status ${card.status}` } : {}"
      :class="[cardClass, card.status ? 'hover:border-blue-400' : '']"
    >
      <p class="text-2xl font-bold leading-tight text-slate-800">{{ card.value }}</p>
      <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">{{ card.label }}</p>
    </component>
  </div>
</template>
