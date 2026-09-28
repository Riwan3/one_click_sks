<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Trophy, Award, RefreshCw, XCircle, CheckCircle2, Lightbulb, Download } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ANSWER_LETTERS } from '@/utils/procedureAssessment';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  result: { type: Object, required: true },
});

const timeTakenLabel = computed(() => {
  if (!props.result.time_taken) return '—';
  const m = Math.floor(props.result.time_taken / 60);
  const s = props.result.time_taken % 60;
  return `${m}m ${s}s`;
});

const competencyLabel = computed(() => {
  const score = props.result.score;
  if (score >= 80) return { label: 'Competent', class: 'bg-green-100 text-green-700' };
  if (score >= 70) return { label: 'Need Improvement', class: 'bg-yellow-100 text-yellow-800' };
  return { label: 'Not Competent', class: 'bg-red-100 text-red-700' };
});

const optionsFor = (answer) => {
  if (answer.question_type === 'true_false') return { A: 'Benar', B: 'Salah' };
  return Object.fromEntries(ANSWER_LETTERS.map((l) => [l, answer[`option_${l.toLowerCase()}`]]).filter(([, text]) => text));
};

const optionClass = (answer, letter) => {
  if (letter === answer.user_answer && answer.is_correct) return 'bg-green-100 text-green-700';
  if (letter === answer.user_answer && !answer.is_correct) return 'bg-red-100 text-red-700';
  if (letter === answer.correct_answer && !answer.is_correct) return 'bg-blue-100 text-blue-700';
  return '';
};
</script>

<template>
  <Head title="Hasil Assessment - One Click" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="mx-auto max-w-3xl space-y-4">
      <div class="flex items-center gap-2">
        <Link href="/one-procedure/assessment" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700">
          <ArrowLeft class="h-3.5 w-3.5" />Kembali
        </Link>
        <h1 class="text-base font-bold text-slate-800">Hasil Assessment</h1>
      </div>

      <div class="rounded-2xl p-7 text-center text-white" :class="result.passed ? 'bg-gradient-to-br from-green-800 to-green-600' : 'bg-gradient-to-br from-red-800 to-red-600'">
        <div class="mx-auto mb-3 flex h-24 w-24 items-center justify-center rounded-full border-[3px] border-white/30 bg-white/15">
          <div class="text-3xl font-extrabold">{{ Math.round(result.score) }}</div>
        </div>
        <div class="mb-1 text-xl font-extrabold">{{ result.passed ? '🎉 LULUS!' : '😔 BELUM LULUS' }}</div>
        <div class="text-sm text-white/70">
          {{ result.procedure_title }}<br />
          Pass Grade: {{ result.passing_grade }}% &nbsp;|&nbsp; Nilai Anda: {{ Number(result.score).toFixed(1) }}%
        </div>
        <span class="mt-3 inline-block rounded-full px-3.5 py-1 text-xs font-bold" :class="competencyLabel.class">{{ competencyLabel.label }}</span>
      </div>

      <div v-if="result.passed && result.certificate_code" class="rounded-2xl bg-gradient-to-br from-slate-900 to-blue-950 p-5 text-center">
        <Trophy class="mx-auto mb-2 h-8 w-8 text-amber-400" />
        <div class="mb-1 font-bold text-amber-400">Sertifikat Tersedia!</div>
        <div class="mb-3 text-xs text-white/60">Kode: {{ result.certificate_code }}</div>
        <a
          :href="`/one-procedure/evaluasi/certificates/${result.certificate_code}/pdf`"
          target="_blank"
          class="inline-flex items-center gap-2 rounded-full bg-amber-400 px-4 py-2 text-sm font-bold text-amber-950 hover:bg-amber-300"
        >
          <Download class="h-4 w-4" />Download Sertifikat
        </a>
      </div>

      <div v-if="!result.passed && result.can_retake" class="flex items-center gap-3 rounded-2xl border-l-4 border-orange-500 bg-orange-50 p-4">
        <RefreshCw class="h-6 w-6 shrink-0 text-orange-600" />
        <div class="flex-1">
          <div class="text-sm font-bold text-orange-900">Retake Tersedia</div>
          <div class="text-xs text-orange-700">Percobaan {{ result.total_attempts }} / {{ result.retry_limit >= 99 ? '∞' : result.effective_retry_limit }}</div>
        </div>
        <Link :href="`/one-procedure/assessment/${result.assessment_id}/read`" class="rounded-full bg-orange-500 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-orange-600">Ulangi</Link>
      </div>
      <div v-else-if="!result.passed && !result.can_retake" class="rounded-2xl bg-red-50 p-4 text-sm text-red-700">
        <XCircle class="mr-1.5 inline h-4 w-4" />Batas percobaan habis. Hubungi supervisor Anda.
      </div>

      <div class="grid grid-cols-4 gap-2">
        <div class="rounded-2xl bg-white py-3 text-center shadow-sm">
          <div class="text-xl font-extrabold text-green-600">{{ result.correct_count }}</div>
          <div class="text-[10px] font-semibold uppercase text-slate-400">Benar</div>
        </div>
        <div class="rounded-2xl bg-white py-3 text-center shadow-sm">
          <div class="text-xl font-extrabold text-red-600">{{ result.wrong_count }}</div>
          <div class="text-[10px] font-semibold uppercase text-slate-400">Salah</div>
        </div>
        <div class="rounded-2xl bg-white py-3 text-center shadow-sm">
          <div class="text-xl font-extrabold text-slate-600">{{ result.total_questions }}</div>
          <div class="text-[10px] font-semibold uppercase text-slate-400">Total</div>
        </div>
        <div class="rounded-2xl bg-white py-3 text-center shadow-sm">
          <div class="text-sm font-extrabold text-slate-600">{{ timeTakenLabel }}</div>
          <div class="text-[10px] font-semibold uppercase text-slate-400">Waktu</div>
        </div>
      </div>

      <div>
        <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">Review Jawaban</div>
        <div v-for="(a, i) in result.answers" :key="i" class="mb-2 rounded-xl bg-white p-3.5 shadow-sm">
          <div class="mb-2 flex items-center gap-2">
            <span class="text-xs font-bold text-slate-400">{{ i + 1 }}.</span>
            <CheckCircle2 v-if="a.is_correct" class="h-4 w-4 text-green-600" />
            <XCircle v-else class="h-4 w-4 text-red-600" />
          </div>
          <div class="mb-2 text-sm font-semibold text-slate-800">{{ a.question }}</div>

          <div v-for="(text, letter) in optionsFor(a)" :key="letter" class="mb-1 flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-[13px]" :class="optionClass(a, letter)">
            <span class="w-4 shrink-0 font-extrabold">{{ letter }}.</span>
            <span class="flex-1">{{ text }}</span>
            <span v-if="letter === a.user_answer" class="text-[10px] font-bold">Jawaban Anda</span>
            <span v-else-if="letter === a.correct_answer && !a.is_correct" class="text-[10px] font-bold">✓ Benar</span>
          </div>

          <div v-if="a.explanation" class="mt-2 flex gap-1.5 rounded-lg bg-slate-50 px-2.5 py-2 text-xs text-slate-500">
            <Lightbulb class="h-3.5 w-3.5 shrink-0 text-amber-500" />{{ a.explanation }}
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
