<script setup>
import { computed, reactive } from 'vue';
import { ChevronLeft, ChevronRight, RefreshCw } from 'lucide-vue-next';
import FormInput from '@/Components/ui/FormInput.vue';
import { dmy, monthLabel } from '@/utils/rosterMatrix';

const props = defineProps({
  upload: { type: Object, required: true },
});

const emit = defineEmits(['apply', 'change-month']);

const form = reactive({ start: props.upload.start, end: props.upload.end, pos: props.upload.pos, nrps: props.upload.nrps });

const months = computed(() => props.upload.months);
const currentIndex = computed(() => months.value.indexOf(props.upload.month));
const prevMonth = computed(() => months.value[currentIndex.value - 1] ?? null);
const nextMonth = computed(() => months.value[currentIndex.value + 1] ?? null);
const dates = computed(() => props.upload.dates);

const NAV_BUTTON = 'rounded-lg border border-slate-300 bg-white p-1.5 text-slate-600 hover:bg-slate-50 disabled:opacity-40';
</script>

<template>
  <form class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm" @submit.prevent="emit('apply', { ...form })">
    <div class="grid grid-cols-2 items-end gap-3 md:grid-cols-[auto_auto_auto_auto_1fr_auto]">
      <div class="col-span-2 md:col-span-1">
        <span class="mb-1 block text-xs font-semibold text-slate-600">Bulan</span>
        <div class="flex gap-1">
          <button type="button" :disabled="!prevMonth" title="Bulan sebelumnya" :class="NAV_BUTTON" @click="emit('change-month', prevMonth)"><ChevronLeft class="h-4 w-4" /></button>
          <select :value="upload.month" class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm" @change="emit('change-month', $event.target.value)">
            <option v-for="month in months" :key="month" :value="month">{{ monthLabel(month) }}</option>
          </select>
          <button type="button" :disabled="!nextMonth" title="Bulan berikutnya" :class="NAV_BUTTON" @click="emit('change-month', nextMonth)"><ChevronRight class="h-4 w-4" /></button>
        </div>
      </div>
      <FormInput v-model="form.start" label="Dari tanggal" type="date" :min="upload.roster.period_start" :max="upload.roster.period_end" />
      <FormInput v-model="form.end" label="Sampai tanggal" type="date" :min="upload.roster.period_start" :max="upload.roster.period_end" />
      <div class="w-24"><FormInput v-model="form.pos" label="Kode OT / Pos" maxlength="10" /></div>
      <label class="col-span-2 block md:col-span-1">
        <span class="mb-1 block text-xs font-semibold text-slate-600">NRP yang di-upload <span class="font-normal text-slate-500">(opsional, pisah spasi/koma/baris; kosong = semua)</span></span>
        <textarea v-model="form.nrps" rows="1" placeholder="mis. 230256 220574" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm"></textarea>
      </label>
      <button type="submit" class="col-span-2 flex items-center justify-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700 md:col-span-1"><RefreshCw class="h-4 w-4" />Terapkan</button>
    </div>
    <p class="mt-2 text-xs text-slate-500">
      Periode: <strong>{{ dmy(dates[0]) }}</strong> s/d <strong>{{ dmy(dates[dates.length - 1]) }}</strong> ({{ dates.length }} hari)
      &middot; {{ upload.people.length }} orang &middot; {{ upload.rows.length }} baris upload
    </p>
  </form>
</template>
