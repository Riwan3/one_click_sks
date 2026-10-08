<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
  containHeight: { type: Number, default: null }, // bila diisi, lembar juga dikecilkan agar tinggi tidak melebihi nilai ini (px)
});

const MIN_FIT_SCALE = 0.1;

const frameRef = ref(null);
const sheetRef = ref(null);
const scale = ref(1);
const sheetSize = ref({ width: 0, height: 0 });

const fittedSize = computed(() => ({
  width: `${sheetSize.value.width * scale.value}px`,
  height: `${sheetSize.value.height * scale.value}px`,
}));

const handleFit = () => {
  const frame = frameRef.value;
  const sheet = sheetRef.value;
  if (!frame || !sheet) return;

  const { offsetWidth: width, offsetHeight: height } = sheet;
  const widthScale = frame.clientWidth / width;
  const heightScale = props.containHeight ? props.containHeight / height : Infinity;

  sheetSize.value = { width, height };
  scale.value = Math.max(MIN_FIT_SCALE, Math.min(1, widthScale, heightScale));
};

let observer = null;

onMounted(() => {
  handleFit();
  observer = new ResizeObserver(handleFit);
  observer.observe(frameRef.value);
  observer.observe(sheetRef.value);
});

onBeforeUnmount(() => observer?.disconnect());

watch(() => props.containHeight, () => nextTick(handleFit));
</script>

<template>
  <div ref="frameRef" class="flex w-full justify-center">
    <div class="shrink-0 overflow-hidden bg-white" :style="fittedSize">
      <div ref="sheetRef" class="w-max origin-top-left" :style="{ transform: `scale(${scale})` }">
        <slot />
      </div>
    </div>
  </div>
</template>
