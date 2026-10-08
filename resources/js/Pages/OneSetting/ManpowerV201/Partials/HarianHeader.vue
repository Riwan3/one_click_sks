<script setup>
import { computed } from 'vue';
import { CircleCheck, LockOpen, RefreshCw } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { SHIFT_LABEL, formatDateLabel, formatShortDateTime } from '@/utils/manpowerV201';

const props = defineProps({
  setting: { type: Object, required: true },
  summary: { type: Object, required: true },
  hadir: { type: Number, default: 0 },
  isFinal: { type: Boolean, default: false },
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['sync', 'finalize', 'reopen']);

const title = computed(() => `${formatDateLabel(props.setting.work_date)} · ${SHIFT_LABEL[props.setting.shift]}`);

const BADGES = computed(() => [
  { label: `Terisi ${props.summary.filled} / ${props.summary.units}`, class: 'bg-emerald-600 text-white' },
  { label: `Kosong ${props.summary.empty}`, class: 'bg-red-600 text-white' },
  { label: `Double ${props.summary.double}`, class: 'bg-amber-400 text-slate-900' },
  { label: `Sisa ${props.summary.sisa}`, class: 'bg-sky-500 text-white' },
  { label: `Hadir di roster ${props.hadir}`, class: 'border border-slate-300 bg-slate-50 text-slate-700' },
]);

const handleSync = async () => {
  const isConfirmed = await confirmDialog('Tarik ulang dari Roster V2? Penempatan manual dan batangan yang dilepas tetap dipertahankan.');
  if (isConfirmed) emit('sync');
};

const handleFinalize = async () => {
  const isConfirmed = await confirmDialog('Finalkan setting ini? Setelah final tidak bisa diubah sampai dibuka kembali.');
  if (isConfirmed) emit('finalize');
};
</script>

<template>
  <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-center gap-2">
      <h2 class="text-base font-bold text-slate-800">{{ title }}</h2>
      <span :class="['rounded px-2 py-0.5 text-[10px] font-bold text-white', isFinal ? 'bg-emerald-600' : 'bg-slate-500']">{{ isFinal ? 'FINAL' : 'DRAFT' }}</span>
      <span class="text-xs text-slate-500">
        Ditarik: {{ formatShortDateTime(setting.synced_at) }} · diubah {{ setting.updated_by || '-' }} {{ formatShortDateTime(setting.updated_at) }}
      </span>

      <div class="flex gap-2 sm:ml-auto">
        <template v-if="!isFinal">
          <button type="button" :disabled="isProcessing" class="flex items-center gap-1 rounded-lg border border-blue-600 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50 disabled:opacity-60" @click="handleSync">
            <RefreshCw class="h-3.5 w-3.5" /> Tarik ulang dari Roster
          </button>
          <button type="button" :disabled="isProcessing" class="flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 disabled:opacity-60" @click="handleFinalize">
            <CircleCheck class="h-3.5 w-3.5" /> Finalkan
          </button>
        </template>
        <button v-else type="button" :disabled="isProcessing" class="flex items-center gap-1 rounded-lg border border-slate-400 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 disabled:opacity-60" @click="emit('reopen')">
          <LockOpen class="h-3.5 w-3.5" /> Buka final
        </button>
      </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-2">
      <span v-for="badge in BADGES" :key="badge.label" :class="['rounded-full px-3 py-1 text-xs font-bold', badge.class]">{{ badge.label }}</span>
    </div>
  </div>
</template>
