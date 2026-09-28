<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Clock, RefreshCw, Shuffle, Settings, Send, Trash2 } from 'lucide-vue-next';
import { formatShortDate } from '@/utils/evalAcademy';
import { assessmentStatusClass, assessmentStatusLabel } from '@/utils/academyAssessment';

const props = defineProps({
  assessment: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['delete-requested']);

const manageUrl = computed(() => `/one-academy/evaluasi-kompetensi?tab=assessment&manage=${props.assessment.id}`);

const stats = computed(() => [
  { label: 'Soal', value: props.assessment.total_questions },
  { label: 'Target Role', value: props.assessment.total_targets },
  { label: 'Pass Grade', value: `${props.assessment.passing_grade}%` },
]);
</script>

<template>
  <div class="flex h-full flex-col rounded-2xl border-[1.5px] border-slate-100 bg-white shadow-sm">
    <div class="flex-1 p-5">
      <div class="mb-2 flex items-start justify-between">
        <span :class="['rounded-full px-2.5 py-0.5 text-[11px] font-semibold', assessmentStatusClass(assessment.status)]">
          {{ assessmentStatusLabel(assessment.status) }}
        </span>
        <small class="text-[11px] text-slate-400">{{ formatShortDate(assessment.created_at) }}</small>
      </div>
      <div class="mb-1 text-[15px] font-bold text-slate-800">{{ assessment.title }}</div>
      <small class="mb-3 flex items-center gap-1 text-xs text-slate-400">
        <BookOpen class="h-3.5 w-3.5" /> {{ assessment.procedure_title || '—' }}
      </small>

      <div class="mb-3 grid grid-cols-3 divide-x divide-slate-200 text-center">
        <div v-for="stat in stats" :key="stat.label">
          <div class="text-base font-extrabold text-slate-800">{{ stat.value }}</div>
          <div class="text-[10px] text-slate-400">{{ stat.label }}</div>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 text-xs text-slate-500">
        <span class="inline-flex items-center gap-1"><Clock class="h-3.5 w-3.5" />{{ assessment.timer_minutes }} mnt</span>
        <span class="inline-flex items-center gap-1"><RefreshCw class="h-3.5 w-3.5" />{{ assessment.retry_limit === 99 ? '∞' : assessment.retry_limit }}x</span>
        <span v-if="assessment.randomize_questions" class="inline-flex items-center gap-1"><Shuffle class="h-3.5 w-3.5" />Acak</span>
      </div>
    </div>

    <div class="flex flex-wrap gap-2 px-4 pb-4">
      <Link :href="manageUrl" class="inline-flex flex-1 items-center justify-center gap-1 rounded-full border border-blue-500 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50">
        <Settings class="h-3.5 w-3.5" /> Kelola
      </Link>
      <template v-if="canManage">
        <Link v-if="assessment.status === 'draft'" :href="manageUrl" class="inline-flex items-center gap-1 rounded-full border border-green-600 px-3 py-1.5 text-xs font-semibold text-green-700 hover:bg-green-50">
          <Send class="h-3.5 w-3.5" /> Publish
        </Link>
        <button
          type="button"
          title="Hapus Assessment"
          class="inline-flex items-center rounded-full border border-rose-500 px-3 py-1.5 text-rose-600 hover:bg-rose-50"
          @click="emit('delete-requested', assessment)"
        >
          <Trash2 class="h-3.5 w-3.5" />
        </button>
      </template>
    </div>
  </div>
</template>
