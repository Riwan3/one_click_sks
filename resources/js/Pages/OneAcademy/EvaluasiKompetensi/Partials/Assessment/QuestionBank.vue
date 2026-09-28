<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { HelpCircle, Plus, Pencil, Trash2, Lightbulb, Diamond } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { ASSESSMENT_BASE_URL, ANSWER_LETTERS, questionTypeOf } from '@/utils/academyAssessment';
import QuestionFormModal from './QuestionFormModal.vue';

defineProps({
  assessmentId: { type: Number, required: true },
  questions: { type: Array, default: () => [] },
  canManage: { type: Boolean, default: false },
});

const editingQuestion = ref(null);
const isFormOpen = ref(false);

const optionsOf = (question) => ANSWER_LETTERS
  .map((letter) => ({ letter, text: question[`option_${letter.toLowerCase()}`], isCorrect: question.correct === letter }))
  .filter((option) => option.text);

const openForm = (question = null) => {
  editingQuestion.value = question;
  isFormOpen.value = true;
};

const handleDelete = async (question) => {
  if (await confirmDialog('Hapus soal ini?')) router.delete(`${ASSESSMENT_BASE_URL}/questions/${question.id}`, { preserveScroll: true });
};
</script>

<template>
  <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 p-4">
      <h6 class="flex items-center gap-2 text-sm font-bold text-slate-800">
        <HelpCircle class="h-4 w-4 text-rose-600" />Bank Soal
        <span class="rounded bg-slate-500 px-1.5 py-0.5 text-[11px] text-white">{{ questions.length }}</span>
      </h6>
      <button v-if="canManage" type="button" class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700" @click="openForm()">
        <Plus class="h-3.5 w-3.5" />Tambah Soal
      </button>
    </div>

    <div v-if="questions.length === 0" class="px-4 py-12 text-center">
      <Diamond class="mx-auto mb-2 h-8 w-8 text-slate-300" />
      <p class="mb-1 text-sm font-semibold text-slate-500">Belum Ada Soal</p>
      <p class="text-xs text-slate-400">Klik "Tambah Soal" untuk membuat soal pertama.</p>
    </div>

    <div v-else class="space-y-2 p-4">
      <div v-for="(question, idx) in questions" :key="question.id" class="flex items-start gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
        <div class="min-w-[22px] pt-0.5 text-[13px] font-bold text-slate-400">{{ idx + 1 }}.</div>
        <div class="flex-1">
          <span :class="['mb-1 inline-block rounded-full px-2 py-0.5 text-[10px] font-semibold', questionTypeOf(question.question_type).badge]">
            {{ questionTypeOf(question.question_type).label }}
          </span>
          <div class="mb-1 text-sm font-semibold text-slate-800">{{ question.question }}</div>
          <div class="grid grid-cols-1 gap-1 text-xs md:grid-cols-2">
            <div v-for="option in optionsOf(question)" :key="option.letter">
              <span :class="['mr-1 font-bold', option.isCorrect ? 'text-green-600' : 'text-slate-400']">{{ option.isCorrect ? '✓' : '○' }} {{ option.letter }}.</span>{{ option.text }}
            </div>
          </div>
          <div v-if="question.explanation" class="mt-1 flex items-start gap-1 text-[11px] text-slate-500">
            <Lightbulb class="mt-0.5 h-3 w-3 flex-shrink-0 text-amber-500" />{{ question.explanation }}
          </div>
        </div>
        <div v-if="canManage" class="flex flex-shrink-0 gap-1">
          <button type="button" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500 text-white hover:bg-amber-600" @click="openForm(question)"><Pencil class="h-4 w-4" /></button>
          <button type="button" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-600 text-white hover:bg-rose-700" @click="handleDelete(question)"><Trash2 class="h-4 w-4" /></button>
        </div>
      </div>
    </div>

    <QuestionFormModal v-if="isFormOpen" :assessment-id="assessmentId" :question="editingQuestion" @closed="isFormOpen = false" />
  </div>
</template>
