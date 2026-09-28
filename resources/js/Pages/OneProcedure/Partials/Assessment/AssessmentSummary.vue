<script setup>
import { computed } from 'vue';
import { ClipboardList, Info, CheckCircle2 } from 'lucide-vue-next';
import { formatShortDate } from '@/utils/evalProcedure';

const props = defineProps({
  assessment: { type: Object, required: true },
  stats: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
});

const rows = computed(() => [
  ['Total Soal', props.stats.total_questions],
  ['Target Role', `${props.stats.total_roles} role`],
  ['Est. Karyawan', `${props.stats.total_employees} orang`],
  ['Waktu', `${props.stats.estimated_time} menit`],
  ['Passing Grade', `${props.stats.passing_grade}%`],
  ['Dibuat', formatShortDate(props.assessment.created_at)],
]);
</script>

<template>
  <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <h6 class="flex items-center gap-2 border-b border-slate-100 p-4 text-sm font-bold text-slate-800"><ClipboardList class="h-4 w-4 text-rose-600" />Summary</h6>
    <div class="p-4">
      <table class="w-full text-[13px]">
        <tr v-for="[label, value] in rows" :key="label">
          <td class="py-1 text-slate-500">{{ label }}</td>
          <td class="py-1 text-right font-bold text-slate-800">{{ value }}</td>
        </tr>
      </table>
      <div v-if="canManage && assessment.status === 'draft'" class="mt-3 flex gap-1 rounded-lg border border-orange-200 bg-orange-50 p-2 text-xs text-orange-800">
        <Info class="mt-0.5 h-3.5 w-3.5 flex-shrink-0" />
        <span>Assessment masih <strong>Draft</strong>. Tambahkan soal & pilih target, lalu klik <strong>Publish & Kirim</strong>.</span>
      </div>
      <div v-else-if="assessment.status === 'published'" class="mt-3 flex gap-1 rounded-lg border border-green-200 bg-green-100 p-2 text-xs text-green-700">
        <CheckCircle2 class="mt-0.5 h-3.5 w-3.5 flex-shrink-0" />
        <span>Assessment sudah <strong>Published</strong>. Notifikasi terkirim ke operator.</span>
      </div>
    </div>
  </div>
</template>
