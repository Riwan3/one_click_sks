<script setup>
import { Download } from 'lucide-vue-next';
import { dayName } from '@/utils/rosterMatrix';

defineProps({
  rows: { type: Array, required: true },
  downloadUrl: { type: String, required: true },
});
</script>

<template>
  <div class="space-y-2">
    <div class="flex flex-wrap items-center justify-between gap-2">
      <p class="text-xs text-slate-500">
        <strong>Upload</strong> — satu baris per orang per tanggal. Status = huruf kode + jenis hari, dihitung dari <strong>hari OFF</strong> orang itu:
        hari OFF = hari ke-7 (<code>L</code>), sehari sebelumnya hari ke-6 (<code>E</code>), sisanya <code>R</code>. Days = urutan hari 1–7 dalam pekan itu.
      </p>
      <a :href="rows.length ? downloadUrl : undefined" :class="['flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700', rows.length ? '' : 'pointer-events-none opacity-50']"><Download class="h-4 w-4" />Unduh Excel</a>
    </div>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <p v-if="!rows.length" class="px-4 py-12 text-center text-sm text-slate-500">Belum ada isi roster pada periode ini.</p>
      <div v-else class="max-h-[70vh] overflow-auto">
        <table class="w-full min-w-[720px] text-sm">
          <thead class="sticky top-0 bg-slate-50 text-left text-xs font-semibold text-slate-600">
            <tr><th class="px-3 py-2">No</th><th class="px-3 py-2">NRP</th><th class="px-3 py-2">Nama</th><th class="px-3 py-2">Idx_tgl</th><th class="px-3 py-2">Tanggal</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Unit</th><th class="px-3 py-2">Lookup</th><th class="px-3 py-2">Days</th></tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(row, index) in rows" :key="index" class="hover:bg-slate-50">
              <td class="px-3 py-1">{{ row.no }}</td><td class="px-3 py-1">{{ row.nrp }}</td><td class="px-3 py-1">{{ row.nama }}</td><td class="px-3 py-1">{{ row.idx }}</td>
              <td class="whitespace-nowrap px-3 py-1">{{ row.tanggal }}</td><td class="px-3 py-1 font-bold">{{ row.status }}</td>
              <td class="px-3 py-1">{{ row.unit }}</td><td class="px-3 py-1 text-slate-500">{{ row.lookup }}</td>
              <td class="px-3 py-1">{{ row.days }} <span class="text-xs text-slate-500">{{ dayName(row.days) }}</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
