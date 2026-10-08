<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue';
import { shiftClass } from '@/utils/rosterMatrix';

const props = defineProps({
  anchor: { type: Object, required: true }, // DOMRect sel yang diklik
  shifts: { type: Array, required: true },
  current: { type: String, default: '' },
});

const emit = defineEmits(['choose', 'close']);

const ROW_HEIGHT = 30;
const POPOVER_WIDTH = 190;
const MARGIN = 8;

const style = computed(() => {
  const height = (props.shifts.length + 1) * ROW_HEIGHT + 12;
  const left = Math.max(MARGIN, Math.min(props.anchor.left, window.innerWidth - POPOVER_WIDTH - MARGIN));
  const opensUp = props.anchor.bottom + height > window.innerHeight - MARGIN && props.anchor.top - height > MARGIN;
  return { left: `${left}px`, top: `${opensUp ? props.anchor.top - height - 2 : props.anchor.bottom + 2}px` };
});

const handleKeydown = (event) => { if (event.key === 'Escape') emit('close'); };
const handleScroll = () => emit('close');
const handleOutside = (event) => { if (!event.target.closest('[data-shift-popover]')) emit('close'); };

onMounted(() => {
  document.addEventListener('mousedown', handleOutside);
  document.addEventListener('keydown', handleKeydown);
  window.addEventListener('scroll', handleScroll, true);
});
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', handleOutside);
  document.removeEventListener('keydown', handleKeydown);
  window.removeEventListener('scroll', handleScroll, true);
});
</script>

<template>
  <Teleport to="body">
    <div data-shift-popover class="fixed z-[70] min-w-[150px] rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl" :style="style">
      <button
        v-for="shift in shifts" :key="shift.code" type="button"
        :class="['flex w-full items-center gap-2 rounded-md px-2 py-1 text-left text-xs hover:bg-blue-50', shift.code === current ? 'font-bold' : '']"
        @click="emit('choose', shift.code)"
      >
        <span :class="['inline-block min-w-[30px] rounded border border-slate-300 px-1 text-center font-semibold', shiftClass(shift.code)]">{{ shift.code }}</span>
        <span class="text-slate-500">{{ shift.label }}</span>
      </button>
      <button type="button" class="block w-full rounded-md px-2 py-1 text-left text-xs text-red-600 hover:bg-red-50" @click="emit('choose', '')">Kosongkan</button>
    </div>
  </Teleport>
</template>
