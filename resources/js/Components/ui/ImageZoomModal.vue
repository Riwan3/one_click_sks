<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { 
  X, 
  ZoomIn, 
  ZoomOut, 
  RotateCcw, 
  Maximize2, 
  Minimize2, 
  Download, 
  ExternalLink,
  Move
} from 'lucide-vue-next';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  imageUrl: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    default: 'Setting Board',
  },
  subtitle: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['close']);

const containerRef = ref(null);
const imageRef = ref(null);

const scale = ref(1);
const translateX = ref(0);
const translateY = ref(1);
const isDragging = ref(false);
const dragStart = ref({ x: 0, y: 0 });
const lastTapTime = ref(0);
const isFullscreen = ref(false);

// Pinch zoom state
const initialPinchDistance = ref(null);
const initialPinchScale = ref(1);

const minScale = 0.8;
const maxScale = 6.0;

const scalePercentage = computed(() => `${Math.round(scale.value * 100)}%`);

const resetZoom = () => {
  scale.value = 1;
  translateX.value = 0;
  translateY.value = 0;
};

const zoomIn = () => {
  scale.value = Math.min(maxScale, Number((scale.value + 0.35).toFixed(2)));
};

const zoomOut = () => {
  scale.value = Math.max(minScale, Number((scale.value - 0.35).toFixed(2)));
  if (scale.value <= 1) {
    translateX.value = 0;
    translateY.value = 0;
  }
};

const fitWidth = () => {
  scale.value = 1.6;
  translateX.value = 0;
  translateY.value = 0;
};

// Double tap or double click to toggle zoom
const handleDoubleTap = (e) => {
  if (scale.value > 1.2) {
    resetZoom();
  } else {
    scale.value = 2.4;
  }
};

// Mouse Drag / Pan
const handleMouseDown = (e) => {
  if (scale.value <= 1) return;
  e.preventDefault();
  isDragging.value = true;
  dragStart.value = {
    x: e.clientX - translateX.value,
    y: e.clientY - translateY.value,
  };
};

const handleMouseMove = (e) => {
  if (!isDragging.value || scale.value <= 1) return;
  e.preventDefault();
  translateX.value = e.clientX - dragStart.value.x;
  translateY.value = e.clientY - dragStart.value.y;
};

const handleMouseUp = () => {
  isDragging.value = false;
};

// Wheel Zoom
const handleWheel = (e) => {
  e.preventDefault();
  const delta = e.deltaY > 0 ? -0.2 : 0.2;
  const newScale = Math.min(maxScale, Math.max(minScale, Number((scale.value + delta).toFixed(2))));
  scale.value = newScale;
  if (newScale <= 1) {
    translateX.value = 0;
    translateY.value = 0;
  }
};

// Touch Gestures (Pinch to Zoom & Drag)
const handleTouchStart = (e) => {
  if (e.touches.length === 2) {
    // Start pinch
    const t1 = e.touches[0];
    const t2 = e.touches[1];
    initialPinchDistance.value = Math.hypot(t2.clientX - t1.clientX, t2.clientY - t1.clientY);
    initialPinchScale.value = scale.value;
  } else if (e.touches.length === 1) {
    const now = Date.now();
    if (now - lastTapTime.value < 300) {
      // Double tap detected
      handleDoubleTap(e);
      lastTapTime.value = 0;
      return;
    }
    lastTapTime.value = now;

    if (scale.value > 1) {
      isDragging.value = true;
      dragStart.value = {
        x: e.touches[0].clientX - translateX.value,
        y: e.touches[0].clientY - translateY.value,
      };
    }
  }
};

const handleTouchMove = (e) => {
  if (e.touches.length === 2 && initialPinchDistance.value) {
    e.preventDefault();
    const t1 = e.touches[0];
    const t2 = e.touches[1];
    const currentDistance = Math.hypot(t2.clientX - t1.clientX, t2.clientY - t1.clientY);
    const diffRatio = currentDistance / initialPinchDistance.value;
    const targetScale = initialPinchScale.value * diffRatio;
    scale.value = Math.min(maxScale, Math.max(minScale, Number(targetScale.toFixed(2))));
  } else if (e.touches.length === 1 && isDragging.value && scale.value > 1) {
    e.preventDefault();
    translateX.value = e.touches[0].clientX - dragStart.value.x;
    translateY.value = e.touches[0].clientY - dragStart.value.y;
  }
};

const handleTouchEnd = (e) => {
  if (e.touches.length < 2) {
    initialPinchDistance.value = null;
  }
  if (e.touches.length === 0) {
    isDragging.value = false;
  }
};

const toggleFullscreen = () => {
  if (!containerRef.value) return;
  if (!document.fullscreenElement) {
    containerRef.value.requestFullscreen().then(() => {
      isFullscreen.value = true;
    }).catch(() => {});
  } else {
    document.exitFullscreen().then(() => {
      isFullscreen.value = false;
    }).catch(() => {});
  }
};

const close = () => {
  resetZoom();
  emit('close');
};

const handleKeyDown = (e) => {
  if (!props.show) return;
  if (e.key === 'Escape') close();
  if (e.key === '+' || e.key === '=') zoomIn();
  if (e.key === '-') zoomOut();
  if (e.key === '0') resetZoom();
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    resetZoom();
    window.addEventListener('keydown', handleKeyDown);
    document.body.style.overflow = 'hidden';
  } else {
    window.removeEventListener('keydown', handleKeyDown);
    document.body.style.overflow = '';
  }
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
  document.body.style.overflow = '';
});
</script>

<template>
  <Teleport to="body">
    <div 
      v-if="show"
      ref="containerRef"
      class="fixed inset-0 z-9999 flex flex-col bg-slate-950/95 text-white backdrop-blur-md select-none touch-none overflow-hidden"
    >
      <!-- Top Navigation Bar -->
      <div class="flex items-center justify-between px-4 py-3 bg-slate-900/90 border-b border-slate-800/80 z-20 shrink-0">
        <div class="flex items-center gap-2.5 truncate max-w-[65%] sm:max-w-md">
          <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></div>
          <div class="truncate">
            <h2 class="text-xs sm:text-sm font-bold text-white tracking-tight truncate">{{ title }}</h2>
            <p v-if="subtitle" class="text-[10px] text-slate-400 truncate">{{ subtitle }}</p>
          </div>
        </div>

        <div class="flex items-center gap-1.5 sm:gap-2">
          <!-- Scale Pill -->
          <span class="px-2.5 py-1 text-[11px] font-mono font-bold bg-slate-800 text-sky-400 rounded-lg border border-slate-700">
            {{ scalePercentage }}
          </span>

          <!-- Download / External Link -->
          <a 
            :href="imageUrl" 
            target="_blank" 
            download 
            class="p-2 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition"
            title="Download HD Image"
          >
            <Download class="w-4 h-4" />
          </a>

          <!-- Fullscreen Toggle -->
          <button 
            @click="toggleFullscreen"
            class="p-2 text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition hidden sm:block"
            title="Fullscreen"
          >
            <Maximize2 v-if="!isFullscreen" class="w-4 h-4" />
            <Minimize2 v-else class="w-4 h-4" />
          </button>

          <!-- Close Button -->
          <button 
            @click="close"
            class="p-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl shadow-lg transition active:scale-95"
            title="Tutup (Esc)"
          >
            <X class="w-4 h-4 sm:w-5 sm:h-5" />
          </button>
        </div>
      </div>

      <!-- Main Viewer Area -->
      <div 
        class="relative flex-1 w-full h-full flex items-center justify-center overflow-hidden cursor-grab active:cursor-grabbing"
        @mousedown="handleMouseDown"
        @mousemove="handleMouseMove"
        @mouseup="handleMouseUp"
        @mouseleave="handleMouseUp"
        @wheel="handleWheel"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
      >
        <!-- Notice gesture hint (mobile) -->
        <div 
          v-if="scale === 1" 
          class="absolute top-3 left-1/2 -translate-x-1/2 z-10 px-3 py-1 bg-slate-900/80 border border-slate-700/80 rounded-full text-[11px] text-slate-300 pointer-events-none flex items-center gap-1.5 backdrop-blur-xs shadow-md"
        >
          <Move class="w-3 h-3 text-sky-400" />
          <span>Cubit (pinch) untuk zoom &bull; Geser untuk geser &bull; Ketuk 2x untuk 2.5x</span>
        </div>

        <!-- The Image Wrapper -->
        <div 
          class="transition-transform duration-75 ease-out max-w-full max-h-full flex items-center justify-center p-2 sm:p-4"
          :style="{
            transform: `translate3d(${translateX}px, ${translateY}px, 0) scale(${scale})`,
            transformOrigin: 'center center',
          }"
        >
          <img 
            ref="imageRef"
            :src="imageUrl" 
            :alt="title"
            class="max-w-[94vw] max-h-[82vh] object-contain rounded-lg shadow-2xl pointer-events-none"
            style="image-rendering: -webkit-optimize-contrast; image-rendering: high-quality;"
          />
        </div>
      </div>

      <!-- Bottom Floating Control Bar -->
      <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-1 bg-slate-900/90 border border-slate-700/80 px-3 py-1.5 rounded-2xl backdrop-blur-md shadow-2xl">
        <button 
          @click="zoomOut" 
          class="p-2 text-slate-200 hover:text-white hover:bg-slate-800 rounded-xl transition active:scale-90"
          title="Zoom Out (-)"
        >
          <ZoomOut class="w-4 h-4 sm:w-5 sm:h-5" />
        </button>

        <button 
          @click="resetZoom" 
          class="px-2.5 py-1 text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800 rounded-xl transition active:scale-95 flex items-center gap-1"
          title="Reset Zoom"
        >
          <RotateCcw class="w-3.5 h-3.5" />
          <span>100%</span>
        </button>

        <button 
          @click="zoomIn" 
          class="p-2 text-slate-200 hover:text-white hover:bg-slate-800 rounded-xl transition active:scale-90"
          title="Zoom In (+)"
        >
          <ZoomIn class="w-4 h-4 sm:w-5 sm:h-5" />
        </button>

        <div class="w-px h-5 bg-slate-700 mx-1"></div>

        <button 
          @click="fitWidth" 
          class="px-2.5 py-1 text-xs font-medium text-sky-400 hover:bg-slate-800 rounded-xl transition hidden sm:inline-block"
          title="Zoom Lebar (160%)"
        >
          Lebar Layar
        </button>
      </div>
    </div>
  </Teleport>
</template>
