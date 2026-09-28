<script setup>
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';

const props = defineProps({
    open: Boolean,
    row: Object,
    nrpLookup: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close']);

const form = reactive({
    id: null,
    tanggal: new Date().toISOString().substring(0, 10),
    shift: 'Shift 1',
    nrp: '',
    nama_operator: '',
    jam_mulai: '',
    jam_akhir: '',
    total_jam: '',
});

watch(() => props.row, (row) => {
    if (row) {
        Object.assign(form, row);
    } else {
        Object.assign(form, {
            id: null,
            tanggal: new Date().toISOString().substring(0, 10),
            shift: 'Shift 1',
            nrp: '',
            nama_operator: '',
            jam_mulai: '',
            jam_akhir: '',
            total_jam: '',
        });
    }
}, { immediate: true });

watch([() => form.jam_mulai, () => form.jam_akhir], ([mulai, akhir]) => {
    if (mulai && akhir) {
        const [hMulai, mMulai] = mulai.split(':').map(Number);
        const [hAkhir, mAkhir] = akhir.split(':').map(Number);
        
        let start = hMulai * 60 + mMulai;
        let end = hAkhir * 60 + mAkhir;
        if (end < start) end += 24 * 60;
        
        const diff = end - start;
        const hours = Math.floor(diff / 60);
        const mins = diff % 60;
        
        if (hours === 0) {
            form.total_jam = `${mins} Menit`;
        } else if (mins === 0) {
            form.total_jam = `${hours} Jam`;
        } else {
            form.total_jam = `${hours} Jam ${mins} Menit`;
        }
    } else {
        form.total_jam = '';
    }
});

const handleNrpLookup = () => {
    const nrpUpper = form.nrp.toUpperCase().trim();
    if (props.nrpLookup && props.nrpLookup[nrpUpper]) {
        form.nama_operator = props.nrpLookup[nrpUpper];
    }
};

const submit = () => {
    router.post('/one-dms/izin-fatigue/store', form, {
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
                    {{ form.id ? 'Edit' : 'Tambah' }} Data Izin Fatigue
                </h5>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('close')">
                    <X class="w-5 h-5" />
                </button>
            </div>
            
            <form @submit.prevent="submit" class="p-6">
                <div class="grid grid-cols-3 gap-6">
                    
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Tanggal</label>
                        <input v-model="form.tanggal" type="date" required 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>
                    
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Shift</label>
                        <select v-model="form.shift" required 
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                            <option>Shift 1</option>
                            <option>Shift 2</option>
                        </select>
                    </div>
                    
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">NRP</label>
                        <input v-model="form.nrp" type="text" list="izin-nrp-list" placeholder="Ketik NRP atau Nama..." 
                            @input="handleNrpLookup" @change="handleNrpLookup" @blur="handleNrpLookup"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                        <datalist id="izin-nrp-list">
                            <option v-for="(name, nrp) in nrpLookup" :key="nrp" :value="nrp">{{ name }}</option>
                        </datalist>
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Nama Operator</label>
                        <input v-model="form.nama_operator" type="text" readonly placeholder="Otomatis dari NRP..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-100 text-slate-600 px-3 py-2.5 text-sm outline-none">
                    </div>
                    
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jam Mulai</label>
                        <input v-model="form.jam_mulai" type="time"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>

                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Jam Akhir</label>
                        <input v-model="form.jam_akhir" type="time"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:bg-white transition-colors">
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Total Jam Izin</label>
                        <input v-model="form.total_jam" type="text" placeholder="Otomatis terhitung" readonly
                            class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-500 outline-none">
                    </div>
                    
                </div>
                
                <div class="mt-8">
                    <button type="submit" class="w-full rounded-xl bg-[#3b82f6] py-3.5 text-sm font-bold text-white hover:bg-blue-600 transition-colors">
                        SIMPAN DATA
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
