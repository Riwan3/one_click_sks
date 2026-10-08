<script setup>
import { computed, ref, watch } from 'vue';
import { getJson } from '@/composables/useMoncuApi';
import { formatDateId, formatLongDate } from '@/utils/moncu';
import MoncuModal from '../../Moncu/Partials/MoncuModal.vue';

const props = defineProps({
  date: { type: String, default: null }, // null = tertutup
  filters: { type: Object, required: true }, // { group, position, search } yang sedang aktif
});

const emit = defineEmits(['close']);

const DAY_URL = '/one-roster/moncu-calendar/day';
const SECTION_TITLES = { ct: 'CUTI', id: 'INDUKSI', plan: 'PLAN Cuti Berikutnya' };
const SECTION_KEYS = Object.keys(SECTION_TITLES);
const STATUS_TEXT = { PLAN: 'PLAN (rencana, bukan cuti aktual)', INDUKSI: 'ID / Induksi', CT: 'CT / Cuti' };

const data = ref(null);
const selected = ref(null);
const errorMessage = ref('');
const isLoading = ref(false);

const title = computed(() => (props.date ? formatLongDate(props.date) + (data.value?.is_today ? ' (Today)' : '') : ''));

const sectionColumns = (key) => (key === 'plan' ? ['NRP', 'Nama', 'Jabatan', 'Periode', 'AWAL aktual berikutnya'] : ['NRP', 'Nama', 'Jabatan']);

const itemCells = (key, item) => {
  const base = [item.nrp, item.name, item.jabatan];
  return key === 'plan' ? [...base, `P${item.period_no}/${item.cycle_year}`, item.next_awal ? formatDateId(item.next_awal) : 'belum ada AWAL'] : base;
};

const detailRows = computed(() => {
  const item = selected.value;
  return [
    ['NRP', item.nrp], ['Nama', item.name], ['Jabatan', item.jabatan], ['Group', item.group], ['Hiring Date', formatDateId(item.hiring_date)],
    [`Status pada ${formatDateId(props.date)}`, STATUS_TEXT[item.status]],
    ['Cycle Year / Period No', `${item.cycle_year} / ${item.period_no}`],
    ['AWAL', formatDateId(item.awal)], ['AKHIR', formatDateId(item.akhir)], ['TAHUNAN', item.tahunan], ['IZIN RESMI', item.izin_resmi],
    ['INDUKSI', formatDateId(item.induksi)], ['PLAN', formatDateId(item.plan)],
  ];
});

const load = async (params = {}) => {
  errorMessage.value = '';
  isLoading.value = true;
  try {
    const result = await getJson(DAY_URL, { ...props.filters, date: props.date, ...params });
    data.value = params.section ? { ...data.value, [params.section]: result[params.section] } : result;
  } catch (e) {
    errorMessage.value = e.message;
  } finally {
    isLoading.value = false;
  }
};

const changePage = (key, page) => load({ section: key, page });

watch(() => props.date, (date) => {
  data.value = null;
  selected.value = null;
  if (date) load();
});
</script>

<template>
  <MoncuModal :open="date !== null" :title="title || 'Detail tanggal'" wide @close="emit('close')">
    <p v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</p>
    <p v-else-if="!data" class="text-sm text-slate-500">Memuat…</p>

    <div v-else-if="selected" class="space-y-3">
      <button type="button" class="rounded-lg border border-slate-300 px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-50" @click="selected = null">‹ Kembali ke daftar</button>
      <dl class="grid gap-x-6 gap-y-2 md:grid-cols-2">
        <div v-for="[label, value] in detailRows" :key="label">
          <dt class="text-[11px] font-medium text-slate-500">{{ label }}</dt>
          <dd class="text-sm text-slate-800">{{ value === null || value === undefined || value === '' ? '-' : value }}</dd>
        </div>
      </dl>
      <p v-if="selected.tahunan !== null && selected.tahunan < 0" class="text-xs text-amber-700">TAHUNAN negatif memperpendek cuti (tidak dihitung sebagai pemakaian hak).</p>
      <p class="text-xs text-slate-400">Tampilan hanya-baca. Perubahan dilakukan di halaman MONCU (input cuti).</p>
    </div>

    <div v-else class="space-y-4">
      <p v-if="data.warnings.length" class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">{{ data.warnings[0] }}</p>
      <section v-for="key in SECTION_KEYS" :key="key">
        <p class="mb-1 text-sm font-semibold text-slate-700">{{ SECTION_TITLES[key] }} ({{ data[key].total }})</p>
        <p v-if="key === 'plan'" class="mb-1 text-xs text-slate-500">Rencana saja — bukan cuti aktual dan tidak menghasilkan CT/ID.</p>
        <p v-if="!data[key].total" class="text-xs text-slate-400">Tidak ada.</p>
        <template v-else>
          <div class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead class="bg-slate-50 text-[10px] font-bold uppercase text-slate-500">
                <tr><th v-for="column in sectionColumns(key)" :key="column" class="px-2 py-1.5 text-left">{{ column }}</th></tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="item in data[key].items" :key="`${item.nrp}-${item.period_no}-${item.cycle_year}`" class="cursor-pointer hover:bg-blue-50/50" @click="selected = item">
                  <td v-for="(cell, i) in itemCells(key, item)" :key="i" :class="['px-2 py-1.5', cell === 'belum ada AWAL' ? 'text-slate-400' : '']">{{ cell }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-if="data[key].pages > 1" class="mt-1 flex items-center gap-2 text-xs">
            <button type="button" :disabled="data[key].page <= 1 || isLoading" class="rounded border border-slate-300 px-2 disabled:opacity-40" @click="changePage(key, data[key].page - 1)">‹</button>
            <span>Halaman {{ data[key].page }}/{{ data[key].pages }}</span>
            <button type="button" :disabled="data[key].page >= data[key].pages || isLoading" class="rounded border border-slate-300 px-2 disabled:opacity-40" @click="changePage(key, data[key].page + 1)">›</button>
          </div>
        </template>
      </section>
      <p class="text-xs text-slate-400">Tampilan hanya-baca. Klik nama untuk melihat detail periode.</p>
    </div>

    <template #footer>
      <button type="button" class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700" @click="emit('close')">Tutup</button>
    </template>
  </MoncuModal>
</template>
