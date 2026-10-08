<script setup>
import { shiftClass } from '@/utils/rosterMatrix';

defineProps({
  shifts: { type: Array, required: true },
  canEdit: { type: Boolean, default: false },
});

const CHIP = 'inline-block min-w-[30px] rounded border border-slate-300 px-1.5 text-center text-xs font-semibold';
const TARGETS = [
  ['bg-[#F8B4B4]', 'Di bawah target'],
  ['bg-[#FFE58A]', 'Pas target'],
  ['bg-[#A8E0B8]', 'Di atas target'],
];
</script>

<template>
  <div class="space-y-2 text-xs text-slate-700">
    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
      <span v-for="shift in shifts" :key="shift.code"><span :class="[CHIP, shiftClass(shift.code)]">{{ shift.code }}</span> {{ shift.label }}</span>
      <span v-if="canEdit" class="text-slate-500">· Klik sel untuk mengisi/mengubah shift.</span>
    </div>
    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
      <span v-for="[color, label] in TARGETS" :key="label"><span :class="[CHIP, color]">&nbsp;</span> {{ label }}</span>
      <span class="text-slate-500">Target SIANG/MALAM diatur per section{{ canEdit ? ' lewat ikon gerigi pada baris SIANG/MALAM' : '' }}.</span>
    </div>
  </div>
</template>
