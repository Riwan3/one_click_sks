<script setup>
import { reactive, ref } from 'vue';
import { Plus } from 'lucide-vue-next';
import { useMasterActions } from '@/composables/useManpowerV201';
import ManpowerShell from './Partials/ManpowerShell.vue';
import MasterCsaCard from './Partials/MasterCsaCard.vue';
import MasterEditModal from './Partials/MasterEditModal.vue';

defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  ready: { type: Boolean, default: true },
  loadError: { type: String, default: null },
  tree: { type: Array, default: () => [] },
});

const actions = useMasterActions();
const newCsa = reactive({ name: '' });
const editTarget = ref(null);

const handleAddCsa = () => actions.addCsa(newCsa.name, () => { newCsa.name = ''; });

const closeEdit = () => { editTarget.value = null; };

const SAVE_EDIT = {
  csa: (t) => actions.renameCsa(t.id, t.name, closeEdit),
  category: (t) => actions.updateCategory(t.id, t.name, t.col_pos, closeEdit),
  unit: (t) => actions.renameUnit(t.id, t.name, closeEdit),
};

const handleSaveEdit = (target) => SAVE_EDIT[target.kind](target);
</script>

<template>
  <ManpowerShell
    :user="user"
    :notif-counts="notifCounts"
    :menu="menu"
    :ready="ready"
    :load-error="loadError"
    tab="master"
    subtitle="Master CSA: daftar unit per CSA dan kategori alat. Diisi sekali, diubah hanya bila ada perubahan."
  >
    <form class="flex flex-wrap items-end gap-2 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm" @submit.prevent="handleAddCsa">
      <label class="block w-full md:w-96">
        <span class="mb-1 block text-xs font-semibold text-slate-600">TAMBAH CSA</span>
        <input v-model="newCsa.name" required maxlength="100" placeholder="mis. CSA MONTE BARU" class="w-full rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm uppercase focus:border-blue-500 focus:outline-none">
      </label>
      <button type="submit" :disabled="actions.isProcessing.value" class="flex items-center gap-1 rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900 disabled:opacity-60"><Plus class="h-4 w-4" /> Tambah CSA</button>
    </form>

    <p v-if="!tree.length" class="rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-700">Belum ada CSA. Tambahkan CSA di atas.</p>

    <MasterCsaCard v-for="csa in tree" :key="csa.id" :csa="csa" @edit="editTarget = $event" />

    <MasterEditModal :target="editTarget" :is-processing="actions.isProcessing.value" @close="closeEdit" @submit="handleSaveEdit" />
  </ManpowerShell>
</template>
