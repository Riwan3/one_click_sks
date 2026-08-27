<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { UserPlus, Search } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import IzinKaryawanTable from './Partials/IzinKaryawanTable.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    izins: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const searchInput = ref(props.filters.search);
const jenisInput = ref(props.filters.jenis_izin);
const statusInput = ref(props.filters.status);

const baseUrl = '/one-power/deviasi-operator/izin-karyawan';

const submitFilter = () => {
    router.get(baseUrl, { search: searchInput.value, jenis_izin: jenisInput.value, status: statusInput.value }, { preserveScroll: true, preserveState: true });
};

const doAction = (action, row, confirmMsg) => {
    if (confirmMsg && ! confirm(confirmMsg)) return;
    router.post(`${baseUrl}/${row.id}/${action}`, {}, { preserveScroll: true });
};

const handleApprove = (row) => doAction('approve', row, `Setujui pengajuan izin "${row.nama}"?`);
const handleDecline = (row) => doAction('decline', row, `Tolak pengajuan izin "${row.nama}"?`);
const handleReset = (row) => doAction('reset', row, `Reset status pengajuan "${row.nama}" ke Menunggu Persetujuan?`);
const handleCancel = (row) => doAction('cancel', row, `Batalkan pengajuan izin "${row.nama}"?`);

const handleDelete = (row) => {
    if (! confirm(`Hapus data izin "${row.nama}"? Tindakan ini tidak bisa dibatalkan.`)) return;
    router.delete(`${baseUrl}/${row.id}`, { preserveScroll: true });
};
</script>

<template>
    <Head title="Izin Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 text-xl font-extrabold text-slate-800">Izin Karyawan</h1>
                <p class="m-0 text-sm text-slate-500">
                    Total ditemukan: <strong class="text-slate-700">{{ pagination.total }}</strong> data.
                </p>
            </div>

            <Link :href="`${baseUrl}/create`" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white hover:bg-blue-600">
                <UserPlus class="h-4 w-4" /> Ajukan Izin
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
            <select v-model="jenisInput" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500" @change="submitFilter">
                <option value="">-- Semua Jenis --</option>
                <option value="izin">Izin</option>
                <option value="sakit">Sakit</option>
                <option value="emergency">Emergency</option>
            </select>
            <select v-model="statusInput" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500" @change="submitFilter">
                <option value="">-- Semua Status --</option>
                <option value="Awaiting Approval">Menunggu Persetujuan</option>
                <option value="Approved">Disetujui</option>
                <option value="Declined">Ditolak</option>
                <option value="Cancelled">Dibatalkan</option>
            </select>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <IzinKaryawanTable
            :izins="izins"
            @approve="handleApprove"
            @decline="handleDecline"
            @reset="handleReset"
            @cancel="handleCancel"
            @delete="handleDelete"
        />

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                :base-url="baseUrl"
                :query="{ search: filters.search, jenis_izin: filters.jenis_izin, status: filters.status }"
            />
        </div>
    </AppLayout>
</template>
