<script setup>
import { CheckCircle2, TriangleAlert } from 'lucide-vue-next';
import { STATUS_BADGE_CLASS, formatDate } from '@/utils/moncu';

defineProps({
  rows: { type: Array, required: true },
  sort: { type: String, required: true },
  dir: { type: String, required: true },
  statusLabel: { type: String, required: true },
});

const emit = defineEmits(['sort', 'show-detail']);

const columns = [
  { key: 'nrp', label: 'NRP' },
  { key: 'nama', label: 'Nama' },
  { key: 'jabatan', label: 'Jabatan' },
  { key: 'group', label: 'Group' },
  { key: 'status', label: null },
  { key: 'awal', label: 'AWAL' },
  { key: 'akhir', label: 'AKHIR' },
  { key: 'induksi', label: 'INDUKSI' },
  { key: 'plan', label: 'PLAN', title: 'PLAN Berikutnya — hanya acuan, tidak menghasilkan CT' },
];

const arrow = (key, sort, dir) => (key === sort ? (dir === 'asc' ? ' ▲' : ' ▼') : '');
</script>

<template>
  <div class="overflow-x-auto">
    <table class="w-full min-w-[1080px] text-left text-xs">
      <thead class="border-b border-slate-100 bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500">
        <tr>
          <th v-for="column in columns" :key="column.key" class="px-3 py-3" :title="column.title">
            <button type="button" class="whitespace-nowrap uppercase hover:underline" @click="emit('sort', column.key)">{{ column.label ?? statusLabel }}{{ arrow(column.key, sort, dir) }}</button>
          </th>
          <th class="px-3 py-3">Roster V2</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700">
        <tr v-for="row in rows" :key="row.nrp" class="cursor-pointer hover:bg-blue-50/40" @click="emit('show-detail', row.nrp)">
          <td class="whitespace-nowrap px-3 py-2.5">{{ row.nrp }}</td>
          <td class="whitespace-nowrap px-3 py-2.5">{{ row.name }}</td>
          <td class="px-3 py-2.5">{{ row.jabatan }}</td>
          <td class="px-3 py-2.5">{{ row.group }}</td>
          <td class="px-3 py-2.5"><span :class="['rounded-full px-2 py-0.5 text-[10px] font-bold', STATUS_BADGE_CLASS[row.status] ?? 'bg-slate-200 text-slate-700']">{{ row.status }}</span></td>
          <td class="whitespace-nowrap px-3 py-2.5">{{ formatDate(row.awal) }}</td>
          <td class="whitespace-nowrap px-3 py-2.5">{{ formatDate(row.akhir) }}</td>
          <td class="whitespace-nowrap px-3 py-2.5">{{ formatDate(row.induksi) }}</td>
          <td class="whitespace-nowrap px-3 py-2.5">
            <span v-if="row.plan" :class="row.plan_is_ref ? 'italic text-slate-500' : ''" :title="row.plan_is_ref ? 'Rencana (acuan), bukan cuti aktual' : ''">{{ formatDate(row.plan) }}</span>
          </td>
          <td class="whitespace-nowrap px-3 py-2.5">
            <span v-if="row.in_roster === null" class="text-slate-400" title="Keanggotaan roster hanya dicek untuk tahun berjalan">—</span>
            <span v-else-if="row.in_roster" class="flex items-center gap-1 text-emerald-600"><CheckCircle2 class="h-3.5 w-3.5" /> Terdaftar</span>
            <span v-else class="flex items-center gap-1 text-amber-700"><TriangleAlert class="h-3.5 w-3.5" /> Belum ada di roster</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
