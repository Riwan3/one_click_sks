<script setup>
import { computed, ref } from 'vue';
import { Copy, Plus, Trash2, TriangleAlert } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { FLEET_URL } from '@/utils/fleet';
import { SHIFT_LABEL, formatDateLabel } from '@/utils/manpowerV201';
import { useFleetSettingActions } from '@/composables/useFleet';
import DateShiftFilter from '@/Pages/OneSetting/ManpowerV201/Partials/DateShiftFilter.vue';
import FleetShell from './Partials/FleetShell.vue';
import FleetLoaderCard from './Partials/FleetLoaderCard.vue';
import LoaderFormModal from './Partials/LoaderFormModal.vue';
import PickHaulerModal from './Partials/PickHaulerModal.vue';
import CopyFromModal from './Partials/CopyFromModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  ready: { type: Boolean, default: true },
  loadError: { type: String, default: null },
  filters: { type: Object, required: true },
  view: { type: Object, default: null },
});

const actions = useFleetSettingActions(() => props.filters);

const loaderTarget = ref(null);
const pickLoader = ref(null);
const isCopyOpen = ref(false);

const title = computed(() => `${formatDateLabel(props.filters.date)} · ${SHIFT_LABEL[props.filters.shift]}`);
const hasData = computed(() => (props.view?.groups.length ?? 0) > 0);
const allLoaderCodes = computed(() => props.view.groups.flatMap((group) => group.loaders.map((loader) => loader.code)));
const otherLoaders = (loader) => allLoaderCodes.value.filter((code) => code !== loader.code);

const BADGES = computed(() => [
  { label: `Loader ${props.view.summary.loaders}`, class: 'bg-slate-800 text-white' },
  { label: `Hauler terpasang ${props.view.summary.haulers}`, class: 'bg-emerald-600 text-white' },
  { label: `Loader tanpa hauler ${props.view.summary.loaders_without_hauler}`, class: 'bg-red-600 text-white' },
  { label: `Sisa hauler ${props.view.summary.sisa}`, class: 'bg-sky-500 text-white' },
]);

const closeLoaderForm = () => { loaderTarget.value = null; };

const handleSubmitLoader = (data, isEdit) => (isEdit ? actions.updateLoader : actions.addLoader)(data, closeLoaderForm);

const handleRemoveLoader = async (loader) => {
  if (await confirmDialog(`Hapus loader ${loader.code} beserta haulernya dari setting ini?`)) actions.removeLoader(loader.code);
};

const handlePick = (hauler) => {
  const loaderCode = pickLoader.value.code;
  pickLoader.value = null;
  actions.assignHauler(loaderCode, hauler.code);
};

const handleCopy = (source) => actions.copy(source, hasData.value, () => { isCopyOpen.value = false; });

const handleClear = async () => {
  if (await confirmDialog('Hapus semua setting fleet pada tanggal dan shift ini?')) actions.clear();
};
</script>

<template>
  <FleetShell
    :user="user"
    :notif-counts="notifCounts"
    :menu="menu"
    :ready="ready"
    :load-error="loadError"
    :filters="filters"
    tab="harian"
    subtitle="Setting fleet harian per tanggal dan shift: group, loader, hauler, dan status unit."
  >
    <DateShiftFilter :url="FLEET_URL" :filters="filters" />

    <template v-if="view">
      <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
          <h2 class="text-base font-bold text-slate-800">{{ title }}</h2>
          <div class="flex flex-wrap gap-2 sm:ml-auto">
            <button type="button" class="flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700" @click="loaderTarget = { mode: 'add' }"><Plus class="h-3.5 w-3.5" /> Tambah loader</button>
            <button type="button" class="flex items-center gap-1 rounded-lg border border-blue-600 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50" @click="isCopyOpen = true"><Copy class="h-3.5 w-3.5" /> Salin dari...</button>
            <button v-if="hasData" type="button" class="flex items-center gap-1 rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50" @click="handleClear"><Trash2 class="h-3.5 w-3.5" /> Hapus semua</button>
          </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
          <span v-for="badge in BADGES" :key="badge.label" :class="['rounded-full px-3 py-1 text-xs font-bold', badge.class]">{{ badge.label }}</span>
        </div>
      </div>

      <div v-if="view.warnings.length" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-800">
        <p class="mb-1 flex items-center gap-1 font-bold"><TriangleAlert class="h-3.5 w-3.5" /> Perlu diperhatikan</p>
        <ul class="list-disc pl-5"><li v-for="warning in view.warnings" :key="warning">{{ warning }}</li></ul>
      </div>

      <p v-if="!hasData" class="rounded-2xl border border-slate-200/80 bg-white px-4 py-12 text-center text-sm text-slate-500 shadow-sm">
        Belum ada setting untuk {{ title }}. Tambah loader atau salin dari tanggal / shift lain.
      </p>

      <section v-for="group in view.groups" :key="group.name" class="space-y-2">
        <h3 class="rounded-lg bg-slate-700 px-3 py-1.5 text-xs font-extrabold tracking-wide text-white">{{ group.name }} <span class="font-semibold text-slate-300">· {{ group.loaders.length }} loader</span></h3>
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
          <FleetLoaderCard
            v-for="loader in group.loaders"
            :key="loader.code"
            :loader="loader"
            :other-loaders="otherLoaders(loader)"
            @edit="loaderTarget = { mode: 'edit', loader: $event }"
            @remove="handleRemoveLoader"
            @assign="pickLoader = $event"
            @status="(hauler, status) => actions.setStatus(hauler.code, status)"
            @move="(hauler, code) => actions.moveHauler(hauler.code, code)"
            @release="(hauler) => actions.removeHauler(hauler.code)"
          />
        </div>
      </section>

      <LoaderFormModal :target="loaderTarget" :available-loaders="view.available_loaders" :group-options="view.group_options" :is-processing="actions.isProcessing.value" @close="closeLoaderForm" @submit="handleSubmitLoader" />
      <PickHaulerModal :loader="pickLoader" :haulers="view.available_haulers" @close="pickLoader = null" @pick="handlePick" />
      <CopyFromModal :open="isCopyOpen" :filters="filters" :has-data="hasData" :is-processing="actions.isProcessing.value" @close="isCopyOpen = false" @submit="handleCopy" />
    </template>
  </FleetShell>
</template>
