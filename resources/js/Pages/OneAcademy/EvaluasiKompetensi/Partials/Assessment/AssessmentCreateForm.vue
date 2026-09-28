<script setup>
import { ref, onMounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, ArrowRight, BookOpen, Settings, Users, Award, AlertTriangle } from 'lucide-vue-next';
import { ASSESSMENT_BASE_URL, RETRY_OPTIONS, VALIDITY_OPTIONS } from '@/utils/academyAssessment';

const props = defineProps({
  materiList: { type: Array, default: () => [] },
  targetRoles: { type: Array, default: () => [] },
  procId: { type: Number, default: 0 },
  focus: { type: String, default: '' },
});

const form = useForm({
  procedure_id: props.materiList.some((m) => m.id === props.procId) ? props.procId : '',
  title: '',
  passing_grade: 75,
  timer_minutes: 60,
  retry_limit: 3,
  validity_months: 6,
  randomize_questions: false,
  mandatory_read: false,
  target_roles: [],
});

const targetSection = ref(null);
const isTargetHighlighted = ref(false);

const inputClass = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20';
const labelClass = 'mb-1 block text-[13px] font-semibold text-slate-700';

const handleSubmit = () => form.post(ASSESSMENT_BASE_URL);

onMounted(() => {
  if (props.focus !== 'target') return;
  setTimeout(() => {
    targetSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    isTargetHighlighted.value = true;
    setTimeout(() => { isTargetHighlighted.value = false; }, 3000);
  }, 300);
});
</script>

<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <div class="flex items-center gap-2 text-[13px]">
      <Link href="/one-academy/evaluasi-kompetensi?tab=assessment" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-700"><ArrowLeft class="h-3.5 w-3.5" />Daftar Assessment</Link>
      <span class="text-slate-400">/</span>
      <span class="font-semibold text-slate-800">Buat Assessment Baru</span>
    </div>

    <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
      <h6 class="flex items-center gap-2 border-b border-slate-100 p-4 text-sm font-bold text-slate-800"><BookOpen class="h-4 w-4 text-rose-600" />1. Pilih Materi</h6>
      <div class="grid grid-cols-1 gap-3 p-5 md:grid-cols-3">
        <div class="md:col-span-2">
          <label :class="labelClass">Materi <span class="text-rose-600">*</span></label>
          <select v-model="form.procedure_id" required :class="inputClass">
            <option value="">-- Pilih Materi --</option>
            <option v-for="m in materiList" :key="m.id" :value="m.id">{{ m.title }} ({{ m.category || 'Umum' }})</option>
          </select>
          <small v-if="materiList.length === 0" class="mt-1 inline-flex items-center gap-1 text-xs text-amber-600">
            <AlertTriangle class="h-3.5 w-3.5" />Belum ada materi. <Link href="/one-academy/evaluasi-kompetensi?tab=materi" class="underline">Upload materi dulu.</Link>
          </small>
          <p v-if="form.errors.procedure_id" class="mt-1 text-[11px] text-rose-600">{{ form.errors.procedure_id }}</p>
        </div>
        <div>
          <label :class="labelClass">Judul Assessment <span class="text-rose-600">*</span></label>
          <input v-model="form.title" type="text" required placeholder="Judul assessment..." :class="inputClass" />
          <p v-if="form.errors.title" class="mt-1 text-[11px] text-rose-600">{{ form.errors.title }}</p>
        </div>
      </div>
    </section>

    <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
      <h6 class="flex items-center gap-2 border-b border-slate-100 p-4 text-sm font-bold text-slate-800"><Settings class="h-4 w-4 text-rose-600" />2. Pengaturan Assessment</h6>
      <div class="grid grid-cols-2 gap-3 p-5 md:grid-cols-5">
        <div>
          <label :class="labelClass">Passing Grade (%)</label>
          <input v-model.number="form.passing_grade" type="number" min="0" max="100" :class="inputClass" />
        </div>
        <div>
          <label :class="labelClass">Timer (menit)</label>
          <input v-model.number="form.timer_minutes" type="number" min="1" :class="inputClass" />
        </div>
        <div>
          <label :class="labelClass">Retry Limit</label>
          <select v-model.number="form.retry_limit" :class="inputClass">
            <option v-for="o in RETRY_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
          </select>
        </div>
        <div>
          <label :class="[labelClass, 'inline-flex items-center gap-1']"><Award class="h-3.5 w-3.5 text-amber-500" />Masa Berlaku Sertifikat</label>
          <select v-model.number="form.validity_months" :class="inputClass">
            <option v-for="o in VALIDITY_OPTIONS" :key="o.value" :value="o.value">{{ o.label }}</option>
          </select>
        </div>
        <div class="col-span-2 flex flex-col justify-end gap-2 pb-1 text-[13px] md:col-span-1">
          <label class="inline-flex items-center gap-2"><input v-model="form.randomize_questions" type="checkbox" class="rounded" />Acak Urutan Soal</label>
          <label class="inline-flex items-center gap-2"><input v-model="form.mandatory_read" type="checkbox" class="rounded" />Wajib Baca Materi Dulu</label>
        </div>
      </div>
    </section>

    <section
      ref="targetSection"
      :class="['rounded-2xl border border-slate-200/80 bg-white shadow-sm transition', isTargetHighlighted ? 'ring-[3px] ring-[#0f3460]' : '']"
    >
      <h6 class="flex items-center gap-2 border-b border-slate-100 p-4 text-sm font-bold text-slate-800"><Users class="h-4 w-4 text-rose-600" />3. Target Distribusi</h6>
      <div class="p-5">
        <p class="mb-3 text-[13px] text-slate-500">Pilih jabatan yang akan menerima assessment ini. Notifikasi akan dikirim saat assessment dipublish.</p>
        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
          <label v-for="role in targetRoles" :key="role" class="flex cursor-pointer items-start gap-2 rounded-xl border border-slate-200 bg-slate-50 p-3 text-[13px] font-semibold text-slate-700">
            <input v-model="form.target_roles" type="checkbox" :value="role" class="mt-0.5 rounded" />
            {{ role }}
          </label>
        </div>
      </div>
    </section>

    <div class="flex justify-end gap-2">
      <Link href="/one-academy/evaluasi-kompetensi?tab=assessment" class="rounded-full border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</Link>
      <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-6 py-2 text-sm font-semibold text-white hover:bg-rose-700 disabled:opacity-60">
        <ArrowRight class="h-4 w-4" /> Buat Assessment & Tambah Soal
      </button>
    </div>
  </form>
</template>
