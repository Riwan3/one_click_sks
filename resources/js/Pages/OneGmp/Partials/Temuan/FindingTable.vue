<script setup>
import { Eye, Pencil, CheckCircle2, Inbox } from 'lucide-vue-next';
import { findingStatusClass, priorityClass, formatFindingDate } from '@/utils/gmpFinding';

defineProps({
  findings: { type: Array, default: () => [] },
  statusLabels: { type: Object, default: () => ({}) },
});

/** Tiap tombol aksi membuka drawer di tab yang relevan. */
const emit = defineEmits(['open-requested']);

const ACTIONS = [
  { tab: 'info', title: 'Detail', icon: Eye },
  { tab: 'action', title: 'Edit', icon: Pencil },
  { tab: 'verif', title: 'Verifikasi', icon: CheckCircle2 },
];
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[1100px] text-left text-[13px] text-slate-700">
        <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-3 py-3">Kode</th>
            <th class="px-3 py-3">Tanggal &amp; Lokasi</th>
            <th class="px-3 py-3">Aspek &amp; Temuan</th>
            <th class="px-3 py-3 text-center">Prioritas</th>
            <th class="px-3 py-3">PIC</th>
            <th class="px-3 py-3">Deadline</th>
            <th class="px-3 py-3">Progress</th>
            <th class="px-3 py-3 text-center">Status</th>
            <th class="px-3 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="findings.length === 0">
            <td colspan="9" class="py-12 text-center">
              <Inbox class="mx-auto mb-2 h-10 w-10 text-slate-300" />
              <h6 class="font-bold text-slate-600">Belum ada data temuan</h6>
              <p class="text-xs text-slate-400">Klik Tambah Temuan untuk mencatat data baru atau ubah filter pencarian.</p>
            </td>
          </tr>
          <tr v-for="f in findings" :key="f.id" class="hover:bg-slate-50">
            <td class="whitespace-nowrap px-3 py-3 font-bold text-blue-800">{{ f.finding_code }}</td>
            <td class="px-3 py-3">
              <div class="font-bold text-slate-800">{{ formatFindingDate(f.finding_date) }}</div>
              <div class="text-xs text-slate-500"><span class="mr-1 rounded border border-slate-200 bg-slate-50 px-1.5">{{ f.area_id }}</span>{{ f.location_id }}</div>
            </td>
            <td class="px-3 py-3">
              <div class="mb-0.5 text-xs font-bold text-blue-600">{{ f.aspect_id }}</div>
              <div class="max-w-[250px] truncate text-xs text-slate-500" :title="f.finding_description">{{ f.finding_description }}</div>
            </td>
            <td class="px-3 py-3 text-center">
              <span v-if="f.priority" :class="['rounded-full px-2.5 py-0.5 text-xs font-bold', priorityClass(f.priority)]">{{ f.priority }}</span>
              <span v-else class="text-slate-400">-</span>
            </td>
            <td class="px-3 py-3">
              <div v-if="f.pic_nama" class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">{{ f.pic_nama.charAt(0).toUpperCase() }}</div>
                <div class="min-w-0">
                  <div class="truncate font-semibold text-slate-800">{{ f.pic_nama }}</div>
                  <div class="text-xs text-slate-400">{{ f.pic_user_id }}</div>
                </div>
              </div>
              <span v-else class="text-xs italic text-slate-400">Belum di-assign</span>
            </td>
            <td :class="['whitespace-nowrap px-3 py-3 font-bold', f.computed_status === 'overdue' ? 'text-red-600' : 'text-slate-800']">{{ formatFindingDate(f.due_date) }}</td>
            <td class="px-3 py-3">
              <div class="flex items-center gap-2">
                <div class="h-1.5 w-20 overflow-hidden rounded bg-slate-100"><div class="h-full rounded bg-blue-600" :style="{ width: `${f.progress_percentage}%` }" /></div>
                <span class="text-xs font-bold text-slate-800">{{ f.progress_percentage }}%</span>
              </div>
            </td>
            <td class="px-3 py-3 text-center">
              <span :class="['whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-bold', findingStatusClass(f.computed_status)]">{{ statusLabels[f.computed_status] ?? f.computed_status }}</span>
            </td>
            <td class="px-3 py-3">
              <div class="flex justify-center gap-1">
                <button
                  v-for="action in ACTIONS"
                  :key="action.tab"
                  type="button"
                  :title="action.title"
                  class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 hover:text-blue-600"
                  @click="emit('open-requested', { id: f.id, tab: action.tab })"
                >
                  <component :is="action.icon" class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="border-t border-slate-100 px-4 py-2 text-xs text-slate-500">Menampilkan {{ findings.length }} baris data.</div>
  </div>
</template>
