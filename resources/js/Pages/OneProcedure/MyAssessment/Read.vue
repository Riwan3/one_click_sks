<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Clock, ShieldCheck, Download, FileText, PlayCircle } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  assessment: { type: Object, required: true },
  procedure: { type: Object, required: true },
});

const RISK_LABEL = { low: 'Rendah', medium: 'Sedang', high: 'Tinggi', critical: 'Kritis' };
const RISK_CLASS = {
  low: 'bg-green-100 text-green-700',
  medium: 'bg-yellow-100 text-yellow-800',
  high: 'bg-red-100 text-red-700',
  critical: 'bg-purple-100 text-purple-700',
};

const hasRead = ref(false);
const form = useForm({});
const startAssessment = () => form.post(`/one-procedure/assessment/${props.assessment.id}/start`);
</script>

<template>
  <Head :title="`Baca Prosedur - ${procedure.title}`" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="mx-auto max-w-3xl space-y-4">
      <Link href="/one-procedure/assessment" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700">
        <ArrowLeft class="h-3.5 w-3.5" />Kembali
      </Link>

      <div class="rounded-2xl bg-gradient-to-br from-slate-900 to-blue-950 p-6 text-white">
        <div class="text-[11px] uppercase tracking-wide text-white/50">Baca Prosedur</div>
        <h1 class="mt-1 text-lg font-bold">{{ procedure.title }}</h1>
        <div class="text-sm text-white/60">{{ assessment.title }}</div>
        <div class="mt-3 flex flex-wrap gap-2">
          <span class="rounded-full px-2.5 py-1 text-[11px] font-bold" :class="RISK_CLASS[procedure.risk_level] ?? 'bg-white/10 text-white/70'">{{ RISK_LABEL[procedure.risk_level] ?? '—' }}</span>
          <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2.5 py-1 text-[11px] text-white/80"><Clock class="h-3 w-3" />{{ assessment.timer_minutes }} menit</span>
          <span class="inline-flex items-center gap-1 rounded-full bg-white/10 px-2.5 py-1 text-[11px] text-white/80"><ShieldCheck class="h-3 w-3" />Pass {{ assessment.passing_grade }}%</span>
        </div>
      </div>

      <div v-if="procedure.file_path" class="overflow-hidden rounded-2xl bg-white shadow-sm">
        <iframe v-if="procedure.file_type === 'pdf'" :src="procedure.file_path" class="h-[420px] w-full border-0" title="Prosedur" />
        <img v-else-if="procedure.file_type === 'image'" :src="procedure.file_path" class="max-h-[420px] w-full object-contain" alt="Prosedur" />
        <div v-else class="flex flex-col items-center gap-3 bg-slate-50 py-10 text-center">
          <FileText class="h-10 w-10 text-blue-500" />
          <p class="px-6 text-sm text-slate-500">File dokumen tidak bisa dipreview. Silakan download untuk membaca.</p>
          <a :href="procedure.file_path" download class="inline-flex items-center gap-1.5 rounded-full border border-blue-300 px-4 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50">
            <Download class="h-3.5 w-3.5" />Download Dokumen
          </a>
        </div>
      </div>

      <div v-if="procedure.description" class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-400">Deskripsi</div>
        <p class="whitespace-pre-line text-sm text-slate-600">{{ procedure.description }}</p>
      </div>

      <div class="rounded-2xl bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm">
          <span class="text-slate-500">Tanggal Berlaku</span>
          <span class="font-semibold text-slate-800">{{ procedure.effective_date ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between border-b border-slate-100 py-2 text-sm">
          <span class="text-slate-500">Tingkat Risiko</span>
          <span class="font-semibold text-slate-800">{{ RISK_LABEL[procedure.risk_level] ?? '—' }}</span>
        </div>
        <div class="flex items-center justify-between py-2 text-sm">
          <span class="text-slate-500">Percobaan ke</span>
          <span class="font-semibold text-slate-800">{{ assessment.attempt_number }} / {{ assessment.effective_retry_limit >= 99 ? '∞' : assessment.effective_retry_limit }}</span>
        </div>
      </div>

      <div class="rounded-2xl bg-white p-4 shadow-sm">
        <label class="mb-3 flex cursor-pointer items-center gap-3 rounded-xl border-2 p-3 transition" :class="hasRead ? 'border-green-300 bg-green-50' : 'border-slate-200 bg-green-50/40'">
          <input v-model="hasRead" type="checkbox" class="h-5 w-5" />
          <div>
            <div class="text-sm font-bold text-slate-800">☑ Saya Sudah Membaca</div>
            <div class="text-xs text-slate-500">Centang setelah membaca prosedur di atas</div>
          </div>
        </label>
        <button
          type="button"
          :disabled="!hasRead || form.processing"
          class="flex w-full items-center justify-center gap-2 rounded-2xl bg-red-600 py-3.5 text-sm font-bold text-white transition disabled:bg-slate-200 disabled:text-slate-400"
          @click="startAssessment"
        >
          <PlayCircle class="h-4 w-4" />Mulai Assessment
        </button>
      </div>
    </div>
  </AppLayout>
</template>
