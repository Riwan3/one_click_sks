<script setup>
import { FLEET_SHIFT_LABEL, allAreaColor, formatSheetDate, materialLabel, maxUnits, sectionColor } from '@/utils/fleetBoard';
import FleetUnitLabel from './FleetUnitLabel.vue';

defineProps({
  sheet: { type: Object, required: true }, // { date, shift, main, side, total_hauler } (FleetBoardResource)
});

const SIDE_HEADER_COLORS = { 'ALL AREA': '#1a3a6e', 'BREAKDOWN / SERVICE': '#e74c3c' };
const sideHeaderColor = (name) => SIDE_HEADER_COLORS[name] ?? '#455a64';
const sideLoaderColor = (group, loader) => (group.name === 'ALL AREA' ? allAreaColor(loader.code) : '#f9a825');
const sideLoaderText = (group) => (group.name === 'ALL AREA' ? '#fff' : '#000');
const haulerCountStyle = (count) => (count === 0 ? { background: '#e74c3c', color: '#fff' } : { background: '#ffff00', color: '#222' });
const sideCountStyle = (count) => (count > 0 ? { background: '#e74c3c', color: '#fff' } : { background: '#2ecc40', color: '#000' });
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

    <div class="flex items-start border-t-2 border-[#333]">
      <div>
        <section v-for="group in sheet.main" :key="group.name" class="border-b-2 border-[#333]">
          <div class="border-b border-white/30 px-2 py-1 text-center text-[9px] font-black tracking-wider" :style="{ background: sectionColor(group.name).bg, color: sectionColor(group.name).fg }">{{ group.name.toUpperCase() }}</div>
          <table class="table-fixed border-collapse text-[7.5px] [&_td]:overflow-hidden [&_td]:whitespace-nowrap [&_td]:border [&_td]:border-[#ccc] [&_td]:px-0.5 [&_td]:py-px [&_td]:text-center [&_td]:align-middle">
            <tbody>
              <tr>
                <td class="w-[58px] min-w-[58px] bg-[#ff99cc] font-black text-[#5d0030]">DISPOSAL</td>
                <td v-for="loader in group.loaders" :key="loader.code" class="bg-[#ff99cc] px-1 font-bold text-[#5d0030]">{{ loader.area }}</td>
              </tr>
              <tr>
                <td class="bg-[#92d050] font-black text-[#1a3a00]">JAM REFUELING</td>
                <td v-for="loader in group.loaders" :key="loader.code" class="bg-[#92d050] px-1 text-[7px] font-bold text-[#1a3a00]">{{ loader.refueling }}</td>
              </tr>
              <tr>
                <td class="bg-[#111]" />
                <td v-for="loader in group.loaders" :key="loader.code" class="bg-[#111] py-1 text-[9px] font-black text-white">{{ loader.code }}</td>
              </tr>
              <tr v-for="row in maxUnits(group.loaders)" :key="row">
                <td v-if="row === 1" :rowspan="maxUnits(group.loaders)" class="bg-[#2d4a1e] text-[7px] font-black text-white [writing-mode:vertical-rl]">{{ materialLabel(group.name) }}</td>
                <td v-for="loader in group.loaders" :key="loader.code" class="w-[88px] min-w-[88px] px-1 text-[8px] font-bold text-[#222]" :class="loader.units[row - 1] ? 'bg-white' : 'bg-[#fafafa]'">
                  <FleetUnitLabel v-if="loader.units[row - 1]" :unit="loader.units[row - 1]" />
                </td>
              </tr>
              <tr>
                <td class="bg-[#6c2fa0] font-black text-white">FRONT</td>
                <td v-for="loader in group.loaders" :key="loader.code" class="bg-[#6c2fa0] px-1 text-[7px] font-bold text-white">{{ loader.front }}</td>
              </tr>
              <tr>
                <td class="bg-[#ecf0f1] font-black text-[#333]">JARAK (M)</td>
                <td v-for="loader in group.loaders" :key="loader.code" class="bg-[#ecf0f1] text-[8px] font-bold text-[#333]">{{ loader.distance > 0 ? loader.distance : '' }}</td>
              </tr>
              <tr class="text-[8px] font-black">
                <td class="!border-t-[1.5px] !border-t-[#aaa] bg-[#ffff00] text-[#222]">HAULER</td>
                <td v-for="loader in group.loaders" :key="loader.code" class="!border-t-[1.5px] !border-t-[#aaa] py-0.5" :style="haulerCountStyle(loader.units.length)">{{ loader.units.length }} Unit</td>
              </tr>
            </tbody>
          </table>
        </section>
        <div class="bg-[#1a3a6e] px-2.5 py-1 text-right text-[8px] font-black text-white">
          TOTAL HAULER : <span class="ml-1.5 text-[11px]">{{ sheet.total_hauler }} Unit</span>
        </div>
      </div>

      <aside v-if="sheet.side.length" class="flex w-[240px] min-w-[240px] shrink-0 flex-col border-l-2 border-[#333]">
        <template v-for="group in sheet.side" :key="group.name">
          <div class="px-1 py-1.5 text-center text-[9px] font-black tracking-wide text-white" :style="{ background: sideHeaderColor(group.name) }">{{ group.name.toUpperCase() }}</div>
          <div class="flex items-stretch border-b-2 border-[#333]">
            <div v-for="loader in group.loaders" :key="loader.code" class="flex min-w-0 flex-1 flex-col border-r border-[#333] last:border-r-0">
              <div class="break-words px-0.5 py-1 text-center text-[7px] font-black leading-tight" :style="{ background: sideLoaderColor(group, loader), color: sideLoaderText(group) }">{{ loader.code }}</div>
              <div class="flex-1">
                <div v-for="row in maxUnits(group.loaders)" :key="row" class="flex min-h-[18px] items-center justify-center border-b border-[#e0e0e0] px-0.5 text-[7.5px] font-bold">
                  <FleetUnitLabel v-if="loader.units[row - 1]" :unit="loader.units[row - 1]" />
                </div>
              </div>
              <div class="border-t-[1.5px] border-[#333] py-0.5 text-center text-[8px] font-black" :style="sideCountStyle(loader.units.length)">{{ loader.units.length }} Unit</div>
            </div>
          </div>
        </template>
      </aside>
    </div>
  </div>
</template>
