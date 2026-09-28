<script setup>
import { computed } from 'vue';
import { X, File, FileText, Download } from 'lucide-vue-next';
import { riskLabel, materiStatusLabel, formatShortDate, materiFileUrl } from '@/utils/evalAcademy';

const props = defineProps({
  materi: { type: Object, required: true },
});

const emit = defineEmits(['closed']);

const STATUS_BADGE = {
  active: 'bg-emerald-600 text-white',
  revision_needed: 'bg-amber-400 text-slate-900',
  expired: 'bg-rose-600 text-white',
};

const fileUrl = computed(() => materiFileUrl(props.materi.id));

const infoRows = computed(() => [
  ['Kategori', props.materi.category || '—'],
  ['Tingkat Risiko', riskLabel(props.materi.risk_level)],
  ['Tanggal Berlaku', props.materi.effective_date || '—'],
  ['Kadaluarsa', props.materi.expired_date || '—'],
  ['Dibuat oleh', props.materi.creator_name || '—'],
  ['Dibuat', formatShortDate(props.materi.created_at)],
]);

const stats = computed(() => [
  { label: 'Soal', value: props.materi.total_questions },
  { label: 'Assigned', value: props.materi.total_assigned },
  { label: 'Selesai', value: props.materi.total_completed },
]);
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <div class="my-8 w-full max-w-6xl rounded-2xl bg-white p-5 shadow-2xl">
      <div class="mb-3 flex items-start justify-between gap-3">
        <h5 class="text-base font-bold text-slate-800">{{ materi.title }}</h5>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
        <div class="md:col-span-7">
          <template v-if="materi.file_path">
            <iframe v-if="materi.file_type === 'pdf'" :src="fileUrl" class="h-[500px] w-full rounded-xl border border-slate-200" />
            <img v-else-if="materi.file_type === 'image'" :src="fileUrl" class="mx-auto max-h-[500px] rounded-xl object-contain" />
            <div v-else class="py-6 text-center text-slate-500">
              <FileText class="mx-auto mb-2 h-12 w-12 text-blue-600" />
              <small>Preview tidak tersedia untuk file DOCX</small>
            </div>
            <div class="mt-2 text-right">
              <a
                :href="fileUrl"
                download
                class="inline-flex items-center gap-1 rounded-full border border-rose-500 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50"
              >
                <Download class="h-3.5 w-3.5" /> Unduh File
              </a>
            </div>
          </template>
          <div v-else class="py-10 text-center text-slate-400">
            <File class="mx-auto mb-2 h-10 w-10 text-slate-200" />
            <p class="text-sm">Tidak ada file</p>
          </div>
        </div>

        <div class="md:col-span-5">
          <div class="mb-3 flex gap-1">
            <span :class="['rounded-full px-2.5 py-0.5 text-[11px] font-semibold', STATUS_BADGE[materi.status] ?? 'bg-slate-500 text-white']">
              {{ materiStatusLabel(materi.status) }}
            </span>
            <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600">{{ materi.revision }}</span>
          </div>

          <table class="w-full text-[13px]">
            <tr v-for="[label, value] in infoRows" :key="label">
              <td class="w-32 py-1 font-semibold text-slate-500">{{ label }}</td>
              <td class="py-1 text-slate-800">{{ value }}</td>
            </tr>
          </table>

          <hr class="my-2 border-slate-200" />
          <h6 class="text-[13px] font-bold text-slate-800">Deskripsi</h6>
          <p class="whitespace-pre-line text-[13px] text-slate-600">{{ materi.description || 'Tidak ada deskripsi.' }}</p>
          <hr class="my-2 border-slate-200" />

          <div class="grid grid-cols-3 divide-x divide-slate-200 text-center">
            <div v-for="stat in stats" :key="stat.label">
              <div class="text-lg font-extrabold text-slate-800">{{ stat.value }}</div>
              <div class="text-[11px] text-slate-400">{{ stat.label }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
