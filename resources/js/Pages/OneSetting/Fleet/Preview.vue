<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ClipboardCopy, Image, Pencil } from 'lucide-vue-next';
import { usePreviewImage } from '@/composables/usePreviewImage';
import { FLEET_URL } from '@/utils/fleet';
import { SHIFT_LABEL, formatDateLabel } from '@/utils/manpowerV201';
import DateShiftFilter from '@/Pages/OneSetting/ManpowerV201/Partials/DateShiftFilter.vue';
import FleetBoardSheet from '@/Pages/OneSetting/Partials/FleetBoardSheet.vue';
import FleetShell from './Partials/FleetShell.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  ready: { type: Boolean, default: true },
  loadError: { type: String, default: null },
  filters: { type: Object, required: true }, // { date, shift }
  sheet: { type: Object, default: null },
});

const sheetRef = ref(null);
const { busyAction, download, copy } = usePreviewImage(sheetRef, () => `fleet-${props.filters.date}-${props.filters.shift}.png`);

const dateShiftLabel = computed(() => `${formatDateLabel(props.filters.date)} ${SHIFT_LABEL[props.filters.shift]}`);
const settingUrl = computed(() => `${FLEET_URL}?${new URLSearchParams(props.filters)}`);
</script>

<template>
  <FleetShell
    :user="user"
    :notif-counts="notifCounts"
    :menu="menu"
    :ready="ready"
    :load-error="loadError"
    :filters="filters"
    tab="preview"
    subtitle="Preview lembar setting fleet dan unduh sebagai gambar."
  >
    <DateShiftFilter :url="`${FLEET_URL}/preview`" :filters="filters" />

    <div v-if="!sheet" class="flex flex-col items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-4 py-12 text-center shadow-sm">
      <p class="text-sm text-slate-500">Belum ada setting fleet untuk {{ dateShiftLabel }}.</p>
      <Link :href="settingUrl" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Ke Setting Fleet</Link>
    </div>

    <template v-else>
      <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs text-slate-500">{{ dateShiftLabel }} &middot; {{ sheet.total_hauler }} hauler terpasang</span>
        <div class="flex flex-wrap gap-2 sm:ml-auto">
          <Link :href="settingUrl" class="flex items-center gap-1 rounded-lg border border-slate-400 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"><Pencil class="h-3.5 w-3.5" /> Ubah setting</Link>
          <button type="button" :disabled="!!busyAction" class="flex items-center gap-1 rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-800 hover:bg-slate-50 disabled:opacity-60" @click="copy">
            <ClipboardCopy class="h-3.5 w-3.5" /> {{ busyAction === 'copy' ? 'Memproses...' : 'Salin gambar' }}
          </button>
          <button type="button" :disabled="!!busyAction" class="flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-60" @click="download">
            <Image class="h-3.5 w-3.5" /> {{ busyAction === 'download' ? 'Memproses...' : 'Unduh PNG' }}
          </button>
        </div>
      </div>

      <div class="overflow-x-auto bg-white shadow-sm">
        <div ref="sheetRef" class="w-max">
          <FleetBoardSheet :sheet="sheet" />
        </div>
      </div>
    </template>
  </FleetShell>
</template>
