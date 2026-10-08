<script setup>
import { computed } from 'vue';

const props = defineProps({
  share: { type: Object, required: true },
  days: { type: Array, required: true },
  monthName: { type: String, required: true },
});

const INFO_ROWS = [['NAMA', 'nama'], ['NRP', 'nrp'], ['JABATAN', 'jabatan']];
const HEADERS = [['NO', 'w-10'], ['HARI', 'w-24'], ['TANGGAL', 'w-40'], ['ROSTER', 'w-20'], ['KETERANGAN', '']];

const periodLabel = computed(() => `PERIODE ${props.monthName.toUpperCase()} ${props.share.tahun}`);
const signDate = computed(() => `Tanjung, ${new Date().getDate()} ${props.monthName} ${props.share.tahun}`);
</script>

<template>
  <div class="mx-auto max-w-2xl rounded-xl border border-slate-300 bg-white p-6 shadow-sm print:max-w-none print:border-0 print:p-0 print:shadow-none">
    <div class="mb-4 flex items-center justify-between gap-4 border-b pb-3">
      <div class="flex items-center gap-2.5">
        <img src="/images/images.png" alt="Safe & Strong" class="h-10 object-contain sm:h-11" />
        <div class="leading-tight">
          <div class="text-xs font-black tracking-tight text-slate-900 sm:text-sm">SAFE & STRONG</div>
          <div class="text-[8px] font-bold tracking-wider text-slate-600 sm:text-[9px]">OPERATIONAL PERFORMANCE</div>
        </div>
      </div>
      <img src="/images/logo_synergy.png" alt="Synergy" class="h-10 object-contain sm:h-12" />
    </div>

    <div class="mb-3 border-y-2 border-slate-900 py-1.5 text-center text-xs font-black uppercase tracking-wider text-slate-900">
      ROSTER KARYAWAN DEPT. PRODUKSI - PPA JOBSITE ADARO INDONESIA
    </div>

    <div class="mb-3 flex items-start justify-between gap-4">
      <div class="space-y-1 text-xs">
        <div v-for="[label, key] in INFO_ROWS" :key="key" class="flex">
          <span class="w-20 font-bold text-slate-800">{{ label }}</span>
          <span class="w-4 font-bold text-slate-800">:</span>
          <span class="font-bold text-slate-900">{{ share.employee?.[key] || '-' }}</span>
        </div>
      </div>
      <div class="min-w-36 overflow-hidden rounded-xs border-2 border-slate-900 text-center">
        <div class="border-b-2 border-slate-900 bg-white px-4 py-0.5 text-xs font-black text-slate-900">UNIT</div>
        <div class="flex min-h-10 items-center justify-center px-4 py-1 text-base font-black text-slate-900">{{ share.employee?.unit || '-' }}</div>
      </div>
    </div>

    <div class="mb-3 border border-slate-900 bg-white py-1 text-center text-xs font-extrabold uppercase text-slate-900">{{ periodLabel }}</div>

    <div class="overflow-x-auto">
      <table class="w-full border-collapse border border-slate-900 text-xs">
        <thead>
          <tr class="bg-slate-200 text-slate-900">
            <th v-for="[header, width] in HEADERS" :key="header" :class="['border border-slate-900 px-2 py-1 text-center font-bold', width]">{{ header }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in days" :key="row.day" :class="row.isSunday ? 'bg-[#fef9c3]' : 'bg-white'">
            <td class="border border-slate-900 px-1 py-0.5 text-center font-medium">{{ row.day }}</td>
            <td class="border border-slate-900 px-2 py-0.5 text-center font-medium">{{ row.dayName }}</td>
            <td class="border border-slate-900 px-2 py-0.5 text-center font-medium">{{ row.dateLabel }}</td>
            <td :class="['border border-slate-900 px-1 py-0.5 text-center font-extrabold', row.tone]">{{ row.kode }}</td>
            <td class="border border-slate-900 px-2 py-0.5 text-center font-medium">{{ row.note }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="mt-4 pt-2 text-xs">
      <div class="flex items-start justify-between gap-4">
        <div class="space-y-2">
          <div>
            <div class="font-extrabold text-red-600">**Note</div>
            <div class="text-[11px] font-medium text-slate-800">- Update roster per hari harap menyesuaikan dengan SS6.</div>
          </div>
          <div class="pt-1 text-xs font-black leading-tight">
            <div class="text-green-600">Saya Sehat, Saya Selamat,</div>
            <div class="text-red-600">Saya Semangat, Saya Manfaat!!!!!</div>
          </div>
          <div class="pt-2">
            <img src="/images/logo_safe_strong.png" alt="SAFE & STRONG" class="h-9 object-contain sm:h-10" />
          </div>
        </div>

        <div class="min-w-[180px] shrink-0 text-right sm:min-w-[200px]">
          <div class="font-medium text-slate-900">Mengetahui,</div>
          <div class="mt-0.5 font-medium text-slate-900">{{ signDate }}</div>
          <div class="h-14 sm:h-16"></div>
          <div class="text-sm font-bold text-slate-900">{{ share.pic.nama }}</div>
          <div class="italic text-slate-800">{{ share.pic.jabatan }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
