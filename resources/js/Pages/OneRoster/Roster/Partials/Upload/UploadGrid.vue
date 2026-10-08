<script setup>
import { computed } from 'vue';
import { dayAbbr, isoWeekday } from '@/utils/rosterMatrix';

const props = defineProps({
  upload: { type: Object, required: true },
  step: { type: Number, required: true }, // 1 = Data, 2 = Kode
});

const showCheck = computed(() => props.step === 2);
const weekdays = computed(() => props.upload.dates.map(isoWeekday));
const valueOf = (person, date) => (props.step === 1 ? person.data[date] : person.kode[date]);
const checkLabel = (cek) => ({ true: 'OK', false: 'BEDA' }[cek] ?? '-');
</script>

<template>
  <div class="max-h-[70vh] overflow-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
    <table class="w-full border-collapse text-center align-middle text-[12px]">
      <thead class="sticky top-0 z-[2] bg-slate-50">
        <tr>
          <th class="border border-slate-200 px-1.5 py-1">No</th>
          <th class="border border-slate-200 px-1.5 py-1 text-left">NRP</th>
          <th class="border border-slate-200 px-1.5 py-1 text-left">Nama</th>
          <th class="border border-slate-200 px-1.5 py-1">Kode</th>
          <th v-for="(date, index) in upload.dates" :key="date" :class="['border border-slate-200 px-1.5 py-1', weekdays[index] >= 6 ? 'text-red-600' : '']" :title="date">
            {{ Number(date.slice(8, 10)) }}<div class="text-[10px] font-normal">{{ dayAbbr(weekdays[index]) }}</div>
          </th>
          <template v-if="showCheck"><th class="border border-slate-200 px-1.5 py-1">Acuan Hari-7</th><th class="border border-slate-200 px-1.5 py-1">OFF Aktual</th><th class="border border-slate-200 px-1.5 py-1">Cek</th></template>
        </tr>
      </thead>
      <tbody>
        <tr v-for="person in upload.people" :key="person.nrp">
          <td class="border border-slate-200 px-1.5 py-0.5">{{ person.no }}</td>
          <td class="border border-slate-200 px-1.5 py-0.5 text-left">{{ person.nrp }}</td>
          <td class="whitespace-nowrap border border-slate-200 px-1.5 py-0.5 text-left">{{ person.nama }}</td>
          <td class="border border-slate-200 px-1.5 py-0.5">{{ person.kode_off }}</td>
          <td v-for="date in upload.dates" :key="date" class="whitespace-nowrap border border-slate-200 px-1.5 py-0.5">{{ valueOf(person, date) }}</td>
          <template v-if="showCheck">
            <td class="border border-slate-200 px-1.5 py-0.5">{{ person.acuan }}</td>
            <td class="border border-slate-200 px-1.5 py-0.5">{{ person.aktual }}</td>
            <td :class="['border border-slate-200 px-1.5 py-0.5', person.cek === false ? 'bg-red-600 font-bold text-white' : '']">{{ checkLabel(person.cek) }}</td>
          </template>
        </tr>
      </tbody>
    </table>
  </div>
</template>
