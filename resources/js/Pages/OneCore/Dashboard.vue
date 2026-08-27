<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Lock, FileUp, Search, UserPlus } from 'lucide-vue-next';
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
    roles: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const searchInput = ref(props.search);
const passwordRow = ref(null);
const isAddUserOpen = ref(false);

const newUserForm = useForm({
    username: '',
    name: '',
    role: 'tamu',
    email: '',
    password: '',
});

const submitSearch = () => {
    router.get('/one-core', { search: searchInput.value }, { preserveScroll: true, preserveState: true });
};

const handleLock = () => {
    router.post('/one-core/lock');
};

const resetPassword = (row) => {
    router.post(`/one-core/users/${row.id}/reset-password`, {}, { preserveScroll: true });
    passwordRow.value = null;
};

const submitNewUser = () => {
    newUserForm.post('/one-core/users', {
        preserveScroll: true,
        onSuccess: () => {
            isAddUserOpen.value = false;
            newUserForm.reset();
        },
    });
};
</script>

<template>
    <Head title="One-Core & Value - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 text-xl font-extrabold text-slate-800">Manajemen Pengguna</h1>
                <p class="m-0 text-sm text-slate-500">
                    Total ditemukan: <strong class="text-slate-700">{{ pagination.total }}</strong> akun. Data karyawan baru otomatis dapat akun login — kelola lewat
                    <Link href="/one-power/employees" class="font-semibold text-blue-600 hover:underline">Data Karyawan</Link>.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" class="flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50" @click="handleLock">
                    <Lock class="h-4 w-4" /> Kunci Halaman
                </button>
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 px-3 py-2 text-sm font-bold text-white shadow-sm transition" @click="isAddUserOpen = true">
                    <UserPlus class="h-4 w-4" /> Tambah User
                </button>
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

        <UserTable :users="users" :roles="roles" @show-password="(row) => (passwordRow = row)" />

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                base-url="/one-core"
                :query="{ search }"
            />
        </div>

        <PasswordInfoModal :row="passwordRow" @close="passwordRow = null" @reset="resetPassword" />

        <!-- MODAL TAMBAH USER -->
        <div v-if="isAddUserOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50">
                    <h3 class="font-bold text-slate-800">Tambah Akun Pengguna Baru</h3>
                    <button @click="isAddUserOpen = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>

                <form @submit.prevent="submitNewUser" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Username / NRP</label>
                        <input v-model="newUserForm.username" type="text" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Contoh: 21000123" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
                        <input v-model="newUserForm.name" type="text" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Nama karyawan / pengguna" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Role Akses</label>
                        <select v-model="newUserForm.role" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm capitalize">
                            <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email (Opsional)</label>
                        <input v-model="newUserForm.email" type="email" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="email@domain.com" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Password (Kosongkan jika default NRP)</label>
                        <input v-model="newUserForm.password" type="password" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="Default: Sesuai Username / NRP" />
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="isAddUserOpen = false" class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">Batal</button>
                        <button type="submit" :disabled="newUserForm.processing" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm">
                            Simpan Akun
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
