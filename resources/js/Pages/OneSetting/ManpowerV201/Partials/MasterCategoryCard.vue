<script setup>
import { reactive } from 'vue';
import { ArrowDown, ArrowUp, Pencil, Plus, Trash2, TriangleAlert, X } from 'lucide-vue-next';
import { useMasterActions } from '@/composables/useManpowerV201';
import { confirmDialog } from '@/utils/confirm';

const props = defineProps({
  category: { type: Object, required: true },
  siblings: { type: Array, default: () => [] }, // kategori lain dalam CSA yang sama (tujuan pindah unit)
  isLocked: { type: Boolean, default: false },
});

const emit = defineEmits(['edit']);

const actions = useMasterActions();
const form = reactive({ units: '', highRisk: false });

const handleAddUnits = () => actions.addUnits(props.category.id, form.units, form.highRisk, () => { form.units = ''; form.highRisk = false; });

const handleDeleteCategory = async () => {
  if (await confirmDialog(`Hapus kategori ${props.category.name}?`)) actions.deleteCategory(props.category.id);
};

const handleDeleteUnit = async (unit) => {
  if (await confirmDialog(`Hapus unit ${unit.unit}?`)) actions.deleteUnit(unit.id);
};

const handleMoveToCategory = (unit, event) => {
  const categoryId = event.target.value;
  event.target.value = '';
  if (categoryId) actions.moveUnitToCategory(unit.id, categoryId);
};

const iconButton = 'rounded border p-1 hover:bg-slate-50';
</script>

<template>
  <div class="overflow-hidden rounded-xl border border-slate-200">
    <div class="flex items-center gap-1 bg-slate-100 px-2 py-1.5">
      <span class="text-xs font-bold text-slate-800">{{ category.name }}</span>
      <span class="rounded bg-slate-500 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ category.units.length }}</span>
      <div v-if="!isLocked" class="ml-auto flex gap-1">
        <button type="button" :class="[iconButton, 'border-slate-300 text-slate-600']" title="Naik" @click="actions.moveCategory(category.id, 'up')"><ArrowUp class="h-3 w-3" /></button>
        <button type="button" :class="[iconButton, 'border-slate-300 text-slate-600']" title="Turun" @click="actions.moveCategory(category.id, 'down')"><ArrowDown class="h-3 w-3" /></button>
        <button type="button" :class="[iconButton, 'border-slate-300 text-slate-600']" title="Ubah nama / kolom" @click="emit('edit', { kind: 'category', id: category.id, name: category.name, col_pos: category.col_pos })"><Pencil class="h-3 w-3" /></button>
        <button type="button" :class="[iconButton, 'border-red-300 text-red-600']" title="Hapus kategori" @click="handleDeleteCategory"><Trash2 class="h-3 w-3" /></button>
      </div>
    </div>

    <div class="divide-y divide-slate-100">
      <div v-for="u in category.units" :key="u.id" class="flex flex-wrap items-center gap-1.5 px-2 py-1">
        <span :class="['min-w-[7.5rem] text-sm font-semibold', u.is_high_risk ? 'text-red-600' : '']">{{ u.unit }}</span>
        <span v-if="u.is_spare_slot" class="rounded bg-emerald-600 px-1.5 py-0.5 text-[10px] font-bold text-white">SPARE</span>
        <span v-if="u.is_high_risk" class="rounded bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white">HIGH RISK</span>

        <div v-if="!isLocked" class="ml-auto flex items-center gap-1">
          <button v-if="!u.is_spare_slot" type="button" :class="[iconButton, u.is_high_risk ? 'border-red-600 bg-red-600 text-white hover:bg-red-700' : 'border-red-300 text-red-600']" :title="u.is_high_risk ? 'Cabut tanda high risk' : 'Tandai high risk'" @click="actions.setUnitRisk(u.id, !u.is_high_risk)"><TriangleAlert class="h-3 w-3" /></button>
          <button type="button" :class="[iconButton, 'border-slate-300 text-slate-600']" title="Naik" @click="actions.moveUnit(u.id, 'up')"><ArrowUp class="h-3 w-3" /></button>
          <button type="button" :class="[iconButton, 'border-slate-300 text-slate-600']" title="Turun" @click="actions.moveUnit(u.id, 'down')"><ArrowDown class="h-3 w-3" /></button>
          <button type="button" :class="[iconButton, 'border-slate-300 text-slate-600']" title="Ubah kode" @click="emit('edit', { kind: 'unit', id: u.id, name: u.unit })"><Pencil class="h-3 w-3" /></button>
          <select class="w-36 rounded border border-slate-300 bg-white px-1 py-0.5 text-[11px]" title="Pindah ke kategori lain" @change="handleMoveToCategory(u, $event)">
            <option value="">Pindah ke...</option>
            <option v-for="other in siblings" :key="other.id" :value="other.id">{{ other.name }}</option>
          </select>
          <button type="button" :class="[iconButton, 'border-red-300 text-red-600']" title="Hapus" @click="handleDeleteUnit(u)"><X class="h-3 w-3" /></button>
        </div>
      </div>
      <p v-if="!category.units.length" class="px-2 py-2 text-xs italic text-slate-400">Belum ada unit.</p>
    </div>

    <form v-if="!isLocked" class="space-y-1 border-t border-slate-200 p-2" @submit.prevent="handleAddUnits">
      <textarea v-model="form.units" rows="1" required class="w-full rounded-lg border border-slate-300 px-2 py-1 text-xs uppercase focus:border-blue-500 focus:outline-none" placeholder="Tambah unit: pisahkan dengan koma atau baris baru. Slot: SPARE 785A" />
      <div class="flex items-center gap-3">
        <label class="flex items-center gap-1 text-xs"><input v-model="form.highRisk" type="checkbox" class="rounded border-slate-300"> High risk</label>
        <button type="submit" :disabled="actions.isProcessing.value" class="ml-auto flex items-center gap-1 rounded-lg border border-slate-700 px-2.5 py-1 text-xs font-semibold hover:bg-slate-50 disabled:opacity-60"><Plus class="h-3 w-3" /> Tambah</button>
      </div>
    </form>
  </div>
</template>
