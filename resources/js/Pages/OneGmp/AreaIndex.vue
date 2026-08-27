<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { MapPin, Plus, Search, CheckCircle2, Clock, XCircle, RotateCcw } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    type: { type: String, required: true },
    filters: { type: Object, required: true },
    records: { type: Object, required: true },
});

const search = ref(props.filters.search || '');

const handleSearch = () => {
    router.get(`/one-gmp/area/${props.type}`, { q: search.value }, { preserveState: true });
};

const statusBadge = (status) => {
    switch (status) {
        case 'approved': return { text: 'Disetujui', class: 'bg-emerald-50 text-emerald-700 border-emerald-200', icon: CheckCircle2 };
        case 'pending': return { text: 'Pending Approval', class: 'bg-amber-50 text-amber-700 border-amber-200', icon: Clock };
        case 'rejected': return { text: 'Ditolak', class: 'bg-rose-50 text-rose-700 border-rose-200', icon: XCircle };
        case 'returned': return { text: 'Perlu Revisi', class: 'bg-purple-50 text-purple-700 border-purple-200', icon: RotateCcw };
        default: return { text: status, class: 'bg-slate-50 text-slate-700 border-slate-200', icon: Clock };
    }
};
</script>

<template>
    <Head :title="`GMP Area - ${type}`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Banner Header -->
        <div class="mb-6 rounded-3xl p-6 text-white shadow-md flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold flex items-center gap-3">
                    <MapPin class="w-8 h-8 text-sky-200" />
                    GMP Area: {{ type }}
                </h1>
                <p class="m-0 text-sm text-white/90">
                    Daftar riwayat inspeksi Good Mining Practice untuk kategori area {{ type }}.
                </p>
            </div>
            <Link :href="`/one-gmp/penilaian/${type}`" class="px-5 py-2.5 bg-white text-sky-900 font-extrabold rounded-2xl text-xs shadow hover:bg-slate-100 flex items-center gap-2">
                <Plus class="w-4 h-4 text-sky-600" /> Form Input {{ type }}
            </Link>
        </div>

        <!-- Search Bar -->
        <div class="mb-6 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
            <form @submit.prevent="handleSearch" class="flex gap-3 w-full max-w-md">
                <input v-model="search" type="text" placeholder="Cari nama lokasi, penilai, NRP..." class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-sky-500" />
                <button type="submit" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-xl text-xs flex items-center gap-1.5 shadow">
                    <Search class="w-3.5 h-3.5" /> Cari
                </button>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 font-bold text-slate-700">
                        <tr>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Minggu</th>
                            <th class="p-3">Lokasi / Equipment</th>
                            <th class="p-3">Penilai (NRP)</th>
                            <th class="p-3 text-center">Skor Akhir (%)</th>
                            <th class="p-3 text-center">Status Approval</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="records.data.length === 0">
                            <td colspan="6" class="p-6 text-center text-slate-400">Belum ada catatan inspeksi untuk area {{ type }}.</td>
                        </tr>
                        <tr v-for="r in records.data" :key="r.id" class="hover:bg-slate-50">
                            <td class="p-3 font-semibold text-slate-800">{{ r.tanggal_gmp }}</td>
                            <td class="p-3 font-mono text-slate-500">{{ r.week }}</td>
                            <td class="p-3 font-bold text-slate-900">{{ r.lokasi }}</td>
                            <td class="p-3 text-slate-600">{{ r.penilai }} <span class="text-slate-400">({{ r.nrp }})</span></td>
                            <td class="p-3 text-center font-black text-blue-600">{{ r.total_percent }}%</td>
                            <td class="p-3 text-center">
                                <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border', statusBadge(r.status).class]">
                                    <component :is="statusBadge(r.status).icon" class="w-3.5 h-3.5" />
                                    {{ statusBadge(r.status).text }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
