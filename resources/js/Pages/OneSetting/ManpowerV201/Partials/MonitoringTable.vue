<script setup>
import { Link } from '@inertiajs/vue3';
import { Image } from 'lucide-vue-next';
import { BASE_URL, SHIFT_SHORT_LABEL, formatDayDate, formatFullDateTime } from '@/utils/manpowerV201';

defineProps({
  rows: { type: Array, default: () => [] },
  emptyText: { type: String, default: '' },
});

const STATUS_META = {
  final: { label: 'FINAL', badge: 'bg-emerald-600 text-white' },
  draft: { label: 'DRAFT', badge: 'bg-slate-500 text-white' },
  belum: { label: 'BELUM DISETTING', badge: 'bg-red-600 text-white' },
  mendatang: { label: 'MENDATANG', badge: 'border border-slate-300 bg-slate-50 text-slate-500' },
};
const ROW_CLASS = { belum: 'bg-red-50', mendatang: 'text-slate-400' };

const query = (row) => `?${new URLSearchParams({ date: row.date, shift: row.shift })}`;
const emphasis = (value, tone) => (value > 0 ? `font-bold ${tone}` : 'text-slate-400');
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full min-w-[56rem] text-sm">
      <thead class="bg-slate-50 text-left text-xs text-slate-600">
        <tr>
          <th class="px-3 py-2">Tanggal</th><th class="px-3 py-2">Shift</th><th class="px-3 py-2">Status</th>
          <th class="px-3 py-2 text-center">Terisi</th><th class="px-3 py-2 text-center">Kosong</th>
          <th class="px-3 py-2 text-center">Double</th><th class="px-3 py-2 text-center">Sisa</th>
          <th class="px-3 py-2">Terakhir diubah</th><th class="px-3 py-2" />
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-for="r in rows" :key="`${r.date}-${r.shift}`" :class="ROW_CLASS[r.status]">
          <td class="whitespace-nowrap px-3 py-2">{{ formatDayDate(r.date) }}</td>
          <td class="px-3 py-2">{{ SHIFT_SHORT_LABEL[r.shift] }}</td>
          <td class="px-3 py-2">
            <span :class="['rounded px-2 py-0.5 text-[10px] font-bold', STATUS_META[r.status].badge]">{{ STATUS_META[r.status].label }}</span>
            <span v-if="r.warnings > 0" class="ml-1 rounded bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold text-slate-900" title="Ada peringatan di setting ini">{{ r.warnings }} peringatan</span>
          </td>
          <template v-if="r.summary">
            <td class="px-3 py-2 text-center">{{ r.summary.filled }} / {{ r.summary.units }}</td>
            <td :class="['px-3 py-2 text-center', emphasis(r.summary.empty, 'text-red-600')]">{{ r.summary.empty }}</td>
            <td :class="['px-3 py-2 text-center', emphasis(r.summary.double, 'text-amber-600')]">{{ r.summary.double }}</td>
            <td :class="['px-3 py-2 text-center', emphasis(r.summary.sisa, '')]">{{ r.summary.sisa }}</td>
            <td class="px-3 py-2 text-xs">
              {{ r.updated_by_name || '-' }}
              <div class="text-slate-400">{{ formatFullDateTime(r.updated_at) }}</div>
            </td>
          </template>
          <template v-else>
            <td colspan="4" class="px-3 py-2 text-xs italic text-slate-400">
              {{ r.status === 'belum' ? `Roster mencatat ${r.hadir} orang hadir, setting belum dibuat.` : `Roster ${r.hadir} hadir` }}
            </td>
            <td class="px-3 py-2 text-xs text-slate-400">-</td>
          </template>
          <td class="whitespace-nowrap px-3 py-2 text-right">
            <Link :href="`${BASE_URL}${query(r)}`" :class="['rounded border px-2 py-0.5 text-xs font-semibold', r.status === 'belum' ? 'border-red-600 bg-red-600 text-white' : 'border-slate-700 text-slate-800 hover:bg-slate-50']">{{ r.has_setting ? 'Buka' : 'Buat' }}</Link>
            <Link v-if="r.has_setting" :href="`${BASE_URL}/preview${query(r)}`" class="ml-1 inline-block rounded border border-slate-300 p-1 align-middle text-slate-600 hover:bg-slate-50" title="Preview & gambar"><Image class="h-3.5 w-3.5" /></Link>
          </td>
        </tr>
        <tr v-if="!rows.length"><td colspan="9" class="px-3 py-8 text-center text-sm italic text-slate-400">{{ emptyText }}</td></tr>
      </tbody>
    </table>
  </div>
</template>
