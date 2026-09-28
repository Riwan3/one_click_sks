<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { CloudDownload, FilePlus, Lock, Search } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import SuratPeringatanTable from './Partials/SuratPeringatanTable.vue';
import SuratPeringatanImportModal from './Partials/SuratPeringatanImportModal.vue';
import SuratPeringatanSettingModal from './Partials/SuratPeringatanSettingModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    dateFrom: { type: String, default: '' },
    dateTo: { type: String, default: '' },
    suratPeringatans: { type: Array, required: true },
    pagination: { type: Object, required: true },
    canManage: { type: Boolean, default: true },
    documentSettings: { type: Object, default: () => ({}) },
});

const isAdmin = computed(() => {
    const role = String(props.user?.role || '').toLowerCase();
    const jabatan = String(props.user?.jabatan || '').toLowerCase();
    return role === 'admin' || role === 'admin_produksi' || role === 'management' || jabatan.includes('admin');
});

const baseUrl = '/one-power/pembinaan/surat-peringatan';
const searchInput = ref(props.search);
const dateFromInput = ref(props.dateFrom);
const dateToInput = ref(props.dateTo);

watch(() => props.search, (val) => { searchInput.value = val; });
watch(() => props.dateFrom, (val) => { dateFromInput.value = val; });
watch(() => props.dateTo, (val) => { dateToInput.value = val; });

const importModalOpen = ref(false);
const settingModalOpen = ref(false);

const submitFilter = () => {
    router.get(baseUrl, { search: searchInput.value, date_from: dateFromInput.value, date_to: dateToInput.value }, { preserveScroll: true, preserveState: true });
};

const handleDelete = async (row) => {
    if (! await confirmDialog(`Hapus surat peringatan "${row.nama}"? Tindakan ini tidak bisa dibatalkan.`)) return;
    router.delete(`${baseUrl}/${row.id}`, { preserveScroll: true });
};

const importForm = useForm({ file: null });

const submitImport = () => {
    importForm.post(`${baseUrl}/import`, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            importModalOpen.value = false;
            importForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Surat Peringatan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 text-xl font-extrabold text-slate-800">Daftar Surat Peringatan</h1>
                <p class="m-0 text-sm text-slate-500">
                    Kelola data Surat Peringatan Karyawan.
                </p>
            </div>

            <div v-if="canManage" class="flex flex-wrap gap-2">
                <button
                    v-if="isAdmin"
                    type="button"
                    class="flex items-center gap-1.5 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm font-bold text-amber-800 shadow-xs hover:bg-amber-100 transition"
                    @click="settingModalOpen = true"
                >
                    <Lock class="h-4 w-4 text-amber-600" /> Kunci Dokumen &amp; No. Surat
                </button>
                <button type="button" class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-700 shadow-sm" @click="importModalOpen = true">
                    <CloudDownload class="h-4 w-4" /> Import Excel
                </button>
                <Link :href="`${baseUrl}/create`" class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-2 text-sm font-bold text-white hover:bg-blue-600 shadow-sm">
                    <FilePlus class="h-4 w-4" /> Buat Surat Peringatan
                </Link>
            </div>
        </div>

        <form class="mb-4 flex flex-wrap gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-md flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input
                    v-model="searchInput"
                    type="text"
                    placeholder="Cari NRP, nama, atau no. SP..."
                    class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                >
            </div>
            <input v-model="dateFromInput" type="date" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500">
            <input v-model="dateToInput" type="date" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500">
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <SuratPeringatanTable :surat-peringatans="suratPeringatans" :can-manage="canManage" @delete="handleDelete" />

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                :base-url="baseUrl"
                :query="{ search, date_from: dateFrom, date_to: dateTo }"
            />
        </div>

        <SuratPeringatanImportModal :open="importModalOpen" :form="importForm" @close="importModalOpen = false" @submit="submitImport" />

        <SuratPeringatanSettingModal
            :open="settingModalOpen"
            :settings="documentSettings"
            @close="settingModalOpen = false"
        />
    </AppLayout>
</template>
