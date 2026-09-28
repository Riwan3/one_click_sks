<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, Check } from 'lucide-vue-next';
import { ASSESSMENT_BASE_URL, ANSWER_LETTERS, QUESTION_TYPES, questionTypeOf } from '@/utils/procedureAssessment';

const props = defineProps({
  assessmentId: { type: Number, required: true },
  question: { type: Object, default: null },
});

const emit = defineEmits(['closed']);

const form = useForm({
  question_type: props.question?.question_type ?? 'multiple_choice',
  question: props.question?.question ?? '',
  option_a: props.question?.option_a ?? '',
  option_b: props.question?.option_b ?? '',
  option_c: props.question?.option_c ?? '',
  option_d: props.question?.option_d ?? '',
  correct: props.question?.correct ?? '',
  explanation: props.question?.explanation ?? '',
});

const isTrueFalse = computed(() => form.question_type === 'true_false');
const answerLetters = computed(() => (isTrueFalse.value ? ANSWER_LETTERS.slice(0, 2) : ANSWER_LETTERS));
const optionFields = computed(() => answerLetters.value.map((letter) => ({ letter, key: `option_${letter.toLowerCase()}`, isRequired: letter <= 'B' })));

const inputClass = 'w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 disabled:bg-slate-100';

const selectType = (type) => {
  form.question_type = type;
  if (type !== 'true_false') return;
  Object.assign(form, { option_a: 'Benar', option_b: 'Salah', option_c: '', option_d: '' });
  if (!['A', 'B'].includes(form.correct)) form.correct = '';
};

const handleSubmit = () => {
  const options = { preserveScroll: true, onSuccess: () => emit('closed') };
  if (props.question) {
    form.put(`${ASSESSMENT_BASE_URL}/questions/${props.question.id}`, options);
    return;
  }
  form.post(`${ASSESSMENT_BASE_URL}/${props.assessmentId}/questions`, options);
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <form class="my-8 w-full max-w-3xl space-y-4 rounded-2xl bg-white p-6 shadow-2xl" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h5 class="text-base font-bold text-slate-800">{{ question ? 'Edit Soal' : 'Tambah Soal' }}</h5>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <div>
        <label class="mb-1 block text-[13px] font-semibold text-slate-700">Tipe Soal <span class="text-rose-600">*</span></label>
        <div class="flex flex-wrap gap-2">
          <button
            v-for="type in QUESTION_TYPES"
            :key="type.value"
            type="button"
            :class="['rounded-lg border-[1.5px] px-3 py-1 text-xs', form.question_type === type.value ? 'border-blue-300 bg-blue-100 text-blue-700' : 'border-slate-200 bg-slate-100 text-slate-600']"
            @click="selectType(type.value)"
          >
            ■ {{ type.label }}
          </button>
        </div>
      </div>

      <div>
        <label class="mb-1 block text-[13px] font-semibold text-slate-700">{{ questionTypeOf(form.question_type).questionLabel }} <span class="text-rose-600">*</span></label>
        <textarea v-model="form.question" rows="3" placeholder="Tulis pertanyaan di sini..." :class="inputClass" />
        <p v-if="form.errors.question" class="mt-1 text-[11px] text-rose-600">{{ form.errors.question }}</p>
      </div>

      <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
        <div v-for="field in optionFields" :key="field.key">
          <label class="mb-1 flex items-center gap-1 text-xs font-semibold text-slate-700">
            <span class="rounded bg-blue-100 px-1.5 text-blue-700">{{ field.letter }}</span>Opsi {{ field.letter }}
            <span v-if="field.isRequired" class="text-rose-600">*</span>
          </label>
          <input v-model="form[field.key]" type="text" :disabled="isTrueFalse" :placeholder="`Opsi ${field.letter}...`" :class="inputClass" />
          <p v-if="form.errors[field.key]" class="mt-1 text-[11px] text-rose-600">{{ form.errors[field.key] }}</p>
        </div>
      </div>

      <div>
        <label class="mb-1 block text-[13px] font-semibold text-slate-700">Jawaban Benar <span class="text-rose-600">*</span></label>
        <div class="flex gap-2">
          <button
            v-for="letter in answerLetters"
            :key="letter"
            type="button"
            :class="['w-10 rounded-lg border-[1.5px] py-1 text-sm', form.correct === letter ? 'border-green-300 bg-green-100 text-green-700' : 'border-slate-200 bg-slate-100 text-slate-600']"
            @click="form.correct = letter"
          >
            {{ letter }}
          </button>
        </div>
        <p v-if="form.errors.correct" class="mt-1 text-[11px] text-rose-600">{{ form.errors.correct }}</p>
      </div>

      <div>
        <label class="mb-1 block text-[13px] font-semibold text-slate-700">Penjelasan (opsional)</label>
        <input v-model="form.explanation" type="text" placeholder="Penjelasan singkat jawaban benar..." :class="inputClass" />
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" class="rounded-full border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-600" @click="emit('closed')">Batal</button>
        <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-5 py-1.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-60">
          <Check class="h-3.5 w-3.5" />Simpan Soal
        </button>
      </div>
    </form>
  </div>
</template>
