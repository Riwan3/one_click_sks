<script setup>
import { computed, ref, watch } from 'vue';
import { getJson } from '@/composables/useMoncuApi';
import { formatDateId } from '@/utils/moncu';
import MoncuModal from '../../Moncu/Partials/MoncuModal.vue';

const props = defineProps({
  nrp: { type: String, default: null }, // null = tertutup
  year: { type: Number, required: true },
});

const emit = defineEmits(['close']);

const DETAIL_URL = '/one-roster/moncu-monitoring/detail';

const detail = ref(null);
const errorMessage = ref('');
const isLoading = ref(false);

const periodColumns = ['Periode', 'Tahun', 'AWAL', 'AKHIR', 'TAHUNAN', 'IZIN RESMI', 'INDUKSI', 'PLAN'];

const periodCells = (period) => [
  period.period_no, period.cycle_year, formatDateId(period.awal), formatDateId(period.akhir),
  period.tahunan, period.izin_resmi, formatDateId(period.induksi), formatDateId(period.plan),
];

const rosterLabel = computed(() => {
  const inRoster = detail.value.in_roster;
  if (inRoster === null) return '— (hanya dicek untuk tahun berjalan)';
  return inRoster ? 'Terdaftar di roster' : 'Belum ada di roster';
});

const summaryRows = computed(() => {
  const { employee, context, entitlement } = detail.value;
  return {
    left: [
      ['NRP', employee.nrp], ['Nama', employee.name], ['Jabatan', employee.jabatan],
      ['Group', employee.group], ['Hiring Date', formatDateId(employee.hiring_date)],
    ],
    right: [
      [`${context.status_label} (per ${formatDateId(context.as_of)})`, detail.value.status],
      ...(context.mode === 'current' ? [] : [['Konteks', `${context.label} — bukan kondisi hari ini`]]),
      ['PLAN Berikutnya', formatDateId(detail.value.plan_next)],
      ['Roster V2', rosterLabel.value],
      [`Hak tahunan ${detail.value.year}`, `Hak ${entitlement.hak} · Pakai ${entitlement.pemakaian} · Sisa ${entitlement.sisa}`],
    ],
  };
});

const load = async (nrp) => {
  detail.value = null;
  errorMessage.value = '';
  isLoading.value = true;
  try {
    detail.value = await getJson(DETAIL_URL, { nrp, year: props.year });
  } catch (e) {
    errorMessage.value = e.message;
  } finally {
    isLoading.value = false;
  }
};

watch(() => props.nrp, (nrp) => nrp && load(nrp));
</script>

<template>
  <MoncuModal :open="nrp !== null" title="Detail Cuti" wide @close="emit('close')">
    <p v-if="isLoading" class="text-sm text-slate-500">Memuat…</p>
    <p v-else-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>
    <div v-else-if="detail" class="space-y-4">
      <div class="grid gap-4 md:grid-cols-2">
        <dl v-for="(rows, side) in summaryRows" :key="side" class="space-y-2">
          <div v-for="[label, value] in rows" :key="label">
            <dt class="text-[11px] font-medium text-slate-500">{{ label }}</dt>
            <dd class="text-sm text-slate-800">{{ value === null || value === '' ? '-' : value }}</dd>
          </div>
        </dl>
      </div>
      <div>
        <p class="mb-1 text-sm font-semibold text-slate-700">Periode tahun {{ detail.year }}</p>
        <div class="overflow-x-auto">
          <table class="w-full border border-slate-200 text-xs">
            <thead class="bg-slate-50 text-[10px] font-bold uppercase text-slate-500">
              <tr><th v-for="column in periodColumns" :key="column" class="border border-slate-200 px-2 py-1.5 text-left">{{ column }}</th></tr>
            </thead>
            <tbody>
              <tr v-for="period in detail.periods" :key="period.period_no">
                <td
                  v-for="(cell, i) in periodCells(period)"
                  :key="i"
                  :class="['border border-slate-200 px-2 py-1.5', i === 4 && period.tahunan < 0 ? 'font-semibold text-amber-700' : '']"
                  :title="i === 4 && period.tahunan < 0 ? 'TAHUNAN negatif memperpendek cuti' : ''"
                >{{ cell ?? '' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div v-if="detail.rosters.length">
        <p class="mb-1 text-sm font-semibold text-slate-700">Roster V2 terkait</p>
        <ul class="list-disc pl-5 text-xs text-slate-700">
          <li v-for="roster in detail.rosters" :key="roster.id">{{ roster.group }} ({{ roster.status }}) {{ formatDateId(roster.start) }} s/d {{ formatDateId(roster.end) }}</li>
        </ul>
      </div>
      <p class="text-xs text-slate-400">Tampilan hanya-baca. Perubahan dilakukan di halaman MONCU (input cuti).</p>
    </div>
    <template #footer>
      <button type="button" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700" @click="emit('close')">Tutup</button>
    </template>
  </MoncuModal>
</template>
