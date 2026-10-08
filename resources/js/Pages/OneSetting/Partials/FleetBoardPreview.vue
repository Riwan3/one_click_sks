<script setup>
import BoardFitFrame from './BoardFitFrame.vue';
import FleetBoardSheet from './FleetBoardSheet.vue';

defineProps({
  preview: { type: Object, required: true }, // { sheet }
  containHeight: { type: Number, default: null },
});
</script>

<template>
  <BoardFitFrame :contain-height="containHeight">
    <FleetBoardSheet :sheet="preview.sheet" />
  </BoardFitFrame>
</template>
