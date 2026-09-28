<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { SquarePen, X } from 'lucide-vue-next';

const props = defineProps({
  answer: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({ score: '', reviewer_note: '' });

watch(() => props.answer, (a) => {
  if (! a) return;
  form.score = a.final_score ?? a.manual_score ?? '';
  form.reviewer_note = a.reviewer_note ?? '';
  form.clearErrors();
}, { immediate: true });

const submit = () => {
  form.post(`/one-academy/best-employee/answers/${props.answer.id}/grade`, {
    preserveScroll: true,
    onSuccess: () => emit('saved'),
  });
};
</script>

<template>
  <div v-if="answer" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-base font-bold text-slate-900 dark:text-white">
          <SquarePen class="h-5 w-5 text-amber-600" /> Penilaian Manual
        </h3>
        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="emit('close')">
          <X class="h-5 w-5" />
        </button>
      </div>

      <form class="space-y-3" @submit.prevent="submit">
        <div>
          <label class="text-xs font-bold text-slate-500">Pertanyaan</label>
          <div class="mt-1 rounded-lg bg-slate-50 p-2 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ answer.pertanyaan }}</div>
        </div>
        <div>
          <label class="text-xs font-bold text-slate-500">Jawaban Peserta</label>
          <div class="mt-1 whitespace-pre-wrap rounded-lg bg-slate-50 p-2 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ answer.answer_text || '(kosong)' }}</div>
        </div>
        <div>
          <label class="text-xs font-bold text-slate-500">Nilai (maks {{ answer.score_weight }})</label>
          <input v-model="form.score" type="number" min="0" :max="answer.score_weight" step="0.5" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
          <p v-if="form.errors.score" class="mt-1 text-xs text-rose-600">{{ form.errors.score }}</p>
        </div>
        <div>
          <label class="text-xs font-bold text-slate-500">Catatan Penilai (opsional)</label>
          <textarea v-model="form.reviewer_note" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="emit('close')">Batal</button>
          <button type="submit" :disabled="form.processing" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 shadow disabled:opacity-60">Simpan Nilai</button>
        </div>
      </form>
    </div>
  </div>
</template>
