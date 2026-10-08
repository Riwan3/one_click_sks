<script setup>
import PreviewSheet from '../ManpowerV201/Partials/PreviewSheet.vue';
import BoardFitFrame from './BoardFitFrame.vue';

defineProps({
  preview: { type: Object, required: true }, // { view, checkPointPassword }
  containHeight: { type: Number, default: null },
});
</script>

<template>
  <BoardFitFrame :contain-height="containHeight">
    <PreviewSheet
      :setting="preview.view.setting"
      :tree="preview.view.tree"
      :gl="preview.view.gl"
      :lain="preview.view.lain"
      :check-point-password="preview.checkPointPassword"
    />
  </BoardFitFrame>
</template>
