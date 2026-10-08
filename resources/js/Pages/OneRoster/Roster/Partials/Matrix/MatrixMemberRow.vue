<script setup>
import { computed, nextTick, ref } from 'vue';
import { ArrowLeftRight, CalendarRange, Repeat, UserMinus } from 'lucide-vue-next';
import { dayCellClass, shiftClass } from '@/utils/rosterMatrix';

const props = defineProps({
  member: { type: Object, required: true },
  index: { type: Number, required: true },
  days: { type: Array, required: true },
  canEdit: { type: Boolean, default: false },
  edits: { type: Object, required: true }, // { cellCode, unitOf }
});

const emit = defineEmits(['cell-click', 'save-unit', 'rotation', 'fill', 'move', 'remove']);

const ACTION = 'rounded border border-slate-300 px-1 py-0 text-slate-600 hover:bg-slate-100';
const STICKY = 'sticky z-[2] overflow-hidden text-ellipsis border border-slate-300 bg-white px-1.5 py-0.5';

const unit = computed(() => props.edits.unitOf(props.member));
const isEditingUnit = ref(false);
const unitDraft = ref('');
const unitInput = ref(null);

const startEditUnit = () => {
  if (!props.canEdit || isEditingUnit.value) return;
  unitDraft.value = unit.value ?? '';
  isEditingUnit.value = true;
  nextTick(() => { unitInput.value?.focus(); unitInput.value?.select(); });
};

const finishEditUnit = (shouldSave) => {
  if (!isEditingUnit.value) return;
  isEditingUnit.value = false;
  const value = unitDraft.value.trim();
  if (shouldSave && value !== (unit.value ?? '')) emit('save-unit', value);
};
</script>

<template>
  <tr>
    <td :class="[STICKY, 'left-0 w-[38px] min-w-[38px] max-w-[38px] text-center']">{{ index + 1 }}</td>
    <td :class="[STICKY, 'left-[38px] w-[76px] min-w-[76px] max-w-[76px] text-center']">{{ member.nrp }}</td>
    <td :class="[STICKY, 'left-[114px] w-[200px] min-w-[200px] max-w-[200px] whitespace-nowrap text-left']">{{ member.employee_name }}</td>
    <td
      :class="[STICKY, 'left-[314px] w-[130px] min-w-[130px] max-w-[130px] whitespace-nowrap text-center shadow-[2px_0_0_#C9CFDA]', canEdit ? 'cursor-text hover:bg-blue-50' : '']"
      :title="canEdit ? 'Klik untuk mengubah unit' : ''" @click="startEditUnit"
    >
      <input
        v-if="isEditingUnit" ref="unitInput" v-model="unitDraft" type="text" maxlength="50"
        class="h-6 w-full rounded border border-slate-300 px-1 text-xs"
        @keydown.enter.prevent="finishEditUnit(true)" @keydown.esc="finishEditUnit(false)" @blur="finishEditUnit(true)"
      />
      <template v-else>{{ unit }}</template>
    </td>
    <td class="whitespace-nowrap border border-slate-300 px-1.5 py-0.5 text-center">
      {{ member.kode_off }}<span v-if="member.work_pattern === 'FULL_SIANG'" class="ml-1 rounded bg-amber-300 px-1 text-[10px] font-semibold text-slate-900" title="Full siang: tidak ada shift malam">FS</span>
    </td>
    <td
      v-for="(day, dayIndex) in days" :key="day.date"
      :class="['h-[26px] min-w-[38px] border border-slate-300 px-1 text-center font-semibold', shiftClass(edits.cellCode(member, day.date)), dayCellClass(day, dayIndex), canEdit ? 'cursor-pointer outline-offset-[-2px] hover:outline hover:outline-2 hover:outline-blue-500' : '']"
      @click="canEdit && emit('cell-click', { member, date: day.date, event: $event })"
    >{{ edits.cellCode(member, day.date) }}</td>
    <td v-if="canEdit" class="whitespace-nowrap border border-slate-300 bg-slate-50 px-1 py-0.5">
      <div class="flex gap-1">
        <button type="button" title="Kode Off & putaran" :class="ACTION" @click="emit('rotation')"><Repeat class="h-3.5 w-3.5" /></button>
        <button type="button" title="Isi rentang tanggal" :class="ACTION" @click="emit('fill')"><CalendarRange class="h-3.5 w-3.5" /></button>
        <button type="button" title="Pindah section / ubah nomor" :class="ACTION" @click="emit('move')"><ArrowLeftRight class="h-3.5 w-3.5" /></button>
        <button type="button" title="Keluarkan dari roster" class="rounded border border-red-300 px-1 py-0 text-red-600 hover:bg-red-50" @click="emit('remove')"><UserMinus class="h-3.5 w-3.5" /></button>
      </div>
    </td>
  </tr>
</template>
