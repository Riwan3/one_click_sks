<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, Plus, Settings } from 'lucide-vue-next';
import { monthLabel } from '@/utils/rosterMatrix';

const props = defineProps({
  rosterId: { type: Number, required: true },
  matrix: { type: Object, required: true },
  ctxQuery: { type: String, default: '' }, // p = 10 hari bulan sebelumnya, n = 10 hari bulan sesudahnya
  canEdit: { type: Boolean, default: false },
});

const CTX_STORAGE_KEY = 'rv2_matrix_ctx';

const isMenuOpen = ref(false);
const ctx = reactive({ prev: props.ctxQuery.includes('p'), next: props.ctxQuery.includes('n') });
const sectionForm = useForm({ name: '' });

const currentIndex = computed(() => props.matrix.months.indexOf(props.matrix.month));
const prevMonth = computed(() => props.matrix.months[currentIndex.value - 1] ?? null);
const nextMonth = computed(() => props.matrix.months[currentIndex.value + 1] ?? null);
const ctxValue = computed(() => `${ctx.prev ? 'p' : ''}${ctx.next ? 'n' : ''}`);

const visit = (month, ctxFlags, replace = false) => router.get(
  `/one-roster/roster/${props.rosterId}`,
  { tab: 'matrix', month, ...(ctxFlags ? { ctx: ctxFlags } : {}) },
  { preserveScroll: true, replace },
);

const handleMonthChange = (event) => visit(event.target.value, ctxValue.value);

const handleCtxChange = () => {
  try { localStorage.setItem(CTX_STORAGE_KEY, ctxValue.value); } catch { /* localStorage tidak tersedia: abaikan */ }
  visit(props.matrix.month, ctxValue.value);
};

const handleAddSection = () => sectionForm.post(`/one-roster/roster/${props.rosterId}/sections`, {
  preserveScroll: true,
  onSuccess: () => sectionForm.reset(),
});

// Pilihan kolom konteks terakhir diingat di browser ini: bila URL belum memuatnya, terapkan yang tersimpan.
onMounted(() => {
  try {
    const saved = localStorage.getItem(CTX_STORAGE_KEY) ?? '';
    if (!new URLSearchParams(window.location.search).has('ctx') && saved) visit(props.matrix.month, saved, true);
  } catch { /* localStorage tidak tersedia: abaikan */ }
});
</script>

<template>
  <div class="flex flex-wrap items-end justify-between gap-2">
    <div class="flex flex-wrap items-center gap-2">
      <button type="button" :disabled="!prevMonth" title="Bulan sebelumnya" class="rounded-lg border border-slate-300 bg-white p-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-40" @click="visit(prevMonth, ctxValue)"><ChevronLeft class="h-4 w-4" /></button>
      <select :value="matrix.month" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm" @change="handleMonthChange">
        <option v-for="month in matrix.months" :key="month" :value="month">{{ monthLabel(month) }}</option>
      </select>
      <button type="button" :disabled="!nextMonth" title="Bulan berikutnya" class="rounded-lg border border-slate-300 bg-white p-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-40" @click="visit(nextMonth, ctxValue)"><ChevronRight class="h-4 w-4" /></button>
      <div class="relative">
        <button type="button" title="Tampilkan hari bulan sebelum/sesudahnya untuk memantau" :class="['rounded-lg border border-slate-300 p-1.5 text-slate-600 hover:bg-slate-50', ctxValue ? 'bg-slate-200' : 'bg-white']" @click="isMenuOpen = !isMenuOpen"><Settings class="h-4 w-4" /></button>
        <div v-if="isMenuOpen" class="absolute left-0 top-full z-20 mt-1 w-72 rounded-xl border border-slate-200 bg-white p-3 shadow-lg">
          <div class="mb-2 text-xs font-semibold">Tampilan kolom</div>
          <label class="mb-1 flex items-center gap-2 text-xs"><input v-model="ctx.prev" type="checkbox" class="rounded border-slate-300" @change="handleCtxChange" />10 hari di bulan sebelumnya</label>
          <label class="flex items-center gap-2 text-xs"><input v-model="ctx.next" type="checkbox" class="rounded border-slate-300" @change="handleCtxChange" />10 hari ke bulan setelahnya</label>
          <p class="mt-2 text-[11px] text-slate-500">Untuk memantau sambungan jadwal antarbulan. Kolom pudar = di luar bulan ini; garis tebal memisahkan bulan.</p>
        </div>
      </div>
    </div>
    <form v-if="canEdit" class="flex gap-2" @submit.prevent="handleAddSection">
      <input v-model="sectionForm.name" type="text" maxlength="100" required placeholder="Nama section, mis. ADMIN PRODUKSI" class="min-w-0 rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm sm:min-w-[260px]" />
      <button type="submit" :disabled="sectionForm.processing" class="flex items-center gap-1 whitespace-nowrap rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60"><Plus class="h-4 w-4" />Tambah Section</button>
    </form>
  </div>
  <p v-if="sectionForm.errors.form" class="mt-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">{{ sectionForm.errors.form }}</p>
</template>
