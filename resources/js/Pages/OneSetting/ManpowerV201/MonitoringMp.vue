<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Printer, TriangleAlert, Users } from 'lucide-vue-next';
import { BASE_URL, SHIFT_LABEL, formatDateLabel, countFilled } from '@/utils/manpowerV201';
import ManpowerShell from './Partials/ManpowerShell.vue';
import DateShiftFilter from './Partials/DateShiftFilter.vue';
import EmptySettingCard from './Partials/EmptySettingCard.vue';
import MonitoringMpCsa from './Partials/MonitoringMpCsa.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  ready: { type: Boolean, default: true },
  loadError: { type: String, default: null },
  filters: { type: Object, required: true },
  view: { type: Object, default: null },
});

const setting = computed(() => props.view?.setting ?? null);
const isFinal = computed(() => setting.value?.status === 'final');
const assigned = computed(() => props.view.tree.reduce((sum, csa) => sum + countFilled(csa), 0));
const hasNoSs6 = computed(() => assigned.value > 0 && Object.keys(props.view.codes.ss6).length === 0);
const dateShiftLabel = computed(() => `${formatDateLabel(props.filters.date)} ${SHIFT_LABEL[props.filters.shift]}`);
const handlePrint = () => window.print();
const harianUrl = computed(() => `${BASE_URL}?${new URLSearchParams(props.filters)}`);
</script>

<template>
  <ManpowerShell
    :user="user"
    :notif-counts="notifCounts"
    :menu="menu"
    :ready="ready"
    :load-error="loadError"
    subtitle="Monitoring settingan manpower per tanggal dan shift (data dari Setting Manpower)."
  >
    <div class="flex flex-wrap items-center gap-3 print:hidden">
      <DateShiftFilter :url="`${BASE_URL}/monitoring-mp`" :filters="filters" class="flex-1" />
      <div class="flex flex-wrap items-center gap-2">
        <span v-if="setting" :class="['rounded-full px-3 py-1.5 text-xs font-bold text-white', isFinal ? 'bg-emerald-600' : 'bg-slate-500']">{{ isFinal ? 'FINAL' : 'DRAFT' }}</span>
        <span v-if="setting" class="flex items-center gap-1 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700"><Users class="h-3.5 w-3.5" /> {{ assigned }} Operator Teralokasi</span>
        <button type="button" class="flex items-center gap-1 rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50" @click="handlePrint"><Printer class="h-3.5 w-3.5" /> Print</button>
      </div>
    </div>

    <EmptySettingCard v-if="!setting" :filters="filters" hint="Buat setting dulu di Setting Manpower.">
      <Link :href="harianUrl" class="mt-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Ke Setting Harian</Link>
    </EmptySettingCard>

    <p v-else-if="!view.tree.length" class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">Master CSA masih kosong. Isi dulu di tab Master CSA.</p>

    <template v-else>
      <p v-if="hasNoSs6" class="flex items-center gap-1 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-800 print:hidden">
        <TriangleAlert class="h-3.5 w-3.5" /> Data SS6 untuk {{ dateShiftLabel }} belum ada, kolom SS6 tampil "-". Import dulu di Personal Data > Attendance (One-Power), lalu muat ulang halaman ini.
      </p>
      <MonitoringMpCsa v-for="csa in view.tree" :key="csa.id" :csa="csa" :codes="view.codes" />
    </template>
  </ManpowerShell>
</template>
