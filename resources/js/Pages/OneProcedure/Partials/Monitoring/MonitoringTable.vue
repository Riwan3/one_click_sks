<script setup>
import { Table2, Inbox, Eye, Trash2 } from 'lucide-vue-next';
import { competencyOf, formatScore, monitoringStatusClass } from '@/utils/procedureMonitoring';

defineProps({
  rows: { type: Array, required: true },
  rowOffset: { type: Number, default: 0 },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['detail-requested', 'remove-requested']);

const scoreClass = (row) => (row.score >= row.passing_grade ? 'text-emerald-700' : 'text-rose-700');
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex items-center gap-2 border-b border-slate-100 p-4">
      <Table2 class="h-4 w-4 text-rose-600" />
      <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">Hasil Assessment Karyawan</h3>
    </div>

    <div v-if="rows.length === 0" class="px-4 py-12 text-center text-slate-400">
      <Inbox class="mx-auto mb-2 h-8 w-8" />
      <p class="text-sm font-semibold text-slate-500">Belum Ada Data</p>
      <p class="text-xs">Data monitoring akan tampil setelah assessment dipublish dan ada karyawan yang terdaftar.</p>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="w-full min-w-[960px] border-collapse text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-600">
          <tr>
            <th class="w-10 px-3 py-3">#</th>
            <th class="px-3 py-3">NRP</th>
            <th class="px-3 py-3">Nama</th>
            <th class="px-3 py-3">Jabatan</th>
            <th class="px-3 py-3">Assessment</th>
            <th class="px-3 py-3 text-center">Score</th>
            <th class="px-3 py-3 text-center">Kompetensi</th>
            <th class="px-3 py-3 text-center">Attempt</th>
            <th class="px-3 py-3 text-center">Status</th>
            <th class="px-3 py-3">Tanggal</th>
            <th class="px-3 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <tr v-for="(row, idx) in rows" :key="`${row.assessment_id}-${row.nrp}`" class="hover:bg-slate-50">
            <td class="px-3 py-3 text-slate-400">{{ rowOffset + idx + 1 }}</td>
            <td class="px-3 py-3 font-semibold">{{ row.nrp }}</td>
            <td class="px-3 py-3 font-semibold text-slate-800">{{ row.name }}</td>
            <td class="px-3 py-3 text-slate-500">{{ row.jabatan || '—' }}</td>
            <td class="max-w-[180px] truncate px-3 py-3" :title="row.assessment_title">{{ row.assessment_title }}</td>
            <td class="px-3 py-3 text-center text-sm font-bold">
              <span v-if="row.score !== null" :class="scoreClass(row)">{{ formatScore(row.score) }}</span>
              <span v-else class="text-slate-400">—</span>
            </td>
            <td class="px-3 py-3 text-center">
              <span :class="['rounded-full px-2 py-0.5 text-[10px] font-semibold', competencyOf(row.score, row.passing_grade).badge]">
                {{ competencyOf(row.score, row.passing_grade).label }}
              </span>
            </td>
            <td class="px-3 py-3 text-center">{{ row.attempt ?? '—' }}</td>
            <td class="px-3 py-3 text-center">
              <span :class="['rounded-full px-2 py-0.5 text-[10px] font-bold', monitoringStatusClass(row.status)]">{{ row.status_label }}</span>
            </td>
            <td class="whitespace-nowrap px-3 py-3 text-slate-500">{{ row.completed_at ?? '—' }}</td>
            <td class="whitespace-nowrap px-3 py-3 text-center">
              <button
                v-if="row.evaluation_id"
                type="button"
                class="inline-flex items-center gap-1 rounded-full border border-blue-500 px-2.5 py-0.5 text-[11px] font-semibold text-blue-600 hover:bg-blue-50"
                @click="emit('detail-requested', row.evaluation_id)"
              >
                <Eye class="h-3 w-3" /> Detail
              </button>
              <span v-else class="text-slate-400">—</span>
              <button
                v-if="canManage"
                type="button"
                title="Hapus dari Assessment"
                class="ml-1 inline-flex items-center rounded-full border border-rose-400 px-2 py-0.5 text-rose-600 hover:bg-rose-50"
                @click="emit('remove-requested', row)"
              >
                <Trash2 class="h-3 w-3" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <slot name="footer" />
  </div>
</template>
