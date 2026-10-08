<script setup>
import { Link } from '@inertiajs/vue3';
import { FLEET_SETTING_URL } from '@/utils/fleetBoard';
import { SHIFT_SHORT_LABEL, formatDayDate, formatFullDateTime } from '@/utils/manpowerV201';

defineProps({
  rows: { type: Array, default: () => [] },
  emptyText: { type: String, default: '' },
});

const STATUS_META = {
  terisi: { label: 'TERISI', badge: 'bg-emerald-600 text-white' },
  kosong: { label: 'ADA LOADER KOSONG', badge: 'bg-slate-500 text-white' },
  dobel: { label: 'HAULER DOBEL', badge: 'bg-amber-500 text-slate-900' },
  belum: { label: 'BELUM DISETTING', badge: 'bg-red-600 text-white' },
};

const query = (row) => `?${new URLSearchParams({ date: row.date, shift: row.shift })}`;
const emphasis = (value, tone) => (value > 0 ? `font-bold ${tone}` : 'text-slate-400');
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full min-w-[48rem] text-sm">
      <thead class="bg-slate-50 text-left text-xs text-slate-600">
        <tr>
          <th class="px-3 py-2">Tanggal</th><th class="px-3 py-2">Shift</th><th class="px-3 py-2">Status</th>
          <th class="px-3 py-2 text-center">Loader</th><th class="px-3 py-2 text-center">Hauler</th>
          <th class="px-3 py-2 text-center">Loader kosong</th><th class="px-3 py-2 text-center">Hauler dobel</th>
          <th class="px-3 py-2">Terakhir diubah</th><th class="px-3 py-2" />
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-for="r in rows" :key="`${r.date}-${r.shift}`" :class="{ 'bg-red-50': r.status === 'belum' }">
          <td class="whitespace-nowrap px-3 py-2">{{ formatDayDate(r.date) }}</td>
          <td class="px-3 py-2">{{ SHIFT_SHORT_LABEL[r.shift] }}</td>
          <td class="px-3 py-2"><span :class="['rounded px-2 py-0.5 text-[10px] font-bold', STATUS_META[r.status].badge]">{{ STATUS_META[r.status].label }}</span></td>
          <template v-if="r.has_setting">
            <td class="px-3 py-2 text-center">{{ r.loaders }}</td>
            <td class="px-3 py-2 text-center">{{ r.haulers }}</td>
            <td :class="['px-3 py-2 text-center', emphasis(r.loaders_empty, 'text-red-600')]">{{ r.loaders_empty }}</td>
            <td :class="['px-3 py-2 text-center', emphasis(r.doubles, 'text-amber-600')]">{{ r.doubles }}</td>
            <td class="px-3 py-2 text-xs text-slate-500">{{ formatFullDateTime(r.updated_at) || '-' }}</td>
          </template>
          <td v-else colspan="5" class="px-3 py-2 text-xs italic text-slate-400">Setting fleet belum dibuat.</td>
          <td class="whitespace-nowrap px-3 py-2 text-right">
            <Link :href="`${FLEET_SETTING_URL}${query(r)}`" :class="['rounded border px-2 py-0.5 text-xs font-semibold', r.has_setting ? 'border-slate-700 text-slate-800 hover:bg-slate-50' : 'border-red-600 bg-red-600 text-white']">{{ r.has_setting ? 'Buka' : 'Buat' }}</Link>
          </td>
        </tr>
        <tr v-if="!rows.length"><td colspan="9" class="px-3 py-8 text-center text-sm italic text-slate-400">{{ emptyText }}</td></tr>
      </tbody>
    </table>
  </div>
</template>
