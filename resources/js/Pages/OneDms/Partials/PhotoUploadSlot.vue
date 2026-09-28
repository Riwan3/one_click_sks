<script setup>
import { ref } from 'vue';
import { ImagePlus, X } from '@lucide/vue';

const props = defineProps({
    label: { type: String, required: true },
    modelValue: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue']);
const inputRef = ref(null);

const handleFile = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        emit('update:modelValue', URL.createObjectURL(file));
    }
};

const clear = () => {
    emit('update:modelValue', null);
    if (inputRef.value) inputRef.value.value = '';
};
</script>

<template>
    <div>
        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">{{ label }}</label>
        <div
            class="relative flex h-28 w-full items-center justify-center overflow-hidden rounded-lg border-2 border-dashed border-slate-200 bg-slate-50 transition-colors hover:border-[#1e5e3f]"
        >
            <template v-if="modelValue">
                <img :src="modelValue" class="h-full w-full object-cover">
                <button
                    type="button"
                    class="absolute right-1.5 top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80"
                    @click="clear"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
            </template>
            <button v-else type="button" class="flex h-full w-full flex-col items-center justify-center gap-1 text-slate-400 hover:bg-slate-100" @click="inputRef.click()">
                <ImagePlus class="h-6 w-6" />
                <span class="text-xs font-semibold text-center leading-tight">Pilih foto<br/>atau Ctrl+V</span>
            </button>
            <input ref="inputRef" type="file" accept="image/*" class="hidden" @change="handleFile">
        </div>
    </div>
</template>
