<script setup>
import { ref, watch, computed } from 'vue';
import { X, Calendar, Loader2, FileText, Layers, Navigation, Scale } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    open: Boolean,
    nrp: String,
    nama: String,
    quartal: Number,
    tahun: Number,
});

const emit = defineEmits(['close']);

const loading = ref(false);
const rows = ref([]);

const fetchData = async () => {
    if (!props.nrp || !props.open) return;
    loading.value = true;
    try {
        const resp = await axios.get('/one-power/quartal-operator-report/dpr-daily', {
            params: {
                nrp: props.nrp,
                quartal: props.quartal,
                tahun: props.tahun,
            }
        });
        rows.value = resp.data.data || [];
    } catch (e) {
        rows.value = [];
    } finally {
        loading.value = false;
    }
};

watch(() => [props.open, props.nrp], () => {
    if (props.open && props.nrp) {
        fetchData();
    } else {
        rows.value = [];
    }
}, { immediate: true });

const totalRit = computed(() => rows.value.reduce((acc, r) => acc + (parseFloat(r.rit) || 0), 0));
const totalBcm = computed(() => rows.value.reduce((acc, r) => acc + (parseFloat(r.total_bcm) || 0), 0));
const totalTon = computed(() => rows.value.reduce((acc, r) => acc + (parseFloat(r.total_ton) || 0), 0));
const avgJarak = computed(() => {
    if (rows.value.length === 0) return 0;
    const sum = rows.value.reduce((acc, r) => acc + (parseFloat(r.jarak) || 0), 0);
    return (sum / rows.value.length).toFixed(1);
});
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="flex h-[85vh] w-full max-w-4xl flex-col rounded-2xl bg-white shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 font-bold">
                        {{ nrp.slice(-3) }}
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-800">{{ nama || nrp }}</h3>
                        <p class="text-xs text-slate-500">
                            NRP: <span class="font-semibold text-slate-700">{{ nrp }}</span> • Kuartal {{ quartal }} / {{ tahun }}
                        </p>
                    </div>
                </div>
                <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="emit('close')">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-4 border-b border-slate-100 bg-slate-50/70 px-6 py-3 gap-4">
                <div class="flex items-center gap-2">
                    <Layers class="h-4 w-4 text-blue-600" />
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400">Total Ritase</div>
                        <div class="text-sm font-extrabold text-slate-800">{{ totalRit.toLocaleString('id-ID') }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Scale class="h-4 w-4 text-amber-600" />
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400">Total BCM (OB)</div>
                        <div class="text-sm font-extrabold text-amber-600">{{ totalBcm.toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Scale class="h-4 w-4 text-slate-700" />
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400">Total TON (Coal)</div>
                        <div class="text-sm font-extrabold text-slate-800">{{ totalTon.toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Navigation class="h-4 w-4 text-emerald-600" />
                    <div>
                        <div class="text-[10px] uppercase font-bold text-slate-400">Rata-rata Jarak</div>
                        <div class="text-sm font-extrabold text-slate-800">{{ avgJarak }} m</div>
                    </div>
                </div>
            </div>

            <!-- Table Body -->
            <div class="flex-1 overflow-y-auto p-6">
                <div v-if="loading" class="flex h-48 flex-col items-center justify-center text-slate-400">
                    <Loader2 class="h-8 w-8 animate-spin text-blue-600 mb-2" />
                    <span class="text-xs">Memuat data log DPR...</span>
                </div>

                <div v-else-if="rows.length === 0" class="flex h-48 flex-col items-center justify-center text-slate-400">
                    <FileText class="h-8 w-8 text-slate-300 mb-2" />
                    <span class="text-xs">Tidak ada data DPR harian untuk operator ini di Kuartal {{ quartal }} / {{ tahun }}.</span>
                </div>

                <div v-else class="rounded-xl border border-slate-200 overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 font-semibold border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-2.5">Tanggal</th>
                                <th class="px-3 py-2.5">Role</th>
                                <th class="px-3 py-2.5">Unit Hauler</th>
                                <th class="px-3 py-2.5">Loader</th>
                                <th class="px-3 py-2.5 text-right">Jarak (m)</th>
                                <th class="px-3 py-2.5 text-right">Ritase</th>
                                <th class="px-3 py-2.5 text-right">BCM</th>
                                <th class="px-3 py-2.5 text-right">TON</th>
                                <th class="px-3 py-2.5 text-center">Sumber</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(r, idx) in rows" :key="idx" class="hover:bg-blue-50/40">
                                <td class="px-3 py-2 font-medium text-slate-700 whitespace-nowrap">{{ r.tanggal }}</td>
                                <td class="px-3 py-2">
                                    <span class="rounded px-1.5 py-0.5 text-[10px] font-bold" :class="r.role_op === 'EXCA' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'">
                                        {{ r.role_op }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-slate-600 font-semibold">{{ r.kode_unit || '-' }}</td>
                                <td class="px-3 py-2 text-slate-600">{{ r.loader || '-' }}</td>
                                <td class="px-3 py-2 text-right text-slate-600">{{ r.jarak ? Number(r.jarak).toLocaleString('id-ID') : '-' }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-800">{{ Number(r.rit || 0).toLocaleString('id-ID') }}</td>
                                <td class="px-3 py-2 text-right font-bold text-amber-600">{{ Number(r.total_bcm || 0).toLocaleString('id-ID') }}</td>
                                <td class="px-3 py-2 text-right font-semibold text-slate-700">{{ Number(r.total_ton || 0).toLocaleString('id-ID') }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span class="rounded px-1.5 py-0.5 text-[9px] font-bold" :class="r.source === 'COAL' ? 'bg-slate-800 text-white' : 'bg-amber-100 text-amber-800'">
                                        {{ r.source || 'OB' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between border-t border-slate-100 px-6 py-3 bg-slate-50/50">
                <div class="text-xs text-slate-400">Total: {{ rows.length }} rekaman harian</div>
                <button type="button" class="rounded-lg bg-slate-200 px-4 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-300" @click="emit('close')">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>
