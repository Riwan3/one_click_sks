<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Pencil, PlusCircle } from 'lucide-vue-next';
import { PERIOD_MONTH_LABELS, formatDate } from '@/utils/moncu';

const props = defineProps({
  rows: { type: Array, required: true },
  canManage: { type: Boolean, default: false },
  config: { type: Object, required: true },
});

const emit = defineEmits(['edit-period', 'edit-employee']);

const periodNumbers = computed(() => Array.from({ length: props.config.periods_per_year }, (_, i) => i + 1));

const periodTitle = (no) => {
  const months = props.config.period_months[no] ?? [];
  return months.length ? `PERIODE ${no} (${months.map((m) => PERIOD_MONTH_LABELS[m - 1]).join(', ')})` : 'PERIODE TAMBAHAN';
};

const periodColumns = [
  { label: 'AWAL', manual: true },
  { label: 'AKHIR', manual: false },
  { label: 'TAHUNAN', manual: true },
  { label: 'IZIN RESMI', manual: true },
  { label: 'INDUKSI', manual: false },
  { label: 'PLAN', manual: false },
];

const manualClass = 'bg-amber-50';
const calcClass = 'bg-slate-50';
const stickyHead = 'sticky z-10 bg-slate-100 px-2 py-2';
const stickyCell = 'sticky z-10 bg-white px-2 py-2 whitespace-nowrap';
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-max border-collapse text-[11px]">
      <thead class="bg-slate-100 text-[10px] font-bold uppercase tracking-wider text-slate-600">
        <tr>
          <th rowspan="2" :class="[stickyHead, 'left-0 w-24 text-left']">NRP</th>
          <th rowspan="2" :class="[stickyHead, 'left-24 w-44 text-left']">Nama</th>
          <th rowspan="2" :class="[stickyHead, 'left-[17rem] w-40 text-left']">Jabatan</th>
          <th rowspan="2" class="px-2 py-2 text-left">Roster / Section</th>
          <th rowspan="2" class="px-2 py-2 text-left">Hiring Date</th>
          <th rowspan="2" class="px-2 py-2" title="Hak cuti tahunan">Hak</th>
          <th rowspan="2" class="px-2 py-2" title="TAHUNAN aktual (periode ber-AWAL)">Pakai</th>
          <th rowspan="2" class="px-2 py-2">Sisa</th>
          <th
            v-for="no in periodNumbers"
            :key="no"
            colspan="6"
            :class="['border-l border-slate-200 px-2 py-2 text-center', config.period_months[no]?.length ? '' : 'bg-slate-200']"
            :title="config.period_months[no]?.length ? '' : 'Dipakai bila karyawan cuti lebih dari 4 kali dalam setahun'"
          >{{ periodTitle(no) }}</th>
          <th v-if="canManage" rowspan="2" class="px-2 py-2"></th>
        </tr>
        <tr>
          <template v-for="no in periodNumbers" :key="no">
            <th v-for="(col, i) in periodColumns" :key="col.label" :class="['px-2 py-1.5 text-center', i === 0 ? 'border-l border-slate-200' : '', col.manual ? 'text-amber-700' : '']">{{ col.label }}</th>
          </template>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 text-slate-700">
        <tr v-for="row in rows" :key="row.nrp" class="hover:bg-slate-50/60">
          <td :class="[stickyCell, 'left-0 w-24']">{{ row.nrp }}</td>
          <td :class="[stickyCell, 'left-24 w-44']">{{ row.name }}</td>
          <td :class="[stickyCell, 'left-[17rem] w-40 text-[10px]']">{{ row.jabatan }}</td>
          <td class="px-2 py-2">
            <span v-if="!row.rosters.length" class="text-slate-400" title="Belum menjadi anggota section roster mana pun">-</span>
            <div v-for="roster in row.rosters" v-else :key="`${roster.roster_id}-${roster.section}`" class="whitespace-nowrap">
              <Link :href="`/one-roster/roster/${roster.roster_id}`" class="text-blue-600 underline">{{ roster.label }}</Link>
              <span class="ml-1 rounded border border-slate-200 bg-slate-50 px-1.5 text-[10px] text-slate-700">{{ roster.section }}</span>
            </div>
          </td>
          <td class="whitespace-nowrap px-2 py-2">
            <template v-if="row.hiring_date">{{ formatDate(row.hiring_date) }}</template>
            <span v-else class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-800" title="Isi Hiring Date lewat tombol ubah agar jatah cuti tahunan dihitung">Belum diisi</span>
          </td>
          <td class="px-2 py-2 text-center">{{ row.entitlement }}</td>
          <td class="px-2 py-2 text-center">{{ row.used }}</td>
          <td :class="['px-2 py-2 text-center font-bold', row.balance < 0 ? 'text-red-600' : '']">{{ row.balance }}</td>
          <template v-for="period in row.periods" :key="period.period_no">
            <td :class="[manualClass, 'whitespace-nowrap border-l border-slate-200 px-2 py-2']">
              <button v-if="canManage" type="button" class="text-blue-600 hover:underline" :title="`Isi / ubah periode ${period.period_no}`" @click="emit('edit-period', row, period)">
                <template v-if="period.awal">{{ formatDate(period.awal) }}</template>
                <PlusCircle v-else class="h-3.5 w-3.5" />
              </button>
              <template v-else>{{ formatDate(period.awal) }}</template>
            </td>
            <td :class="[calcClass, 'whitespace-nowrap px-2 py-2']">{{ formatDate(period.akhir) }}</td>
            <td :class="[manualClass, 'px-2 py-2 text-center']">{{ period.awal ? period.tahunan : '' }}</td>
            <td :class="[manualClass, 'px-2 py-2 text-center']">{{ period.awal ? period.izin_resmi : '' }}</td>
            <td :class="[period.induksi_issue ? 'bg-red-100 font-semibold text-red-700' : calcClass, 'whitespace-nowrap px-2 py-2']" :title="period.induksi_issue ?? ''">
              {{ formatDate(period.induksi) }}
              <small v-if="period.induksi_issue" class="block">({{ period.induksi_hari }})</small>
            </td>
            <td :class="[calcClass, 'whitespace-nowrap px-2 py-2']">
              <template v-if="period.plan">{{ formatDate(period.plan) }}</template>
              <span v-else-if="period.plan_ref" class="italic text-slate-400" title="Acuan dari periode sebelumnya (bukan cuti aktual)">{{ formatDate(period.plan_ref) }}</span>
            </td>
          </template>
          <td v-if="canManage" class="px-2 py-2">
            <button type="button" class="rounded-lg border border-slate-300 p-1.5 text-slate-600 hover:bg-slate-50" title="Ubah karyawan" @click="emit('edit-employee', row)">
              <Pencil class="h-3.5 w-3.5" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
