<script setup>
import { computed, ref } from 'vue';
import { Copy, Plus, Trash2, TriangleAlert } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { FLEET_URL } from '@/utils/fleet';
import { SHIFT_LABEL, formatDateLabel } from '@/utils/manpowerV201';
import { useFleetSettingActions } from '@/composables/useFleet';
import DateShiftFilter from '@/Pages/OneSetting/ManpowerV201/Partials/DateShiftFilter.vue';
import FleetShell from './Partials/FleetShell.vue';
import AreaBand from './Partials/AreaBand.vue';
import LoaderColumn from './Partials/LoaderColumn.vue';
import AreaFormModal from './Partials/AreaFormModal.vue';
import ClusterFormModal from './Partials/ClusterFormModal.vue';
import ClusterSection from './Partials/ClusterSection.vue';
import LoaderFormModal from './Partials/LoaderFormModal.vue';
import PickHaulerModal from './Partials/PickHaulerModal.vue';
import CopyFromModal from './Partials/CopyFromModal.vue';
import FleetSidebar from './Partials/FleetSidebar.vue';

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

const areaTarget = ref(null);
const clusterTarget = ref(null);
const loaderTarget = ref(null);
const pickLoader = ref(null);
const isCopyOpen = ref(false);

const title = computed(() => `${formatDateLabel(props.filters.date)} · ${SHIFT_LABEL[props.filters.shift]}`);
const allClusters = computed(() => props.view.areas.flatMap((area) => area.clusters));
const hasData = computed(() => (props.view?.areas.length ?? 0) + (props.view?.breakdown.length ?? 0) > 0);
const areaNames = computed(() => props.view.areas.filter((area) => area.is_editable).map((area) => area.name));
const allLoaderCodes = computed(() => allClusters.value.flatMap((cluster) => cluster.loaders.map((loader) => loader.code)));
const otherLoaders = (loader) => allLoaderCodes.value.filter((code) => code !== loader.code);

const BADGES = computed(() => [
  { label: `Area ${props.view.summary.areas}`, class: 'bg-violet-600 text-white' },
  { label: `Cluster ${props.view.summary.clusters}`, class: 'bg-indigo-600 text-white' },
  { label: `Loader ${props.view.summary.loaders}`, class: 'bg-slate-800 text-white' },
  { label: `Hauler terpasang ${props.view.summary.haulers}`, class: 'bg-emerald-600 text-white' },
  { label: `Loader tanpa hauler ${props.view.summary.loaders_without_hauler}`, class: 'bg-red-600 text-white' },
  { label: `Sisa hauler ${props.view.summary.sisa}`, class: 'bg-sky-500 text-white' },
]);

const closeAreaForm = () => { areaTarget.value = null; };
const closeClusterForm = () => { clusterTarget.value = null; };
const closeLoaderForm = () => { loaderTarget.value = null; };

const handleSubmitArea = (name, currentName) => (currentName
  ? actions.updateArea(currentName, name, closeAreaForm)
  : actions.addArea(name, closeAreaForm));

const handleRemoveArea = async (area) => {
  const loaderCount = area.clusters.reduce((sum, cluster) => sum + cluster.loaders.length, 0);
  const note = area.clusters.length ? ` beserta ${area.clusters.length} cluster dan ${loaderCount} loader di dalamnya (hauler kembali ke Ready)` : '';
  if (await confirmDialog(`Hapus area ${area.name}${note}?`)) actions.removeArea(area.name);
};

const handleSubmitCluster = (data, currentName) => (currentName
  ? actions.updateCluster(currentName, data, closeClusterForm)
  : actions.addCluster(data, closeClusterForm));

const handleRemoveCluster = async (cluster) => {
  const note = cluster.loaders.length ? ` beserta ${cluster.loaders.length} loader-nya (hauler kembali ke Ready)` : '';
  if (await confirmDialog(`Hapus cluster ${cluster.name}${note}?`)) actions.removeCluster(cluster.name);
};

const handleSubmitLoader = (data, isEdit) => (isEdit ? actions.updateLoader : actions.addLoader)(data, closeLoaderForm);

const handleRemoveLoader = async (loader) => {
  if (await confirmDialog(`Hapus loader ${loader.code} beserta haulernya dari setting ini?`)) actions.removeLoader(loader.code);
};

const handlePick = (hauler) => {
  const loaderCode = pickLoader.value.code;
  pickLoader.value = null;
  actions.assignHauler(loaderCode, hauler.code);
};

const handleBreakdown = async (unit) => {
  const loader = allClusters.value.flatMap((cluster) => cluster.loaders).find((item) => item.code === unit.code);
  const hasHaulers = !!loader?.haulers.length;
  if (hasHaulers && !(await confirmDialog(`Loader ${unit.code} dilepas dari setting dan hauler-nya kembali ke Ready. Lanjutkan?`))) return;
  actions.sendBreakdown(unit.code);
};

const handleAssignFromReady = (hauler, loaderCode) => actions.assignHauler(loaderCode, hauler.code);

const handleAddLoaderFromReady = (loader) => { loaderTarget.value = { mode: 'add', code: loader.code }; };

const handleAddLoaderToCluster = (cluster) => { loaderTarget.value = { mode: 'add', cluster: cluster.name }; };

const handleCopy = (source) => actions.copy(source, hasData.value, () => { isCopyOpen.value = false; });

const handleClear = async () => {
  if (await confirmDialog('Hapus semua setting fleet pada tanggal dan shift ini, termasuk daftar Breakdown/Service?')) actions.clear();
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
    subtitle="Setting fleet harian per tanggal dan shift: cluster, loader, hauler, dan parkiran unit."
  >
    <DateShiftFilter :url="FLEET_URL" :filters="filters" />

    <template v-if="view">
      <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
          <h2 class="text-base font-bold text-slate-800">{{ title }}</h2>
          <div class="flex flex-wrap gap-2 sm:ml-auto">
            <button type="button" class="flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-700" @click="areaTarget = { mode: 'add' }"><Plus class="h-3.5 w-3.5" /> Tambah area</button>
            <button type="button" class="flex items-center gap-1 rounded-lg border border-blue-600 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50" @click="isCopyOpen = true"><Copy class="h-3.5 w-3.5" /> Salin dari...</button>
            <button v-if="hasData" type="button" class="flex items-center gap-1 rounded-lg border border-red-300 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50" @click="handleClear"><Trash2 class="h-3.5 w-3.5" /> Hapus semua</button>
          </div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2">
          <span v-for="badge in BADGES" :key="badge.label" :class="['rounded-full px-3 py-1 text-xs font-bold', badge.class]">{{ badge.label }}</span>
        </div>
      </div>

      <div class="grid gap-3 xl:grid-cols-[19rem_minmax(0,1fr)]">
      <aside class="xl:sticky xl:top-24 xl:max-h-[calc(100vh-7rem)] xl:self-start xl:overflow-y-auto">
        <FleetSidebar :view="view" :loader-codes="allLoaderCodes" @assign="handleAssignFromReady" @breakdown="handleBreakdown" @add-loader="handleAddLoaderFromReady" @return="(unit) => actions.returnBreakdown(unit.code)" />
      </aside>
      <div class="min-w-0 space-y-4">
      <div v-if="view.warnings.length" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-800">
        <p class="mb-1 flex items-center gap-1 font-bold"><TriangleAlert class="h-3.5 w-3.5" /> Perlu diperhatikan</p>
        <ul class="list-disc pl-5"><li v-for="warning in view.warnings" :key="warning">{{ warning }}</li></ul>
      </div>

      <p v-if="!hasData" class="rounded-2xl border border-slate-200/80 bg-white px-4 py-12 text-center text-sm text-slate-500 shadow-sm">
        Belum ada setting untuk {{ title }}. Tambah area atau salin dari tanggal / shift lain.
      </p>

      <section v-for="area in view.areas" :key="area.name ?? 'tanpa-area'" class="space-y-3">
        <AreaBand :area="area" @add-cluster="(item) => clusterTarget = { mode: 'add', area: item.name }" @edit="(item) => areaTarget = { mode: 'edit', area: item }" @remove="handleRemoveArea" />
        <p v-if="!area.clusters.length" class="rounded-lg border border-dashed border-slate-300 px-3 py-4 text-center text-xs italic text-slate-400">Belum ada cluster</p>
        <div v-else class="grid items-start gap-3 xl:grid-cols-2">
          <ClusterSection v-for="cluster in area.clusters" :key="cluster.name" :cluster="cluster" @edit="clusterTarget = { mode: 'edit', cluster: $event }" @remove="handleRemoveCluster" @add-loader="handleAddLoaderToCluster">
            <LoaderColumn
              v-for="loader in cluster.loaders"
              :key="loader.code"
              :loader="loader"
              :other-loaders="otherLoaders(loader)"
              @edit="loaderTarget = { mode: 'edit', loader: $event, cluster: cluster.name }"
              @remove="handleRemoveLoader"
              @assign="pickLoader = $event"
              @breakdown="handleBreakdown"
              @move="(hauler, code) => actions.moveHauler(hauler.code, code)"
              @release="(hauler) => actions.removeHauler(hauler.code)"
            />
          </ClusterSection>
        </div>
      </section>
      </div>
      </div>

      <AreaFormModal :target="areaTarget" :is-processing="actions.isProcessing.value" @close="closeAreaForm" @submit="handleSubmitArea" />
      <ClusterFormModal :target="clusterTarget" :name-options="view.cluster_options" :area-names="areaNames" :is-processing="actions.isProcessing.value" @close="closeClusterForm" @submit="handleSubmitCluster" />
      <LoaderFormModal :target="loaderTarget" :available-loaders="view.available_loaders" :areas="view.areas" :is-processing="actions.isProcessing.value" @close="closeLoaderForm" @submit="handleSubmitLoader" />
      <PickHaulerModal :loader="pickLoader" :haulers="view.available_haulers" @close="pickLoader = null" @pick="handlePick" />
      <CopyFromModal :open="isCopyOpen" :filters="filters" :has-data="hasData" :is-processing="actions.isProcessing.value" @close="isCopyOpen = false" @submit="handleCopy" />
    </template>
  </FleetShell>
</template>
