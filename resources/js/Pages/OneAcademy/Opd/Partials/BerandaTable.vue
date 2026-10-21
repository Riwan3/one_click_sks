<script setup>
import { ArrowDownUp } from 'lucide-vue-next';

defineProps({
  rows: { type: Array, required: true },
  year: { type: Number, required: true },
});

const emit = defineEmits(['sort', 'ajukan']);

const COLUMNS = [
  { key: 'name', label: 'Karyawan' },
  { key: 'position', label: 'Posisi / Jabatan' },
  { key: 'rank', label: 'Grade' },
  { key: 'status', label: '1·2 Status Grade' },
  { key: 'yearsInGrade', label: '3 Lama di Grade' },
  { key: 'yearsInPosition', label: '4 Lama di Posisi' },
];

const STATUS_BADGE = {
  under: { label: 'UNDER GRADE', class: 'bg-red-100 text-red-700' },
  high: { label: 'DI ATAS MAX', class: 'bg-amber-100 text-amber-700' },
  ok: { label: 'SESUAI', class: 'bg-emerald-100 text-emerald-700' },
  na: { label: 'BELUM ADA DATA', class: 'bg-slate-100 text-slate-500' },
};

const ROW_TINT = { under: 'bg-red-50/60', high: 'bg-amber-50/60', ok: '', na: '' };

const formatYears = (years) => (years === null ? '-' : `${years.toFixed(1).replace('.', ',')} th`);
const progressOf = (row) => `${Math.min(100, ((row.yearsInGrade ?? 0) / row.minYears) * 100)}%`;
</script>

<template>
  <div class="max-h-[65vh] overflow-auto rounded-xl border border-slate-200">
    <table class="w-full text-left text-xs">
      <thead class="sticky top-0 z-10 bg-slate-800 text-white">
        <tr>
          <th class="w-11 px-3 py-2 text-center">No</th>
          <th v-for="c in COLUMNS" :key="c.key" class="cursor-pointer select-none whitespace-nowrap px-3 py-2" @click="emit('sort', c.key)">
            {{ c.label }} <ArrowDownUp class="inline h-3 w-3 opacity-60" />
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(r, index) in rows" :key="r.nrp" :class="['border-t border-slate-100', ROW_TINT[r.status]]">
          <td class="px-3 py-2 text-center text-slate-400">{{ index + 1 }}</td>
          <td class="px-3 py-2">
            <button type="button" class="font-semibold text-blue-700 hover:underline" title="Klik untuk mengajukan grade" @click="emit('ajukan', r)">{{ r.name }}</button>
            <span v-for="t in r.usedTermins" :key="t" class="ml-1 rounded bg-emerald-100 px-1 text-[10px] font-bold text-emerald-700" :title="`Sudah diajukan di termin ${t} tahun ${year}`">T{{ t }}</span>
            <div class="text-[11px] text-slate-400">{{ r.nrp }}</div>
          </td>
          <td class="px-3 py-2">{{ r.position || '-' }}</td>
          <td class="px-3 py-2 text-center">
            <span class="rounded bg-slate-100 px-2 py-0.5 font-bold text-slate-700">{{ r.grade || '-' }}</span>
            <div class="text-[11px] text-slate-400">min–max: {{ r.gradeRange || '-' }}</div>
          </td>
          <td class="px-3 py-2 text-center">
            <span :class="['rounded-full px-2 py-0.5 text-[10px] font-bold', STATUS_BADGE[r.status].class]">{{ STATUS_BADGE[r.status].label }}</span>
          </td>
          <td class="px-3 py-2">
            <div class="font-semibold">
              {{ formatYears(r.yearsInGrade) }}
              <span v-if="r.isOverMinYears" class="ml-1 rounded bg-blue-100 px-1 text-[10px] font-bold text-blue-700">≥ 3 TH</span>
            </div>
            <div class="mt-1 h-1 rounded bg-slate-200"><i class="block h-1 rounded bg-blue-500" :style="{ width: progressOf(r) }" /></div>
          </td>
          <td class="px-3 py-2 text-center">{{ formatYears(r.yearsInPosition) }}</td>
        </tr>
        <tr v-if="!rows.length">
          <td colspan="7" class="px-3 py-10 text-center text-slate-400">Tidak ada karyawan yang cocok dengan saringan.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
