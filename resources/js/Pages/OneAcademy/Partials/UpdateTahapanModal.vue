<script setup>
import { ref, watch } from 'vue';
import { KeySquare } from '@lucide/vue';

const props = defineProps({
    row: { type: Object, default: null },
    tahapanOptions: { type: Array, required: true },
});

const emit = defineEmits(['close', 'save']);

const tahapan = ref('');
const catatan = ref('');

watch(() => props.row, (row) => {
    if (row) {
        tahapan.value = row.tahapan;
        catatan.value = row.catatan;
    }
}, { immediate: true });

const handleSave = () => {
    emit('save', { id: props.row.id, tahapan: tahapan.value, catatan: catatan.value });
};
</script>

<template>
    <div v-if="row" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
        <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-slate-800 px-6 py-4 text-white">
                <h5 class="flex items-center gap-2 text-base font-bold">
                    <KeySquare class="h-4 w-4" /> Update Tahapan
                </h5>
                <button type="button" class="text-2xl leading-none text-white/80 hover:text-white" @click="emit('close')">&times;</button>
            </div>

            <form @submit.prevent="handleSave">
                <div class="space-y-4 p-6">
                    <p class="text-sm text-slate-500">
                        {{ row.typeUnit }} &middot; Pengaju {{ row.diajukanNama }}
                    </p>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-800">Status Tahapan</label>
                        <select v-model="tahapan" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                            <option v-for="option in tahapanOptions.filter((o) => o !== 'Semua Tahapan')" :key="option" :value="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-800">Catatan</label>
                        <textarea
                            v-model="catatan"
                            rows="3"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        ></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" class="text-sm font-bold text-slate-500" @click="emit('close')">Batal</button>
                    <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
