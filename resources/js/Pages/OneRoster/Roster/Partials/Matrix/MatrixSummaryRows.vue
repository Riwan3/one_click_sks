<script setup>
import { computed } from 'vue';
import { Settings } from 'lucide-vue-next';
import { dayCellClass, targetClass } from '@/utils/rosterMatrix';

const props = defineProps({
  section: { type: Object, required: true },
  days: { type: Array, required: true },
  summaryCodes: { type: Object, required: true }, // label => kode shift yang dihitung
  canEdit: { type: Boolean, default: false },
  edits: { type: Object, required: true },
});

const emit = defineEmits(['edit-target']);

const STICKY_FOOT = 'sticky left-0 z-[2] border border-slate-300 px-2 text-right font-semibold shadow-[2px_0_0_#C9CFDA]';

const labelOfCode = computed(() => Object.fromEntries(Object.entries(props.summaryCodes).map(([label, code]) => [code, label])));

// Jumlah orang per label per tanggal, dihitung dari isi sel (termasuk edit lokal).
const counts = computed(() => {
  const result = Object.fromEntries(Object.keys(props.summaryCodes).map((label) => [label, {}]));
  props.section.members.forEach((member) => {
    props.days.forEach((day) => {
      const label = labelOfCode.value[props.edits.cellCode(member, day.date)];
      if (label) result[label][day.date] = (result[label][day.date] ?? 0) + 1;
    });
  });
  return result;
});

const targetOf = (label) => props.section.targets[label];
const hasTarget = (label) => label in props.section.targets;
const countOf = (label, date) => counts.value[label][date] ?? 0;
</script>

<template>
  <tfoot>
    <tr v-for="label in Object.keys(summaryCodes)" :key="label" class="bg-slate-50 text-[11px]">
      <td colspan="4" :class="[STICKY_FOOT, 'bg-slate-50']">
        {{ label }}
        <template v-if="hasTarget(label)">
          <span class="ml-1 text-[10px] font-normal text-slate-500" title="Target jumlah orang per hari">{{ targetOf(label) === null ? 'target: -' : `target: ${targetOf(label)}` }}</span>
          <button v-if="canEdit" type="button" title="Atur target SIANG/MALAM" class="ml-1 text-blue-600" @click="emit('edit-target')"><Settings class="inline h-3 w-3" /></button>
        </template>
      </td>
      <td class="border border-slate-300"></td>
      <td
        v-for="(day, index) in days" :key="day.date"
        :class="['border border-slate-300 px-1 text-center', hasTarget(label) ? targetClass(countOf(label, day.date), targetOf(label)) : '', dayCellClass(day, index)]"
      >{{ countOf(label, day.date) }}</td>
      <td v-if="canEdit" class="border border-slate-300"></td>
    </tr>
    <tr class="bg-[#E8EEF6] text-[11px] font-bold">
      <td colspan="4" :class="[STICKY_FOOT, 'bg-[#E8EEF6]']">TOTAL</td>
      <td class="border border-slate-300"></td>
      <td v-for="(day, index) in days" :key="day.date" :class="['border border-slate-300 px-1 text-center', dayCellClass(day, index)]">{{ section.total }}</td>
      <td v-if="canEdit" class="border border-slate-300"></td>
    </tr>
  </tfoot>
</template>
