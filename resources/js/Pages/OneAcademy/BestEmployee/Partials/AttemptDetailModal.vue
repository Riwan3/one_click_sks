<script setup>
import { ref, watch } from 'vue';
import { FileText, RotateCcw, SquarePen, X } from 'lucide-vue-next';
import { beGetJson } from '@/composables/useBestEmployeeApi';
import GradeAnswerModal from './GradeAnswerModal.vue';

const props = defineProps({
  attemptId: { type: [Number, null], default: null },
  canReview: { type: Boolean, default: false },
  canReset: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'reset-attempt', 'graded']);

const loading = ref(false);
const detail = ref(null);
const gradingAnswer = ref(null);

const STATUS_LABELS = {
  belum_mulai: ['Belum Mulai', 'bg-slate-100 text-slate-600'],
  in_progress: ['Sedang Mengerjakan', 'bg-amber-100 text-amber-800'],
  submitted: ['Selesai', 'bg-emerald-100 text-emerald-800'],
  expired: ['Expired', 'bg-slate-800 text-white'],
  timed_out: ['Time Out', 'bg-rose-100 text-rose-800'],
  reset: ['Reset', 'bg-sky-100 text-sky-800'],
};

const fmtDateTime = (s) => (s ? String(s).replace('T', ' ').slice(0, 16) : '-');

async function load() {
  if (! props.attemptId) return;
  loading.value = true;
  detail.value = null;
  try {
    detail.value = await beGetJson(`/one-academy/best-employee/attempts/${props.attemptId}`);
  } finally {
    loading.value = false;
  }
}

watch(() => props.attemptId, load, { immediate: true });

const canResetNow = (status) => ['in_progress', 'submitted', 'timed_out', 'expired'].includes(status);
</script>

<template>
  <div v-if="attemptId" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="flex max-h-[90vh] w-full max-w-3xl flex-col rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800">
        <h3 class="flex items-center gap-2 text-base font-bold text-slate-900 dark:text-white">
          <FileText class="h-5 w-5 text-amber-600" /> Detail Attempt
        </h3>
        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="emit('close')">
          <X class="h-5 w-5" />
        </button>
      </div>

      <div class="overflow-y-auto p-5">
        <div v-if="loading" class="py-12 text-center text-sm text-slate-400">Memuat...</div>

        <template v-else-if="detail">
          <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <h4 class="text-xs font-bold uppercase text-slate-400">Peserta</h4>
              <p class="mt-1 font-bold text-slate-900 dark:text-white">{{ detail.participant?.user_nama }} <span class="font-normal text-slate-500">({{ detail.participant?.user_nrp }})</span></p>
              <p class="text-xs text-slate-500">{{ detail.participant?.user_jabatan || '-' }} &middot; {{ detail.participant?.user_unit || '-' }}</p>
            </div>
            <div>
              <h4 class="text-xs font-bold uppercase text-slate-400">Attempt</h4>
              <p class="mt-1">
                <span class="rounded px-2 py-0.5 text-xs font-bold" :class="(STATUS_LABELS[detail.attempt.status] || [detail.attempt.status, 'bg-slate-100'])[1]">
                  {{ (STATUS_LABELS[detail.attempt.status] || [detail.attempt.status])[0] }}
                </span>
              </p>
              <p class="mt-1 text-xs text-slate-500">Mulai: {{ fmtDateTime(detail.attempt.started_at) }} &middot; Selesai: {{ fmtDateTime(detail.attempt.submitted_at) }}</p>
              <p class="text-xs text-slate-500">IP: {{ detail.attempt.ip_address || '-' }}</p>
              <p class="mt-1 text-sm font-bold text-slate-900 dark:text-white">
                Skor: {{ detail.attempt.total_score ?? '-' }}
                <span class="font-normal text-slate-500">(Objektif: {{ detail.attempt.objective_score ?? '-' }}, Manual: {{ detail.attempt.manual_score ?? '-' }})</span>
              </p>
            </div>
          </div>

          <button
            v-if="canReset && canResetNow(detail.attempt.status)"
            type="button"
            class="mb-4 flex items-center gap-1 rounded-lg border border-rose-200 px-3 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-50"
            @click="emit('reset-attempt', detail.attempt.id)"
          >
            <RotateCcw class="h-3.5 w-3.5" /> Reset Attempt Ini
          </button>

          <h4 class="mb-2 text-xs font-bold uppercase text-slate-400">Jawaban</h4>
          <div v-if="!detail.answers.length" class="mb-4 rounded-lg bg-slate-50 p-4 text-center text-xs text-slate-400 dark:bg-slate-800">
            Belum ada jawaban tercatat (peserta belum mengerjakan).
          </div>
          <div v-else class="mb-4 space-y-2">
            <div v-for="(a, i) in detail.answers" :key="a.question_id" class="rounded-lg border border-slate-100 p-3 text-xs dark:border-slate-800">
              <div class="mb-1 flex items-start justify-between gap-2">
                <p class="font-semibold text-slate-800 dark:text-slate-200">{{ i + 1 }}. {{ a.pertanyaan }}</p>
                <span class="shrink-0 font-bold text-slate-600">{{ a.final_score ?? (a.tipe_soal !== 'multiple_choice' ? 'Belum dinilai' : '-') }} / {{ a.score_weight }}</span>
              </div>
              <div v-if="a.tipe_soal === 'multiple_choice'" class="ml-4 space-y-0.5">
                <div
                  v-for="opt in a.options"
                  :key="opt.id"
                  :class="[
                    opt.is_correct ? 'font-bold text-emerald-600' : 'text-slate-500',
                    Number(a.selected_option_id) === Number(opt.id) && !opt.is_correct ? 'font-bold text-rose-600 line-through' : '',
                  ]"
                >
                  {{ Number(a.selected_option_id) === Number(opt.id) ? '● ' : '○ ' }}{{ opt.option_text }}
                </div>
              </div>
              <div v-else class="ml-4 flex items-start justify-between gap-2">
                <p class="whitespace-pre-wrap text-slate-600 dark:text-slate-300">{{ a.answer_text || '(kosong)' }}</p>
                <button
                  v-if="canReview"
                  type="button"
                  class="shrink-0 rounded-lg p-1 text-amber-600 hover:bg-amber-50"
                  title="Nilai jawaban ini"
                  @click="gradingAnswer = a"
                >
                  <SquarePen class="h-4 w-4" />
                </button>
              </div>
            </div>
          </div>

          <h4 class="mb-2 text-xs font-bold uppercase text-slate-400">Activity Timeline</h4>
          <div class="max-h-40 space-y-1 overflow-y-auto text-xs">
            <div v-for="log in detail.timeline" :key="log.id" class="flex justify-between border-b border-slate-100 py-1 dark:border-slate-800">
              <span class="text-slate-600 dark:text-slate-300">{{ log.description }}</span>
              <span class="text-slate-400">{{ fmtDateTime(log.created_at) }}</span>
            </div>
            <p v-if="!detail.timeline.length" class="text-slate-400">Belum ada aktivitas tercatat.</p>
          </div>
        </template>
      </div>
    </div>

    <GradeAnswerModal
      :answer="gradingAnswer"
      @close="gradingAnswer = null"
      @saved="gradingAnswer = null; load(); emit('graded')"
    />
  </div>
</template>
