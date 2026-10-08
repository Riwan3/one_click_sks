<script setup>
import { computed } from 'vue';
import { CircleAlert } from 'lucide-vue-next';

const props = defineProps({
  items: { type: Array, required: true }, // [{ key, label, count, items: [{ nrp, name, detail }] }]
  isApplicable: { type: Boolean, default: true },
});

const emit = defineEmits(['show-detail']);

const PREVIEW_COUNT = 5;

const total = computed(() => props.items.reduce((sum, group) => sum + group.count, 0));
const activeGroups = computed(() => props.items.filter((group) => group.count > 0));
</script>

<template>
  <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-700">
      <span class="flex items-center gap-1.5"><CircleAlert class="h-4 w-4" /> Perlu Perhatian</span>
      <span :class="['rounded-full px-2 py-0.5 text-[11px] font-bold', total ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-400']">{{ total }}</span>
    </div>
    <div class="p-4">
      <p v-if="!isApplicable" class="text-xs text-slate-500">Bagian ini menggambarkan kondisi hari ini (PLAN dekat/lewat, keanggotaan roster), sehingga hanya ditampilkan untuk tahun berjalan.</p>
      <p v-else-if="!total" class="text-xs text-slate-500">Tidak ada kondisi data yang perlu diperhatikan.</p>
      <div v-else class="grid gap-4 lg:grid-cols-2">
        <div v-for="group in activeGroups" :key="group.key">
          <p class="mb-1 text-xs font-semibold text-slate-700">{{ group.label }} <span class="ml-1 rounded-full bg-slate-200 px-1.5 text-[10px]">{{ group.count }}</span></p>
          <div v-for="item in group.items.slice(0, PREVIEW_COUNT)" :key="item.nrp" class="flex justify-between gap-2 border-b border-dashed border-slate-200 py-1.5 text-xs last:border-0">
            <button type="button" class="text-left text-blue-600 hover:underline" @click="emit('show-detail', item.nrp)">{{ item.name }} <span class="text-slate-400">({{ item.nrp }})</span></button>
            <span class="text-right text-slate-400">{{ item.detail }}</span>
          </div>
          <p v-if="group.count > PREVIEW_COUNT" class="mt-1 text-xs text-slate-400">+{{ group.count - PREVIEW_COUNT }} lainnya</p>
        </div>
      </div>
      <p class="mt-3 text-xs text-slate-400">Hanya informasi kondisi data di halaman ini — bukan notifikasi otomatis.</p>
    </div>
  </div>
</template>
