<script setup>
import { MapPin, Pencil, Trash2, Users, X } from 'lucide-vue-next';

defineProps({
  details: { type: Array, required: true },
  startNumber: { type: Number, default: 0 },
  canEdit: { type: Boolean, default: false },
});

const emit = defineEmits(['edit', 'allocate', 'delete', 'remove-allocation']);

const ICON_BUTTON = 'rounded-lg border px-1.5 py-1';
const sectionsOf = (detail) => detail.allocations.map((a) => a.section).filter(Boolean).join(', ');
</script>

<template>
  <div v-if="!details.length" class="px-4 py-12 text-center text-slate-500">
    <Users class="mx-auto h-10 w-10 text-slate-300" />
    <p class="mt-2 text-sm">Belum ada detail pada roster ini.</p>
  </div>
  <div v-else class="overflow-x-auto">
    <table class="w-full min-w-[860px] text-sm">
      <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-600">
        <tr>
          <th class="px-3 py-2.5">No</th><th class="px-3 py-2.5">NRP</th><th class="px-3 py-2.5">Nama</th><th class="px-3 py-2.5">Jabatan</th><th class="px-3 py-2.5">Tanggal</th>
          <th class="px-3 py-2.5">Shift</th><th class="px-3 py-2.5">Unit</th><th class="px-3 py-2.5">Section</th><th class="px-3 py-2.5 text-right">Action</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <tr v-for="(detail, index) in details" :key="detail.id" class="hover:bg-slate-50">
          <td class="px-3 py-2">{{ startNumber + index + 1 }}</td>
          <td class="whitespace-nowrap px-3 py-2">{{ detail.nrp }}</td>
          <td class="px-3 py-2">{{ detail.employee_name }}</td>
          <td class="px-3 py-2">{{ detail.jabatan }}</td>
          <td class="whitespace-nowrap px-3 py-2">{{ detail.work_date }}</td>
          <td class="px-3 py-2"><span class="rounded-md bg-blue-600 px-2 py-0.5 text-[11px] font-semibold text-white" :title="detail.shift_label">{{ detail.shift_code }}</span></td>
          <td class="px-3 py-2">
            <span v-for="allocation in detail.allocations" :key="`${allocation.unit}|${allocation.section}`" class="mr-1 inline-flex items-center gap-1 rounded-lg bg-blue-50 py-0.5 pl-2 pr-1 text-xs text-blue-800">
              {{ allocation.unit }}
              <button v-if="canEdit" type="button" title="Hapus alokasi" class="px-0.5 leading-none" @click="emit('remove-allocation', detail, allocation)"><X class="h-3 w-3" /></button>
            </span>
          </td>
          <td class="px-3 py-2">{{ sectionsOf(detail) }}</td>
          <td class="whitespace-nowrap px-3 py-2 text-right">
            <div v-if="canEdit" class="inline-flex gap-1.5">
              <button type="button" title="Edit detail" :class="[ICON_BUTTON, 'border-slate-300 text-slate-600 hover:bg-slate-50']" @click="emit('edit', detail)"><Pencil class="h-3.5 w-3.5" /></button>
              <button type="button" title="Tambah alokasi" :class="[ICON_BUTTON, 'border-blue-300 text-blue-600 hover:bg-blue-50']" @click="emit('allocate', detail)"><MapPin class="h-3.5 w-3.5" /></button>
              <button type="button" title="Hapus" :class="[ICON_BUTTON, 'border-red-300 text-red-600 hover:bg-red-50']" @click="emit('delete', detail)"><Trash2 class="h-3.5 w-3.5" /></button>
            </div>
            <span v-else class="text-xs text-slate-400">-</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
