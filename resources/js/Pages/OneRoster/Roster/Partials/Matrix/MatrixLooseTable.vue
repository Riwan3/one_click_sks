<script setup>
import MatrixDayHeaders from './MatrixDayHeaders.vue';
import { dayCellClass, shiftClass } from '@/utils/rosterMatrix';

defineProps({
  members: { type: Array, required: true }, // [{ nrp, employee_name, cells }]
  days: { type: Array, required: true },
  canEdit: { type: Boolean, default: false },
});

const HEAD = 'sticky top-0 z-[5] border border-slate-300 bg-slate-800 px-1.5 py-1 text-center text-[11px] font-semibold text-white';
const STICKY = 'sticky z-[2] border border-slate-300 bg-white px-1.5 py-0.5';
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-4 py-2 text-sm">
      <strong>Tanpa Section</strong>
      <span class="ml-2 text-xs text-slate-500">
        Jadwal dari import/daftar yang orangnya belum dimasukkan ke section{{ canEdit ? ' — gunakan Tambah Anggota pada section tujuan (NRP yang sama, jadwal lama tetap dipakai)' : '' }}.
      </span>
    </div>
    <div class="max-h-[max(320px,calc(100vh-260px))] overflow-auto">
      <table class="w-max min-w-full border-separate border-spacing-0 text-[12px]">
        <thead>
          <tr>
            <th :class="[HEAD, 'left-0 w-[38px] min-w-[38px]']">NO</th>
            <th :class="[HEAD, 'left-[38px] w-[76px] min-w-[76px]']">NRP</th>
            <th :class="[HEAD, 'left-[114px] w-[200px] min-w-[200px] text-left']">NAMA</th>
            <MatrixDayHeaders :days="days" />
          </tr>
        </thead>
        <tbody>
          <tr v-for="(member, index) in members" :key="member.nrp">
            <td :class="[STICKY, 'left-0 text-center']">{{ index + 1 }}</td>
            <td :class="[STICKY, 'left-[38px] text-center']">{{ member.nrp }}</td>
            <td :class="[STICKY, 'left-[114px] whitespace-nowrap']">{{ member.employee_name }}</td>
            <td
              v-for="(day, dayIndex) in days" :key="day.date"
              :class="['h-[26px] min-w-[38px] border border-slate-300 px-1 text-center font-semibold', shiftClass(member.cells[day.date] ?? ''), dayCellClass(day, dayIndex)]"
            >{{ member.cells[day.date] ?? '' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
