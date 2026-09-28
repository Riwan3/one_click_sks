<script setup>
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import { X, Loader2, CheckCircle2, XCircle } from 'lucide-vue-next';
import { competencyOf, formatScore } from '@/utils/academyMonitoring';

const props = defineProps({
  resultId: { type: Number, required: true },
});

const emit = defineEmits(['closed']);

const detail = ref(null);
const loadError = ref('');

const result = computed(() => detail.value?.result);
const competency = computed(() => competencyOf(result.value?.score, result.value?.passing_grade));
const infoRows = computed(() => [
  ['NRP', result.value.nrp],
  ['Jabatan', result.value.jabatan || '—'],
  ['Materi', result.value.procedure_title || '—'],
  ['Passing Grade', `${result.value.passing_grade}%`],
  ['Status', result.value.passed ? 'LULUS ✓' : 'TIDAK LULUS ✗'],
  ['Percobaan', `#${result.value.attempt}`],
  ['Selesai', result.value.completed_at || '—'],
]);

const optionText = (answer, letter) => answer[`option_${letter.toLowerCase()}`];

const correctCount = computed(() => (detail.value?.answers ?? []).filter((a) => a.is_correct).length);
const totalAnswered = computed(() => (detail.value?.answers ?? []).length);
const correctPercent = computed(() => (totalAnswered.value ? Math.round((correctCount.value / totalAnswered.value) * 100) : 0));

const loadDetail = async () => {
  loadError.value = '';
  try {
    const { data } = await axios.get(`/one-academy/evaluasi-kompetensi/monitoring/${props.resultId}`);
    detail.value = data;
  } catch {
    loadError.value = 'Gagal memuat detail hasil assessment.';
  }
};

watch(() => props.resultId, loadDetail, { immediate: true });
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <div class="my-8 w-full max-w-5xl rounded-2xl bg-white p-5 shadow-2xl">
      <div class="mb-4 flex items-start justify-between gap-3">
        <h5 class="text-sm font-bold text-slate-800">
          {{ result ? `${result.name} — ${result.assessment_title}` : 'Detail Hasil Assessment' }}
        </h5>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <p v-if="loadError" class="py-8 text-center text-sm text-rose-600">{{ loadError }}</p>
      <div v-else-if="!result" class="flex justify-center py-12"><Loader2 class="h-8 w-8 animate-spin text-rose-600" /></div>

      <div v-else class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="md:col-span-1">
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

        <div class="md:col-span-2">
          <h6 class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-600">Jawaban per Soal</h6>
          <div v-if="!detail.answers.length" class="rounded-xl border border-slate-100 bg-slate-50 p-4 text-center text-xs text-slate-400">
            Belum ada jawaban tercatat.
          </div>
          <div v-else class="max-h-[360px] overflow-y-auto rounded-xl border border-slate-100">
            <table class="w-full text-left text-xs">
              <thead class="sticky top-0 bg-slate-50 text-[10px] font-bold uppercase text-slate-500">
                <tr><th class="px-2 py-2">#</th><th class="px-2 py-2">Pertanyaan</th><th class="px-2 py-2">Jawaban</th><th class="px-2 py-2">Benar</th><th class="w-8 px-2 py-2"></th></tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="(a, idx) in detail.answers" :key="a.question_id">
                  <td class="px-2 py-2 text-slate-400">{{ idx + 1 }}</td>
                  <td class="max-w-[160px] px-2 py-2">{{ a.question }}</td>
                  <td class="px-2 py-2">{{ a.user_answer ? `${a.user_answer}. ${optionText(a, a.user_answer) ?? ''}` : '—' }}</td>
                  <td class="px-2 py-2 font-semibold text-emerald-700">{{ a.correct_answer }}. {{ optionText(a, a.correct_answer) ?? '' }}</td>
                  <td class="px-2 py-2 text-center">
                    <CheckCircle2 v-if="a.is_correct" class="inline h-4 w-4 text-emerald-600" />
                    <XCircle v-else class="inline h-4 w-4 text-rose-600" />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-if="detail.answers.length" class="mt-2 text-right text-[11px] text-slate-500">
            {{ correctCount }} benar dari {{ totalAnswered }} soal ({{ correctPercent }}%)
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
