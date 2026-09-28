<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { FileText, FileImage, File, Calendar, User, Eye, Pencil, Trash2, PlusCircle, Users, BarChart3 } from 'lucide-vue-next';
import { riskClass, riskLabel, procedureStatusClass, procedureStatusLabel, formatShortDate } from '@/utils/evalProcedure';

const props = defineProps({
  procedure: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['detail-requested', 'edit-requested', 'delete-requested']);

const FILE_ICONS = {
  pdf: { icon: FileText, color: 'text-rose-600' },
  image: { icon: FileImage, color: 'text-sky-600' },
  docx: { icon: FileText, color: 'text-blue-600' },
};

const fileIcon = computed(() => FILE_ICONS[props.procedure.file_type] ?? { icon: File, color: 'text-rose-600' });

const assessmentUrl = computed(() => `/one-procedure/evaluasi?tab=assessment&create=1&proc_id=${props.procedure.id}`);

const stats = computed(() => [
  { label: 'Soal', value: props.procedure.total_questions },
  { label: 'Assigned', value: props.procedure.total_assigned },
  { label: 'Selesai', value: props.procedure.total_completed },
]);

const actionClass = 'flex h-8 w-8 items-center justify-center rounded-lg text-white shadow-sm transition';
</script>

<template>
  <div
    class="group flex cursor-pointer flex-col justify-between rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:border-rose-400 hover:shadow-md"
    @click="emit('detail-requested', procedure)"
  >
    <div class="p-5 pb-3">
      <div class="mb-2 flex items-start gap-3">
        <component :is="fileIcon.icon" :class="['mt-0.5 h-6 w-6 flex-shrink-0', fileIcon.color]" />
        <div class="min-w-0 flex-1">
          <h4 class="truncate text-sm font-bold text-slate-800 group-hover:text-rose-600" :title="procedure.title">{{ procedure.title }}</h4>
          <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ procedure.category || '—' }}</p>
        </div>
      </div>
      <div class="mt-3 flex flex-wrap items-center gap-1.5">
        <span :class="['rounded-full border px-2 py-0.5 text-[10px] font-bold', riskClass(procedure.risk_level)]">{{ riskLabel(procedure.risk_level) }}</span>
        <span :class="['rounded-full border px-2 py-0.5 text-[10px] font-bold', procedureStatusClass(procedure.status)]">{{ procedureStatusLabel(procedure.status) }}</span>
        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">{{ procedure.revision }}</span>
      </div>
    </div>

    <div class="border-y border-slate-100 bg-slate-50/50 px-5 py-3">
      <div class="grid grid-cols-3 divide-x divide-slate-200/80 text-center">
        <div v-for="stat in stats" :key="stat.label">
          <div class="text-sm font-extrabold text-slate-800">{{ stat.value }}</div>
          <div class="text-[10px] font-semibold text-slate-400">{{ stat.label }}</div>
        </div>
      </div>
      <div class="mt-3 space-y-0.5 text-[11px] text-slate-400">
        <div class="flex items-center gap-1.5 truncate">
          <Calendar class="h-3.5 w-3.5 flex-shrink-0" />
          <span>{{ formatShortDate(procedure.effective_date) }}</span>
          <span v-if="procedure.expired_date">· Exp: {{ formatShortDate(procedure.expired_date) }}</span>
        </div>
        <div class="flex items-center gap-1.5 truncate">
          <User class="h-3.5 w-3.5 flex-shrink-0" />
          <span>{{ procedure.creator_name || '—' }} · {{ formatShortDate(procedure.created_at) }}</span>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-1.5 rounded-b-2xl bg-slate-50 p-3" @click.stop>
      <button type="button" title="Lihat Detail" :class="[actionClass, 'bg-blue-600 hover:bg-blue-700']" @click="emit('detail-requested', procedure)">
        <Eye class="h-4 w-4" />
      </button>
      <template v-if="canManage">
        <button type="button" title="Edit" :class="[actionClass, 'bg-amber-500 hover:bg-amber-600']" @click="emit('edit-requested', procedure)">
          <Pencil class="h-4 w-4" />
        </button>
        <button type="button" title="Hapus" :class="[actionClass, 'bg-rose-600 hover:bg-rose-700']" @click="emit('delete-requested', procedure)">
          <Trash2 class="h-4 w-4" />
        </button>
        <Link :href="assessmentUrl" title="Buat Assessment & Tambah Soal" :class="[actionClass, 'bg-emerald-600 hover:bg-emerald-700']">
          <PlusCircle class="h-4 w-4" />
        </Link>
        <Link :href="`${assessmentUrl}&focus=target`" title="Assign Target Distribusi" :class="[actionClass, 'bg-cyan-500 hover:bg-cyan-600']">
          <Users class="h-4 w-4" />
        </Link>
        <Link href="/one-procedure/evaluasi?tab=monitoring" title="Monitoring" :class="[actionClass, 'bg-slate-500 hover:bg-slate-600']">
          <BarChart3 class="h-4 w-4" />
        </Link>
      </template>
    </div>
  </div>
</template>
