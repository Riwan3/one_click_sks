<script setup>
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { X } from 'lucide-vue-next';
import { RISK_OPTIONS, PROCEDURE_STATUS_OPTIONS, procedureFileError } from '@/utils/evalProcedure';

const props = defineProps({
  procedure: { type: Object, required: true },
  categories: { type: Array, default: () => [] },
});

const emit = defineEmits(['closed']);

const form = useForm({
  title: props.procedure.title,
  category: props.procedure.category ?? '',
  revision: props.procedure.revision ?? '',
  description: props.procedure.description ?? '',
  risk_level: props.procedure.risk_level,
  status: props.procedure.status,
  effective_date: props.procedure.effective_date ?? '',
  expired_date: props.procedure.expired_date ?? '',
  file: null,
});

const inputClass = 'w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs focus:border-rose-500 focus:bg-white focus:ring-2 focus:ring-rose-500/20';

const handleFileChange = (event) => {
  const file = event.target.files[0] ?? null;
  const error = file && procedureFileError(file);
  if (error) {
    Swal.fire({ icon: 'warning', text: error });
    event.target.value = '';
    return;
  }
  form.file = file;
};

const handleSubmit = () => {
  form.post(`/one-procedure/evaluasi/procedure/update/${props.procedure.id}`, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => emit('closed'),
  });
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <form class="my-8 w-full max-w-3xl space-y-4 rounded-2xl bg-white p-6 shadow-2xl" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h5 class="text-base font-bold text-slate-800">Edit Prosedur</h5>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="sm:col-span-2">
          <label class="mb-1 block text-xs font-bold text-slate-700">Judul <span class="text-rose-500">*</span></label>
          <input v-model="form.title" type="text" required :class="inputClass" />
          <p v-if="form.errors.title" class="mt-1 text-[11px] text-rose-600">{{ form.errors.title }}</p>
        </div>
        <div>
          <label class="mb-1 block text-xs font-bold text-slate-700">Kategori</label>
          <input v-model="form.category" type="text" list="eval-edit-cat-list" :class="inputClass" />
          <datalist id="eval-edit-cat-list"><option v-for="c in categories" :key="c" :value="c" /></datalist>
        </div>
        <div>
          <label class="mb-1 block text-xs font-bold text-slate-700">Revisi</label>
          <input v-model="form.revision" type="text" :class="inputClass" />
        </div>
        <div class="sm:col-span-2">
          <label class="mb-1 block text-xs font-bold text-slate-700">Deskripsi</label>
          <textarea v-model="form.description" rows="2" :class="inputClass" />
        </div>
        <div>
          <label class="mb-1 block text-xs font-bold text-slate-700">Tingkat Risiko</label>
          <select v-model="form.risk_level" :class="inputClass">
            <option v-for="r in RISK_OPTIONS" :key="r.value" :value="r.value">{{ r.label }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-xs font-bold text-slate-700">Status</label>
          <select v-model="form.status" :class="inputClass">
            <option v-for="s in PROCEDURE_STATUS_OPTIONS" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-xs font-bold text-slate-700">Tanggal Berlaku</label>
          <input v-model="form.effective_date" type="date" :class="inputClass" />
        </div>
        <div>
          <label class="mb-1 block text-xs font-bold text-slate-700">Tanggal Kadaluarsa</label>
          <input v-model="form.expired_date" type="date" :class="inputClass" />
        </div>
        <div class="sm:col-span-2">
          <label class="mb-1 block text-xs font-bold text-slate-700">Ganti File (opsional)</label>
          <input type="file" accept=".pdf,.jpg,.jpeg,.png,.docx" :class="inputClass" @change="handleFileChange" />
          <p v-if="form.errors.file" class="mt-1 text-[11px] text-rose-600">{{ form.errors.file }}</p>
        </div>
      </div>

      <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
        <button type="button" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100" @click="emit('closed')">Batal</button>
        <button type="submit" :disabled="form.processing" class="rounded-xl bg-rose-600 px-5 py-2 text-xs font-bold text-white hover:bg-rose-700 disabled:opacity-60">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</template>
