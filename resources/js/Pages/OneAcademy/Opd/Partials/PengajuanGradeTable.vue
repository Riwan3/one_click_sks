<script setup>
import { router } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';

defineProps({
  rows: { type: Array, required: true },
});

const GROUPS = [
  { label: 'Hiring', color: 'bg-teal-700', fields: [['doh', 'DOH'], ['masa_kerja', 'Masa Kerja']] },
  { label: 'Posisi / Jabatan', color: 'bg-blue-700', fields: [['pos_saat_ini', 'Saat Ini'], ['pos_tgl_naik', 'Tgl Naik'], ['pos_lama_naik', 'Lama Naik'], ['pos_tujuan', 'Tujuan']] },
  { label: 'Grade', color: 'bg-amber-700', fields: [['grade_saat_ini', 'Saat Ini'], ['grade_tgl_naik', 'Tgl Naik'], ['grade_lama_naik', 'Lama Naik'], ['grade_tujuan', 'Tujuan']] },
  { label: 'Versatility', color: 'bg-violet-600', fields: [['vers_saat_ini', 'Saat Ini'], ['vers_seharusnya', 'Seharusnya']] },
];
const TRAILING_FIELDS = [['mor', 'MOR'], ['atr', 'ATR'], ['sp', 'SP']];

/** Urutan kolom data di tabel: Site, lalu tiap grup, lalu MOR/ATR/SP. */
const DATA_FIELDS = ['site', ...GROUPS.flatMap((g) => g.fields.map(([key]) => key)), ...TRAILING_FIELDS.map(([key]) => key)];

/** Hanya ini yang diisi manual; kolom lain ditarik otomatis dari Monitoring Grade, Remark dari OneClick HO. */
const MANUAL_FIELDS = ['pos_tujuan', 'grade_tujuan', 'mor', 'sp'];

const TERMIN_COLOR = { 1: 'bg-sky-500', 2: 'bg-green-600', 3: 'bg-amber-600', 4: 'bg-violet-600' };
const REMARK_COLOR = { merah: 'bg-red-200 text-red-900', kuning: 'bg-yellow-200 text-yellow-900', hijau: 'bg-green-200 text-green-900' };
/** Lebar minimum kolom (px) supaya isian panjang seperti nama posisi tidak terpotong. */
const FIELD_WIDTH = {
  site: 120, doh: 100, masa_kerja: 100,
  pos_saat_ini: 240, pos_tgl_naik: 110, pos_lama_naik: 110, pos_tujuan: 240,
  grade_saat_ini: 90, grade_tgl_naik: 110, grade_lama_naik: 110, grade_tujuan: 90,
  vers_saat_ini: 100, vers_seharusnya: 100, mor: 80, atr: 80, sp: 80,
};
const widthStyle = (field) => ({ minWidth: `${FIELD_WIDTH[field]}px` });

const HIGHLIGHT_FIELDS = ['pos_tujuan', 'grade_tujuan'];

const saveField = (row, field, event) => {
  const value = event.target.value.trim();
  if (value === row[field]) return;
  router.patch(`/one-academy/opd/pengajuan-grade/${row.id}`, { field, value }, { preserveScroll: true, preserveState: true });
};

const removeRow = (row) => {
  if (!confirm(`Hapus pengajuan ${row.nama} termin ${row.termin}?`)) return;
  router.delete(`/one-academy/opd/pengajuan-grade/${row.id}`, { preserveScroll: true });
};
</script>

<template>
  <div class="max-h-[68vh] overflow-auto rounded-xl border border-slate-200">
    <table class="w-max min-w-full border-separate border-spacing-0 text-[11px]">
      <thead class="sticky top-0 z-20 text-white">
        <tr class="uppercase">
          <th rowspan="2" class="sticky left-0 z-30 bg-slate-800 px-1" style="width:48px;min-width:48px;max-width:48px">No</th>
          <th rowspan="2" class="sticky left-12 z-30 bg-slate-800 px-1" style="width:112px;min-width:112px;max-width:112px">NRP</th>
          <th rowspan="2" class="sticky left-40 z-30 bg-slate-800 px-1" style="width:256px;min-width:256px;max-width:256px">Nama Karyawan</th>
          <th rowspan="2" class="w-14 bg-slate-800 px-1">Termin</th>
          <th rowspan="2" class="bg-slate-800 px-1" :style="widthStyle('site')">Site</th>
          <th v-for="g in GROUPS" :key="g.label" :colspan="g.fields.length" :class="[g.color, 'px-1 py-1']">{{ g.label }}</th>
          <th v-for="[key, label] in TRAILING_FIELDS" :key="key" rowspan="2" class="bg-slate-600 px-1" :style="widthStyle(key)">{{ label }}</th>
          <th rowspan="2" class="min-w-64 bg-slate-600 px-1">Remark</th>
        </tr>
        <tr class="uppercase">
          <template v-for="g in GROUPS" :key="g.label">
            <th v-for="[key, label] in g.fields" :key="key" :class="[g.color, 'px-1 py-1 brightness-110']" :style="widthStyle(key)">{{ label }}</th>
          </template>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in rows" :key="row.id" class="group hover:bg-blue-50">
          <td class="sticky left-0 z-10 border-b border-slate-100 bg-white text-center text-slate-400">
            <span class="group-hover:hidden">{{ index + 1 }}</span>
            <button type="button" class="hidden w-full text-red-600 group-hover:block" title="Hapus pengajuan ini" @click="removeRow(row)"><X class="mx-auto h-3.5 w-3.5" /></button>
          </td>
          <td class="sticky left-12 z-10 border-b border-slate-100 bg-white px-1 text-center">{{ row.nrp }}</td>
          <td class="sticky left-40 z-10 border-b border-slate-100 bg-white px-2 font-semibold text-slate-800">{{ row.nama }}</td>
          <td :class="['border-b border-white text-center font-extrabold text-white', TERMIN_COLOR[row.termin]]">{{ row.termin }}</td>
          <td v-for="field in DATA_FIELDS" :key="field" :class="['border-b border-slate-100 p-0', HIGHLIGHT_FIELDS.includes(field) && 'bg-amber-50 font-bold']">
            <input v-if="MANUAL_FIELDS.includes(field)" :value="row[field]" class="h-8 w-full bg-transparent px-1 text-center ring-1 ring-inset ring-slate-200 focus:bg-white focus:outline focus:outline-2 focus:outline-blue-600" @change="saveField(row, field, $event)">
            <span v-else class="block px-1 py-2 text-center text-slate-700" title="Otomatis dari Monitoring Grade & Jabatan">{{ row[field] }}</span>
          </td>
          <td :class="['border-b border-slate-100 px-2', REMARK_COLOR[row.remark_warna]]" title="Remark diisi oleh OneClick HO">{{ row.remark }}</td>
        </tr>
        <tr v-if="!rows.length">
          <td colspan="21" class="py-12 text-center text-slate-400">
            Belum ada data. Klik nama karyawan di <b>Beranda OPD</b> lalu pilih termin, atau klik <b>Tambah Data</b>.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
