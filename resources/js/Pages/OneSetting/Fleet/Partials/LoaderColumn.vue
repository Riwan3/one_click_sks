<script setup>
import { computed } from 'vue';
import { ArrowRightLeft, Pencil, Plus, Trash2, TriangleAlert, Wrench, X } from 'lucide-vue-next';
import { PARKING_CLASS, PARKING_LABEL } from '@/utils/fleet';

const props = defineProps({
  loader: { type: Object, required: true },
  otherLoaders: { type: Array, default: () => [] }, // kode loader tujuan pindah hauler
});

const emit = defineEmits(['edit', 'remove', 'assign', 'move', 'release', 'breakdown']);

const meta = computed(() => [
  props.loader.area,
  props.loader.front,
  props.loader.refueling_time ? `Refuel ${props.loader.refueling_time}` : null,
  props.loader.distance ? `${props.loader.distance} m` : null,
].filter(Boolean).join(' · '));

const handleMove = (event, hauler) => {
  if (event.target.value) emit('move', hauler, event.target.value);
  event.target.value = '';
};
</script>

<template>
  <div class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="bg-slate-800 px-2 py-1.5 text-white">
      <div class="flex items-center justify-between gap-1">
        <span class="flex min-w-0 items-center gap-1 truncate text-xs font-black tracking-wide">
          {{ loader.code }}
          <TriangleAlert v-if="loader.has_operator === false" class="h-3 w-3 shrink-0 text-amber-300" title="Belum ada operator di Setting Manpower" />
        </span>
        <span class="flex shrink-0">
          <button type="button" class="rounded p-0.5 hover:bg-slate-700" aria-label="Ubah loader" @click="emit('edit', loader)"><Pencil class="h-3 w-3" /></button>
          <button type="button" class="rounded p-0.5 hover:bg-rose-600" title="Breakdown / Service" aria-label="Breakdown / Service" @click="emit('breakdown', loader)"><Wrench class="h-3 w-3" /></button>
          <button type="button" class="rounded p-0.5 hover:bg-red-600" aria-label="Hapus loader" @click="emit('remove', loader)"><Trash2 class="h-3 w-3" /></button>
        </span>
      </div>
      <button type="button" class="mt-0.5 block w-full text-left text-[10px] leading-tight text-slate-300 hover:text-white" title="Klik untuk mengisi dumping point, front, jam refueling, dan jarak" @click="emit('edit', loader)">
        {{ meta || 'Isi dumping point / front' }}
      </button>
    </div>

    <ul class="flex-1 divide-y divide-slate-100 px-2 py-0.5">
      <li v-if="!loader.haulers.length" class="py-2 text-center text-[10px] italic text-slate-400">Belum ada hauler</li>
      <li v-for="hauler in loader.haulers" :key="hauler.id" class="flex items-center gap-1 py-1 text-xs">
        <span class="min-w-0 flex-1 truncate font-bold text-slate-700">{{ hauler.code }}</span>
        <TriangleAlert v-if="hauler.has_operator === false" class="h-3 w-3 shrink-0 text-amber-500" title="Belum ada operator di Setting Manpower" />
        <span v-if="hauler.parking_code" :class="['rounded px-1 text-[9px] font-black', PARKING_CLASS[hauler.parking_code]]" :title="PARKING_LABEL[hauler.parking_code]">{{ hauler.parking_code }}</span>
        <span v-if="otherLoaders.length" class="relative h-4 w-4 shrink-0 text-slate-400 hover:text-blue-600" title="Pindah ke loader lain">
          <ArrowRightLeft class="h-3.5 w-3.5" />
          <select class="absolute inset-0 h-full w-full cursor-pointer opacity-0" aria-label="Pindah ke loader" @change="handleMove($event, hauler)">
            <option value="">Pindah ke...</option>
            <option v-for="code in otherLoaders" :key="code" :value="code">{{ code }}</option>
          </select>
        </span>
        <button type="button" class="shrink-0 text-slate-400 hover:text-rose-600" title="Breakdown / Service" aria-label="Breakdown / Service" @click="emit('breakdown', hauler)"><Wrench class="h-3.5 w-3.5" /></button>
        <button type="button" class="shrink-0 text-slate-300 hover:text-red-600" title="Lepas ke Ready" aria-label="Lepas hauler" @click="emit('release', hauler)"><X class="h-3.5 w-3.5" /></button>
      </li>
    </ul>

    <button type="button" class="flex items-center justify-center gap-1 border-t border-slate-100 py-1.5 text-[11px] font-semibold text-blue-600 hover:bg-blue-50" @click="emit('assign', loader)">
      <Plus class="h-3 w-3" /> Pasang hauler
    </button>
  </div>
</template>
