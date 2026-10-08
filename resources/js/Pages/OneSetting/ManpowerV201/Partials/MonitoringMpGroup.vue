<script setup>
import { computed } from 'vue';
import { MessageCircle } from 'lucide-vue-next';

const props = defineProps({
  category: { type: Object, required: true },
  tone: { type: Object, required: true }, // { unit, spare } kelas lencana per CSA
  codes: { type: Object, required: true }, // { roster: {nrp: kode}, ss6: {nrp: {code, job, jam_in}} }
});

const HIGH_RISK_CLASS = 'bg-red-600 text-white';

const filled = computed(() => props.category.units.filter((u) => u.assignment).length);
const headerClass = computed(() => (props.category.name.toUpperCase().includes('SUPORT HRM') ? 'bg-amber-800' : 'bg-slate-800'));

const badgeClass = (u) => {
  if (u.is_high_risk) return HIGH_RISK_CLASS;

  return u.is_spare_slot ? props.tone.spare : props.tone.unit;
};

const phoneDigits = (phone) => phone.replace(/\D/g, '');

const rosterCode = (a) => props.codes.roster[a.nrp] ?? '';
const isWorkCode = (code) => code === 'D' || code === 'N';
const rosterClass = (code) => {
  if (code === '') return 'text-slate-300';

  return isWorkCode(code) ? 'text-slate-900' : 'bg-amber-50 text-amber-700';
};

const ss6Of = (a) => props.codes.ss6[a.nrp] ?? null;
const ss6Title = (ss6) => (ss6 ? `Job: ${ss6.job || '-'} | Masuk: ${ss6.jam_in || '-'}` : 'Tidak ada data SS6 untuk NRP ini pada tanggal tersebut');

const cell = 'border border-slate-200 px-1.5 py-1 text-[11px]';
</script>

<template>
  <div class="mb-2.5 overflow-hidden rounded-md border border-slate-200">
    <div :class="['flex justify-between px-2 py-1.5 text-[11px] font-extrabold uppercase tracking-wide text-white', headerClass]">
      <span>{{ category.name }}</span>
      <span class="opacity-75">{{ filled }}/{{ category.units.length }}</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[30rem] border-collapse">
        <thead>
          <tr class="bg-slate-100 text-left text-[10px] font-extrabold uppercase text-slate-600">
            <th class="border border-slate-200 px-1.5 py-1">No Unit</th>
            <th class="border border-slate-200 px-1.5 py-1">No Telepon</th>
            <th class="border border-slate-200 px-1.5 py-1">NRP</th>
            <th class="border border-slate-200 px-1.5 py-1">Nama</th>
            <th class="border border-slate-200 px-1.5 py-1 text-center">Roster</th>
            <th class="border border-slate-200 px-1.5 py-1 text-center">SS6</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in category.units" :key="u.id" :class="u.assignment ? '' : 'bg-slate-50'">
            <td :class="[cell, 'text-center']">
              <span :class="['inline-block whitespace-nowrap rounded-[5px] px-[7px] py-0.5 text-[10px] font-bold', badgeClass(u)]">{{ u.unit }}</span>
            </td>

            <template v-if="u.assignment">
              <td :class="cell">
                <a v-if="u.assignment.phone.trim()" :href="`https://wa.me/${phoneDigits(u.assignment.phone)}`" target="_blank" rel="noopener" class="text-emerald-600 no-underline">
                  <MessageCircle class="mr-0.5 inline h-3 w-3 text-[#25d366]" /> {{ u.assignment.phone.trim() }}
                </a>
                <span v-else class="text-slate-300">-</span>
              </td>
              <td :class="[cell, 'font-bold text-blue-700']">{{ u.assignment.nrp }}</td>
              <td :class="[cell, 'font-semibold', u.assignment.spare ? 'bg-[#c3eaae]' : '']">{{ u.assignment.employee_name }}</td>
              <td :class="[cell, 'text-center font-bold', rosterClass(rosterCode(u.assignment))]">{{ rosterCode(u.assignment) || '-' }}</td>
              <td :class="[cell, 'text-center font-bold', ss6Of(u.assignment)?.code ? 'text-slate-900' : 'text-slate-300']" :title="ss6Title(ss6Of(u.assignment))">{{ ss6Of(u.assignment)?.code || '-' }}</td>
            </template>

            <td v-else-if="u.double.length" colspan="5" :class="[cell, 'bg-amber-100 font-bold']">DOUBLE - belum dipilih</td>

            <template v-else>
              <td :class="cell"><span class="text-slate-300">-</span></td>
              <td :class="cell" /><td :class="cell" /><td :class="cell" /><td :class="cell" />
            </template>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
