<script setup>
import { PARKING_OPTIONS } from '@/utils/fleet';
import { FLEET_SHIFT_LABEL, STATUS_STYLE, clusterStyle, dumpingColor, formatSheetDate, haulerRowCount, packClusters, refuelInfo } from '@/utils/fleetBoard';
import FleetSidePanel from './FleetSidePanel.vue';
import FleetUnitCells from './FleetUnitCells.vue';

defineProps({
  sheet: { type: Object, required: true }, // { date, shift, areas, breakdown, ready, summary, total_hauler } (FleetBoardResource)
});

const BLANK = { background: '#fff' };
const SUMMARY_ROWS = [
  { key: 'ob', label: 'OB' },
  { key: 'coal', label: 'COAL' },
  { key: 'breakdown', label: 'STB/BD' },
  { key: 'total', label: 'TOTAL UNIT' },
];

const span = (columns) => ({ gridColumn: `span ${columns}` });
const headerStyle = (cell) => (cell.blank ? BLANK : clusterStyle(cell.name));
const dumpingStyle = (loader) => (loader?.dumping ? { background: dumpingColor(loader.dumping) } : BLANK);
const refuelStyle = (loader) => (loader?.refueling ? { background: refuelInfo(loader.refueling).background } : BLANK);
const countStyle = (loader) => (loader?.units.length ? { background: '#ffff00' } : BLANK);
</script>

<template>
  <div class="w-max border-2 border-[#222] bg-white font-sans">
    <div class="flex items-center gap-2 bg-[#1a3a6e] px-2.5 py-1.5 text-white">
      <img src="/images/logo-oneclick.png" alt="" class="h-9 w-9 rounded-full bg-white object-contain p-0.5">
      <div class="flex-1 text-center">
        <div class="text-[11px] font-black tracking-wider">SETTING FLEET DEPT PRODUKSI PPA SITE ADW</div>
        <div class="mt-0.5 inline-block rounded-full bg-[#e67e00] px-3.5 py-0.5 text-[9px] font-extrabold">{{ formatSheetDate(sheet.date) }} : {{ FLEET_SHIFT_LABEL[sheet.shift] }}</div>
      </div>
    </div>
    <div class="bg-[#e74c3c] px-2 py-1 text-center text-[9px] font-extrabold tracking-wide text-white">HARAP MENGIKUTI JAM REFUELING YANG TELAH DISETTING !!!</div>

    <div class="flex items-stretch border-t-2 border-[#222]">
      <div>
        <section v-for="area in sheet.areas" :key="area.name">
          <div class="bg-[#455a64] py-0.5 text-center text-[9px] font-black tracking-widest text-white">{{ area.name }}</div>
          <p v-if="!area.clusters.length" class="py-2 text-center text-[8px] italic text-slate-400">Belum ada cluster</p>
          <div v-for="(row, rowIndex) in packClusters(area.clusters)" :key="rowIndex" class="grid grid-cols-[repeat(8,98px_26px)] gap-px border-y border-[#777] bg-[#777]">
            <div v-for="(cell, cellIndex) in row.cells" :key="`h${cellIndex}`" class="flex min-h-[20px] items-center justify-center px-1 text-center text-[10px] font-black" :style="{ ...span(cell.slots * 2), ...headerStyle(cell) }">{{ cell.name.toUpperCase() }}</div>

            <div v-for="(loader, i) in row.slots" :key="`d${i}`" class="flex min-h-[34px] items-center justify-center px-1 text-center text-[9px] font-black leading-tight text-black" :style="{ ...span(2), ...dumpingStyle(loader) }">{{ loader?.dumping }}</div>
            <div v-for="(loader, i) in row.slots" :key="`r${i}`" class="flex min-h-[18px] items-center justify-center px-1 text-center text-[9px] font-black text-black" :style="{ ...span(2), ...refuelStyle(loader) }">{{ loader ? refuelInfo(loader.refueling).label : '' }}</div>
            <div v-for="(loader, i) in row.slots" :key="`c${i}`" class="flex min-h-[20px] items-center justify-center text-[10px] font-black" :style="{ ...span(2), background: loader ? '#000' : '#fff', color: '#fff' }">{{ loader?.code }}</div>

            <template v-for="n in haulerRowCount(row.slots)" :key="`u${n}`">
              <template v-for="(loader, i) in row.slots" :key="`u${n}-${i}`">
                <div v-if="n === 1 && loader && !loader.units.length" class="flex h-[18px] items-center justify-center bg-white text-[7px] font-semibold italic text-slate-400" :style="span(2)">BELUM ADA HAULER</div>
                <FleetUnitCells v-else :unit="loader?.units[n - 1] ?? null" />
              </template>
            </template>

            <div v-for="(loader, i) in row.slots" :key="`j${i}`" class="flex min-h-[18px] items-center justify-center bg-white text-[10px] font-black text-black" :style="span(2)">{{ loader?.distance > 0 ? loader.distance : '' }}</div>
            <div v-for="(loader, i) in row.slots" :key="`n${i}`" class="flex min-h-[18px] items-center justify-center text-[10px] font-black text-black" :style="{ ...span(2), ...countStyle(loader) }">{{ loader ? `${loader.units.length} Unit` : '' }}</div>
          </div>
        </section>
      </div>

      <aside class="flex w-[190px] shrink-0 flex-col border-l-[3px] border-[#222] bg-white">
        <FleetSidePanel title="READY CSA MONTE" title-background="#0070c0" sub-title="ALL FLEET MONTE" :units="sheet.ready.monte" />
        <FleetSidePanel title="READY CSA GIBSON" title-background="#ff0000" :units="sheet.ready.gibson" count-background="#ff0000" count-color="#fff" />
        <FleetSidePanel title="BREAKDOWN / SERVICE" title-background="#ff0000" :units="sheet.breakdown?.units ?? []" />
        <div class="bg-[#92d050] py-1 text-center text-[10px] font-black text-black">UNIT</div>
        <div class="grid grid-cols-[1fr_52px] gap-px bg-[#777]">
          <template v-for="item in SUMMARY_ROWS" :key="item.key">
            <div class="bg-[#d9d9d9] px-2 py-0.5 text-[10px] font-black text-black">{{ item.label }}</div>
            <div class="bg-white py-0.5 text-center text-[10px] font-black text-black">{{ sheet.summary[item.key] }}</div>
          </template>
        </div>
        <div class="flex-1" />
      </aside>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 border-t-2 border-[#222] bg-[#1a3a6e] px-2.5 py-1 text-[8px] font-black text-white">
      <span class="flex items-center gap-3">
        <span v-for="option in PARKING_OPTIONS" :key="option.code" class="flex items-center gap-1">
          <span class="inline-flex h-3 w-3 items-center justify-center text-[7px]" :style="STATUS_STYLE[option.code]">{{ option.code }}</span> {{ option.label.slice(4) }}
        </span>
      </span>
      <span>TOTAL HAULER : <span class="ml-1.5 text-[11px]">{{ sheet.total_hauler }} Unit</span></span>
    </div>
  </div>
</template>
