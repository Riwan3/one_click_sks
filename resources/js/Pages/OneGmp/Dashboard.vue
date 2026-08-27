<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Activity, Filter, Layers
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    metrics: { type: Object, required: true },
    areaStats: { type: Array, default: () => [] },
});

const year = ref(props.filters.year);

const handleFilter = () => {
    router.get('/one-gmp/dashboard', { year: year.value }, { preserveState: true });
};
</script>

<template>
    <Head title="Dashboard GMP - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Banner Header -->
        <div class="mb-6 rounded-3xl p-8 text-white shadow-md flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold flex items-center gap-3">
                    <Activity class="w-8 h-8 text-emerald-200" />
                    Dashboard Good Mining Practice (GMP)
                </h1>
                <p class="m-0 max-w-xl text-sm text-white/90">
                    Pengawasan standar operasional Good Mining Practice pada area MHR, MFOB, MFOC, Disposal, dan Dewatering.
                </p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <form @submit.prevent="handleFilter" class="flex gap-3 items-center">
                <label class="text-xs font-bold text-slate-600">Tahun Evaluation:</label>
                <select v-model="year" @change="handleFilter" class="border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 font-bold outline-none focus:border-emerald-500">
                    <option value="2026">2026</option>
                    <option value="2025">2025</option>
                    <option value="2024">2024</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow flex items-center gap-1.5">
                    <Filter class="w-3.5 h-3.5" /> Filter
                </button>
            </form>
        </div>

        <!-- Metric Stat Cards -->
        <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-emerald-200 shadow-sm border-l-4 border-l-emerald-500">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Total Penilaian Disetujui</p>
                <h3 class="text-2xl font-black text-emerald-600">{{ metrics.total_penilaian }}</h3>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-blue-200 shadow-sm border-l-4 border-l-blue-500">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Rata-rata Skor GMP</p>
                <h3 class="text-2xl font-black text-blue-600">{{ metrics.avg_score }}%</h3>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-purple-200 shadow-sm border-l-4 border-l-purple-500">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Total Lokasi Terekam</p>
                <h3 class="text-2xl font-black text-purple-600">{{ metrics.jumlah_lokasi }}</h3>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-rose-200 shadow-sm border-l-4 border-l-rose-500">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Area Kritis (&lt; 50%)</p>
                <h3 class="text-2xl font-black text-rose-600">{{ metrics.critical_areas }}</h3>
            </div>
        </div>

        <!-- Area Breakdown Grid -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-sm font-extrabold text-slate-800 mb-4 flex items-center gap-2">
                <Layers class="w-4 h-4 text-emerald-500" /> Ringkasan Per Kategori Sub-Area GMP
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div v-for="area in areaStats" :key="area.tipe_gmp" class="p-4 rounded-2xl border border-slate-200 bg-slate-50 text-center">
                    <p class="text-xs font-black text-slate-500 uppercase tracking-wider mb-1">{{ area.tipe_gmp }}</p>
                    <h4 class="text-xl font-extrabold text-slate-800">{{ Math.round(area.avg_percent) }}%</h4>
                    <p class="text-[11px] text-slate-400 mt-1">{{ area.total }} Inspeksi</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
