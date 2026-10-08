<script setup>
import { computed } from 'vue';
import { SHIFT_LABEL, formatDateLabel, splitColumns } from '@/utils/manpowerV201';
import PreviewSimpleTable from './PreviewSimpleTable.vue';

const props = defineProps({
  setting: { type: Object, required: true },
  tree: { type: Array, required: true },
  gl: { type: Array, default: () => [] },
  lain: { type: Array, default: () => [] },
  checkPointPassword: { type: String, default: null },
});

const SHIFT_BG = { D: '#1f4e9c', N: '#3a1f6e' };
const CSA_COLOR = { gibson: { bg: '#ff0000', cat: '#c00000' }, default: { bg: '#00b050', cat: '#375623' } };

const dateShift = computed(() => `${formatDateLabel(props.setting.work_date)} ${SHIFT_LABEL[props.setting.shift]}`);

const csaColor = (name) => (name.toUpperCase().includes('GIBSON') ? CSA_COLOR.gibson : CSA_COLOR.default);

/** Kolom terpakai per CSA (tanpa kolom kosong); GL & Lain-lain ditaruh di kolom terakhir CSA terakhir. */
const sections = computed(() => props.tree.map((csa, csaIndex) => {
  const used = Object.values(splitColumns(csa.categories)).filter((list) => list.length);
  const columns = used.length ? used : [[]];

  return {
    csa,
    color: csaColor(csa.name),
    columns: columns.map((categories, colIndex) => ({
      categories,
      hasExtras: csaIndex === props.tree.length - 1 && colIndex === columns.length - 1,
    })),
  };
}));

const glRows = computed(() => props.gl.map((g) => ({ key: g.nrp, label: g.unit, phone: g.phone, nrp: g.nrp, name: g.name })));
const lainRows = computed(() => props.lain.map((o) => ({ key: o.nrp, label: o.label, phone: o.phone, nrp: o.nrp, name: o.name })));

const unitClass = (u) => ['pv-unit', { 'pv-risk': u.is_high_risk, 'pv-slot': u.is_spare_slot }];
const cellClass = (a, base) => [base, { 'pv-spare': a.spare }];
</script>

<template>
  <div class="pv-sheet">
    <div class="pv-head">
      <img src="/images/logo_safe_strong.png" alt="Safe & Strong" class="pv-logo-l">
      <div class="pv-title">
        <div class="pv-t1">SETTING MANPOWER DEPARTEMENT PRODUKSI</div>
        <div class="pv-t2" :style="{ background: SHIFT_BG[setting.shift] }">{{ dateShift }}</div>
        <div class="pv-t3">PASSWORD CHECK POINT &nbsp;:&nbsp; {{ checkPointPassword ?? '-' }}</div>
      </div>
      <img src="/images/logo_adaro.png" alt="Adaro" class="pv-logo-r">
    </div>

    <div class="pv-csas">
      <div v-for="section in sections" :key="section.csa.id" class="pv-csa" :style="{ '--pv-cat': section.color.cat }">
        <div class="pv-csa-h" :style="{ background: section.color.bg }">{{ section.csa.name }}</div>
        <div class="pv-csa-cols">
          <div v-for="(column, colIndex) in section.columns" :key="colIndex" class="pv-col">
            <table class="pv-table pv-colhead">
              <colgroup><col style="width: 112px"><col style="width: 100px"><col style="width: 74px"><col></colgroup>
              <thead><tr><th>NO UNIT</th><th>NO TELEPON</th><th>NRP</th><th>NAMA</th></tr></thead>
            </table>

            <table v-for="cat in column.categories" :key="cat.id" class="pv-table">
              <colgroup><col style="width: 112px"><col style="width: 100px"><col style="width: 74px"><col></colgroup>
              <thead><tr><th colspan="4" class="pv-cat">{{ cat.name }}</th></tr></thead>
              <tbody>
                <tr v-for="u in cat.units" :key="u.id" :class="{ 'pv-slot-row': u.is_spare_slot }">
                  <td :class="unitClass(u)">{{ u.unit }}</td>
                  <template v-if="u.assignment">
                    <td :class="cellClass(u.assignment, 'pv-c')">{{ u.assignment.phone }}</td>
                    <td :class="cellClass(u.assignment, 'pv-c')">{{ u.assignment.nrp }}</td>
                    <td :class="cellClass(u.assignment, 'pv-name')">{{ u.assignment.employee_name }}</td>
                  </template>
                  <td v-else-if="u.double.length" colspan="3" class="pv-double">DOUBLE - belum dipilih</td>
                  <template v-else><td /><td /><td /></template>
                </tr>
              </tbody>
            </table>

            <template v-if="column.hasExtras">
              <PreviewSimpleTable title="GROUP LEADER" :rows="glRows" />
              <PreviewSimpleTable title="LAIN - LAIN" :rows="lainRows" />
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
/* Gambar preview berukuran piksel tetap (spreadsheet look) supaya hasil unduhan konsisten di semua layar. */
.pv-sheet { background: #fff; font-family: Arial, Helvetica, sans-serif; color: #000; width: max-content; padding: 14px; }
.pv-head { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 18px; margin-bottom: 14px; }
.pv-logo-l { height: 64px; justify-self: start; }
.pv-logo-r { height: 76px; justify-self: end; }
.pv-title { width: 680px; border: 2px solid #222; text-align: center; font-weight: 800; }
.pv-t1 { background: #d9d9d9; font-size: 20px; white-space: nowrap; height: 46px; line-height: 46px; padding: 0 4px; }
.pv-t2 { color: #fff; font-size: 19px; height: 42px; line-height: 42px; padding: 0 4px; }
.pv-t3 { background: #ffff00; font-size: 17px; height: 38px; line-height: 38px; padding: 0 4px; }
.pv-csas { display: flex; gap: 26px; align-items: flex-start; }
.pv-csa-h { color: #fff; text-align: center; font-weight: 800; font-size: 19px; padding: 6px 4px; margin-bottom: 8px; }
.pv-csa-cols { display: flex; gap: 16px; align-items: flex-start; }
.pv-col { width: 545px; }
.pv-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; table-layout: fixed; font-size: 11.5px; }
.pv-table :is(td, th) { border: 1px solid #222; padding: 1px 4px; height: 17px; white-space: nowrap; overflow: hidden; text-overflow: clip; }
.pv-colhead { margin-bottom: 0; }
.pv-colhead th { background: #fff; color: #000; text-align: center; font-weight: 800; font-size: 12px; height: 24px; border-bottom: 2px solid #222; }
.pv-cat { background: var(--pv-cat, #375623); color: #fff; text-align: center; font-size: 11.5px; font-weight: 800; }
.pv-cat-gl { background: #ffc000; color: #000; }
.pv-unit { font-weight: 700; text-align: center; }
.pv-slot, .pv-slot-row td { background: #d9d9d9; }
.pv-risk { background: #ff0000; color: #fff; }
.pv-name { font-weight: 600; }
.pv-spare { background: #c3eaae; }
.pv-c { text-align: center; }
.pv-double { background: #ffff00; font-weight: 700; text-align: center; }
</style>
