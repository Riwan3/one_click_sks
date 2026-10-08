<script setup>
import { ref } from 'vue';
import { DatabaseZap } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { useFleetMasterActions } from '@/composables/useFleet';
import FleetShell from './Partials/FleetShell.vue';
import MasterUnitTable from './Partials/MasterUnitTable.vue';
import MasterUnitModal from './Partials/MasterUnitModal.vue';
import PullManpowerModal from './Partials/PullManpowerModal.vue';

defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  ready: { type: Boolean, default: true },
  loadError: { type: String, default: null },
  loaders: { type: Array, default: () => [] },
  haulers: { type: Array, default: () => [] },
  groupOptions: { type: Array, default: () => [] },
  pullPreview: { type: Array, default: () => [] },
});

const actions = useFleetMasterActions();

const unitTarget = ref(null);
const isPullOpen = ref(false);

const closeUnitForm = () => { unitTarget.value = null; };

const handleSubmitUnit = (data, id) => (id ? actions.update(id, data, closeUnitForm) : actions.create(data, closeUnitForm));

const handleRemove = async (unit) => {
  if (await confirmDialog(`Hapus ${unit.code} dari Master Unit? Riwayat setting harian tidak berubah.`)) actions.remove(unit.id);
};

const handlePull = (categories) => actions.pull(categories, () => { isPullOpen.value = false; });
</script>

<template>
  <FleetShell
    :user="user"
    :notif-counts="notifCounts"
    :menu="menu"
    :ready="ready"
    :load-error="loadError"
    tab="master"
    subtitle="Master Unit: daftar loader dan hauler yang boleh dipasang di setting fleet harian."
  >
    <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
      <p class="flex-1 text-xs text-slate-500">Awal pengisian bisa ditarik dari Master CSA Setting Manpower (tambah/sinkron, tidak menimpa edit manual).</p>
      <button type="button" :disabled="!pullPreview.length" class="flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60" @click="isPullOpen = true">
        <DatabaseZap class="h-4 w-4" /> Tarik dari Master Manpower
      </button>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
      <MasterUnitTable type="loader" :units="loaders" @add="unitTarget = { type: $event }" @edit="unitTarget = { type: 'loader', unit: $event }" @toggle="(unit, isActive) => actions.setActive(unit.id, isActive)" @remove="handleRemove" />
      <MasterUnitTable type="hauler" :units="haulers" @add="unitTarget = { type: $event }" @edit="unitTarget = { type: 'hauler', unit: $event }" @toggle="(unit, isActive) => actions.setActive(unit.id, isActive)" @remove="handleRemove" />
    </div>

    <MasterUnitModal :target="unitTarget" :group-options="groupOptions" :is-processing="actions.isProcessing.value" @close="closeUnitForm" @submit="handleSubmitUnit" />
    <PullManpowerModal :open="isPullOpen" :preview="pullPreview" :is-processing="actions.isProcessing.value" @close="isPullOpen = false" @submit="handlePull" />
  </FleetShell>
</template>
