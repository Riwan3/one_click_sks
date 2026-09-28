<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, Lock, Pencil, Plus, Trash2, X } from 'lucide-vue-next';

const props = defineProps({
  campaign: { type: Object, required: true },
  questions: { type: Array, required: true },
  canManage: { type: Boolean, default: false },
});

const TYPE_LABELS = {
  multiple_choice: ['Multiple Choice', 'bg-indigo-100 text-indigo-700'],
  short_text: ['Teks Singkat', 'bg-slate-100 text-slate-600'],
  essay: ['Essay', 'bg-slate-100 text-slate-600'],
};

const locked = computed(() => props.campaign.questions_locked);

const showModal = ref(false);
const deleteTarget = ref(null);

const form = useForm({
  question_id: null,
  pertanyaan: '',
  tipe_soal: 'multiple_choice',
  bobot: 1,
  is_required: true,
  use_default_duration: true,
  duration_seconds: '',
  correct_answer: '',
  options: [],
});

function resetForm() {
  form.reset();
  form.clearErrors();
  form.tipe_soal = 'multiple_choice';
  form.bobot = 1;
  form.is_required = true;
  form.use_default_duration = true;
  form.options = [
    { option_text: '', is_correct: true },
    { option_text: '', is_correct: false },
  ];
}

function openAdd() {
  resetForm();
  showModal.value = true;
}

function openEdit(q) {
  form.clearErrors();
  form.question_id = q.id;
  form.pertanyaan = q.pertanyaan;
  form.tipe_soal = q.tipe_soal;
  form.bobot = q.bobot;
  form.is_required = !!q.is_required;
  form.use_default_duration = ! q.duration_seconds;
  form.duration_seconds = q.duration_seconds || '';
  form.correct_answer = q.correct_answer || '';
  form.options = q.tipe_soal === 'multiple_choice' && q.options?.length
    ? q.options.map((o) => ({ option_text: o.option_text, is_correct: !!o.is_correct }))
    : [{ option_text: '', is_correct: true }, { option_text: '', is_correct: false }];
  showModal.value = true;
}

function addOptionRow() {
  form.options.push({ option_text: '', is_correct: false });
}

function removeOptionRow(idx) {
  form.options.splice(idx, 1);
}

function setCorrect(idx) {
  form.options.forEach((o, i) => { o.is_correct = i === idx; });
}

function submit() {
  form.post(`/one-academy/best-employee/${props.campaign.id}/questions`, {
    preserveScroll: true,
    onSuccess: () => { showModal.value = false; },
  });
}

function confirmDelete(q) {
  deleteTarget.value = q;
}

function doDelete() {
  router.delete(`/one-academy/best-employee/${props.campaign.id}/questions/${deleteTarget.value.id}`, {
    preserveScroll: true,
    onSuccess: () => { deleteTarget.value = null; },
  });
}

function move(idx, direction) {
  const order = props.questions.map((q) => q.id);
  const target = idx + direction;
  if (target < 0 || target >= order.length) return;
  [order[idx], order[target]] = [order[target], order[idx]];
  router.post(`/one-academy/best-employee/${props.campaign.id}/questions/reorder`, { question_ids: order }, { preserveScroll: true });
}
</script>

<template>
  <div class="space-y-4">
    <div v-if="locked" class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/10 dark:text-amber-300">
      <Lock class="mt-0.5 h-4 w-4 shrink-0" />
      <p>Soal terkunci karena kegiatan sudah <strong>{{ campaign.status }}</strong>. Tambah/ubah/hapus soal tidak tersedia lagi.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 p-4 dark:border-slate-800">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
          Daftar Pertanyaan <span class="font-normal text-slate-400">(durasi default: {{ campaign.default_question_duration_seconds }} detik)</span>
        </h3>
        <button v-if="canManage" type="button" :disabled="locked" class="flex items-center gap-1 rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-bold text-slate-950 shadow disabled:cursor-not-allowed disabled:opacity-50" @click="openAdd">
          <Plus class="h-4 w-4" /> Tambah Soal
        </button>
      </div>

      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 font-bold uppercase text-slate-500 dark:bg-slate-800/50">
          <tr>
            <th class="w-16 p-3 pl-4">Urutan</th>
            <th class="p-3">Pertanyaan</th>
            <th class="p-3">Tipe</th>
            <th class="p-3">Bobot</th>
            <th class="p-3">Durasi</th>
            <th class="p-3">Wajib</th>
            <th class="p-3 pr-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="(q, i) in questions" :key="q.id">
            <td class="p-3 pl-4">
              <div class="flex items-center gap-1">
                <div class="flex flex-col">
                  <button type="button" :disabled="locked || i === 0" class="rounded p-0.5 text-slate-400 hover:bg-slate-100 disabled:opacity-30 dark:hover:bg-slate-800" @click="move(i, -1)"><ChevronUp class="h-3.5 w-3.5" /></button>
                  <button type="button" :disabled="locked || i === questions.length - 1" class="rounded p-0.5 text-slate-400 hover:bg-slate-100 disabled:opacity-30 dark:hover:bg-slate-800" @click="move(i, 1)"><ChevronDown class="h-3.5 w-3.5" /></button>
                </div>
                <span class="font-bold text-slate-500">{{ i + 1 }}</span>
              </div>
            </td>
            <td class="max-w-md whitespace-pre-wrap p-3 text-slate-700 dark:text-slate-300">{{ q.pertanyaan.length > 160 ? q.pertanyaan.slice(0, 160) + '...' : q.pertanyaan }}</td>
            <td class="p-3"><span class="rounded px-2 py-0.5 font-bold" :class="(TYPE_LABELS[q.tipe_soal] || [q.tipe_soal, 'bg-slate-100'])[1]">{{ (TYPE_LABELS[q.tipe_soal] || [q.tipe_soal])[0] }}</span></td>
            <td class="p-3">{{ parseFloat(q.bobot) }}</td>
            <td class="p-3">{{ q.duration_seconds ? q.duration_seconds + ' dtk' : 'Default' }}</td>
            <td class="p-3">
              <span v-if="q.is_required" class="rounded bg-blue-50 px-2 py-0.5 font-bold text-blue-700">Ya</span>
              <span v-else class="rounded border border-slate-200 px-2 py-0.5 text-slate-400">Tidak</span>
            </td>
            <td class="p-3 pr-4 text-right">
              <button v-if="canManage" type="button" :disabled="locked" class="rounded p-1.5 text-amber-600 hover:bg-amber-50 disabled:opacity-30" @click="openEdit(q)"><Pencil class="h-4 w-4" /></button>
              <button v-if="canManage" type="button" :disabled="locked" class="rounded p-1.5 text-rose-600 hover:bg-rose-50 disabled:opacity-30" @click="confirmDelete(q)"><Trash2 class="h-4 w-4" /></button>
            </td>
          </tr>
          <tr v-if="!questions.length">
            <td colspan="7" class="p-8 text-center text-slate-400">Belum ada soal dibuat.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Tambah/Edit Soal -->
  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ form.question_id ? 'Edit Soal' : 'Tambah Soal' }}</h3>
        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="showModal = false"><X class="h-5 w-5" /></button>
      </div>

      <form class="space-y-3" @submit.prevent="submit">
        <div>
          <label class="text-xs font-bold text-slate-500">Pertanyaan</label>
          <textarea v-model="form.pertanyaan" rows="3" required class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
          <p v-if="form.errors.pertanyaan" class="mt-1 text-xs text-rose-600">{{ form.errors.pertanyaan }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="text-xs font-bold text-slate-500">Tipe Soal</label>
            <select v-model="form.tipe_soal" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
              <option value="multiple_choice">Multiple Choice</option>
              <option value="short_text">Teks Singkat</option>
              <option value="essay">Essay</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold text-slate-500">Bobot Nilai</label>
            <input v-model="form.bobot" type="number" min="0.01" step="0.5" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
            <p v-if="form.errors.bobot" class="mt-1 text-xs text-rose-600">{{ form.errors.bobot }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="flex items-center gap-2 text-xs font-bold text-slate-500">
              <input v-model="form.use_default_duration" type="checkbox" class="h-4 w-4 rounded"> Gunakan durasi default ({{ campaign.default_question_duration_seconds }}dtk)
            </label>
            <input v-if="!form.use_default_duration" v-model="form.duration_seconds" type="number" min="1" placeholder="Durasi khusus (detik)" class="mt-2 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
            <p v-if="form.errors.duration_seconds" class="mt-1 text-xs text-rose-600">{{ form.errors.duration_seconds }}</p>
          </div>
          <div>
            <label class="flex items-center gap-2 text-xs font-bold text-slate-500 pt-1">
              <input v-model="form.is_required" type="checkbox" class="h-4 w-4 rounded"> Wajib dijawab
            </label>
          </div>
        </div>

        <div v-if="form.tipe_soal === 'multiple_choice'">
          <label class="text-xs font-bold text-slate-500">Pilihan Jawaban <span class="text-rose-500">(minimal 2, tandai 1 jawaban benar)</span></label>
          <div class="mt-1 space-y-2">
            <div v-for="(opt, idx) in form.options" :key="idx" class="flex items-center gap-2">
              <input type="radio" :checked="opt.is_correct" class="h-4 w-4 shrink-0" @change="setCorrect(idx)">
              <input v-model="opt.option_text" type="text" placeholder="Teks pilihan" class="w-full rounded-xl border border-slate-200 p-2 text-sm dark:border-slate-700 dark:bg-slate-800">
              <button type="button" class="shrink-0 rounded p-1 text-rose-500 hover:bg-rose-50" @click="removeOptionRow(idx)"><X class="h-4 w-4" /></button>
            </div>
          </div>
          <button type="button" class="mt-2 rounded-lg border border-amber-300 px-3 py-1 text-xs font-bold text-amber-700 hover:bg-amber-50" @click="addOptionRow">+ Tambah Pilihan</button>
          <p v-if="form.errors.options" class="mt-1 text-xs text-rose-600">{{ form.errors.options }}</p>
        </div>
        <div v-else>
          <label class="text-xs font-bold text-slate-500">Kunci Jawaban Referensi (opsional, untuk penilaian manual)</label>
          <input v-model="form.correct_answer" type="text" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="showModal = false">Batal</button>
          <button type="submit" :disabled="form.processing" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 shadow disabled:opacity-60">Simpan Soal</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Hapus Soal -->
  <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
      <h3 class="text-base font-bold text-rose-600">Hapus Soal</h3>
      <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Soal ini akan dihapus dan tidak lagi muncul di assessment. Lanjutkan?</p>
      <div class="mt-4 flex justify-end gap-2">
        <button type="button" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="deleteTarget = null">Batal</button>
        <button type="button" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow" @click="doDelete">Ya, Hapus</button>
      </div>
    </div>
  </div>
</template>
