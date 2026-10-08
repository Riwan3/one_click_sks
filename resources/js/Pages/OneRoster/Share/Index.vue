<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { AlertTriangle, CalendarRange, CalendarX, Info, Lock } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import { buildShareDays } from '@/utils/rosterShare';
import ShareFilter from './Partials/ShareFilter.vue';
import ShareSheet from './Partials/ShareSheet.vue';
import ShareSummary from './Partials/ShareSummary.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  share: { type: Object, required: true },
  monthNames: { type: Object, required: true },
});

const monthName = computed(() => props.monthNames[props.share.bulan]);
const periodLabel = computed(() => `${monthName.value} ${props.share.tahun}`);
const employeeLabel = computed(() => props.share.employee?.nama || props.share.nrp);
const availableMonths = computed(() => props.share.openMonths.map((month) => props.monthNames[month]).join(', '));
const days = computed(() => buildShareDays(props.share.tahun, props.share.bulan, monthName.value, props.share.cells));
const subtitle = computed(() => (props.share.canPick ? 'Pilih karyawan untuk melihat roster kalender bulanannya.' : 'Roster kalender bulanan Anda.'));
const hasSheet = computed(() => props.share.state === 'ok' && !!props.share.employee);

const handlePrint = () => window.print();
</script>

<template>
  <Head title="Share Roster - One-Roster" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="mx-auto max-w-3xl space-y-4">
      <div class="print:hidden">
        <h1 class="mb-0.5 flex items-center gap-2 text-xl font-black tracking-tight text-slate-800">
          <CalendarRange class="h-5 w-5 text-blue-500" /> Share Roster
        </h1>
        <p class="m-0 text-xs font-medium text-slate-500">{{ subtitle }}</p>
      </div>

      <ShareFilter :share="share" :month-names="monthNames" @print="handlePrint" />

      <ShareSummary v-if="hasSheet" :days="days" />

      <div v-if="share.state === 'error'" class="flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 print:hidden">
        <AlertTriangle class="h-4 w-4" /> {{ share.error }}
      </div>
      <div v-else-if="share.state === 'pick'" class="flex items-start gap-2 rounded-xl border border-blue-200 bg-blue-50 p-4 text-xs font-bold text-blue-700 print:hidden">
        <Info class="mt-0.5 h-4 w-4 shrink-0" />
        <span>
          Silakan pilih karyawan dan klik <strong>Tampilkan</strong> untuk melihat roster.
          <small v-if="!share.employees.length" class="mt-1 block font-medium">Belum ada roster yang dipublish. Buat roster di <a href="/one-roster/roster" class="text-blue-600 underline">Roster V2</a>, lalu klik <strong>Publish</strong>.</small>
        </span>
      </div>
      <div v-else-if="share.state === 'closed' || share.state === 'closed_other_months'" class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs font-bold text-amber-700 print:hidden">
        <Lock class="h-4 w-4 shrink-0" />
        <span v-if="share.state === 'closed_other_months'">Roster bulan <strong>{{ periodLabel }}</strong> belum dibuka oleh PIC. Bulan yang tersedia: {{ availableMonths }}.</span>
        <span v-else>Roster Anda belum dibuka oleh PIC. Silakan hubungi PIC roster.</span>
      </div>
      <div v-else-if="share.state === 'empty'" class="flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs font-bold text-amber-700 print:hidden">
        <CalendarX class="h-4 w-4 shrink-0" />
        <span>Belum ada roster yang dipublish untuk <strong>{{ employeeLabel }}</strong> pada {{ periodLabel }}.</span>
      </div>

      <ShareSheet v-if="hasSheet" :share="share" :days="days" :month-name="monthName" />
    </div>
  </AppLayout>
</template>
