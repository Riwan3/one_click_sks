<script setup>
defineProps({
  title: { type: String, required: true },
  hint: { type: String, default: '' },
  hasSectionColumn: { type: Boolean, default: false },
  rows: { type: Array, required: true }, // [{ key, grup?, label, orang, siang, malam, cuti, off, lain, kosong }]
  total: { type: Object, default: null },
});

const NUMBER_COLUMNS = [
  { key: 'orang', label: 'Total Orang' },
  { key: 'siang', label: 'Siang' },
  { key: 'malam', label: 'Malam' },
  { key: 'cuti', label: 'Cuti' },
  { key: 'off', label: 'OFF' },
  { key: 'lain', label: 'Lainnya' },
  { key: 'kosong', label: 'Belum Diisi' },
];
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="border-b border-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-800">
      {{ title }} <span v-if="hint" class="text-xs font-normal text-slate-500">{{ hint }}</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-xs text-slate-600">
          <tr>
            <th class="px-4 py-2 text-left font-semibold">Roster</th>
            <th v-if="hasSectionColumn" class="px-4 py-2 text-left font-semibold">Section</th>
            <th v-for="column in NUMBER_COLUMNS" :key="column.key" class="px-4 py-2 text-right font-semibold">{{ column.label }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="row in rows" :key="row.key" class="hover:bg-slate-50">
            <td :class="['px-4 py-2', hasSectionColumn ? 'text-slate-500' : 'font-semibold']">{{ row.grup }}</td>
            <td v-if="hasSectionColumn" class="px-4 py-2 font-semibold">{{ row.section }}</td>
            <td
              v-for="column in NUMBER_COLUMNS"
              :key="column.key"
              :class="['px-4 py-2 text-right', column.key === 'kosong' ? (row.kosong ? 'font-semibold text-red-600' : 'text-slate-400') : '']"
            >{{ row[column.key] }}</td>
          </tr>
        </tbody>
        <tfoot v-if="total" class="bg-slate-50 font-bold">
          <tr>
            <td class="px-4 py-2">Total</td>
            <td v-for="column in NUMBER_COLUMNS" :key="column.key" class="px-4 py-2 text-right">{{ total[column.key] }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>
