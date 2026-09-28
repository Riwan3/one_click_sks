<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { RotateCcw, X } from 'lucide-vue-next';

const props = defineProps({
  attemptId: { type: [Number, null], default: null },
});

const emit = defineEmits(['close', 'reset']);

const form = useForm({ reason: '' });

watch(() => props.attemptId, () => form.reset());

const submit = () => {
  form.post(`/one-academy/best-employee/attempts/${props.attemptId}/reset`, {
    preserveScroll: true,
    onSuccess: () => emit('reset'),
  });
};
</script>

<template>
  <div v-if="attemptId" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/70 p-4">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-base font-bold text-rose-600">
          <RotateCcw class="h-5 w-5" /> Reset Attempt
        </h3>
        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="emit('close')">
          <X class="h-5 w-5" />
        </button>
      </div>

      <form class="space-y-3" @submit.prevent="submit">
        <p class="text-xs text-slate-500">Attempt lama TIDAK dihapus (tetap tersimpan sebagai audit dengan status "Reset"). Peserta akan mendapat kesempatan baru sesuai batas attempt kegiatan.</p>
        <div>
          <label class="text-xs font-bold text-slate-500">Alasan Reset (wajib)</label>
          <textarea v-model="form.reason" rows="3" placeholder="Contoh: kendala jaringan terverifikasi, diminta mengulang" class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
          <p v-if="form.errors.reason" class="mt-1 text-xs text-rose-600">{{ form.errors.reason }}</p>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="emit('close')">Batal</button>
          <button type="submit" :disabled="form.processing" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow disabled:opacity-60">Ya, Reset Attempt</button>
        </div>
      </form>
    </div>
  </div>
</template>
