<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import PreviewSheet from '../ManpowerV201/Partials/PreviewSheet.vue';

const props = defineProps({
  preview: { type: Object, required: true }, // { view, checkPointPassword }
});

const MIN_FIT_SCALE = 0.1;

const frameRef = ref(null);
const sheetRef = ref(null);
const scale = ref(1);
const sheetHeight = ref(0);

const setting = computed(() => props.preview.view.setting);

const handleFit = () => {
  const frame = frameRef.value;
  const sheet = sheetRef.value;
  if (!frame || !sheet) return;
  scale.value = Math.max(MIN_FIT_SCALE, Math.min(1, frame.clientWidth / sheet.offsetWidth));
  sheetHeight.value = sheet.offsetHeight;
};

let observer = null;

onMounted(() => {
  handleFit();
  observer = new ResizeObserver(handleFit);
  observer.observe(frameRef.value);
  observer.observe(sheetRef.value);
});

onBeforeUnmount(() => observer?.disconnect());

watch(() => props.preview, () => nextTick(handleFit));
</script>

<template>
  <div ref="frameRef" class="w-full overflow-hidden bg-white" :style="{ height: `${sheetHeight * scale}px` }">
    <div ref="sheetRef" class="w-max origin-top-left" :style="{ transform: `scale(${scale})` }">
      <PreviewSheet
        :setting="setting"
        :tree="preview.view.tree"
        :gl="preview.view.gl"
        :lain="preview.view.lain"
        :check-point-password="preview.checkPointPassword"
      />
    </div>
  </div>
</template>
