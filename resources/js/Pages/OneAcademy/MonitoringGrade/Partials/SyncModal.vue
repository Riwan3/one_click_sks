<script setup>
import { useForm } from '@inertiajs/vue3';
import { CloudDownload, X } from 'lucide-vue-next';

const props = defineProps({
  show: { type: Boolean, default: false },
  target: { type: String, required: true }, // 'grade' | 'versality'
  currentUrl: { type: String, default: null },
});

const emit = defineEmits(['close']);

const form = useForm({ sheets_url: '' });

const submit = () => {
  const url = props.target === 'grade'
    ? '/one-academy/monitoring-grade/sync-grade'
    : '/one-academy/monitoring-grade/sync-versatility';
  form.post(url, {
    onSuccess: () => { emit('close'); form.reset(); },
  });
};
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white">
          <CloudDownload class="h-5 w-5 text-blue-600" /> Sync Data {{ target === 'grade' ? 'Grade' : 'Versality' }}
        </h3>
        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="emit('close')"><X class="h-5 w-5" /></button>
      </div>
      <form class="space-y-3" @submit.prevent="submit">
        <div>
          <label class="text-xs font-bold text-slate-500">Link Google Spreadsheet</label>
          <input
            v-model="form.sheets_url"
            type="url"
            :placeholder="currentUrl || 'https://docs.google.com/spreadsheets/d/.../edit#gid=...'"
            class="mt-1 w-full rounded-xl border border-slate-200 p-2.5 text-xs dark:border-slate-700 dark:bg-slate-800"
          >
          <p class="mt-1 text-xs text-slate-400">Pastikan spreadsheet diset ke "Anyone with the link can view". Kosongkan untuk sync ulang dari URL tersimpan.</p>
          <p v-if="form.errors.sheets_url" class="mt-1 text-xs text-rose-600">{{ form.errors.sheets_url }}</p>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="rounded-xl bg-slate-200 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="emit('close')">Batal</button>
          <button type="submit" :disabled="form.processing" class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow disabled:opacity-60">
            <CloudDownload class="h-3.5 w-3.5" /> Tarik Data
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
