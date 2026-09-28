<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Settings, Send, Archive } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { ASSESSMENT_BASE_URL, assessmentStatusClass, assessmentStatusLabel, validityLabel } from '@/utils/academyAssessment';
import QuestionBank from './QuestionBank.vue';
import TargetDistribution from './TargetDistribution.vue';
import AssessmentSummary from './AssessmentSummary.vue';
import AssessmentSettingsModal from './AssessmentSettingsModal.vue';

const props = defineProps({
  assessment: { type: Object, required: true },
  questions: { type: Array, default: () => [] },
  stats: { type: Object, required: true },
  targetRoles: { type: Array, default: () => [] },
  signers: { type: Array, default: () => [] },
  canManage: { type: Boolean, default: false },
});

const isSettingsOpen = ref(false);

const settingBoxes = computed(() => [
  { label: 'PASSING GRADE', value: `${props.assessment.passing_grade}%` },
  { label: 'TIMER', value: `${props.assessment.timer_minutes} mnt` },
  { label: 'RETRY LIMIT', value: `${props.assessment.retry_limit}x` },
]);

const postAction = (action) => router.post(`${ASSESSMENT_BASE_URL}/${props.assessment.id}/${action}`, {}, { preserveScroll: true });

const handlePublish = async () => {
  if (await confirmDialog('Publish assessment ini? Notifikasi akan dikirim ke semua target.')) postAction('publish');
};

const handleRetrigger = async () => {
  if (await confirmDialog('Kirim ulang notifikasi ke semua karyawan yang sesuai target role? Notifikasi lama akan di-reset.')) postAction('retrigger');
};
</script>

<template>
  <div class="space-y-4">
    <div class="flex items-center gap-2 text-[13px]">
      <Link href="/one-academy/evaluasi-kompetensi?tab=assessment" class="inline-flex items-center gap-1 text-slate-500 hover:text-slate-700"><ArrowLeft class="h-3.5 w-3.5" />Daftar Assessment</Link>
      <span class="text-slate-400">/</span>
      <span class="font-semibold text-slate-800">{{ assessment.title }}</span>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
      <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 p-4">
        <div>
          <h6 class="text-sm font-bold text-slate-800">{{ assessment.title }}</h6>
          <small class="text-xs text-slate-500">Materi: <strong>{{ assessment.procedure_title }}</strong></small>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', assessmentStatusClass(assessment.status)]">{{ assessmentStatusLabel(assessment.status) }}</span>
          <template v-if="canManage">
            <button type="button" class="inline-flex items-center gap-1 rounded-full border border-slate-400 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="isSettingsOpen = true">
              <Settings class="h-3.5 w-3.5" />Edit Settings
            </button>
            <button v-if="assessment.status === 'draft'" type="button" class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700" @click="handlePublish">
              <Send class="h-3.5 w-3.5" />Publish & Kirim
            </button>
            <template v-else-if="assessment.status === 'published'">
              <button type="button" class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700" @click="handleRetrigger">
                <Send class="h-3.5 w-3.5" />Kirim Ulang Notifikasi
              </button>
              <button type="button" class="inline-flex items-center gap-1 rounded-full border border-amber-500 px-3 py-1.5 text-xs font-semibold text-amber-600 hover:bg-amber-50" @click="postAction('archive')">
                <Archive class="h-3.5 w-3.5" />Arsipkan
              </button>
            </template>
          </template>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-3 p-4 md:grid-cols-4">
        <div v-for="box in settingBoxes" :key="box.label" class="rounded-lg bg-slate-50 p-3">
          <div class="text-[11px] font-semibold text-slate-400">{{ box.label }}</div>
          <div class="text-xl font-bold text-slate-800">{{ box.value }}</div>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
          <div class="text-[11px] font-semibold text-amber-700">MASA BERLAKU SERTIFIKAT</div>
          <div class="mt-1 text-base font-bold text-amber-900">{{ validityLabel(assessment.validity_months) }}</div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
      <QuestionBank class="lg:col-span-2" :assessment-id="assessment.id" :questions="questions" :can-manage="canManage" />
      <div class="space-y-4">
        <TargetDistribution :assessment-id="assessment.id" :target-roles="targetRoles" :selected-roles="assessment.target_roles" :can-manage="canManage" />
        <AssessmentSummary :assessment="assessment" :stats="stats" :can-manage="canManage" />
      </div>
    </div>

    <AssessmentSettingsModal v-if="isSettingsOpen" :assessment="assessment" :signers="signers" @closed="isSettingsOpen = false" />
  </div>
</template>
