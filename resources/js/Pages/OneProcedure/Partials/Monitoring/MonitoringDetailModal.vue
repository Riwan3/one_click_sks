<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { X, Loader2 } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { competencyOf, formatScore } from '@/utils/procedureMonitoring';
import FollowupPanel from './FollowupPanel.vue';
import CoachingScheduleModal from './CoachingScheduleModal.vue';

const props = defineProps({
  evaluationId: { type: Number, required: true },
  mentors: { type: Array, default: () => [] },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['closed']);

const detail = ref(null);
const loadError = ref('');
const isCoachingModalOpen = ref(false);

const result = computed(() => detail.value?.result);
const competency = computed(() => competencyOf(result.value?.score, result.value?.passing_grade));
const infoRows = computed(() => [
  ['NRP', result.value.nrp],
  ['Jabatan', result.value.jabatan || '—'],
  ['Prosedur', result.value.procedure_title || '—'],
  ['Passing Grade', `${result.value.passing_grade}%`],
  ['Status', result.value.passed ? 'LULUS ✓' : 'TIDAK LULUS ✗'],
  ['Percobaan', `#${result.value.attempt}`],
  ['Selesai', result.value.completed_at || '—'],
]);

const loadDetail = async () => {
  loadError.value = '';
  try {
    const { data } = await axios.get(`/one-procedure/evaluasi/monitoring/${props.evaluationId}`);
    detail.value = data;
  } catch {
    loadError.value = 'Gagal memuat detail hasil assessment.';
  }
};

const postAction = (url) => router.post(url, {}, { preserveScroll: true, preserveState: true, onSuccess: loadDetail });

const handleGrantRemedial = async () => {
  const isConfirmed = await confirmDialog('Berikan akses remedial untuk karyawan ini? Karyawan akan bisa mengulang assessment ini satu kali lagi.');
  if (isConfirmed) postAction(`/one-procedure/evaluasi/monitoring/${props.evaluationId}/remedial`);
};

const handleGrantFollowup = async (followupId) => {
  const isConfirmed = await confirmDialog('Coaching sudah selesai dilakukan? Karyawan akan diberikan akses untuk mengulang assessment.');
  if (isConfirmed) postAction(`/one-procedure/evaluasi/monitoring/followup/${followupId}/grant`);
};

const handleCoachingScheduled = () => {
  isCoachingModalOpen.value = false;
  loadDetail();
};

watch(() => props.evaluationId, loadDetail, { immediate: true });
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <div class="my-8 w-full max-w-4xl rounded-2xl bg-white p-5 shadow-2xl">
      <div class="mb-4 flex items-start justify-between gap-3">
        <h5 class="text-sm font-bold text-slate-800">
          {{ result ? `${result.name} — ${result.assessment_title}` : 'Detail Hasil Assessment' }}
        </h5>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <p v-if="loadError" class="py-8 text-center text-sm text-rose-600">{{ loadError }}</p>
      <div v-else-if="!result" class="flex justify-center py-12"><Loader2 class="h-8 w-8 animate-spin text-rose-600" /></div>

      <div v-else class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <div>
          <div class="relative mx-auto mb-3 h-[100px] w-[100px]">
            <svg viewBox="0 0 36 36" class="h-full w-full -rotate-90">
              <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e2e8f0" stroke-width="3" />
              <circle
                cx="18" cy="18" r="15.9" fill="none" stroke-width="3" stroke-linecap="round"
                :stroke="competency.stroke"
                :stroke-dasharray="`${result.score} ${100 - result.score}`"
              />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center text-xl font-black" :style="{ color: competency.stroke }">
              {{ formatScore(result.score) }}
            </div>
          </div>
          <div class="mb-3 text-center">
            <span :class="['rounded-full px-3 py-1 text-xs font-semibold', competency.badge]">{{ competency.label }}</span>
          </div>

          <table class="w-full text-xs">
            <tr v-for="[label, value] in infoRows" :key="label">
              <td class="py-1 pr-3 text-slate-500">{{ label }}</td>
              <td class="py-1 font-semibold text-slate-800">{{ value }}</td>
            </tr>
          </table>

          <div class="mt-3">
            <div class="mb-1.5 text-xs font-semibold text-slate-700">Riwayat Percobaan</div>
            <span
              v-for="h in detail.history"
              :key="h.attempt"
              :class="['mr-1 inline-block rounded-full px-2 py-0.5 text-[11px] font-semibold', h.passed ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700']"
            >
              #{{ h.attempt }} — {{ formatScore(h.score) }}
            </span>
          </div>
        </div>

        <FollowupPanel
          :is-passed="result.passed"
          :followups="detail.followups"
          :extra-attempts="detail.extra_attempts"
          :can-manage="canManage"
          @remedial-requested="handleGrantRemedial"
          @coaching-requested="isCoachingModalOpen = true"
          @grant-requested="handleGrantFollowup"
        />
      </div>
    </div>

    <CoachingScheduleModal
      v-if="isCoachingModalOpen"
      :evaluation-id="evaluationId"
      :mentors="mentors"
      @closed="isCoachingModalOpen = false"
      @scheduled="handleCoachingScheduled"
    />
  </div>
</template>
