<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';
import { Camera, Image as ImageIcon, Eye, Trash2, X, RefreshCw } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: [Object, File, null], default: null },
    existingPhoto: { type: [String, null], default: null },
    hapusFoto: { type: Boolean, default: false },
    error: { type: String, default: null },
    label: { type: String, default: 'Foto Bukti / Dokumentasi' },
    hint: { type: String, default: 'Opsional • Format: JPG, PNG, WEBP (Maks. 10MB)' },
});

const emit = defineEmits(['update:modelValue', 'update:hapusFoto']);

const fileInputRef = ref(null);
const previewUrl = ref(null);
const isPreviewModalOpen = ref(false);
const previewModalSrc = ref('');

watch(
    () => props.modelValue,
    (newVal) => {
        if (previewUrl.value) {
            URL.revokeObjectURL(previewUrl.value);
            previewUrl.value = null;
        }
        if (newVal instanceof File || newVal instanceof Blob) {
            previewUrl.value = URL.createObjectURL(newVal);
        }
    },
    { immediate: true }
);

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
});

const triggerFileSelect = () => {
    fileInputRef.value?.click();
};

const handleFileChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) {
        alert('Ukuran foto terlalu besar. Maksimal 10MB.');
        e.target.value = '';
        return;
    }

    emit('update:hapusFoto', false);
    emit('update:modelValue', file);
};

const removeNewFile = () => {
    emit('update:modelValue', null);
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const markDeleteExisting = () => {
    emit('update:hapusFoto', true);
    removeNewFile();
};

const undoDeleteExisting = () => {
    emit('update:hapusFoto', false);
};

const openLightbox = (url) => {
    if (!url) return;
    previewModalSrc.value = url;
    isPreviewModalOpen.value = true;
};
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                {{ label }}
            </label>
            <span class="text-[11px] text-slate-400">{{ hint }}</span>
        </div>

        <input
            ref="fileInputRef"
            type="file"
            accept="image/jpeg,image/png,image/jpg,image/webp"
            class="hidden"
            @change="handleFileChange"
        />

        <!-- Empty state: No new file, and no active existing photo -->
        <div
            v-if="!modelValue && (!existingPhoto || hapusFoto)"
            class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-2xl p-6 bg-slate-50/60 hover:bg-blue-50/30 transition-all cursor-pointer group"
            @click="triggerFileSelect"
        >
            <div class="p-3 bg-white rounded-full shadow-2xs border border-slate-200 group-hover:scale-110 group-hover:text-blue-600 transition text-slate-400">
                <Camera class="w-6 h-6" />
            </div>
            <p class="text-sm font-semibold text-slate-700 mt-2">
                Klik untuk memilih atau upload foto bukti
            </p>
            <p class="text-xs text-slate-400 mt-0.5">
                Ambil dari galeri atau kamera perangkat
            </p>
            <div v-if="existingPhoto && hapusFoto" class="mt-3 inline-flex items-center gap-2 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-700 text-xs rounded-lg font-medium">
                <span>Foto tersimpan akan dihapus saat disimpan.</span>
                <button type="button" @click.stop="undoDeleteExisting" class="font-bold underline hover:text-amber-900">
                    Urungkan
                </button>
            </div>
        </div>

        <!-- State: New file picked -->
        <div
            v-else-if="modelValue"
            class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-2xl border border-blue-200 bg-blue-50/50"
        >
            <div class="flex items-center gap-3.5 w-full sm:w-auto">
                <div class="relative group cursor-pointer shrink-0" @click="openLightbox(previewUrl)">
                    <img
                        :src="previewUrl"
                        alt="Preview Foto"
                        class="w-16 h-16 object-cover rounded-xl border border-blue-300 shadow-xs"
                    />
                    <div class="absolute inset-0 bg-slate-900/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white">
                        <Eye class="w-5 h-5" />
                    </div>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-600 text-white uppercase tracking-wider">
                            Foto Baru
                        </span>
                        <span class="text-xs text-slate-700 font-semibold truncate max-w-[200px]">
                            {{ modelValue.name }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ (modelValue.size / 1024).toFixed(1) }} KB &bull; Klik gambar untuk memperbesar
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                <button
                    type="button"
                    @click="openLightbox(previewUrl)"
                    class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs"
                >
                    <Eye class="w-3.5 h-3.5 text-blue-600" /> Lihat Foto
                </button>
                <button
                    type="button"
                    @click="triggerFileSelect"
                    class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs"
                >
                    Ganti
                </button>
                <button
                    type="button"
                    @click="removeNewFile"
                    class="p-1.5 bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 transition shadow-2xs"
                    title="Hapus foto baru"
                >
                    <Trash2 class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- State: Existing photo in edit mode -->
        <div
            v-else-if="existingPhoto && !hapusFoto"
            class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-2xl border border-slate-200 bg-slate-50/70"
        >
            <div class="flex items-center gap-3.5 w-full sm:w-auto">
                <div class="relative group cursor-pointer shrink-0" @click="openLightbox(existingPhoto)">
                    <img
                        :src="existingPhoto"
                        alt="Foto Tersimpan"
                        class="w-16 h-16 object-cover rounded-xl border border-slate-300 shadow-xs"
                    />
                    <div class="absolute inset-0 bg-slate-900/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white">
                        <Eye class="w-5 h-5" />
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-600 text-white uppercase tracking-wider">
                            Foto Tersimpan
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Klik gambar atau tombol "Lihat Foto" untuk melihat ukuran penuh
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                <button
                    type="button"
                    @click="openLightbox(existingPhoto)"
                    class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs"
                >
                    <Eye class="w-3.5 h-3.5 text-blue-600" /> Lihat Foto
                </button>
                <button
                    type="button"
                    @click="triggerFileSelect"
                    class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs"
                >
                    Ganti Foto
                </button>
                <button
                    type="button"
                    @click="markDeleteExisting"
                    class="p-1.5 bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 transition shadow-2xs"
                    title="Hapus foto tersimpan"
                >
                    <Trash2 class="w-4 h-4" />
                </button>
            </div>
        </div>

        <p v-if="error" class="mt-1 text-xs text-rose-600 font-medium">
            {{ error }}
        </p>

        <!-- Lightbox Modal -->
        <div
            v-if="isPreviewModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
            @click.self="isPreviewModalOpen = false"
        >
            <div class="relative max-w-4xl max-h-[90vh] bg-white rounded-2xl overflow-hidden shadow-2xl flex flex-col">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <ImageIcon class="w-4 h-4 text-blue-600" />
                        Preview {{ label }}
                    </h3>
                    <div class="flex items-center gap-2">
                        <a
                            :href="previewModalSrc"
                            target="_blank"
                            class="text-xs font-semibold text-blue-600 hover:underline px-2 py-1"
                        >
                            Buka Tab Baru &nearr;
                        </a>
                        <button
                            type="button"
                            @click="isPreviewModalOpen = false"
                            class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200/60 transition"
                        >
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                <div class="p-4 overflow-auto flex items-center justify-center bg-slate-900/5 max-h-[calc(90vh-60px)]">
                    <img :src="previewModalSrc" alt="Full Preview" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-sm" />
                </div>
            </div>
        </div>
    </div>
</template>
