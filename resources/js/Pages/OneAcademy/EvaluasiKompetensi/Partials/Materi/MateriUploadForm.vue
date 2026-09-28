<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { UploadCloud, CheckCircle2 } from 'lucide-vue-next';
import { RISK_OPTIONS, MATERI_STATUS_OPTIONS, materiFileError } from '@/utils/evalAcademy';

defineProps({
  categories: { type: Array, default: () => [] },
});

const emit = defineEmits(['closed']);

const form = useForm({
  file: null,
  title: '',
  category: '',
  revision: 'Rev. 0',
  description: '',
  risk_level: 'medium',
  status: 'active',
  effective_date: '',
  expired_date: '',
});

const fileInput = ref(null);
const isDragging = ref(false);

const inputClass = 'w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20';

const pickFile = (file) => {
  if (!file) return;
  const error = materiFileError(file);
  if (error) {
    Swal.fire({ icon: 'warning', text: error });
    return;
  }
  form.file = file;
};

const handleDrop = (event) => {
  isDragging.value = false;
  pickFile(event.dataTransfer.files[0]);
};

const handleSubmit = () => {
  form.post('/one-academy/evaluasi-kompetensi/materi', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      emit('closed');
    },
  });
};
</script>

<template>
  <form class="space-y-4 border-t border-slate-100 bg-slate-50/50 p-5" @submit.prevent="handleSubmit">
    <div
      :class="['cursor-pointer rounded-2xl border-2 border-dashed p-6 text-center transition', isDragging ? 'border-rose-500 bg-rose-50' : 'border-slate-300 hover:border-rose-500 hover:bg-rose-50/20']"
      @click="fileInput.click()"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleDrop"
    >
      <component :is="form.file ? CheckCircle2 : UploadCloud" :class="['mx-auto mb-2 h-8 w-8', form.file ? 'text-emerald-500' : 'text-slate-400']" />
      <p class="mb-0.5 text-xs font-bold text-slate-700">{{ form.file ? 'File siap diupload:' : 'Seret & lepas file di sini atau klik untuk pilih' }}</p>
      <p class="mb-2 text-[11px] text-slate-400">PDF · JPG · PNG · DOCX — Maks. 20 MB</p>
      <span :class="['inline-block rounded-full px-3 py-1 text-[11px] font-semibold', form.file ? 'bg-emerald-600 text-white' : 'bg-slate-500 text-white']">
        {{ form.file ? `${form.file.name} (${(form.file.size / 1024 / 1024).toFixed(1)} MB)` : 'Belum ada file' }}
      </span>
      <input ref="fileInput" type="file" accept=".pdf,.jpg,.jpeg,.png,.docx" class="hidden" @change="pickFile($event.target.files[0])" />
    </div>
    <p v-if="form.errors.file" class="text-[11px] text-rose-600">{{ form.errors.file }}</p>

    <div class="grid grid-cols-1 gap-3 md:grid-cols-12">
      <div class="md:col-span-6">
        <label class="mb-1 block text-xs font-bold text-slate-700">Judul Materi <span class="text-rose-500">*</span></label>
        <input v-model="form.title" type="text" required placeholder="Judul materi training..." :class="inputClass" />
        <p v-if="form.errors.title" class="mt-1 text-[11px] text-rose-600">{{ form.errors.title }}</p>
      </div>
      <div class="md:col-span-3">
        <label class="mb-1 block text-xs font-bold text-slate-700">Kategori</label>
        <input v-model="form.category" type="text" list="am-cat-list" placeholder="Contoh: K3, Operasional..." :class="inputClass" />
        <datalist id="am-cat-list"><option v-for="c in categories" :key="c" :value="c" /></datalist>
      </div>
      <div class="md:col-span-3">
        <label class="mb-1 block text-xs font-bold text-slate-700">Revisi</label>
        <input v-model="form.revision" type="text" placeholder="Rev. 0" :class="inputClass" />
      </div>
      <div class="md:col-span-12">
        <label class="mb-1 block text-xs font-bold text-slate-700">Deskripsi</label>
        <textarea v-model="form.description" rows="2" placeholder="Deskripsi singkat materi..." :class="inputClass" />
      </div>
      <div class="md:col-span-3">
        <label class="mb-1 block text-xs font-bold text-slate-700">Tingkat Risiko <span class="text-rose-500">*</span></label>
        <select v-model="form.risk_level" required :class="inputClass">
          <option v-for="r in RISK_OPTIONS" :key="r.value" :value="r.value">{{ r.label }}</option>
        </select>
      </div>
      <div class="md:col-span-3">
        <label class="mb-1 block text-xs font-bold text-slate-700">Status</label>
        <select v-model="form.status" :class="inputClass">
          <option v-for="s in MATERI_STATUS_OPTIONS" :key="s.value" :value="s.value">{{ s.label }}</option>
        </select>
      </div>
      <div class="md:col-span-3">
        <label class="mb-1 block text-xs font-bold text-slate-700">Tanggal Berlaku</label>
        <input v-model="form.effective_date" type="date" :class="inputClass" />
      </div>
      <div class="md:col-span-3">
        <label class="mb-1 block text-xs font-bold text-slate-700">Tanggal Kadaluarsa</label>
        <input v-model="form.expired_date" type="date" :class="inputClass" />
      </div>
    </div>

    <div class="flex items-center justify-end gap-2 border-t border-slate-200 pt-3">
      <button type="button" class="rounded-xl px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100" @click="emit('closed')">Batal</button>
      <button
        type="submit"
        :disabled="form.processing"
        class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-rose-700 disabled:opacity-60"
      >
        <UploadCloud class="h-3.5 w-3.5" /> Simpan Materi
      </button>
    </div>
  </form>
</template>
