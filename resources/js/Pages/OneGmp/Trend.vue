<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { TrendingUp, Filter, BarChart3 } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import GmpTrendCards from './Partials/Trend/GmpTrendCards.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    weeklyTrend: { type: Array, default: () => [] },
    trend: { type: Object, default: () => ({ weeks: [], series: {} }) },
    yearOptions: { type: Array, default: () => [] },
});

const year = ref(props.filters.year);

const handleFilter = () => {
    router.get('/one-gmp/trend', { year: year.value }, { preserveState: true });
};
</script>

<template>
    <Head title="Trend GMP - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-6 rounded-3xl p-8 text-white shadow-md flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold flex items-center gap-3">
                    <TrendingUp class="w-8 h-8 text-teal-200" />
                    Trend & Analytics Good Mining Practice
                </h1>
                <p class="m-0 max-w-xl text-sm text-white/90">
                    Tren akumulasi skor inspeksi mingguan per kategori area operasional GMP.
                </p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <form @submit.prevent="handleFilter" class="flex gap-3 items-center">
                <label class="text-xs font-bold text-slate-600">Tahun Tren:</label>
                <select v-model="year" @change="handleFilter" class="border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 font-bold outline-none focus:border-teal-500">
                    <option v-for="y in yearOptions" :key="y" :value="String(y)">{{ y }}</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded-xl text-xs shadow flex items-center gap-1.5">
                    <Filter class="w-3.5 h-3.5" /> Filter
                </button>
            </form>
        </div>

        <GmpTrendCards class="mb-6" :weeks="trend.weeks" :series="trend.series" />

        <!-- Weekly Trend Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-sm font-extrabold text-slate-800 mb-4 flex items-center gap-2">
                <BarChart3 class="w-4 h-4 text-teal-500" /> Tren Rata-rata Skor Mingguan
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 font-bold text-slate-700">
                        <tr>
                            <th class="p-3">Minggu (Week)</th>
                            <th class="p-3">Kategori Area</th>
                            <th class="p-3 text-right">Rata-rata Skor (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="weeklyTrend.length === 0">
                            <td colspan="3" class="p-6 text-center text-slate-400">Belum ada data tren inspeksi pada tahun ini.</td>
                        </tr>
                        <tr v-for="(row, idx) in weeklyTrend" :key="idx" class="hover:bg-slate-50">
                            <td class="p-3 font-bold text-slate-900">{{ row.week }}</td>
                            <td class="p-3 font-semibold text-slate-600">{{ row.tipe_gmp }}</td>
                            <td class="p-3 text-right font-black" :class="row.avg_percent >= 85 ? 'text-emerald-600' : 'text-amber-600'">
                                {{ Math.round(row.avg_percent) }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
