<script setup>
import { Image as ImageIcon, X } from 'lucide-vue-next';

defineProps({
    open: { type: Boolean, default: false },
    src: { type: String, default: '' },
    title: { type: String, default: 'Foto Bukti Dokumen' },
    subtitle: { type: String, default: '' },
});

defineEmits(['close']);
</script>

<template>
    <div 
        v-if="open && src"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
        @click.self="$emit('close')"
    >
        <div class="relative max-w-3xl w-full bg-white rounded-2xl overflow-hidden shadow-2xl flex flex-col">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 bg-slate-50">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <ImageIcon class="w-4 h-4 text-blue-600" />
                        {{ title }}
                    </h3>
                    <p v-if="subtitle" class="text-xs text-slate-500 mt-0.5">
                        {{ subtitle }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        :href="src"
                        target="_blank"
                        class="text-xs font-semibold text-blue-600 hover:underline px-2.5 py-1 rounded-lg hover:bg-blue-50"
                    >
                        Buka Tab Baru &nearr;
                    </a>
                    <button
                        type="button"
                        class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200/60 transition"
                        @click="$emit('close')"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>
            <div class="p-4 overflow-auto flex items-center justify-center bg-slate-900/5 max-h-[75vh]">
                <img 
                    :src="src" 
                    :alt="title" 
                    class="max-w-full max-h-[70vh] object-contain rounded-xl shadow-sm border border-slate-200 bg-white" 
                />
            </div>
        </div>
    </div>
</template>
