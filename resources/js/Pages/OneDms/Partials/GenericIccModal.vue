<script setup>
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';

const props = defineProps({
    open: Boolean,
    row: Object,
    category: String,
    nrpLookup: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close']);

const form = reactive({
    id: null,
    tanggal: '',
    shift: '',
    nrp: '',
    nama_operator: '',
    unit: '',
    area: '',
    waktu: '',
    deviasi_ke: 1,
    intervensi: '',
    evidence: '',
    fu: '',
    group_leader: '',
    status: '',
});

const emptyForm = () => ({
    id: null,
    tanggal: new Date().toISOString().substring(0, 10),
    shift: 'Shift 1',
    nrp: '',
    nama_operator: '',
    unit: '',
    area: '',
    waktu: '',
    deviasi_ke: 1,
    intervensi: '',
    evidence: '',
    fu: '',
    group_leader: '',
    status: 'Open',
});

watch(() => props.row, (row) => {
    if (row) {
        Object.assign(form, {
            id: row.id,
            tanggal: row.tanggal || '',
            shift: row.shift || 'Shift 1',
            nrp: row.nrp || '',
            nama_operator: row.nama_operator || '',
            unit: row.unit || '',
            area: row.area || '',
            waktu: row.waktu || '',
            deviasi_ke: row.deviasi_ke || 1,
            intervensi: row.intervensi || '',
            evidence: row.evidence || '',
            fu: row.fu || '',
            group_leader: row.group_leader || '',
            status: row.status || 'Close',
        });
    } else {
        Object.assign(form, emptyForm());
    }
}, { immediate: true });

const handleNrpLookup = () => {
    const nrpUpper = form.nrp.toUpperCase().trim();
    if (props.nrpLookup && props.nrpLookup[nrpUpper]) {
        form.nama_operator = props.nrpLookup[nrpUpper];
    }
};

const submit = () => {
    router.post('/one-dms/data-icc/update', { ...form, category: props.category }, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            emit('close');
        }
    });
};
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
        <div class="w-full max-w-3xl rounded-2xl bg-white shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b p-5">
                <h5 class="text-lg font-extrabold text-slate-800">
                    {{ form.id ? 'Edit' : 'Tambah' }} Data ICC
                </h5>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('close')">
                    <X class="w-5 h-5" />
                </button>
            </div>
            
            <form @submit.prevent="submit" class="p-6">
                <div class="grid grid-cols-3 gap-6">
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal</label>
                        <input v-model="form.tanggal" type="date" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Shift</label>
                        <select v-model="form.shift" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                            <option>Shift 1</option>
                            <option>Shift 2</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Status</label>
                        <select v-model="form.status" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                            <option>Open</option>
                            <option>Close</option>
                            <option>On Progress</option>
                        </select>
                    </div>
                    
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">NRP</label>
                        <input v-model="form.nrp" type="text" list="icc-nrp-list" placeholder="NRP..." 
                            @input="handleNrpLookup" @change="handleNrpLookup" @blur="handleNrpLookup"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        <datalist id="icc-nrp-list">
                            <option v-for="(name, nrp) in nrpLookup" :key="nrp" :value="nrp">{{ name }}</option>
                        </datalist>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Operator</label>
                        <input v-model="form.nama_operator" type="text" readonly class="w-full rounded-xl border border-slate-200 bg-slate-100 text-slate-600 px-3 py-2.5 text-sm outline-none focus:border-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">No. Unit</label>
                        <input v-model="form.unit" type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Area</label>
                        <input v-model="form.area" type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Group Leader</label>
                        <input v-model="form.group_leader" type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>
                    
                    <!-- ADAS & Deviasi Fatigue Fields -->
                    <template v-if="category !== 'smoking-deviasi'">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Waktu</label>
                            <input v-model="form.waktu" type="time" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Deviasi Ke</label>
                            <input v-model="form.deviasi_ke" type="number" min="1" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Intervensi</label>
                            <input v-model="form.intervensi" type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        </div>
                    </template>
                    
                    <!-- Smoking Deviasi Fields -->
                    <template v-if="category === 'smoking-deviasi'">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Evidence</label>
                            <input v-model="form.evidence" type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        </div>
                        <div class="col-span-1">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Follow Up</label>
                            <input v-model="form.fu" type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        </div>
                    </template>
                </div>
                
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="emit('close')" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                        BATAL
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#3b82f6] text-sm font-bold text-white hover:bg-blue-600 transition-colors">
                        {{ form.id ? 'SIMPAN PERUBAHAN' : 'SIMPAN DATA' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
