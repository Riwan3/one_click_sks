<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { ShieldAlert } from 'lucide-vue-next';

const props = defineProps({
  campaign: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
});

const overrideChecked = ref(false);
const overrideReason = ref('');

const locked = computed(() => props.campaign.settings_critical_locked);
const fieldsDisabled = computed(() => locked.value && ! overrideChecked.value);

const form = useForm({
  judul: props.campaign.judul,
  deskripsi: props.campaign.deskripsi ?? '',
  aturan: props.campaign.aturan ?? '',
  show_score_after_submit: !!props.campaign.show_score_after_submit,
  show_correct_answer: !!props.campaign.show_correct_answer,
  publish_start: props.campaign.publish_start ? props.campaign.publish_start.slice(0, 16) : '',
  publish_end: props.campaign.publish_end ? props.campaign.publish_end.slice(0, 16) : '',
  latest_start_at: props.campaign.latest_start_at ? props.campaign.latest_start_at.slice(0, 16) : '',
  timezone: props.campaign.timezone ?? 'Asia/Makassar',
  attempt_limit: props.campaign.attempt_limit,
  default_question_duration_seconds: props.campaign.default_question_duration_seconds,
  total_duration_minutes: props.campaign.total_duration_minutes ?? '',
  shuffle_questions: !!props.campaign.shuffle_questions,
  shuffle_options: !!props.campaign.shuffle_options,
  allow_previous_question: !!props.campaign.allow_previous_question,
  auto_submit: !!props.campaign.auto_submit,
});

const submit = () => {
  form.transform((data) => (overrideChecked.value ? { ...data, force: true, reason: overrideReason.value } : data))
    .put(`/one-academy/best-employee/${props.campaign.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        overrideChecked.value = false;
        overrideReason.value = '';
      },
    });
};
</script>

<template>
  <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <form class="space-y-6" @submit.prevent="submit">
      <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="space-y-4">
          <div>
            <label class="text-xs font-bold text-slate-500">Judul Kegiatan</label>
            <input v-model="form.judul" type="text" required maxlength="255" :disabled="!canManage" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
            <p v-if="form.errors.judul" class="mt-1 text-xs text-rose-600">{{ form.errors.judul }}</p>
          </div>
          <div>
            <label class="text-xs font-bold text-slate-500">Deskripsi</label>
            <textarea v-model="form.deskripsi" rows="2" :disabled="!canManage" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
          </div>
          <div>
            <label class="text-xs font-bold text-slate-500">Instruksi &amp; Aturan Kegiatan</label>
            <textarea v-model="form.aturan" rows="6" :disabled="!canManage" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
            <p class="mt-1 text-xs text-slate-400">Teks polos (tidak mendukung HTML) - ditampilkan apa adanya ke peserta.</p>
          </div>

          <hr class="border-slate-100 dark:border-slate-800">
          <h4 class="text-xs font-bold uppercase text-slate-400">Tampilan Hasil</h4>
          <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
            <input v-model="form.show_score_after_submit" type="checkbox" :disabled="!canManage" class="h-4 w-4 rounded">
            Tampilkan nilai setelah submit
          </label>
          <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
            <input v-model="form.show_correct_answer" type="checkbox" :disabled="fieldsDisabled || !canManage" class="h-4 w-4 rounded">
            Tampilkan jawaban benar
          </label>
        </div>

        <div class="space-y-4">
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs font-bold text-slate-500">Waktu Publish (Mulai)</label>
              <input v-model="form.publish_start" type="datetime-local" :disabled="fieldsDisabled || !canManage" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:disabled:bg-slate-800/50">
            </div>
            <div>
              <label class="text-xs font-bold text-slate-500">Waktu Selesai</label>
              <input v-model="form.publish_end" type="datetime-local" :disabled="fieldsDisabled || !canManage" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:disabled:bg-slate-800/50">
            </div>
          </div>
          <p v-if="form.errors.publish_end" class="text-xs text-rose-600">{{ form.errors.publish_end }}</p>

          <div>
            <label class="text-xs font-bold text-slate-500">Batas Terakhir Peserta Boleh Mulai</label>
            <input v-model="form.latest_start_at" type="datetime-local" :disabled="fieldsDisabled || !canManage" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm disabled:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:disabled:bg-slate-800/50">
            <p class="mt-1 text-xs text-slate-400">Kosongkan jika sama dengan waktu selesai.</p>
            <p v-if="form.errors.latest_start_at" class="mt-1 text-xs text-rose-600">{{ form.errors.latest_start_at }}</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs font-bold text-slate-500">Zona Waktu</label>
              <select v-model="form.timezone" :disabled="fieldsDisabled || !canManage" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="Asia/Jakarta">WIB (Asia/Jakarta)</option>
                <option value="Asia/Makassar">WITA (Asia/Makassar)</option>
                <option value="Asia/Jayapura">WIT (Asia/Jayapura)</option>
              </select>
            </div>
            <div>
              <label class="text-xs font-bold text-slate-500">Maksimal Attempt</label>
              <input v-model="form.attempt_limit" type="number" min="1" :disabled="fieldsDisabled || !canManage" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="text-xs font-bold text-slate-500">Durasi Soal Default (detik)</label>
              <input v-model="form.default_question_duration_seconds" type="number" min="1" required :disabled="fieldsDisabled || !canManage" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
            <div>
              <label class="text-xs font-bold text-slate-500">Durasi Total (menit)</label>
              <input v-model="form.total_duration_minutes" type="number" min="1" placeholder="Opsional" :disabled="fieldsDisabled || !canManage" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
          </div>

          <hr class="border-slate-100 dark:border-slate-800">
          <h4 class="text-xs font-bold uppercase text-slate-400">Perilaku Assessment</h4>
          <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
            <input v-model="form.shuffle_questions" type="checkbox" :disabled="fieldsDisabled || !canManage" class="h-4 w-4 rounded">
            Acak urutan soal
          </label>
          <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
            <input v-model="form.shuffle_options" type="checkbox" :disabled="fieldsDisabled || !canManage" class="h-4 w-4 rounded">
            Acak pilihan jawaban
          </label>
          <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
            <input v-model="form.allow_previous_question" type="checkbox" :disabled="fieldsDisabled || !canManage" class="h-4 w-4 rounded">
            Izinkan kembali ke soal sebelumnya
          </label>
          <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
            <input v-model="form.auto_submit" type="checkbox" :disabled="fieldsDisabled || !canManage" class="h-4 w-4 rounded">
            Auto-submit ketika waktu habis
          </label>
        </div>
      </div>

      <div v-if="locked && canManage" class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/40 dark:bg-amber-900/10">
        <label class="flex items-center gap-2 text-sm font-bold text-rose-600">
          <input v-model="overrideChecked" type="checkbox" class="h-4 w-4 rounded">
          <ShieldAlert class="h-4 w-4" /> Override pengaturan kritis
        </label>
        <div v-if="overrideChecked" class="mt-2">
          <label class="text-xs font-bold text-slate-500">Alasan override (wajib)</label>
          <textarea v-model="overrideReason" rows="2" placeholder="Contoh: jadwal mundur karena permintaan pimpinan" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
        </div>
        <p v-if="form.errors.reason" class="mt-1 text-xs text-rose-600">{{ form.errors.reason }}</p>
      </div>

      <div v-if="canManage" class="flex justify-end">
        <button type="submit" :disabled="form.processing" class="rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-slate-950 shadow hover:bg-amber-400 disabled:opacity-60">
          Simpan Pengaturan
        </button>
      </div>
    </form>
  </div>
</template>
