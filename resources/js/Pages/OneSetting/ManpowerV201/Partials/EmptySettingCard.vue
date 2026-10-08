<script setup>
import { computed } from 'vue';
import { CalendarDays, CloudDownload } from 'lucide-vue-next';
import { SHIFT_LABEL, formatDateLabel } from '@/utils/manpowerV201';

const props = defineProps({
  filters: { type: Object, required: true },
  hadir: { type: Number, default: 0 },
  canCreate: { type: Boolean, default: false }, // tombol "Buat setting" hanya di tab Setting Harian
  isProcessing: { type: Boolean, default: false },
  hint: { type: String, default: '' },
});

const emit = defineEmits(['create']);

const label = computed(() => `${formatDateLabel(props.filters.date)} · ${SHIFT_LABEL[props.filters.shift]}`);
</script>

<template>
  <div class="flex flex-col items-center gap-2 rounded-2xl border border-slate-200/80 bg-white px-4 py-12 text-center shadow-sm">
    <CalendarDays class="h-10 w-10 text-slate-300" />
    <h2 class="text-base font-bold text-slate-800">Belum ada setting untuk {{ label }}</h2>

    <template v-if="canCreate">
      <p class="text-sm text-slate-500">
        Roster V2 (published) mencatat <strong>{{ hadir }}</strong> orang hadir (kode {{ filters.shift }}) pada tanggal ini.
      </p>
      <button
        v-if="hadir > 0"
        type="button"
        :disabled="isProcessing"
        class="mt-2 flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
        @click="emit('create')"
      >
        <CloudDownload class="h-4 w-4" /> Buat setting dari Roster V2
      </button>
      <p v-else class="text-xs text-red-600">
        Tidak ada yang hadir di roster berstatus published pada tanggal dan shift ini. Pastikan rosternya sudah dipublish di Roster V2.
      </p>
    </template>

    <template v-else>
      <p class="text-sm text-slate-500">{{ hint }}</p>
      <slot />
    </template>
  </div>
</template>
