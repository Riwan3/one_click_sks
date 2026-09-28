<script setup>
import { reactive, watch } from 'vue';
import { FolderOpen } from '@lucide/vue';
import PhotoUploadSlot from './PhotoUploadSlot.vue';

const props = defineProps({
    row: { type: Object, default: null },
    open: { type: Boolean, default: false },
    formOptions: { type: Object, required: true },
    unitLookup: { type: Object, required: true },
});

const emit = defineEmits(['close', 'save']);

const blankForm = () => {
    const now = new Date();
    const currentWaktu = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`;
    
    return {
        noUnit: '', namaOperator: '', nrp: '', area: '', waktu: currentWaktu,
        jenisDeviasi: props.formOptions.jenisDeviasi[0], deviasiKe: 1,
        temuan: props.formOptions.temuan[0], evidenceIcc: null, followUp1: null, followUp2: null,
        note: props.formOptions.note[0], sumber: 'TRANSTRACK', groupLeader: '',
        status: props.formOptions.status[0], remark: '',
    };
};

const form = reactive(blankForm());

watch(() => props.row, (row) => {
    Object.assign(form, row ? { ...row } : blankForm());
}, { immediate: true });

const handleUnitLookup = () => {
    const unitUpper = form.noUnit.toUpperCase().trim();
    if (props.unitLookup && props.unitLookup[unitUpper]) {
        const found = props.unitLookup[unitUpper];
        form.namaOperator = found.nama || '';
        form.nrp = found.nrp || '';
        if (found.gl) {
            form.groupLeader = found.gl;
        }
        if (found.area) {
            form.area = found.area;
        }
    } else {
        // Only clear operator and nrp if not found, let user type manually
        // But do not auto-clear area/GL if they were typing them manually
        if (form.namaOperator.includes('Otomatis') || form.namaOperator === '') {
             // just leave as is, allow manual
        }
    }
};

import { onMounted, onUnmounted } from 'vue';

const handleSave = () => {
    emit('save', { ...form });
};

const handleGlobalPaste = (e) => {
    if (!props.open) return;
    const items = e.clipboardData?.items;
    if (!items) return;

    for (const item of items) {
        if (item.type.startsWith('image/')) {
            const file = item.getAsFile();
            if (file) {
                const url = URL.createObjectURL(file);
                if (!form.evidenceIcc) {
                    form.evidenceIcc = url;
                } else if (!form.followUp1) {
                    form.followUp1 = url;
                } else if (!form.followUp2) {
                    form.followUp2 = url;
                }
                e.preventDefault();
                return;
            }
        }
    }
};

onMounted(() => {
    window.addEventListener('paste', handleGlobalPaste);
});

onUnmounted(() => {
    window.removeEventListener('paste', handleGlobalPaste);
});
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
        <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl bg-white shadow-2xl">
            <div class="sticky top-0 z-10 flex items-center justify-between px-6 py-4 text-white" style="background:#1e5e3f;">
                <h5 class="flex items-center gap-2 text-base font-bold">
                    <FolderOpen class="h-4 w-4" /> {{ row ? 'Edit' : 'Tambah' }} Data DMS
                </h5>
                <button type="button" class="text-2xl leading-none text-white/80 hover:text-white" @click="emit('close')">&times;</button>
            </div>

            <form @submit.prevent="handleSave">
                <div class="grid grid-cols-1 gap-x-4 gap-y-5 p-6 sm:grid-cols-12">
                    
                    <!-- Row 1 -->
                    <div class="sm:col-span-3">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">No. Unit <span class="text-red-500">*</span></label>
                        <input v-model="form.noUnit" type="text" list="unit-list" placeholder="KETIK NO. UNIT..." required
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm uppercase outline-none focus:border-[#1e5e3f]"
                            @input="handleUnitLookup" @change="handleUnitLookup" @blur="handleUnitLookup">
                        <datalist id="unit-list">
                            <option v-for="(data, unit) in unitLookup" :key="unit" :value="unit"></option>
                        </datalist>
                        <div class="mt-1 text-[10px] text-slate-500">Ketik No. Unit untuk mencari operator</div>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Nama Operator</label>
                        <input v-model="form.namaOperator" type="text" readonly placeholder="Otomatis dari roster..."
                            class="w-full rounded border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">NRP</label>
                        <input v-model="form.nrp" type="text" readonly placeholder="Otomatis..."
                            class="w-full rounded border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Area</label>
                        <input v-model="form.area" type="text" required placeholder="WARA"
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm uppercase outline-none focus:border-[#1e5e3f]">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Waktu</label>
                        <input v-model="form.waktu" type="time" required
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                    </div>

                    <!-- Row 2 -->
                    <div class="sm:col-span-3">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Jenis Deviasi <span class="text-red-500">*</span></label>
                        <select v-model="form.jenisDeviasi" required class="w-full rounded border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                            <option v-for="option in formOptions.jenisDeviasi" :key="option" :value="option">{{ option }}</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Deviasi Ke</label>
                        <input v-model.number="form.deviasiKe" type="number" min="1" required
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Temuan</label>
                        <select v-model="form.temuan" class="w-full rounded border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                            <option v-for="option in formOptions.temuan" :key="option" :value="option">{{ option }}</option>
                        </select>
                    </div>
                    <div class="sm:col-span-4">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Note / Tindak Lanjut (Intervensi)</label>
                        <select v-model="form.note" class="w-full rounded border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                            <option v-for="option in formOptions.note" :key="option" :value="option">{{ option }}</option>
                        </select>
                    </div>

                    <!-- Row 3: Photo Slots -->
                    <div class="grid grid-cols-3 gap-4 sm:col-span-12">
                        <PhotoUploadSlot v-model="form.evidenceIcc" label="Evidence ICC" />
                        <PhotoUploadSlot v-model="form.followUp1" label="Follow UP Hasil Intervensi" />
                        <PhotoUploadSlot v-model="form.followUp2" label="Follow UP Hasil Intervensi 2" />
                    </div>

                    <!-- Row 4 -->
                    <div class="sm:col-span-4">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Sumber</label>
                        <input v-model="form.sumber" type="text"
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm uppercase outline-none focus:border-[#1e5e3f]">
                    </div>
                    <div class="sm:col-span-4">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Group Leader</label>
                        <input v-model="form.groupLeader" type="text" readonly placeholder="Otomatis dari unit..."
                            class="w-full rounded border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 outline-none">
                    </div>
                    <div class="sm:col-span-4">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Status</label>
                        <select v-model="form.status" class="w-full rounded border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                            <option v-for="option in formOptions.status" :key="option" :value="option">{{ option }}</option>
                        </select>
                    </div>

                    <!-- Row 5 -->
                    <div class="sm:col-span-12">
                        <label class="mb-1.5 block text-xs font-bold text-slate-700">Remark</label>
                        <textarea v-model="form.remark" rows="2" placeholder="Keterangan tambahan"
                            class="w-full rounded border border-slate-300 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 bg-white px-6 py-4 rounded-b-2xl">
                    <button type="button" class="rounded bg-slate-500 px-5 py-2 text-sm font-bold text-white hover:bg-slate-600" @click="emit('close')">
                        BATAL
                    </button>
                    <button type="submit" class="flex items-center gap-1.5 rounded bg-[#1e5e3f] px-5 py-2 text-sm font-bold text-white hover:bg-[#16472f]">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        SIMPAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
