<script setup>
import { LogIn, X } from 'lucide-vue-next';

defineProps({
  sisa: { type: Array, default: () => [] },
  gl: { type: Array, default: () => [] },
  lain: { type: Array, default: () => [] },
  isEditable: { type: Boolean, default: false },
});

const emit = defineEmits(['open-lain', 'remove-lain']);

const GROUP_LABEL = { master: 'MASTER', batangan: 'BATANGAN (DILEPAS / BELUM TERPASANG)', unknown: 'UNIT BELUM ADA DI MASTER CSA' };

const groupKey = (p) => `${p.kind}|${p.home_label}`;
const isGroupStart = (list, index) => index === 0 || groupKey(list[index - 1]) !== groupKey(list[index]);
</script>

<template>
  <div class="space-y-3">
    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
      <h3 class="bg-sky-50 px-3 py-2 text-xs font-bold text-slate-700">DAFTAR SISA <span class="ml-1 rounded bg-slate-500 px-1.5 py-0.5 text-[10px] text-white">{{ sisa.length }}</span></h3>
      <div class="max-h-[60vh] overflow-y-auto">
        <template v-for="(p, i) in sisa" :key="p.nrp">
          <div v-if="isGroupStart(sisa, i)" class="border-t border-slate-100 px-3 pb-1 pt-2 text-[11px] font-bold text-slate-500">{{ GROUP_LABEL[p.kind] ?? p.kind }} · {{ p.home_label }}</div>
          <div class="flex items-center gap-1 px-3 py-1 text-xs">
            <span>{{ p.name }} <span class="text-slate-400">{{ p.nrp }}</span></span>
            <span class="ml-auto flex items-center gap-1">
              <span v-if="p.kind === 'batangan'" class="rounded border border-slate-300 bg-slate-50 px-1.5 py-0.5 text-[10px]">{{ p.unit }}</span>
              <button v-if="isEditable" type="button" class="rounded border border-slate-300 p-1 text-slate-600 hover:bg-slate-50" title="Pindahkan ke Lain-lain" @click="emit('open-lain', p.nrp)"><LogIn class="h-3 w-3" /></button>
            </span>
          </div>
        </template>
        <p v-if="!sisa.length" class="px-3 py-2 text-xs italic text-slate-400">Tidak ada sisa.</p>
      </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
      <h3 class="bg-amber-50 px-3 py-2 text-xs font-bold text-slate-700">GROUP LEADER <span class="ml-1 rounded bg-slate-500 px-1.5 py-0.5 text-[10px] text-white">{{ gl.length }}</span></h3>
      <ul class="divide-y divide-slate-100 text-xs">
        <li v-for="g in gl" :key="g.nrp" class="px-3 py-1">
          <div class="font-bold">{{ g.name }}</div>
          <div class="text-slate-500">{{ [g.nrp, g.unit, g.phone].filter(Boolean).join(' · ') }}</div>
        </li>
        <li v-if="!gl.length" class="px-3 py-2 italic text-slate-400">Belum ada GL hadir di roster.</li>
      </ul>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
      <h3 class="flex items-center gap-2 bg-amber-50 px-3 py-2 text-xs font-bold text-slate-700">
        <span>LAIN - LAIN <span class="ml-1 rounded bg-slate-500 px-1.5 py-0.5 text-[10px] text-white">{{ lain.length }}</span></span>
        <button v-if="isEditable" type="button" class="ml-auto flex items-center gap-1 rounded border border-slate-700 px-2 py-0.5 text-[11px] hover:bg-white" title="Pindahkan orang dari Daftar Sisa ke Lain-lain" @click="emit('open-lain', null)">
          <LogIn class="h-3 w-3" /> Pindahkan
        </button>
      </h3>
      <ul class="divide-y divide-slate-100 text-xs">
        <li v-for="o in lain" :key="o.nrp" class="flex items-start gap-2 px-3 py-1">
          <div>
            <div class="font-bold">{{ o.name }}</div>
            <div class="text-slate-500">
              {{ o.nrp }} · <span class="rounded border border-slate-300 bg-slate-50 px-1.5 py-0.5 text-[10px] text-slate-700">{{ o.label }}</span>
              <span v-if="o.source === 'manual'" class="ml-1 rounded border border-slate-300 bg-slate-50 px-1.5 py-0.5 text-[10px] text-slate-700">manual</span>
              <span v-if="o.absent" class="ml-1 rounded bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white" title="Tidak hadir menurut Roster V2 saat ini">tidak hadir</span>
            </div>
          </div>
          <button v-if="isEditable" type="button" class="ml-auto rounded border border-red-300 p-1 text-red-600 hover:bg-red-50" title="Hapus dari Lain-lain" @click="emit('remove-lain', o)"><X class="h-3 w-3" /></button>
        </li>
        <li v-if="!lain.length" class="px-3 py-2 italic text-slate-400">Tidak ada orang di Lain-lain.</li>
      </ul>
    </section>
  </div>
</template>
