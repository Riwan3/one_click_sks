<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import axios from 'axios';
import { ChevronLeft, ChevronRight, Send } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { questionTypeOf, ANSWER_LETTERS } from '@/utils/procedureAssessment';

const props = defineProps({
  evaluation: { type: Object, required: true },
  questions: { type: Array, default: () => [] },
});

const TIMER_KEY = `quiz_start_${props.evaluation.id}`;
const TOTAL = props.questions.length;
const TIMER_SECONDS = props.evaluation.timer_minutes * 60;

const current = ref(0);
const answers = reactive(Object.fromEntries(props.questions.map((q) => [q.id, q.saved_answer || ''])));
const timeLeft = ref(TIMER_SECONDS);
const submitting = ref(false);
let timerInterval = null;
let autosaveInterval = null;

const optionsFor = (question) => {
  if (question.question_type === 'true_false') return { A: 'Benar', B: 'Salah' };
  return Object.fromEntries(ANSWER_LETTERS.map((l) => [l, question[`option_${l.toLowerCase()}`]]).filter(([, text]) => text));
};

const answeredCount = computed(() => Object.values(answers).filter((v) => v).length);
const timeTaken = computed(() => TIMER_SECONDS - timeLeft.value);
const timerLabel = computed(() => {
  const m = Math.floor(timeLeft.value / 60).toString().padStart(2, '0');
  const s = (timeLeft.value % 60).toString().padStart(2, '0');
  return `${m}:${s}`;
});

const selectAnswer = (question, letter) => { answers[question.id] = letter; };
const navigate = (dir) => { current.value = Math.min(TOTAL - 1, Math.max(0, current.value + dir)); window.scrollTo(0, 0); };

const autosave = () => {
  axios.post(`/one-procedure/assessment/evaluation/${props.evaluation.id}/autosave`, { answers }).catch(() => {});
};

const doSubmit = () => {
  submitting.value = true;
  clearInterval(timerInterval);
  clearInterval(autosaveInterval);
  localStorage.removeItem(TIMER_KEY);
  router.post(`/one-procedure/assessment/evaluation/${props.evaluation.id}/submit`, { answers, time_taken: timeTaken.value });
};

const confirmSubmit = async () => {
  const unanswered = TOTAL - answeredCount.value;
  if (unanswered > 0) {
    const confirmed = await confirmDialog({
      title: 'Masih Ada Soal Belum Dijawab',
      text: `${unanswered} soal belum dijawab. Yakin ingin submit?`,
      confirmButtonText: 'Ya, Submit',
      danger: true,
    });
    if (!confirmed) return;
  }
  doSubmit();
};

onMounted(() => {
  const startedAt = parseInt(localStorage.getItem(TIMER_KEY) || '0', 10);
  if (startedAt > 0) {
    const elapsed = Math.floor((Date.now() - startedAt) / 1000);
    timeLeft.value = Math.max(0, TIMER_SECONDS - elapsed);
  } else {
    localStorage.setItem(TIMER_KEY, String(Date.now()));
  }

  timerInterval = setInterval(() => {
    timeLeft.value--;
    if (timeLeft.value <= 0) doSubmit();
  }, 1000);

  autosaveInterval = setInterval(autosave, 30000);
});

onBeforeUnmount(() => {
  clearInterval(timerInterval);
  clearInterval(autosaveInterval);
});
</script>

<template>
  <Head :title="`Quiz - ${evaluation.procedure_title}`" />

  <div class="min-h-screen bg-slate-100 pb-28 pt-20" style="font-family: 'Segoe UI', sans-serif">
    <!-- Sticky header -->
    <div class="fixed inset-x-0 top-0 z-20 bg-white px-4 pb-2 pt-2.5 shadow-md">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-[11px] font-semibold text-slate-400">{{ evaluation.procedure_title }}</div>
          <div class="text-[13px] font-bold text-slate-800">Soal {{ current + 1 }} dari {{ TOTAL }}</div>
        </div>
        <div class="font-mono text-lg font-extrabold" :class="timeLeft < 60 ? 'animate-pulse text-red-600' : 'text-blue-950'">{{ timerLabel }}</div>
      </div>
      <div class="mt-1.5 h-1 overflow-hidden rounded-full bg-slate-200">
        <div class="h-full rounded-full bg-gradient-to-r from-red-700 to-red-500 transition-all" :style="{ width: `${((current + 1) / TOTAL) * 100}%` }" />
      </div>
      <div class="mt-1.5 flex flex-wrap gap-1">
        <div
          v-for="(q, i) in questions"
          :key="q.id"
          class="h-2 w-2 rounded-full transition"
          :class="i === current ? 'scale-125 bg-red-600' : answers[q.id] ? 'bg-blue-950' : 'bg-slate-200'"
        />
      </div>
    </div>

    <!-- Question -->
    <div v-if="TOTAL === 0" class="px-6 py-16 text-center text-slate-400">Belum ada soal dalam assessment ini.</div>
    <div v-else class="px-4">
      <div v-for="(q, i) in questions" v-show="i === current" :key="q.id">
        <div class="mb-2 text-[11px] font-bold tracking-wide text-slate-400">SOAL {{ i + 1 }} DARI {{ TOTAL }}</div>
        <span class="mb-3 inline-block rounded-full px-2.5 py-0.5 text-[11px] font-bold" :class="questionTypeOf(q.question_type).badge">
          {{ questionTypeOf(q.question_type).label }}
        </span>
        <div class="mb-5 whitespace-pre-line text-[15px] font-semibold leading-relaxed text-slate-800">{{ q.question }}</div>

        <button
          v-for="(text, letter) in optionsFor(q)"
          :key="letter"
          type="button"
          class="mb-2.5 flex w-full items-center gap-3 rounded-2xl border-2 px-4 py-3.5 text-left text-sm font-medium transition"
          :class="answers[q.id] === letter ? 'border-blue-950 bg-blue-50 font-bold text-blue-950' : 'border-slate-200 bg-white text-slate-800'"
          @click="selectAnswer(q, letter)"
        >
          <span
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-sm font-extrabold"
            :class="answers[q.id] === letter ? 'bg-blue-950 text-white' : 'bg-slate-100 text-slate-600'"
          >{{ letter }}</span>
          <span>{{ text }}</span>
        </button>
      </div>
    </div>

    <!-- Nav footer -->
    <div class="fixed inset-x-0 bottom-0 z-20 flex gap-2 bg-white px-4 py-3 shadow-[0_-4px_20px_rgba(0,0,0,0.1)]">
      <button
        type="button"
        :disabled="current === 0"
        class="flex-1 rounded-xl border-2 border-slate-200 py-3 text-sm font-bold text-slate-500 disabled:opacity-40"
        @click="navigate(-1)"
      >
        <ChevronLeft class="mr-1 inline h-4 w-4" />Prev
      </button>
      <button
        v-if="current < TOTAL - 1"
        type="button"
        class="flex-1 rounded-xl bg-blue-950 py-3 text-sm font-bold text-white"
        @click="navigate(1)"
      >
        Next<ChevronRight class="ml-1 inline h-4 w-4" />
      </button>
      <button
        v-else
        type="button"
        :disabled="submitting"
        class="flex-1 rounded-xl bg-red-700 py-3 text-sm font-bold text-white disabled:opacity-60"
        @click="confirmSubmit"
      >
        <Send class="mr-1 inline h-4 w-4" />Submit
      </button>
    </div>
  </div>
</template>
