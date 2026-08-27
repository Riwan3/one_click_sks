<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { FilePlus, Search } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import FatigueInterviewTable from './Partials/FatigueInterviewTable.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    dateFrom: { type: String, default: '' },
    dateTo: { type: String, default: '' },
    items: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const baseUrl = '/one-power/pembinaan/interview-fatigue';
const searchInput = ref(props.search);
const dateFromInput = ref(props.dateFrom);
const dateToInput = ref(props.dateTo);

const submitFilter = () => {
    router.get(baseUrl, { search: searchInput.value, date_from: dateFromInput.value, date_to: dateToInput.value }, { preserveScroll: true, preserveState: true });
};

const handleDelete = (row) => {
    if (! confirm(`Hapus interview fatigue "${row.noSurat}"? Tindakan ini tidak bisa dibatalkan.`)) return;
    router.delete(`${baseUrl}/${row.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Interview Fatigue - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 text-xl font-extrabold text-slate-800">Interview Fatigue</h1>
                <p class="m-0 text-sm text-slate-500">
                    Total ditemukan: <strong class="text-slate-700">{{ pagination.total }}</strong> data.
                </p>
            </div>

            <Link :href="`${baseUrl}/create`" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white hover:bg-blue-600">
                <FilePlus class="h-4 w-4" /> Buat Interview Fatigue
            </Link>
        </div>

        <form class="mb-4 flex flex-wrap gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-md flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Cari NRP, nama, atau no. surat..."
                    class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                >
            </div>
            <input v-model="dateFromInput" type="date" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500">
            <input v-model="dateToInput" type="date" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500">
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <FatigueInterviewTable :items="items" @delete="handleDelete" />

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                :base-url="baseUrl"
                :query="{ search, date_from: dateFrom, date_to: dateTo }"
            />
        </div>
    </AppLayout>
</template>
