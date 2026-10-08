<script setup>
import { computed, ref } from 'vue';
import { TriangleAlert } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { BASE_URL } from '@/utils/manpowerV201';
import { useHarianActions } from '@/composables/useManpowerV201';
import ManpowerShell from './Partials/ManpowerShell.vue';
import DateShiftFilter from './Partials/DateShiftFilter.vue';
import EmptySettingCard from './Partials/EmptySettingCard.vue';
import HarianHeader from './Partials/HarianHeader.vue';
import HarianCsaCard from './Partials/HarianCsaCard.vue';
import HarianSidebar from './Partials/HarianSidebar.vue';
import PlotModal from './Partials/PlotModal.vue';
import LainModal from './Partials/LainModal.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  ready: { type: Boolean, default: true },
  loadError: { type: String, default: null },
  filters: { type: Object, required: true },
  view: { type: Object, default: null },
});

const actions = useHarianActions(() => props.view?.setting?.id);

const plotTarget = ref(null);
const isLainOpen = ref(false);
const lainPreselectedNrp = ref(null);

const setting = computed(() => props.view?.setting ?? null);
const isFinal = computed(() => setting.value?.status === 'final');
const isEditable = computed(() => !!setting.value && !isFinal.value);

const handleCreate = () => actions.create(props.filters.date, props.filters.shift);

const handleUnassign = (unit) => actions.unassign(unit.id);

const handleResolveDouble = (unit, person) => actions.resolveDouble(unit.id, person.nrp);

const handlePlot = (unit, category) => { plotTarget.value = { unit, category }; };

const handlePick = (unit, person) => {
  plotTarget.value = null;
  actions.assign(unit.id, person.nrp);
};

const handleOpenLain = (nrp) => {
  lainPreselectedNrp.value = nrp;
  isLainOpen.value = true;
};

const handleAddLain = (nrp, label) => actions.addLain(nrp, label, () => { isLainOpen.value = false; });

const handleRemoveLain = async (person) => {
  const message = person.source === 'manual'
    ? `Hapus ${person.name} dari Lain-lain? Orang ini kembali ke Daftar Sisa.`
    : `Hapus ${person.name} dari Lain-lain? Orang ini dianggap masuk kerja dan pindah ke Daftar Sisa (status di roster tidak berubah).`;
  if (await confirmDialog(message)) actions.removeLain(person.nrp);
};
</script>

<template>
  <ManpowerShell
    :user="user"
    :notif-counts="notifCounts"
    :menu="menu"
    :ready="ready"
    :load-error="loadError"
    :filters="filters"
    tab="harian"
    subtitle="Setting harian per tanggal dan shift. Sumber kehadiran: Roster V2 (published)."
  >
    <DateShiftFilter :url="BASE_URL" :filters="filters" />

    <EmptySettingCard v-if="!setting" :filters="filters" :hadir="view.hadir" can-create :is-processing="actions.isProcessing.value" @create="handleCreate" />

    <template v-else>
      <HarianHeader
        :setting="setting"
        :summary="view.summary"
        :hadir="view.hadir"
        :is-final="isFinal"
        :is-processing="actions.isProcessing.value"
        @sync="actions.sync"
        @finalize="actions.finalize"
        @reopen="actions.reopen"
      />

      <div v-if="view.warnings.length" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs text-amber-800">
        <p class="mb-1 flex items-center gap-1 font-bold"><TriangleAlert class="h-3.5 w-3.5" /> Perlu diperhatikan ({{ view.warnings.length }})</p>
        <ul class="list-disc pl-5"><li v-for="warning in view.warnings" :key="warning">{{ warning }}</li></ul>
      </div>

      <div class="grid gap-3 xl:grid-cols-[minmax(0,3fr)_minmax(0,1fr)]">
        <div class="space-y-4">
          <HarianCsaCard
            v-for="csa in view.tree"
            :key="csa.id"
            :csa="csa"
            :is-editable="isEditable"
            @plot="handlePlot"
            @unassign="handleUnassign"
            @resolve-double="handleResolveDouble"
          />
        </div>
        <div class="xl:sticky xl:top-24 xl:max-h-[calc(100vh-7rem)] xl:self-start xl:overflow-y-auto">
          <HarianSidebar :sisa="view.sisa" :gl="view.gl" :lain="view.lain" :is-editable="isEditable" @open-lain="handleOpenLain" @remove-lain="handleRemoveLain" />
        </div>
      </div>

      <PlotModal :target="plotTarget" :filters="filters" @close="plotTarget = null" @pick="handlePick" />
      <LainModal :open="isLainOpen" :preselected-nrp="lainPreselectedNrp" :people="view.sisa" :is-processing="actions.isProcessing.value" @close="isLainOpen = false" @submit="handleAddLain" />
    </template>
  </ManpowerShell>
</template>
