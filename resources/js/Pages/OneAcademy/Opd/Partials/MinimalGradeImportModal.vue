<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';

const props = defineProps({
  open: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const form = useForm({ sheet_url: '', file_sheet: null, replace_all: false });

watch(() => props.open, (isOpen) => {
  if (isOpen) form.reset().clearErrors();
});

const handleSubmit = () => form.post('/one-academy/opd/master-grade/minimal-grade/import', {
  forceFormData: true,
  preserveScroll: true,
  onSuccess: () => emit('close'),
});
</script>

<template>
  <BaseModal :open="open" title="Import Spreadsheet Minimal Grade" size="md" @close="emit('close')">
    <form class="space-y-4 p-5" @submit.prevent="handleSubmit">
      <p class="text-xs text-slate-500">
        Baris judul sheet harus berisi kolom: <b>UNIT, GRADE MIN-MAX, GRADE MIN, CLUSTER UNIT</b>.
        Data dengan UNIT dan CLUSTER UNIT yang sama diperbarui, selain itu ditambahkan.
      </p>

      <div>
        <label class="text-xs font-semibold text-slate-600">Link Google Sheet</label>
        <input v-model="form.sheet_url" type="url" placeholder="https://docs.google.com/spreadsheets/d/.../edit?gid=0" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500">
        <p class="mt-1 text-[11px] text-slate-400">Sheet harus dibagikan sebagai "Anyone with the link". Tab yang dibaca sesuai gid di link.</p>
        <p v-if="form.errors.sheet_url" class="mt-1 text-[11px] text-red-600">{{ form.errors.sheet_url }}</p>
      </div>

      <p class="text-center text-xs text-slate-400">— atau —</p>

      <div>
        <label class="text-xs font-semibold text-slate-600">File Excel / CSV</label>
        <input type="file" accept=".xlsx,.csv" class="mt-1 w-full text-xs" @input="form.file_sheet = $event.target.files[0]">
        <p class="mt-1 text-[11px] text-slate-400">.xlsx (sheet pertama) atau .csv, maksimal 5 MB.</p>
        <p v-if="form.errors.file_sheet" class="mt-1 text-[11px] text-red-600">{{ form.errors.file_sheet }}</p>
      </div>

      <label class="flex items-center gap-2 text-xs text-slate-700">
        <input v-model="form.replace_all" type="checkbox"> Hapus semua data lama dulu (ganti total)
      </label>

      <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
        <button type="button" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700" @click="emit('close')">Batal</button>
        <button type="submit" :disabled="form.processing" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white disabled:opacity-50">Import</button>
      </div>
    </form>
  </BaseModal>
</template>
