<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { CalendarDays, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { formatLongDate, shiftYmd } from '@/utils/rosterV2';

const props = defineProps({
  filters: { type: Object, required: true },
  jabatanList: { type: Array, default: () => [] },
  today: { type: String, required: true },
});

const dateLabel = computed(() => formatLongDate(props.filters.date));

const handleVisit = (changes) => {
  const query = { ...props.filters, ...changes };
  const params = Object.fromEntries(Object.entries(query).filter(([, value]) => value !== ''));
  router.get('/one-roster/dashboard', params, { preserveScroll: true });
};
</script>

<template>
  <div class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
    <div>
      <label class="mb-1 block text-xs font-semibold text-slate-600">Tanggal</label>
      <div class="flex gap-1">
        <button type="button" title="Hari sebelumnya" class="rounded-lg border border-slate-300 px-2 py-1.5 text-slate-600 hover:bg-slate-50" @click="handleVisit({ date: shiftYmd(filters.date, -1) })">
          <ChevronLeft class="h-4 w-4" />
        </button>
        <input type="date" :value="filters.date" class="rounded-lg border border-slate-300 px-2 py-1 text-sm" @change="handleVisit({ date: $event.target.value })" />
        <button type="button" title="Hari berikutnya" class="rounded-lg border border-slate-300 px-2 py-1.5 text-slate-600 hover:bg-slate-50" @click="handleVisit({ date: shiftYmd(filters.date, 1) })">
          <ChevronRight class="h-4 w-4" />
        </button>
        <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50" @click="handleVisit({ date: today })">Hari ini</button>
      </div>
    </div>
    <div>
      <label class="mb-1 block text-xs font-semibold text-slate-600">Jabatan</label>
      <select :value="filters.jabatan" class="rounded-lg border border-slate-300 px-2 py-1.5 text-sm" @change="handleVisit({ jabatan: $event.target.value })">
        <option value="">Semua jabatan</option>
        <option v-for="jabatan in jabatanList" :key="jabatan" :value="jabatan">{{ jabatan }}</option>
      </select>
    </div>
    <div class="flex items-center gap-1.5 font-semibold text-slate-700 sm:ml-auto">
      <CalendarDays class="h-4 w-4" /> {{ dateLabel }}
    </div>
  </div>
</template>
