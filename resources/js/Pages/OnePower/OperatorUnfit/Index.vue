<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { UserPlus, Search } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import OperatorUnfitTable from './Partials/OperatorUnfitTable.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    unfits: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const searchInput = ref(props.search);

const submitFilter = () => {
    router.get('/one-power/deviasi-operator/unfit', { search: searchInput.value }, { preserveScroll: true, preserveState: true });
};

const handleEndUnfit = (row) => {
    if (! confirm(`Akhiri status unfit untuk "${row.nama}" hari ini?`)) return;
    router.post(`/one-power/deviasi-operator/unfit/${row.id}/end`, {}, { preserveScroll: true });
};

const handleDelete = (row) => {
    if (! confirm(`Hapus data unfit "${row.nama}"? Tindakan ini tidak bisa dibatalkan.`)) return;
    router.delete(`/one-power/deviasi-operator/unfit/${row.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Operator Unfit - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 text-xl font-extrabold text-slate-800">Data Unfit Karyawan</h1>
                <p class="m-0 text-sm text-slate-500">
                    Total ditemukan: <strong class="text-slate-700">{{ pagination.total }}</strong> data.
                </p>
            </div>

            <Link href="/one-power/deviasi-operator/unfit/create" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white hover:bg-blue-600">
                <UserPlus class="h-4 w-4" /> Tambah Data Unfit
            </Link>
        </div>

        <form class="mb-4 flex flex-wrap gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-md flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Cari NRP, nama, atau diagnosa..."
                    class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                >
            </div>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <OperatorUnfitTable :unfits="unfits" @end-unfit="handleEndUnfit" @delete="handleDelete" />

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                base-url="/one-power/deviasi-operator/unfit"
                :query="{ search }"
            />
        </div>
    </AppLayout>
</template>
