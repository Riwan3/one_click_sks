<script setup>
import { ref, onMounted } from 'vue';
import { Eraser } from '@lucide/vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    label: { type: String, required: true },
});

const emit = defineEmits(['update:modelValue']);

const canvasRef = ref(null);
let ctx = null;
let isDrawing = false;

const getPos = (event) => {
    const rect = canvasRef.value.getBoundingClientRect();
    const point = event.touches ? event.touches[0] : event;
    return {
        x: (point.clientX - rect.left) * (canvasRef.value.width / rect.width),
        y: (point.clientY - rect.top) * (canvasRef.value.height / rect.height),
    };
};

const startDraw = (event) => {
    isDrawing = true;
    const { x, y } = getPos(event);
    ctx.beginPath();
    ctx.moveTo(x, y);
};

const draw = (event) => {
    if (! isDrawing) return;
    event.preventDefault();
    const { x, y } = getPos(event);
    ctx.lineTo(x, y);
    ctx.stroke();
};

const stopDraw = () => {
    if (! isDrawing) return;
    isDrawing = false;
    emit('update:modelValue', canvasRef.value.toDataURL('image/png'));
};

const clear = () => {
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
    emit('update:modelValue', '');
};

onMounted(() => {
    ctx = canvasRef.value.getContext('2d');
    ctx.strokeStyle = '#1e293b';
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';

    if (props.modelValue) {
        const img = new Image();
        img.onload = () => ctx.drawImage(img, 0, 0);
        img.src = props.modelValue;
    }
});
</script>

<template>
    <div>
        <label class="mb-1 block text-sm font-semibold text-slate-700">{{ label }}</label>
        <div class="relative h-[150px] rounded-xl border border-slate-200 bg-white shadow-sm">
            <canvas
                ref="canvasRef"
                width="400"
                height="150"
                class="h-full w-full cursor-crosshair touch-none"
                @mousedown="startDraw"
                @mousemove="draw"
                @mouseup="stopDraw"
                @mouseleave="stopDraw"
                @touchstart="startDraw"
                @touchmove="draw"
                @touchend="stopDraw"
            />
            <button
                type="button"
                title="Hapus TTD"
                class="absolute right-1 top-1 flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-red-500 hover:bg-red-50"
                @click="clear"
            >
                <Eraser class="h-3.5 w-3.5" />
            </button>
        </div>
    </div>
</template>
