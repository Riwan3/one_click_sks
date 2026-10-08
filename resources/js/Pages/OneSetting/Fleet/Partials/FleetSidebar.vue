<script setup>
import { computed } from 'vue';
import ReadyHaulerPanel from './ReadyHaulerPanel.vue';
import DiggerReadyPanel from './DiggerReadyPanel.vue';
import BreakdownPanel from './BreakdownPanel.vue';

const props = defineProps({
  view: { type: Object, required: true },
  loaderCodes: { type: Array, default: () => [] },
});

const emit = defineEmits(['assign', 'breakdown', 'add-loader', 'return']);

const byParking = (codes) => props.view.available_haulers.filter((h) => codes.includes(h.parking_code));

const monte = computed(() => byParking(['M']));
const gibson = computed(() => byParking(['G', 'J'])); // Jumbo Vessel juga parkir di CSA Gibson
const unparked = computed(() => props.view.available_haulers.filter((h) => !h.parking_code));
</script>

<template>
  <div class="space-y-3">
    <ReadyHaulerPanel title="Unit Ready CSA Monte" :haulers="monte" :loader-codes="loaderCodes" @assign="(h, code) => emit('assign', h, code)" @breakdown="emit('breakdown', $event)" />
    <ReadyHaulerPanel title="Unit Ready CSA Gibson" :haulers="gibson" :loader-codes="loaderCodes" @assign="(h, code) => emit('assign', h, code)" @breakdown="emit('breakdown', $event)" />
    <ReadyHaulerPanel v-if="unparked.length" title="Unit Ready (parkiran belum diisi)" :haulers="unparked" :loader-codes="loaderCodes" @assign="(h, code) => emit('assign', h, code)" @breakdown="emit('breakdown', $event)" />
    <DiggerReadyPanel :loaders="view.available_loaders" @add="emit('add-loader', $event)" @breakdown="emit('breakdown', $event)" />
    <BreakdownPanel :units="view.breakdown" @return="emit('return', $event)" />
  </div>
</template>
