<script setup>
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { UNIT_TYPE_LABEL } from '@/utils/fleet';

const props = defineProps({
  type: { type: String, required: true }, // loader | hauler
  units: { type: Array, default: () => [] },
});

const emit = defineEmits(['add', 'edit', 'toggle', 'remove']);

const isLoader = props.type === 'loader';
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
      <h2 class="text-sm font-bold text-slate-800">{{ UNIT_TYPE_LABEL[type] }} <span class="font-semibold text-slate-400">({{ units.length }})</span></h2>
      <button type="button" class="flex items-center gap-1 rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-900" @click="emit('add', type)"><Plus class="h-3.5 w-3.5" /> Tambah</button>
    </div>

    <p v-if="!units.length" class="px-4 py-6 text-center text-sm italic text-slate-400">Belum ada {{ UNIT_TYPE_LABEL[type].toLowerCase() }}. Tambah manual atau tarik dari Master Manpower.</p>

    <div v-else class="max-h-[60vh] overflow-auto">
      <table class="w-full text-left text-xs">
        <thead class="sticky top-0 bg-slate-50 text-[11px] uppercase text-slate-500">
          <tr>
            <th class="px-3 py-2">Kode</th>
            <th class="px-3 py-2">Kategori</th>
            <th v-if="isLoader" class="hidden px-3 py-2 md:table-cell">Bawaan (group / area / front)</th>
            <th class="px-3 py-2">Aktif</th>
            <th class="px-3 py-2" />
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="unit in units" :key="unit.id" :class="unit.is_active ? '' : 'bg-slate-50 text-slate-400'">
            <td class="px-3 py-2 font-bold">{{ unit.code }}</td>
            <td class="px-3 py-2">{{ unit.category || '-' }}</td>
            <td v-if="isLoader" class="hidden px-3 py-2 md:table-cell">{{ [unit.default_group, unit.default_area, unit.default_front].filter(Boolean).join(' / ') || '-' }}</td>
            <td class="px-3 py-2">
              <input type="checkbox" :checked="unit.is_active" :aria-label="`Aktif ${unit.code}`" @change="emit('toggle', unit, $event.target.checked)" />
            </td>
            <td class="whitespace-nowrap px-3 py-2 text-right">
              <button type="button" class="p-1 text-slate-500 hover:text-blue-600" aria-label="Ubah" @click="emit('edit', unit)"><Pencil class="h-3.5 w-3.5" /></button>
              <button type="button" class="p-1 text-slate-500 hover:text-red-600" aria-label="Hapus" @click="emit('remove', unit)"><Trash2 class="h-3.5 w-3.5" /></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
