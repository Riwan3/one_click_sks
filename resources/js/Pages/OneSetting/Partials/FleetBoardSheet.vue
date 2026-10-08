<script setup>
import { PARKING_OPTIONS } from '@/utils/fleet';
import { FLEET_SHIFT_LABEL, chunkLoaders, formatSheetDate, loaderMeta } from '@/utils/fleetBoard';
import FleetUnitLabel from './FleetUnitLabel.vue';

defineProps({
  sheet: { type: Object, required: true }, // { date, shift, areas, breakdown, total_hauler } (FleetBoardResource)
});
</script>

<template>
  <div class="w-max min-w-full border-2 border-[#333] bg-white font-sans">
    <div class="flex items-center gap-2 bg-[#1a3a6e] px-2.5 py-1.5 text-white">
      <img src="/images/logo-oneclick.png" alt="" class="h-9 w-9 rounded-full bg-white object-contain p-0.5">
      <div class="flex-1 text-center">
        <div class="text-[11px] font-black tracking-wider">SETTING FLEET DEPT PRODUKSI PPA SITE ADW</div>
        <div class="mt-0.5 inline-block rounded-full bg-[#e67e00] px-3.5 py-0.5 text-[9px] font-extrabold">{{ formatSheetDate(sheet.date) }} : {{ FLEET_SHIFT_LABEL[sheet.shift] }}</div>
      </div>
    </div>
    <div class="bg-[#e74c3c] px-2 py-1 text-center text-[9px] font-extrabold tracking-wide text-white">HARAP MENGIKUTI JAM REFUELING YANG TELAH DISETTING !!!</div>

    <section v-for="area in sheet.areas" :key="area.name">
      <div class="border-y-2 border-[#333] bg-[#455a64] py-1 text-center text-[10px] font-black tracking-widest text-white">{{ area.name }}</div>
      <div class="grid grid-cols-2">
        <div v-for="cluster in area.clusters" :key="cluster.name" class="min-w-[440px] border-b border-r border-[#999] [&:nth-child(2n)]:border-r-0">
          <div class="bg-[#1a3a6e] py-1 text-center text-[9px] font-black tracking-wide text-white">{{ cluster.name.toUpperCase() }}</div>
          <p v-if="!cluster.loaders.length" class="py-3 text-center text-[8px] italic text-slate-400">Belum ada loader</p>
          <div v-for="(row, rowIndex) in chunkLoaders(cluster.loaders)" :key="rowIndex" class="flex border-t border-[#ccc] first:border-t-0">
            <div v-for="loader in row" :key="loader.code" class="w-[88px] shrink-0 border-r border-[#ddd] text-center text-[8px] font-bold text-[#222] last:border-r-0">
              <div class="bg-[#111] py-1 text-[9px] font-black text-white">{{ loader.code }}</div>
              <div class="min-h-[22px] bg-slate-100 px-0.5 py-0.5 text-[6.5px] font-semibold leading-tight text-slate-600">
                <div v-for="meta in loaderMeta(loader)" :key="meta" class="truncate">{{ meta }}</div>
              </div>
              <div v-for="unit in loader.units" :key="unit.code" class="border-t border-[#e5e5e5] py-0.5"><FleetUnitLabel :unit="unit" /></div>
              <p v-if="!loader.units.length" class="border-t border-[#e5e5e5] bg-[#e74c3c] py-0.5 text-[7px] font-black text-white">0 Unit</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section v-if="sheet.breakdown">
      <div class="border-y-2 border-[#333] bg-[#e74c3c] py-1 text-center text-[9px] font-black tracking-wide text-white">BREAKDOWN / SERVICE</div>
      <div class="flex flex-wrap justify-center gap-x-4 gap-y-1 px-2 py-1.5 text-[8px] font-bold text-[#222]">
        <FleetUnitLabel v-for="unit in sheet.breakdown.units" :key="unit.code" :unit="unit" />
        <span v-if="!sheet.breakdown.units.length" class="italic text-slate-400">Tidak ada unit</span>
      </div>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-2 border-t-2 border-[#333] bg-[#1a3a6e] px-2.5 py-1 text-[8px] font-black text-white">
      <span class="flex items-center gap-3">
        <span v-for="option in PARKING_OPTIONS" :key="option.code" class="flex items-center gap-1"><FleetUnitLabel :unit="{ code: '', status: option.code }" /> {{ option.label.slice(4) }}</span>
      </span>
      <span>TOTAL HAULER : <span class="ml-1.5 text-[11px]">{{ sheet.total_hauler }} Unit</span></span>
    </div>
  </div>
</template>
