<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/ui/StatCard.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import ProcedureTable from './Partials/ProcedureTable.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    stats: { type: Object, required: true },
    rows: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const searchInput = ref(props.search);

const submitSearch = () => {
    router.get('/one-procedure', { search: searchInput.value }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <Head title="One-Procedure - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5">
            <h1 class="mb-1 text-xl font-extrabold text-slate-800">Prosedur Operasional</h1>
            <p class="m-0 text-sm text-slate-500">Kelola prosedur, activity, dan evaluasi prosedur</p>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <StatCard label="Total Prosedur" :value="stats.totalProsedur" color="primary" />
            <StatCard label="Aktif" :value="stats.totalAktif" color="success" />
            <StatCard label="Perlu Review" :value="stats.totalReview" color="warning" />
            <StatCard label="Total Kategori" :value="stats.totalKategori" color="danger" />
        </div>

        <form class="mb-4 flex gap-2" @submit.prevent="submitSearch">
            <div class="relative max-w-md flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Cari nama prosedur atau kategori..."
                    class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-emerald-500"
                >
            </div>
            <button type="submit" class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-600">Cari</button>
        </form>

        <ProcedureTable :rows="rows" />

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                base-url="/one-procedure"
                :query="{ search }"
            />
        </div>
    </AppLayout>
</template>
