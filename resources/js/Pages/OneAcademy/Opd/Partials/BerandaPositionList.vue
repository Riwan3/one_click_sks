<script setup>
import { Network } from 'lucide-vue-next';

defineProps({
  positions: { type: Array, required: true },
  total: { type: Number, required: true },
  selected: { type: String, required: true },
});

const emit = defineEmits(['select']);

const widthOf = (count, total) => `${total > 0 ? (count * 100) / total : 0}%`;
</script>

<template>
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="mb-2 flex items-center gap-2 text-sm font-bold text-slate-800"><Network class="h-4 w-4" /> Per Posisi / Jabatan</div>
    <div class="max-h-[60vh] space-y-1.5 overflow-y-auto pr-1">
      <button
        type="button"
        :class="['flex w-full justify-between rounded-lg border px-3 py-2 text-left text-xs font-semibold', selected === '' ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:bg-slate-50']"
        @click="emit('select', '')"
      >
        <span>Semua posisi</span><span>{{ total }}</span>
      </button>
      <button
        v-for="p in positions"
        :key="p.name"
        type="button"
        :class="['w-full rounded-lg border px-3 py-2 text-left', selected === p.name ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:bg-slate-50']"
        @click="emit('select', p.name)"
      >
        <div class="flex justify-between text-xs font-semibold text-slate-800"><span>{{ p.name }}</span><span>{{ p.total }}</span></div>
        <div class="mt-0.5 text-[11px]">
          <span v-if="p.under" class="font-semibold text-red-600">{{ p.under }} under</span>
          <span v-if="p.under && p.high" class="text-slate-400"> · </span>
          <span v-if="p.high" class="font-semibold text-amber-600">{{ p.high }} di atas max</span>
          <span v-if="!p.under && !p.high" class="text-slate-400">tidak ada temuan</span>
        </div>
        <div class="mt-1 flex h-1 overflow-hidden rounded bg-green-500">
          <i class="bg-red-600" :style="{ width: widthOf(p.under, p.total) }" />
          <i class="bg-amber-500" :style="{ width: widthOf(p.high, p.total) }" />
        </div>
      </button>
    </div>
  </div>
</template>
