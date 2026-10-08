<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { CloudUpload } from 'lucide-vue-next';
import UploadFilter from './UploadFilter.vue';
import UploadGrid from './UploadGrid.vue';
import UploadRows from './UploadRows.vue';
import { dayAbbr } from '@/utils/rosterMatrix';

const props = defineProps({
  roster: { type: Object, required: true },
  upload: { type: Object, required: true },
  step: { type: Number, required: true }, // 1 = Data, 2 = Kode, 3 = Upload
});

const STEPS = [[1, '1. Data'], [2, '2. Kode'], [3, '3. Upload']];
const GRID_HELP = {
  1: 'Data — isi roster per orang pada rentang tanggal yang dipilih, kode disederhanakan: CT→C, ID→D, IDS→D, TR→T, MCU→H, FU→H; D, N, OFF, H apa adanya.',
};

const base = computed(() => `/one-roster/roster/${props.roster.id}`);
const query = (extra = {}) => ({
  tab: 'upload', step: props.step, month: props.upload.month, start: props.upload.start, end: props.upload.end,
  pos: props.upload.pos, ...(props.upload.nrps ? { nrps: props.upload.nrps } : {}), ...extra,
});
const downloadUrl = computed(() => {
  const { month, start, end, pos, nrps } = props.upload;
  return `${base.value}/upload/download?${new URLSearchParams({ month, start, end, pos, ...(nrps ? { nrps } : {}) })}`;
});
const codeHelp = computed(() => `Kode — huruf + Unit: D→D${props.upload.pos}, OFF→OOFF, C→CCUTI, T→TTRAINING. Kolom Hari-7 memeriksa hari OFF terhadap Kode Off (A1/B1 = Sabtu, A2/B2 = Minggu, A3/B3 = Senin, … A7/B7 = Jumat, A8 = Sabtu, dst). Merah = tidak ada OFF yang jatuh di hari acuan (cek Kode Off orang itu). Hari L selalu mengikuti Kode Off; OFF di hari lain tetap berstatus OR/OE, bukan OL.`);

const visit = (params) => router.get(base.value, params, { preserveScroll: true });
const handleApply = (filter) => visit(query(filter));
const handleChangeMonth = (month) => visit({ tab: 'upload', step: props.step, month, pos: props.upload.pos, ...(props.upload.nrps ? { nrps: props.upload.nrps } : {}) });
const handleStep = (step) => visit(query({ step }));
</script>

<template>
  <div class="space-y-4">
    <UploadFilter :upload="upload" @apply="handleApply" @change-month="handleChangeMonth" />

    <div class="flex flex-wrap gap-1 text-sm">
      <button
        v-for="[value, label] in STEPS" :key="value" type="button"
        :class="['rounded-full px-3 py-1 text-xs font-semibold', step === value ? 'bg-blue-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100']"
        @click="handleStep(value)"
      >
        {{ label }}<span v-if="value === 2 && upload.bad > 0" class="ml-1 rounded-full bg-red-600 px-1.5 text-[10px] text-white">{{ upload.bad }}</span>
      </button>
    </div>

    <div v-if="!upload.people.length" class="rounded-2xl border border-slate-200 bg-white px-4 py-12 text-center text-slate-500 shadow-sm">
      <CloudUpload class="mx-auto h-10 w-10 text-slate-300" />
      <p class="mt-2 text-sm">Tidak ada orang yang cocok pada roster ini.</p>
    </div>
    <template v-else-if="step === 3">
      <UploadRows :rows="upload.rows" :download-url="downloadUrl" />
    </template>
    <template v-else>
      <p class="rounded-2xl border border-slate-200 bg-white p-3 text-xs text-slate-600 shadow-sm">{{ step === 1 ? GRID_HELP[1] : codeHelp }}</p>
      <UploadGrid :upload="upload" :step="step" />
    </template>
  </div>
</template>
