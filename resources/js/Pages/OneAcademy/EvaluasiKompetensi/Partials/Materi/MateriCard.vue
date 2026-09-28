<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { FileText, FileImage, File, Calendar, User, Eye, Pencil, Trash2, PlusCircle, Users, BarChart3 } from 'lucide-vue-next';
import { riskClass, riskLabel, materiStatusClass, materiStatusLabel, formatShortDate } from '@/utils/evalAcademy';

const props = defineProps({
  materi: { type: Object, required: true },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['detail-requested', 'edit-requested', 'delete-requested']);

const FILE_ICONS = {
  pdf: { icon: FileText, color: 'text-rose-600' },
  image: { icon: FileImage, color: 'text-sky-600' },
  docx: { icon: FileText, color: 'text-blue-600' },
};

const fileIcon = computed(() => FILE_ICONS[props.materi.file_type] ?? { icon: File, color: 'text-rose-600' });

const assessmentUrl = computed(() => `/one-academy/evaluasi-kompetensi?tab=assessment&create=1&proc_id=${props.materi.id}`);

const stats = computed(() => [
  { label: 'Soal', value: props.materi.total_questions },
  { label: 'Assigned', value: props.materi.total_assigned },
  { label: 'Selesai', value: props.materi.total_completed },
]);

const actionClass = 'flex h-8 w-8 items-center justify-center rounded-lg text-white shadow-sm transition';
</script>

<template>
  <div
    class="group flex cursor-pointer flex-col justify-between rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:border-rose-400 hover:shadow-md"
    @click="emit('detail-requested', materi)"
  >
    <div class="p-5 pb-3">
      <div class="mb-2 flex items-start gap-3">
        <component :is="fileIcon.icon" :class="['mt-0.5 h-6 w-6 flex-shrink-0', fileIcon.color]" />
        <div class="min-w-0 flex-1">
          <h4 class="truncate text-sm font-bold text-slate-800 group-hover:text-rose-600" :title="materi.title">{{ materi.title }}</h4>
          <p class="mt-0.5 truncate text-[11px] text-slate-400">{{ materi.category || '—' }}</p>
        </div>
      </div>
      <div class="mt-3 flex flex-wrap items-center gap-1.5">
        <span :class="['rounded-full border px-2 py-0.5 text-[10px] font-bold', riskClass(materi.risk_level)]">{{ riskLabel(materi.risk_level) }}</span>
        <span :class="['rounded-full border px-2 py-0.5 text-[10px] font-bold', materiStatusClass(materi.status)]">{{ materiStatusLabel(materi.status) }}</span>
        <span class="rounded bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">{{ materi.revision }}</span>
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
          <span>{{ formatShortDate(materi.effective_date) }}</span>
          <span v-if="materi.expired_date">· Exp: {{ formatShortDate(materi.expired_date) }}</span>
        </div>
        <div class="flex items-center gap-1.5 truncate">
          <User class="h-3.5 w-3.5 flex-shrink-0" />
          <span>{{ materi.creator_name || '—' }} · {{ formatShortDate(materi.created_at) }}</span>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-1.5 rounded-b-2xl bg-slate-50 p-3" @click.stop>
      <button type="button" title="Lihat Detail" :class="[actionClass, 'bg-blue-600 hover:bg-blue-700']" @click="emit('detail-requested', materi)">
        <Eye class="h-4 w-4" />
      </button>
      <template v-if="canManage">
        <button type="button" title="Edit" :class="[actionClass, 'bg-amber-500 hover:bg-amber-600']" @click="emit('edit-requested', materi)">
          <Pencil class="h-4 w-4" />
        </button>
        <button type="button" title="Hapus" :class="[actionClass, 'bg-rose-600 hover:bg-rose-700']" @click="emit('delete-requested', materi)">
          <Trash2 class="h-4 w-4" />
        </button>
        <Link :href="assessmentUrl" title="Buat Assessment & Tambah Soal" :class="[actionClass, 'bg-emerald-600 hover:bg-emerald-700']">
          <PlusCircle class="h-4 w-4" />
        </Link>
        <Link :href="`${assessmentUrl}&focus=target`" title="Assign Target Distribusi" :class="[actionClass, 'bg-cyan-500 hover:bg-cyan-600']">
          <Users class="h-4 w-4" />
        </Link>
        <Link href="/one-academy/evaluasi-kompetensi?tab=monitoring" title="Monitoring" :class="[actionClass, 'bg-slate-500 hover:bg-slate-600']">
          <BarChart3 class="h-4 w-4" />
        </Link>
      </template>
    </div>
  </div>
</template>
