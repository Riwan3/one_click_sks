<script setup>
import { X, UserSquare, CheckSquare, Square, FileText, ExternalLink } from 'lucide-vue-next';

defineProps({
  assignment: { type: Object, required: true },
  aktivitasDocs: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['closed']);

const isGmpTask = (task) => task.toUpperCase().includes('GMP');
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white p-5 shadow-2xl">
      <div class="mb-3 flex items-center justify-between">
        <h5 class="text-base font-bold text-slate-800">Detail Assignment</h5>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <div class="mb-3 flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-500"><UserSquare class="h-5 w-5" /></div>
        <div>
          <div class="text-[15px] font-bold text-slate-800">{{ assignment.pengawas || '-' }}</div>
          <div class="text-xs text-slate-500">{{ assignment.unit || '-' }} — {{ assignment.area || '-' }}</div>
        </div>
      </div>

      <div class="mb-3">
        <div class="mb-2 text-[13px] font-bold text-slate-600">Task Checklist:</div>
        <ul class="divide-y divide-slate-100 overflow-hidden rounded-lg border border-slate-100 text-sm">
          <li v-if="assignment.tasks.length === 0" class="p-2.5 text-center text-slate-400">Tidak ada task</li>
          <li v-for="(task, idx) in assignment.tasks" :key="idx" :class="['p-2.5', assignment.done[idx] ? 'text-green-800' : 'text-slate-600']">
            <div class="flex items-center gap-2">
              <component :is="assignment.done[idx] ? CheckSquare : Square" :class="['h-4 w-4 shrink-0', assignment.done[idx] ? 'text-green-600' : 'text-slate-400']" />
              <span class="flex-1">{{ task }}</span>
            </div>
            <div v-if="aktivitasDocs[task]?.length" class="ml-6 mt-2 flex flex-wrap gap-1">
              <a
                v-for="(url, docIdx) in aktivitasDocs[task]"
                :key="url"
                :href="url"
                target="_blank"
                class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-2 py-0.5 text-[11px] text-slate-600 hover:bg-slate-50"
              >
                <FileText class="h-3 w-3" />Lihat Dokumen {{ aktivitasDocs[task].length > 1 ? docIdx + 1 : '' }}
              </a>
            </div>
            <div v-if="isGmpTask(task)" class="ml-6 mt-2">
              <a
                :href="`/one-gmp/area/${encodeURIComponent(assignment.area || '')}`"
                target="_blank"
                class="inline-flex items-center gap-1 rounded-md border border-blue-500 px-2 py-0.5 text-[11px] text-blue-600 hover:bg-blue-50"
              >
                <ExternalLink class="h-3 w-3" />Buka Form GMP {{ assignment.area || 'Area' }}
              </a>
            </div>
          </li>
        </ul>
      </div>

      <div class="mb-4">
        <div class="mb-1 text-[13px] font-bold text-slate-600">Keterangan:</div>
        <div class="min-h-[50px] rounded-lg bg-slate-50 p-2 text-sm text-slate-500">{{ assignment.keterangan || '-' }}</div>
      </div>

      <button type="button" class="w-full rounded-lg bg-slate-500 py-2 text-sm font-semibold text-white hover:bg-slate-600" @click="emit('closed')">Tutup</button>
    </div>
  </div>
</template>
