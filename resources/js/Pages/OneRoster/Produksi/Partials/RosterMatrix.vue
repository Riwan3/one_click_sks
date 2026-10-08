<script setup>
import { DAY_SHORT, SHIFT_CELL_CLASS } from '@/utils/rosterV2';

defineProps({
  monitoring: { type: Object, required: true },
  emptyMessage: { type: String, required: true },
});

const SUNDAY_CLASS = 'bg-amber-100';

const cellClass = (kode, dow) => SHIFT_CELL_CLASS[kode.toUpperCase()] ?? (dow === 0 ? SUNDAY_CLASS : '');
</script>

<template>
  <div class="max-h-[700px] overflow-auto border border-slate-200">
    <table class="w-full border-collapse text-[10px]">
      <thead class="sticky top-0 z-10 bg-slate-50">
        <tr>
          <th rowspan="2" class="sticky left-0 z-20 min-w-[150px] border border-slate-200 bg-slate-50 px-2 py-1 text-left">NAMA KARYAWAN</th>
          <th rowspan="2" class="min-w-[70px] border border-slate-200 px-1 py-1">NRP</th>
          <th rowspan="2" class="min-w-[120px] border border-slate-200 px-1 py-1 text-left">JABATAN</th>
          <th :colspan="monitoring.days.length" class="border border-slate-200 px-1 py-1">TANGGAL</th>
        </tr>
        <tr>
          <th v-for="d in monitoring.days" :key="d.day" :class="['min-w-[25px] border border-slate-200 px-0.5 py-1', d.dow === 0 ? SUNDAY_CLASS : '']">
            {{ d.day }}<br /><small>{{ DAY_SHORT[d.dow] }}</small>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="!monitoring.rows.length">
          <td :colspan="monitoring.days.length + 3" class="py-10 text-center text-slate-500">
            <p class="text-sm">{{ emptyMessage }}</p>
            <p v-if="!monitoring.error" class="mt-1 text-xs">Buat roster di <a href="/one-roster/roster" class="text-blue-600 underline">Roster V2</a>, lalu klik <strong>Publish</strong> agar tampil di sini.</p>
          </td>
        </tr>
        <tr v-for="row in monitoring.rows" :key="row.nrp">
          <td class="sticky left-0 z-[5] whitespace-nowrap border border-slate-200 bg-white px-2 py-1 text-left" :title="row.grup">{{ row.nama }}</td>
          <td class="border border-slate-200 px-1 py-1 text-center">{{ row.nrp }}</td>
          <td class="border border-slate-200 px-1 py-1 text-left"><small>{{ row.jabatan }}</small></td>
          <td v-for="(kode, index) in row.cells" :key="index" :class="['border border-slate-200 px-0.5 py-1 text-center', cellClass(kode, monitoring.days[index].dow)]">{{ kode }}</td>
        </tr>

        <template v-if="monitoring.rows.length">
          <tr><td :colspan="monitoring.days.length + 3" class="h-1 bg-slate-200 p-0"></td></tr>
          <tr v-for="line in monitoring.daily" :key="line.kode" class="bg-slate-50">
            <td class="sticky left-0 z-[5] border border-slate-200 bg-slate-50 px-2 py-1 text-center text-[9px] font-bold text-slate-600">{{ line.label }}</td>
            <td class="border border-slate-200"></td>
            <td class="border border-slate-200"></td>
            <td
              v-for="(count, index) in line.counts"
              :key="index"
              :class="['border border-slate-200 px-0.5 py-1 text-center text-[9px] font-bold', monitoring.days[index].dow === 0 ? SUNDAY_CLASS : '', count > 0 ? 'text-black' : 'text-slate-300']"
            >{{ count > 0 ? count : '' }}</td>
          </tr>
          <tr class="bg-slate-700">
            <td class="sticky left-0 z-[5] border border-slate-200 bg-slate-700 px-2 py-1 text-center text-[9px] font-bold text-white">TOTAL</td>
            <td class="border border-slate-200 bg-slate-700"></td>
            <td class="border border-slate-200 bg-slate-700"></td>
            <td v-for="(count, index) in monitoring.dailyTotal" :key="index" class="border border-slate-200 bg-slate-700 px-0.5 py-1 text-center text-[9px] font-black text-white">{{ count > 0 ? count : '' }}</td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>
