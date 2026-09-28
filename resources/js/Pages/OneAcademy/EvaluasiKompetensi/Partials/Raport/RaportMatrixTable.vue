<script setup>
import { CheckCircle2, XCircle, PlayCircle, HourglassIcon, Inbox, Eye } from 'lucide-vue-next';

defineProps({
  rows: { type: Array, required: true },
  assessments: { type: Array, default: () => [] },
});

const emit = defineEmits(['detail-requested']);

const STATUS_ICON = {
  passed: { icon: CheckCircle2, class: 'text-emerald-600' },
  failed: { icon: XCircle, class: 'text-rose-600' },
  in_progress: { icon: PlayCircle, class: 'text-blue-600' },
  pending: { icon: HourglassIcon, class: 'text-slate-400' },
};

const completionClass = (row) => {
  if (row.total_assigned === 0) return 'bg-slate-100 text-slate-500';
  if (row.total_passed === row.total_assigned) return 'bg-emerald-100 text-emerald-700';
  if (row.total_passed > 0) return 'bg-amber-100 text-amber-800';
  return 'bg-slate-100 text-slate-500';
};
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div v-if="assessments.length === 0" class="px-4 py-12 text-center text-slate-400">
      <Inbox class="mx-auto mb-2 h-8 w-8" />
      <p class="text-sm font-semibold text-slate-500">Belum Ada Assessment</p>
      <p class="text-xs">Publish minimal satu assessment untuk melihat raport kompetensi.</p>
    </div>
    <div v-else-if="rows.length === 0" class="px-4 py-12 text-center text-slate-400">
      <Inbox class="mx-auto mb-2 h-8 w-8" />
      <p class="text-sm font-semibold text-slate-500">Belum Ada Data Karyawan</p>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="w-full min-w-[720px] border-collapse text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-600">
          <tr>
            <th class="w-10 px-3 py-3">#</th>
            <th class="px-3 py-3">NRP</th>
            <th class="px-3 py-3">Nama</th>
            <th class="px-3 py-3">Jabatan</th>
            <th v-for="a in assessments" :key="a.id" class="max-w-[100px] truncate px-2 py-3 text-center" :title="a.title">{{ a.title }}</th>
            <th class="px-3 py-3 text-center">Lulus / Total</th>
            <th class="px-3 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <tr v-for="(row, idx) in rows" :key="row.employee.nrp" class="hover:bg-slate-50">
            <td class="px-3 py-3 text-slate-400">{{ idx + 1 }}</td>
            <td class="px-3 py-3 font-semibold">{{ row.employee.nrp }}</td>
            <td class="px-3 py-3 font-semibold text-slate-800">{{ row.employee.name }}</td>
            <td class="px-3 py-3 text-slate-500">{{ row.employee.jabatan || '—' }}</td>
            <td v-for="a in assessments" :key="a.id" class="px-2 py-3 text-center">
              <component
                :is="STATUS_ICON[row.statuses[a.id]]?.icon ?? null"
                v-if="row.statuses[a.id]"
                :class="['inline h-4 w-4', STATUS_ICON[row.statuses[a.id]]?.class]"
              />
              <span v-else class="text-slate-300">—</span>
            </td>
            <td class="px-3 py-3 text-center">
              <span :class="['rounded-full px-2 py-0.5 text-[10px] font-bold', completionClass(row)]">{{ row.total_passed }} / {{ row.total_assigned }}</span>
            </td>
            <td class="px-3 py-3 text-center">
              <button
                type="button"
                class="inline-flex items-center gap-1 rounded-full border border-blue-500 px-2.5 py-0.5 text-[11px] font-semibold text-blue-600 hover:bg-blue-50"
                @click="emit('detail-requested', row.employee.nrp)"
              >
                <Eye class="h-3 w-3" /> Lihat
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
