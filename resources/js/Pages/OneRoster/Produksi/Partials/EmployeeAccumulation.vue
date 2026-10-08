<script setup>
defineProps({
  rows: { type: Array, required: true },
  totals: { type: Array, required: true }, // sejajar dengan rows
  grandTotal: { type: Object, required: true },
});

const COLUMNS = [
  { key: 'siang', label: 'SIANG (D)', tone: 'bg-white text-black' },
  { key: 'malam', label: 'MALAM (N)', tone: 'bg-[#0070c0] text-white' },
  { key: 'off', label: 'OFF', tone: 'bg-[#ff0000] text-white' },
  { key: 'cuti', label: 'CUTI', tone: 'bg-[#ffc000] text-black' },
  { key: 'induksi', label: 'INDUKSI', tone: 'bg-[#7030a0] text-white' },
  { key: 'mcu', label: 'MCU', tone: 'bg-[#00b050] text-white' },
  { key: 'fu', label: 'FU', tone: 'bg-[#006400] text-white' },
  { key: 'h', label: 'H', tone: 'bg-[#ff0000] text-white' },
  { key: 'tr', label: 'TRAINING', tone: 'bg-[#00b0f0] text-black' },
];

const display = (value) => (value > 0 ? value : '');
</script>

<template>
  <div class="mt-4 print:hidden">
    <h6 class="mb-2 text-sm font-bold text-slate-800">Akumulasi Roster Per Karyawan</h6>
    <div class="overflow-x-auto">
      <table class="min-w-[600px] w-full border-collapse text-[11px]">
        <thead class="bg-slate-800 text-white">
          <tr>
            <th class="border border-slate-300 px-2 py-1 text-left">NAMA KARYAWAN</th>
            <th class="border border-slate-300 px-2 py-1 text-left">NRP</th>
            <th v-for="column in COLUMNS" :key="column.key" :class="['border border-slate-300 px-2 py-1 text-center', column.tone]">{{ column.label }}</th>
            <th class="border border-slate-300 px-2 py-1 text-center">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, index) in rows" :key="row.nrp">
            <td class="border border-slate-300 px-2 py-1 font-bold">{{ row.nama }}</td>
            <td class="border border-slate-300 px-2 py-1">{{ row.nrp }}</td>
            <td v-for="column in COLUMNS" :key="column.key" :class="['border border-slate-300 px-2 py-1 text-center font-bold', column.tone]">{{ display(totals[index][column.key]) }}</td>
            <td class="border border-slate-300 bg-slate-50 px-2 py-1 text-center font-bold">{{ totals[index].total }}</td>
          </tr>
          <tr class="bg-slate-800 font-bold text-white">
            <td colspan="2" class="border border-slate-300 px-2 py-1">TOTAL</td>
            <td v-for="column in COLUMNS" :key="column.key" class="border border-slate-300 px-2 py-1 text-center">{{ grandTotal[column.key] }}</td>
            <td class="border border-slate-300 px-2 py-1 text-center">{{ grandTotal.total }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
