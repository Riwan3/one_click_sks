<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { X, Loader2, UserRound } from 'lucide-vue-next';
import { formatScore } from '@/utils/academyMonitoring';

const props = defineProps({
  nrp: { type: String, required: true },
});

const emit = defineEmits(['closed']);

const detail = ref(null);
const loadError = ref('');

const STATUS_META = {
  pending: ['Belum Dikerjakan', 'bg-slate-100 text-slate-500'],
  in_progress: ['Sedang Dikerjakan', 'bg-blue-100 text-blue-700'],
  passed: ['Lulus', 'bg-emerald-100 text-emerald-700'],
  failed: ['Tidak Lulus', 'bg-rose-100 text-rose-700'],
};

const resolveStatus = (c) => {
  if (!c.status) return 'pending';
  if (c.status === 'in_progress') return 'in_progress';
  if (c.status === 'completed') return c.passed ? 'passed' : 'failed';
  return 'pending';
};

const pct = computed(() => {
  if (!detail.value || detail.value.total_assigned === 0) return 0;
  return Math.round((detail.value.total_passed / detail.value.total_assigned) * 100);
});

const loadDetail = async () => {
  loadError.value = '';
  try {
    const { data } = await axios.get(`/one-academy/evaluasi-kompetensi/raport/${props.nrp}`);
    detail.value = data;
  } catch {
    loadError.value = 'Gagal memuat raport karyawan.';
  }
};

watch(() => props.nrp, loadDetail, { immediate: true });
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <div class="my-8 w-full max-w-3xl rounded-2xl bg-white p-5 shadow-2xl">
      <div class="mb-4 flex items-start justify-between gap-3">
        <h5 class="text-sm font-bold text-slate-800">Raport Kompetensi Karyawan</h5>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <p v-if="loadError" class="py-8 text-center text-sm text-rose-600">{{ loadError }}</p>
      <div v-else-if="!detail" class="flex justify-center py-12"><Loader2 class="h-8 w-8 animate-spin text-rose-600" /></div>

      <template v-else>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50 p-4">
          <div class="flex items-center gap-3">
            <img v-if="detail.employee.foto" :src="detail.employee.foto" class="h-14 w-14 rounded-full object-cover" />
            <div v-else class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-200"><UserRound class="h-7 w-7 text-slate-400" /></div>
            <div>
              <div class="text-sm font-bold text-slate-800">{{ detail.employee.name }}</div>
              <div class="text-xs text-slate-500">{{ detail.employee.nrp }} · {{ detail.employee.jabatan || '—' }}</div>
              <div class="text-[11px] text-slate-400">{{ detail.employee.unit || '—' }} · {{ detail.employee.lokasi || '—' }}</div>
            </div>
          </div>
          <div class="text-right">
            <div class="text-2xl font-black text-rose-700">{{ detail.total_passed }}/{{ detail.total_assigned }} <span class="text-sm font-semibold text-slate-400">({{ pct }}%)</span></div>
            <div class="text-[11px] font-semibold text-slate-400">Kompetensi Lulus</div>
          </div>
        </div>

        <h6 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-600">Kompetensi</h6>
        <div v-if="!detail.competencies.length" class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center text-xs text-slate-400">
          Belum ada kompetensi yang ditugaskan.
        </div>
        <div v-else class="overflow-hidden rounded-xl border border-slate-100">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-[10px] font-bold uppercase text-slate-500">
              <tr><th class="px-3 py-2">Materi</th><th class="px-3 py-2">Assessment</th><th class="px-3 py-2 text-center">Status</th><th class="px-3 py-2 text-center">Nilai</th><th class="px-3 py-2">Selesai</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="c in detail.competencies" :key="c.assessment_id">
                <td class="px-3 py-2 text-slate-600">{{ c.procedure_title || '—' }}</td>
                <td class="px-3 py-2 font-semibold text-slate-800">{{ c.assessment_title }}</td>
                <td class="px-3 py-2 text-center">
                  <span :class="['rounded-full px-2 py-0.5 text-[10px] font-bold', STATUS_META[resolveStatus(c)][1]]">{{ STATUS_META[resolveStatus(c)][0] }}</span>
                </td>
                <td class="px-3 py-2 text-center font-semibold">{{ formatScore(c.score) }}</td>
                <td class="px-3 py-2 text-slate-500">{{ c.completed_at ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>
  </div>
</template>
