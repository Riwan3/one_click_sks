<script setup>
import { computed, ref, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';

const props = defineProps({
  loader: { type: Object, default: null }, // loader tujuan pemasangan
  haulers: { type: Array, default: () => [] }, // master hauler aktif yang belum terpasang (sisa)
});

const emit = defineEmits(['close', 'pick']);

const search = ref('');

const visible = computed(() => {
  const query = search.value.toLowerCase().trim();

  return props.haulers.filter((h) => !query || `${h.code} ${h.category ?? ''}`.toLowerCase().includes(query));
});

watch(() => props.loader, () => { search.value = ''; });
</script>

<template>
  <BaseModal :open="!!loader" :title="loader ? `Pasang hauler ke ${loader.code}` : ''" size="sm" @close="emit('close')">
    <div class="space-y-2 px-5 py-4">
      <input v-model="search" type="search" placeholder="Cari kode hauler..." class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm focus:border-blue-500 focus:outline-none" />
      <div class="max-h-[45vh] divide-y divide-slate-100 overflow-y-auto rounded-lg border border-slate-200 text-sm">
        <p v-if="!visible.length" class="p-3 italic text-slate-400">Tidak ada hauler tersisa.</p>
        <button v-for="hauler in visible" :key="hauler.code" type="button" class="block w-full px-3 py-2 text-left hover:bg-blue-50" @click="emit('pick', hauler)">
          <span class="font-bold">{{ hauler.code }}</span>
          <span v-if="hauler.category" class="ml-1 text-xs text-slate-500">{{ hauler.category }}</span>
        </button>
      </div>
      <p class="text-[11px] text-slate-500">Hanya hauler aktif di Master Unit yang belum terpasang pada tanggal dan shift ini.</p>
    </div>
  </BaseModal>
</template>
