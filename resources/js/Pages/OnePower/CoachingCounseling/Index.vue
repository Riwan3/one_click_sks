<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { CloudDownload, FilePlus, Lock, Printer, Search, Users, Sparkles } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import CoachingCounselingTable from './Partials/CoachingCounselingTable.vue';
import CoachingCounselingImportModal from './Partials/CoachingCounselingImportModal.vue';
import CoachingCounselingSettingModal from './Partials/CoachingCounselingSettingModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    dateFrom: { type: String, default: '' },
    dateTo: { type: String, default: '' },
    items: { type: Array, required: true },
    pagination: { type: Object, required: true },
    canManage: { type: Boolean, default: true },
    documentSettings: { type: Object, default: () => ({}) },
    gl: { type: Object, default: null },
});

const isAdmin = computed(() => {
    const role = String(props.user?.role || '').toLowerCase();
    const jabatan = String(props.user?.jabatan || '').toLowerCase();
    return role === 'admin' || role === 'admin_produksi' || role === 'management' || jabatan.includes('admin');
});

const glData = computed(() => props.gl?.data ?? props.gl ?? null);
const isGroupLeader = computed(() => {
    const role = String(props.user?.role || '').toLowerCase();
    const jabatan = String(props.user?.jabatan || '').toUpperCase();
    return role === 'group-leader' || role === 'groupleader' || role === 'gl' || jabatan.includes('GROUP LEADER');
});

const baseUrl = '/one-power/pembinaan/coaching-counseling';
const searchInput = ref(props.search);
const dateFromInput = ref(props.dateFrom);
const dateToInput = ref(props.dateTo);

watch(() => props.search, (val) => { searchInput.value = val; });
watch(() => props.dateFrom, (val) => { dateFromInput.value = val; });
watch(() => props.dateTo, (val) => { dateToInput.value = val; });

const hasActiveFilter = computed(() => {
    return Boolean(searchInput.value) || Boolean(dateFromInput.value) || Boolean(dateToInput.value);
});

const submitFilter = () => {
    router.get(baseUrl, {
        search: searchInput.value,
        date_from: dateFromInput.value,
        date_to: dateToInput.value,
    }, { preserveScroll: true, preserveState: true });
};

const resetFilter = () => {
    searchInput.value = '';
    dateFromInput.value = '';
    dateToInput.value = '';
    router.get(baseUrl, {}, { preserveScroll: true, preserveState: true });
};

const handleDelete = async (row) => {
    if (! await confirmDialog(`Hapus data coaching & counseling "${row.nomorSurat}"? Tindakan ini tidak bisa dibatalkan.`)) return;
    router.delete(`${baseUrl}/${row.id}`, { preserveScroll: true });
};

const importModalOpen = ref(false);
const settingModalOpen = ref(false);
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
    <Head title="Coaching & Counseling - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" :back-url="isGroupLeader ? '/portal/data' : undefined">
        <!-- Header Banner -->
        <div class="mb-5 rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 font-bold">
                        <Users class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="m-0 text-lg sm:text-xl font-black text-slate-800 tracking-tight">
                                Coaching &amp; Counseling
                            </h1>
                            <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 border border-blue-200">
                                {{ pagination.total }} Data
                            </span>
                            <span v-if="glData" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200">
                                GL: {{ glData.name }}
                            </span>
                        </div>
                        <p class="mt-1 mb-0 text-xs sm:text-sm text-slate-500 font-medium">
                            Pembinaan &amp; pendampingan karyawan / anak asuh.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center sm:gap-2">
                    <a
                        :href="`${baseUrl}/print-form`"
                        target="_blank"
                        class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition"
                    >
                        <Printer class="h-4 w-4 text-slate-500" />
                        <span>Form Kosong</span>
                    </a>
                    <template v-if="canManage">
                        <button
                            v-if="isAdmin"
                            type="button"
                            class="col-span-2 sm:col-span-1 flex items-center justify-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-amber-800 shadow-xs hover:bg-amber-100 transition"
                            @click="settingModalOpen = true"
                        >
                            <Lock class="h-4 w-4 text-amber-600" />
                            <span>Kunci Dokumen</span>
                        </button>
                        <button 
                            v-if="isAdmin"
                            type="button" 
                            class="col-span-2 sm:col-span-1 flex items-center justify-center gap-1.5 rounded-xl bg-emerald-700 px-3.5 py-2.5 text-xs sm:text-sm font-bold text-white hover:bg-emerald-800 shadow-sm transition" 
                            @click="importModalOpen = true"
                        >
                            <CloudDownload class="h-4 w-4" />
                            <span>Import Excel</span>
                        </button>
                        <Link
                            :href="`${baseUrl}/create`"
                            class="col-span-1 sm:col-span-1 flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs sm:text-sm font-bold text-white hover:bg-blue-700 shadow-sm shadow-blue-200 transition"
                        >
                            <FilePlus class="h-4 w-4" />
                            <span>Buat CC</span>
                        </Link>
                    </template>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-4 rounded-2xl border border-slate-200/80 bg-white p-3.5 sm:p-4 shadow-sm">
            <form class="flex flex-col gap-2.5 lg:flex-row lg:items-center" @submit.prevent="submitFilter">
                <div class="relative flex-1">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Cari NRP, nama, atau no. surat..."
                        class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-xs sm:text-sm outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition"
                    >
                </div>
                <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                    <div class="relative">
                        <input
                            v-model="dateFromInput"
                            type="date"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs sm:text-sm text-slate-700 outline-none focus:border-blue-500"
                            title="Tanggal Mulai"
                        >
                    </div>
                    <div class="relative">
                        <input
                            v-model="dateToInput"
                            type="date"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs sm:text-sm text-slate-700 outline-none focus:border-blue-500"
                            title="Tanggal Akhir"
                        >
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="submit"
                        class="flex-1 sm:flex-initial flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-blue-700 transition shadow-sm"
                    >
                        <Search class="h-3.5 w-3.5" />
                        <span>Filter</span>
                    </button>
                    <button
                        v-if="hasActiveFilter"
                        type="button"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs sm:text-sm font-bold text-slate-600 hover:bg-slate-100 transition"
                        @click="resetFilter"
                    >
                        Reset
                    </button>
                </div>
            </form>
        </div>

        <CoachingCounselingTable :items="items" :can-manage="canManage" @delete="handleDelete" />

        <div class="mt-4">
            <Pagination
                :current-page="pagination.currentPage"
                :last-page="pagination.lastPage"
                :base-url="baseUrl"
                :query="{ search, date_from: dateFrom, date_to: dateTo }"
            />
        </div>

        <CoachingCounselingImportModal :open="importModalOpen" :form="importForm" @close="importModalOpen = false" @submit="submitImport" />
        <CoachingCounselingSettingModal :open="settingModalOpen" :settings="documentSettings" @close="settingModalOpen = false" />
    </AppLayout>
</template>
