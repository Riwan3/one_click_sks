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

const blankForm = () => ({
    noUnit: '', namaOperator: '', nrp: '', area: '', waktu: '',
    jenisDeviasi: props.formOptions.jenisDeviasi[0], deviasiKe: 1,
    temuan: props.formOptions.temuan[0], evidenceIcc: null, followUp1: null, followUp2: null,
    note: props.formOptions.note[0], sumber: 'ICC', groupLeader: 'Group Leader Shift',
    status: props.formOptions.status[0], remark: '',
});

const form = reactive(blankForm());

watch(() => props.row, (row) => {
    Object.assign(form, row ? { ...row } : blankForm());
}, { immediate: true });

const handleUnitLookup = () => {
    const found = props.unitLookup[form.noUnit];
    if (found) {
        form.namaOperator = found.nama;
        form.nrp = found.nrp;
    }
};

const handleSave = () => {
    emit('save', { ...form });
};
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
                <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">No. Unit</label>
                        <input v-model="form.noUnit" type="text" placeholder="mis. HD785-012" required
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]"
                            @blur="handleUnitLookup">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Area</label>
                        <input v-model="form.area" type="text" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Nama Operator</label>
                        <input v-model="form.namaOperator" type="text" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 outline-none">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">NRP</label>
                        <input v-model="form.nrp" type="text" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 outline-none">
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Waktu</label>
                        <input v-model="form.waktu" type="time" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Deviasi Ke</label>
                        <input v-model.number="form.deviasiKe" type="number" min="1" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Jenis Deviasi</label>
                        <select v-model="form.jenisDeviasi" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                            <option v-for="option in formOptions.jenisDeviasi" :key="option" :value="option">{{ option }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Temuan</label>
                        <select v-model="form.temuan" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                            <option v-for="option in formOptions.temuan" :key="option" :value="option">{{ option }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Status</label>
                        <select v-model="form.status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                            <option v-for="option in formOptions.status" :key="option" :value="option">{{ option }}</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Note / Tindak Lanjut</label>
                        <select v-model="form.note" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                            <option v-for="option in formOptions.note" :key="option" :value="option">{{ option }}</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-3 sm:col-span-2">
                        <PhotoUploadSlot v-model="form.evidenceIcc" label="Evidence ICC" />
                        <PhotoUploadSlot v-model="form.followUp1" label="Follow Up 1" />
                        <PhotoUploadSlot v-model="form.followUp2" label="Follow Up 2" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Sumber</label>
                        <input v-model="form.sumber" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Group Leader</label>
                        <input v-model="form.groupLeader" type="text" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Remark</label>
                        <textarea v-model="form.remark" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#1e5e3f]"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" class="text-sm font-bold text-slate-500" @click="emit('close')">Batal</button>
                    <button type="submit" class="rounded-lg px-4 py-2 text-sm font-bold text-white" style="background:#1e5e3f;">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
