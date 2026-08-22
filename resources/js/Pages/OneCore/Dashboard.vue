<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Lock, UserPlus, FileUp, Search } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import UserTable from './Partials/UserTable.vue';
import PasswordInfoModal from './Partials/PasswordInfoModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    users: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const searchInput = ref(props.search);
const passwordRow = ref(null);

const submitSearch = () => {
    router.get('/one-core', { search: searchInput.value }, { preserveScroll: true, preserveState: true });
};

const handleLock = () => {
    router.post('/one-core/lock');
};

const handleDelete = (row) => {
    if (! confirm(`Hapus user "${row.name}"?`)) return;
    router.delete(`/one-core/users/${row.id}`, { preserveScroll: true });
};

const resetPassword = (row) => {
    router.post(`/one-core/users/${row.id}/reset-password`, {}, { preserveScroll: true });
    passwordRow.value = null;
};
</script>

<template>
    <Head title="One-Core & Value - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 text-xl font-extrabold text-slate-800">Manajemen Pengguna</h1>
                <p class="m-0 text-sm text-slate-500">
                    Total ditemukan: <strong class="text-slate-700">{{ pagination.total }}</strong> akun.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" class="flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50" @click="handleLock">
                    <Lock class="h-4 w-4" /> Kunci Halaman
                </button>
                <button type="button" title="Segera hadir" class="flex cursor-not-allowed items-center gap-1.5 rounded-lg bg-emerald-500/50 px-3 py-2 text-sm font-bold text-white">
                    <FileUp class="h-4 w-4" /> Import
                </button>
                <Link href="/one-core/users/create" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white hover:bg-blue-600">
                    <UserPlus class="h-4 w-4" /> Tambah User
                </Link>
            </div>
        </div>

        <form class="mb-4 flex gap-2" @submit.prevent="submitSearch">
            <div class="relative flex-1 max-w-md">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Cari Username, NRP, atau Nama..."
                    class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                >
            </div>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Cari User</button>
        </form>

        <UserTable :users="users" @show-password="(row) => (passwordRow = row)" @delete="handleDelete" />

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                base-url="/one-core"
                :query="{ search }"
            />
        </div>

        <PasswordInfoModal :row="passwordRow" @close="passwordRow = null" @reset="resetPassword" />
    </AppLayout>
</template>
