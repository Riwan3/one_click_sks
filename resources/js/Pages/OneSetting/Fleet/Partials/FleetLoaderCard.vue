<script setup>
import { Fuel, MapPin, Pencil, Plus, TriangleAlert, Trash2, X } from 'lucide-vue-next';
import { PARKING_CLASS, PARKING_LABEL } from '@/utils/fleet';

defineProps({
  loader: { type: Object, required: true },
  otherLoaders: { type: Array, default: () => [] }, // kode loader tujuan pindah hauler
});

const emit = defineEmits(['edit', 'remove', 'assign', 'move', 'release']);

const handleMove = (event, hauler) => {
  if (event.target.value) emit('move', hauler, event.target.value);
  event.target.value = '';
};
</script>

<template>
  <div class="flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex cursor-pointer items-center justify-center gap-1 border-b border-amber-100 bg-amber-50 px-3 py-1 text-[11px] font-bold text-amber-900 hover:bg-amber-100" title="Klik untuk mengisi area, front, jam refueling, jarak, dan group" @click="emit('edit', loader)">
      <MapPin class="h-3 w-3" /> {{ loader.area || 'AREA -' }}
      <span v-if="loader.distance" class="font-semibold text-amber-700">· {{ loader.distance }} m</span>
    </div>
    <div class="flex cursor-pointer items-center justify-center gap-1 border-b border-orange-100 bg-orange-50 px-3 py-1 text-[11px] font-semibold text-orange-800 hover:bg-orange-100" @click="emit('edit', loader)">
      <Fuel class="h-3 w-3" /> Refuel: {{ loader.refueling_time || '-' }}
    </div>
    <div class="flex items-center justify-between gap-2 bg-slate-800 px-3 py-2 text-white">
      <span class="flex items-center gap-1 text-xs font-black tracking-wide">
        {{ loader.front ? `${loader.code} DI ${loader.front}` : loader.code }}
        <TriangleAlert v-if="loader.has_operator === false" class="h-3.5 w-3.5 text-amber-300" title="Belum ada operator di Setting Manpower" />
      </span>
      <span class="flex gap-1">
        <button type="button" class="rounded p-1 hover:bg-slate-700" aria-label="Ubah loader" @click="emit('edit', loader)"><Pencil class="h-3.5 w-3.5" /></button>
        <button type="button" class="rounded p-1 hover:bg-red-600" aria-label="Hapus loader" @click="emit('remove', loader)"><Trash2 class="h-3.5 w-3.5" /></button>
      </span>
    </div>

    <ul class="flex-1 divide-y divide-slate-100 px-3 py-1">
      <li v-if="!loader.haulers.length" class="py-2 text-center text-[11px] italic text-slate-400">Belum ada hauler</li>
      <li v-for="hauler in loader.haulers" :key="hauler.id" class="flex items-center gap-1.5 py-1.5 text-xs">
        <span class="flex-1 font-bold text-slate-700">{{ hauler.code }}</span>
        <TriangleAlert v-if="hauler.has_operator === false" class="h-3.5 w-3.5 text-amber-500" title="Belum ada operator di Setting Manpower" />
        <span v-if="hauler.parking_code" :class="['rounded px-1.5 py-0.5 text-[10px] font-black', PARKING_CLASS[hauler.parking_code]]" :title="PARKING_LABEL[hauler.parking_code]">{{ hauler.parking_code }}</span>
        <select v-if="otherLoaders.length" class="w-16 rounded border border-slate-200 px-1 py-0.5 text-[10px] text-slate-500" aria-label="Pindah ke loader" @change="handleMove($event, hauler)">
          <option value="">Pindah</option>
          <option v-for="code in otherLoaders" :key="code" :value="code">{{ code }}</option>
        </select>
        <button type="button" class="text-slate-300 hover:text-red-600" aria-label="Lepas hauler" @click="emit('release', hauler)"><X class="h-3.5 w-3.5" /></button>
      </li>
    </ul>

    <button type="button" class="flex items-center justify-center gap-1 border-t border-slate-100 py-2 text-xs font-semibold text-blue-600 hover:bg-blue-50" @click="emit('assign', loader)">
      <Plus class="h-3.5 w-3.5" /> Pasang hauler
    </button>
  </div>
</template>
