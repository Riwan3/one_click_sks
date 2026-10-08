<script setup>
import { computed } from 'vue';
import { buildShareDays } from '@/utils/rosterShare';

const props = defineProps({
  share: { type: Object, required: true },
  monthName: { type: String, required: true },
});

const days = computed(() => buildShareDays(props.share.tahun, props.share.bulan, props.monthName, props.share.cells));
const periodLabel = computed(() => `PERIODE ${props.monthName.toUpperCase()} ${props.share.tahun}`);
const signDate = computed(() => `Tanjung, ${String(new Date().getDate()).padStart(2, '0')} ${props.monthName} ${props.share.tahun}`);

const INFO_ROWS = [['NAMA', 'nama'], ['NRP', 'nrp'], ['JABATAN', 'jabatan']];
const HEADERS = ['NO', 'HARI', 'TANGGAL', 'ROSTER', 'KETERANGAN'];
</script>

<template>
  <div class="mx-auto max-w-[700px] rounded-xl bg-white px-6 py-5 shadow-sm print:max-w-none print:rounded-none print:px-4 print:py-2 print:shadow-none">
    <div class="mb-2.5 flex items-center justify-between">
      <img src="/images/logo_ppa.png" alt="PPA" class="h-7 object-contain sm:h-11" />
      <img src="/images/logo_synergy.png" alt="Synergy" class="h-7 object-contain sm:h-11" />
      <img src="/images/logo_adaro.png" alt="Adaro" class="h-7 object-contain sm:h-11" />
    </div>

    <div class="mb-3 border-y-2 border-black py-1 text-center text-[11px] font-bold">ROSTER KARYAWAN DEPT. PRODUKSI - PPA JOBSITE ADARO INDONESIA</div>

    <div class="mb-3 flex items-start justify-between">
      <div class="flex-1 text-[10px]">
        <div v-for="[label, key] in INFO_ROWS" :key="key" class="mb-0.5 flex">
          <span class="w-[70px] font-bold">{{ label }}</span>
          <span class="w-[15px] text-center">:</span>
          <span>{{ share.employee?.[key] ?? '' }}</span>
        </div>
      </div>
      <div class="min-w-[140px] border-2 border-black text-center">
        <div class="border-b-2 border-black px-4 py-0.5 text-[10px] font-bold">UNIT</div>
        <div class="flex min-h-10 items-center justify-center px-4 py-2 text-[22px] font-bold">{{ share.employee?.unit ?? '' }}</div>
      </div>
    </div>

    <div class="mb-3 border border-black py-1 text-center text-[11px] font-bold">{{ periodLabel }}</div>

    <table class="mb-4 w-full border-collapse text-[10px]">
      <thead>
        <tr><th v-for="header in HEADERS" :key="header" class="border border-black bg-[#d9d9d9] px-1 py-0.5 text-center font-bold">{{ header }}</th></tr>
      </thead>
      <tbody>
        <tr v-for="row in days" :key="row.day" :class="row.isSunday ? 'bg-[#fff3cd]' : ''">
          <td class="w-[30px] border border-black px-1 py-0.5 text-center">{{ row.day }}</td>
          <td class="w-[65px] border border-black px-1 py-0.5 text-center">{{ row.dayName }}</td>
          <td class="w-[110px] border border-black px-1 py-0.5 text-center">{{ row.dateLabel }}</td>
          <td class="w-[80px] border border-black px-1 py-0.5 text-center"><span :class="['font-bold', row.tone]">{{ row.kode }}</span></td>
          <td class="border border-black px-1 py-0.5 text-center">{{ row.note }}</td>
        </tr>
      </tbody>
    </table>

    <div class="mb-4 flex justify-between text-[10px]">
      <div>
        <div class="mb-0.5 font-bold text-red-600">**Note</div>
        <div class="text-[9px]">- Update roster per hari harap menyesuaikan dengan SS6.</div>
      </div>
      <div class="text-right">Mengetahui,<br />{{ signDate }}</div>
    </div>

    <div class="flex items-end justify-between">
      <div>
        <div class="text-[10px] font-bold leading-snug">
          <span class="text-green-600">Saya Sehat, Saya Selamat,</span><br />
          <span class="text-red-600">Saya Semangat, Saya Manfaat!!!!!</span>
        </div>
        <img src="/images/logo_safe_strong.png" alt="Safe & Strong" class="mt-2 h-10" />
      </div>
      <div class="text-right text-[10px]">
        <div class="font-bold">{{ share.pic.nama }}</div>
        <div><i>{{ share.pic.jabatan }}</i></div>
      </div>
    </div>
  </div>
</template>
