<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ClipboardCopy, Image, Pencil } from 'lucide-vue-next';
import FormAlert from '@/Components/ui/FormAlert.vue';
import { usePreviewImage } from '@/composables/usePreviewImage';
import { BASE_URL, SHIFT_LABEL, formatDateLabel } from '@/utils/manpowerV201';
import ManpowerShell from './Partials/ManpowerShell.vue';
import DateShiftFilter from './Partials/DateShiftFilter.vue';
import EmptySettingCard from './Partials/EmptySettingCard.vue';
import PreviewSheet from './Partials/PreviewSheet.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  ready: { type: Boolean, default: true },
  loadError: { type: String, default: null },
  filters: { type: Object, required: true },
  view: { type: Object, default: null },
  checkPointPassword: { type: String, default: null },
});

const sheetRef = ref(null);
const { busyAction, download, copy } = usePreviewImage(sheetRef, () => `manpower-${props.filters.date}-${props.filters.shift}.png`);

const setting = computed(() => props.view?.setting ?? null);
const isFinal = computed(() => setting.value?.status === 'final');
const dateShiftLabel = computed(() => `${formatDateLabel(props.filters.date)} ${SHIFT_LABEL[props.filters.shift]}`);
const incompleteCount = computed(() => (props.view?.summary?.empty ?? 0) + (props.view?.summary?.double ?? 0));
const harianUrl = computed(() => `${BASE_URL}?${new URLSearchParams(props.filters)}`);
</script>

<template>
  <ManpowerShell
    :user="user"
    :notif-counts="notifCounts"
    :menu="menu"
    :ready="ready"
    :load-error="loadError"
    :filters="filters"
    tab="preview"
    subtitle="Preview layout setting manpower dan unduh sebagai gambar."
  >
    <DateShiftFilter :url="`${BASE_URL}/preview`" :filters="filters" />

    <EmptySettingCard v-if="!setting" :filters="filters" hint="Buat setting dulu di tab Setting Harian.">
      <Link :href="harianUrl" class="mt-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Ke Setting Harian</Link>
    </EmptySettingCard>

    <template v-else>
      <div class="flex flex-wrap items-center gap-2">
        <span :class="['rounded px-2 py-0.5 text-[10px] font-bold text-white', isFinal ? 'bg-emerald-600' : 'bg-slate-500']">{{ isFinal ? 'FINAL' : 'DRAFT' }}</span>
        <span class="text-xs text-slate-500">Terisi {{ view.summary.filled }} / {{ view.summary.units }} unit</span>
        <div class="flex flex-wrap gap-2 sm:ml-auto">
          <Link :href="harianUrl" class="flex items-center gap-1 rounded-lg border border-slate-400 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"><Pencil class="h-3.5 w-3.5" /> Ubah setting</Link>
          <button type="button" :disabled="!!busyAction" class="flex items-center gap-1 rounded-lg border border-slate-700 px-3 py-1.5 text-xs font-semibold text-slate-800 hover:bg-slate-50 disabled:opacity-60" @click="copy">
            <ClipboardCopy class="h-3.5 w-3.5" /> {{ busyAction === 'copy' ? 'Memproses...' : 'Salin gambar' }}
          </button>
          <button type="button" :disabled="!!busyAction" class="flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700 disabled:opacity-60" @click="download">
            <Image class="h-3.5 w-3.5" /> {{ busyAction === 'download' ? 'Memproses...' : 'Unduh PNG' }}
          </button>
        </div>
      </div>

      <FormAlert v-if="!isFinal" tone="info" message="Setting masih DRAFT. Finalkan di tab Setting Harian sebelum dibagikan." />
      <FormAlert v-if="checkPointPassword === null" tone="warning" :message="`Password Check Point untuk ${dateShiftLabel} belum diinput, di gambar tampil '-'. Isi di menu Password CP, lalu muat ulang halaman ini.`" />
      <FormAlert v-if="incompleteCount > 0" tone="warning" :message="`Masih ada ${view.summary.empty} unit kosong${view.summary.double ? ` dan ${view.summary.double} unit double` : ''}. Unit kosong tampil kosong di gambar; double tampil kuning.`" />

      <div class="overflow-x-auto bg-white shadow-sm">
        <div ref="sheetRef" class="w-max">
          <PreviewSheet :setting="setting" :tree="view.tree" :gl="view.gl" :lain="view.lain" :check-point-password="checkPointPassword" />
        </div>
      </div>
    </template>
  </ManpowerShell>
</template>
