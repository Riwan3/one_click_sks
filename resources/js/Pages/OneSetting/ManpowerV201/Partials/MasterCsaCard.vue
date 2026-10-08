<script setup>
import { computed, reactive } from 'vue';
import { ArrowDown, ArrowUp, Lock, LockOpen, Pencil, Plus, Trash2 } from 'lucide-vue-next';
import { useMasterActions } from '@/composables/useManpowerV201';
import { confirmDialog } from '@/utils/confirm';
import { splitColumns } from '@/utils/manpowerV201';
import MasterCategoryCard from './MasterCategoryCard.vue';

const props = defineProps({
  csa: { type: Object, required: true },
});

const emit = defineEmits(['edit']);

const COLUMN_TITLE = { 1: 'KOLOM KIRI', 2: 'KOLOM KANAN' };

const actions = useMasterActions();
const form = reactive({ name: '', colPos: 1 });

const columns = computed(() => splitColumns(props.csa.categories));
const stats = computed(() => {
  const units = props.csa.categories.flatMap((c) => c.units);

  return {
    unit: units.filter((u) => !u.is_spare_slot).length,
    spare: units.filter((u) => u.is_spare_slot).length,
    risk: units.filter((u) => u.is_high_risk).length,
  };
});

const siblingsOf = (cat) => props.csa.categories.filter((c) => c.id !== cat.id);

const handleAddCategory = () => actions.addCategory(props.csa.id, form.name, Number(form.colPos), () => { form.name = ''; });

const handleDelete = async () => {
  if (await confirmDialog(`Hapus ${props.csa.name} beserta seluruh kategori dan unitnya?`)) actions.deleteCsa(props.csa.id);
};

const headerButton = 'rounded border border-white/40 p-1 text-white hover:bg-white/10';
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex flex-wrap items-center gap-2 bg-slate-800 px-4 py-2 text-white">
      <span class="text-sm font-bold">{{ csa.name }}</span>
      <span v-if="csa.is_locked" class="flex items-center gap-1 rounded bg-amber-400 px-1.5 py-0.5 text-[10px] font-bold text-slate-900"><Lock class="h-3 w-3" /> Dikunci</span>
      <span class="text-xs opacity-75">{{ stats.unit }} unit · {{ stats.spare }} slot SPARE · {{ stats.risk }} high risk</span>

      <div class="flex gap-1 sm:ml-auto">
        <template v-if="!csa.is_locked">
          <button type="button" :class="headerButton" title="Naik" @click="actions.moveCsa(csa.id, 'up')"><ArrowUp class="h-3.5 w-3.5" /></button>
          <button type="button" :class="headerButton" title="Turun" @click="actions.moveCsa(csa.id, 'down')"><ArrowDown class="h-3.5 w-3.5" /></button>
          <button type="button" :class="headerButton" title="Ubah nama" @click="emit('edit', { kind: 'csa', id: csa.id, name: csa.name })"><Pencil class="h-3.5 w-3.5" /></button>
        </template>
        <button type="button" :class="csa.is_locked ? 'rounded border border-amber-400 bg-amber-400 p-1 text-slate-900' : headerButton" :title="csa.is_locked ? 'Buka kunci' : 'Kunci CSA'" @click="actions.lockCsa(csa.id, !csa.is_locked)">
          <LockOpen v-if="csa.is_locked" class="h-3.5 w-3.5" />
          <Lock v-else class="h-3.5 w-3.5" />
        </button>
        <button v-if="!csa.is_locked" type="button" class="rounded border border-red-400 p-1 text-red-300 hover:bg-red-500/20" title="Hapus CSA" @click="handleDelete"><Trash2 class="h-3.5 w-3.5" /></button>
      </div>
    </div>

    <div class="space-y-4 p-4">
      <div class="grid gap-3 lg:grid-cols-2">
        <div v-for="pos in [1, 2]" :key="pos" class="space-y-3">
          <p class="text-xs font-bold text-slate-500">{{ COLUMN_TITLE[pos] }}</p>
          <p v-if="!columns[pos].length" class="text-xs italic text-slate-400">Belum ada kategori.</p>
          <MasterCategoryCard v-for="cat in columns[pos]" :key="cat.id" :category="cat" :siblings="siblingsOf(cat)" :is-locked="csa.is_locked" @edit="emit('edit', $event)" />
        </div>
      </div>

      <form v-if="!csa.is_locked" class="grid gap-2 border-t border-slate-200 pt-3 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_auto]" @submit.prevent="handleAddCategory">
        <label class="block">
          <span class="mb-1 block text-xs font-semibold text-slate-600">TAMBAH KATEGORI ALAT</span>
          <input v-model="form.name" required maxlength="120" placeholder="mis. HEAVY DUMP TRUCK - KOMATSU 785" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm uppercase focus:border-blue-500 focus:outline-none">
        </label>
        <select v-model="form.colPos" class="self-end rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-sm">
          <option :value="1">Kolom kiri</option>
          <option :value="2">Kolom kanan</option>
        </select>
        <button type="submit" :disabled="actions.isProcessing.value" class="flex items-center justify-center gap-1 self-end rounded-lg bg-slate-800 px-3 py-1.5 text-sm font-semibold text-white hover:bg-slate-900 disabled:opacity-60"><Plus class="h-4 w-4" /> Tambah kategori</button>
      </form>
    </div>
  </div>
</template>
