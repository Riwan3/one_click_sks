<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { UserPlus, FileUp, Download, Search, Trash2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import EmployeeTable from './Partials/EmployeeTable.vue';
import EmployeeImportModal from './Partials/EmployeeImportModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    jabatan: { type: String, default: '' },
    jabatanOptions: { type: Array, required: true },
    onlyInactive: { type: Boolean, required: true },
    employees: { type: Array, required: true },
    pagination: { type: Object, required: true },
});

const baseUrl = computed(() => (props.onlyInactive ? '/one-power/employees/inactive' : '/one-power/employees'));
const searchInput = ref(props.search);
const jabatanInput = ref(props.jabatan);

const submitFilter = () => {
    router.get(baseUrl.value, { search: searchInput.value, jabatan: jabatanInput.value }, { preserveScroll: true, preserveState: true });
};

const handleToggleStatus = async (row) => {
    const nextStatus = props.onlyInactive ? 'FIT' : 'INACTIVE';
    const confirmMsg = props.onlyInactive ? `Aktifkan kembali karyawan "${row.name}"?` : `Nonaktifkan karyawan "${row.name}"?`;
    if (! await confirmDialog(confirmMsg)) return;
    router.post(`/one-power/employees/${row.id}/status`, { status: nextStatus }, { preserveScroll: true });
};

const handleDelete = async (row) => {
    if (! await confirmDialog(`Hapus data karyawan "${row.name}"? Tindakan ini tidak bisa dibatalkan.`)) return;
    router.delete(`/one-power/employees/${row.id}`, { preserveScroll: true });
};

const importModalOpen = ref(false);
const importForm = useForm({ file: null });

const submitImport = () => {
    importForm.post('/one-power/employees/import', {
        preserveScroll: true,
        onSuccess: () => {
            importModalOpen.value = false;
            importForm.reset();
        },
    });
};

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if (searchInput.value) params.set('search', searchInput.value);
    if (jabatanInput.value) params.set('jabatan', jabatanInput.value);
    if (props.onlyInactive) params.set('only_inactive', '1');
    const qs = params.toString();
    return `/one-power/employees/export${qs ? `?${qs}` : ''}`;
});

const handleBulkDeleteInactive = async () => {
    const total = props.pagination.total;
    if (! await confirmDialog(`HAPUS SEMUA ${total} karyawan non-aktif? Tindakan ini TIDAK BISA dibatalkan.`)) return;
    router.post('/one-power/employees/bulk-delete-inactive', {}, { preserveScroll: true });
};

const handleBulkDeleteAll = async () => {
    const total = props.pagination.total;
    if (! await confirmDialog(`PERINGATAN KERAS: Hapus SEMUA data ${total} karyawan beserta akun user-nya? Tindakan ini sangat fatal dan TIDAK BISA dibatalkan.`)) return;
    router.post('/one-power/employees/bulk-delete-all', {}, { preserveScroll: true });
};
</script>

<template>
    <Head :title="`${onlyInactive ? 'Karyawan Non-Aktif' : 'Data Karyawan'} - OneClick`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 text-xl font-extrabold text-slate-800">
                    {{ onlyInactive ? 'Daftar Karyawan Non-Aktif' : 'Daftar Karyawan' }}
                </h1>
                <p class="m-0 text-sm text-slate-500">
                    Total ditemukan: <strong class="text-slate-700">{{ pagination.total }}</strong> data.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button
                    type="button"
                    class="flex items-center gap-1.5 rounded-lg bg-emerald-500 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-600"
                    @click="importModalOpen = true"
                >
                    <FileUp class="h-4 w-4" /> Import
                </button>
                <a
                    :href="exportUrl"
                    class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50"
                >
                    <Download class="h-4 w-4" /> Export
                </a>
                <button
                    v-if="onlyInactive && pagination.total > 0"
                    type="button"
                    class="flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-2 text-sm font-bold text-white hover:bg-red-700"
                    @click="handleBulkDeleteInactive"
                >
                    <Trash2 class="h-4 w-4" /> Hapus Semua Non-Aktif ({{ pagination.total }})
                </button>
                <button
                    v-if="!onlyInactive && pagination.total > 0"
                    type="button"
                    class="flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-2 text-sm font-bold text-white hover:bg-red-700"
                    @click="handleBulkDeleteAll"
                >
                    <Trash2 class="h-4 w-4" /> Hapus Semua Karyawan
                </button>
                <Link href="/one-power/employees/create" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white hover:bg-blue-600">
                    <UserPlus class="h-4 w-4" /> Tambah Karyawan
                </Link>
            </div>
        </div>

        <EmployeeImportModal :open="importModalOpen" :form="importForm" @close="importModalOpen = false" @submit="submitImport" />

        <form class="mb-4 flex flex-wrap gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-md flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Cari NRP atau Nama..."
                    class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                >
            </div>
            <select v-model="jabatanInput" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500" @change="submitFilter">
                <option value="">-- Semua Jabatan --</option>
                <option v-for="j in jabatanOptions" :key="j" :value="j">{{ j }}</option>
            </select>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <EmployeeTable :employees="employees" :only-inactive="onlyInactive" @toggle-status="handleToggleStatus" @delete="handleDelete" />

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                :base-url="baseUrl"
                :query="{ search, jabatan }"
            />
        </div>
    </AppLayout>
</template>
