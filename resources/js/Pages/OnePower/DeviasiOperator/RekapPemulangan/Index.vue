<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Users,
    Calendar,
    Sun,
    Moon,
    Search,
    Plus,
    UploadCloud,
    RotateCcw,
    Clock,
    CheckCircle2,
    Edit3,
    Trash2,
    Inbox,
} from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/ui/Pagination.vue';
import { confirmDialog } from '@/utils/confirm';
import RekapPemulanganModal from './Partials/RekapPemulanganModal.vue';
import RekapPemulanganImportModal from './Partials/RekapPemulanganImportModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({ total: 0, today: 0, shift_d: 0, shift_n: 0 }) },
    data: { type: Array, default: () => [] },
    pagination: { type: Object, required: true },
});

const filterForm = ref({
    search: props.filters.search || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    shift: props.filters.shift || '',
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const selectedItem = ref(null);
const isImportModalOpen = ref(false);

const applyFilter = () => {
    router.get('/one-power/deviasi-operator/rekap-pemulangan', filterForm.value, {
        preserveScroll: true,
        preserveState: true,
    });
};

const resetFilter = () => {
    filterForm.value = {
        search: '',
        date_from: '',
        date_to: '',
        shift: '',
    };
    applyFilter();
};

const openAddModal = () => {
    selectedItem.value = null;
    isEditMode.value = false;
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    selectedItem.value = item;
    isEditMode.value = true;
    isModalOpen.value = true;
};

const handleDelete = async (item) => {
    if (!await confirmDialog(`Hapus data pemulangan untuk "${item.nama}" (${item.nrp})? Tindakan ini tidak bisa dibatalkan.`)) {
        return;
    }

    router.delete(`/one-power/deviasi-operator/rekap-pemulangan/${item.id}`, {
        preserveScroll: true,
    });
};

const calculateRowNo = (index) => {
    return ((props.pagination.currentPage || 1) - 1) * (props.pagination.perPage || 15) + index + 1;
};
</script>

<template>
    <Head title="Rekap Pemulangan Operator - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Header -->
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                        🚪
                    </span>
                    Rekap Pemulangan Operator
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Kelola dan pantau data pemulangan operator saat jam operasional.
                </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm hover:bg-slate-50 hover:border-slate-300 transition"
                    @click="isImportModalOpen = true"
                >
                    <UploadCloud class="h-4 w-4 text-blue-600" /> Import Excel / CSV
                </button>
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition"
                    @click="openAddModal"
                >
                    <Plus class="h-4 w-4" /> Tambah Pemulangan
                </button>
            </div>
        </div>

        <!-- 4 Stat Cards -->
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <Users class="h-5 w-5" />
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500">Total Pemulangan</div>
                    <div class="text-xl font-black text-slate-800">{{ stats.total?.toLocaleString() || 0 }}</div>
                </div>
            </div>

            <div class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                    <Calendar class="h-5 w-5" />
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500">Hari Ini</div>
                    <div class="text-xl font-black text-slate-800">{{ stats.today?.toLocaleString() || 0 }}</div>
                </div>
            </div>

            <div class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <Sun class="h-5 w-5" />
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500">Shift Day (D)</div>
                    <div class="text-xl font-black text-slate-800">{{ stats.shift_d?.toLocaleString() || 0 }}</div>
                </div>
            </div>

            <div class="flex items-center gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <Moon class="h-5 w-5" />
                </div>
                <div>
                    <div class="text-xs font-semibold text-slate-500">Shift Night (N)</div>
                    <div class="text-xl font-black text-slate-800">{{ stats.shift_n?.toLocaleString() || 0 }}</div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-5 rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-sm">
            <form class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-5" @submit.prevent="applyFilter">
                <div class="relative lg:col-span-2">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="filterForm.search"
                        type="text"
                        placeholder="Cari NRP, Nama, Keterangan..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-9 pr-3 text-xs outline-none focus:border-blue-500 focus:bg-white transition"
                    >
                </div>

                <div>
                    <input
                        v-model="filterForm.date_from"
                        type="date"
                        title="Dari Tanggal"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs outline-none focus:border-blue-500 focus:bg-white transition"
                    >
                </div>

                <div>
                    <input
                        v-model="filterForm.date_to"
                        type="date"
                        title="Sampai Tanggal"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs outline-none focus:border-blue-500 focus:bg-white transition"
                    >
                </div>

                <div class="flex items-center gap-1.5">
                    <select
                        v-model="filterForm.shift"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs outline-none focus:border-blue-500 focus:bg-white transition"
                    >
                        <option value="">Semua Shift</option>
                        <option value="D">Shift D (Day)</option>
                        <option value="N">Shift N (Night)</option>
                    </select>
                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition"
                    >
                        Filter
                    </button>
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 bg-white px-2.5 py-2 text-xs text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition"
                        title="Reset Filter"
                        @click="resetFilter"
                    >
                        <RotateCcw class="h-3.5 w-3.5" />
                    </button>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="py-3 px-4 text-center w-12">NO</th>
                            <th class="py-3 px-4">NRP</th>
                            <th class="py-3 px-4">NAMA</th>
                            <th class="py-3 px-4">JABATAN</th>
                            <th class="py-3 px-4">TANGGAL</th>
                            <th class="py-3 px-4 text-center">SHIFT</th>
                            <th class="py-3 px-4">JAM PULANG</th>
                            <th class="py-3 px-4">KETERANGAN</th>
                            <th class="py-3 px-4">KONFIRMASI</th>
                            <th class="py-3 px-4 text-center w-24">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <tr v-if="data.length === 0">
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                <Inbox class="mx-auto h-8 w-8 text-slate-300 mb-2" />
                                Belum ada data pemulangan yang tercatat.
                            </td>
                        </tr>

                        <tr
                            v-for="(row, idx) in data"
                            :key="row.id"
                            class="hover:bg-slate-50/80 transition"
                        >
                            <td class="py-3 px-4 text-center font-semibold text-slate-400">
                                {{ calculateRowNo(idx) }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ row.nrp }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ row.nama }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ row.jabatan || '-' }}
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-800">
                                {{ row.tanggal }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span
                                    v-if="row.shift === 'D'"
                                    class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700 border border-amber-200/50"
                                >
                                    <Sun class="h-3 w-3 text-amber-500" /> D
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-700 border border-slate-200"
                                >
                                    <Moon class="h-3 w-3 text-slate-500" /> N
                                </span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                <div class="flex items-center gap-1">
                                    <Clock class="h-3.5 w-3.5 text-slate-400" />
                                    {{ row.jam }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-slate-600 max-w-xs truncate" :title="row.keterangan">
                                {{ row.keterangan || '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <span
                                    v-if="row.konfirmasi"
                                    class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 border border-emerald-200/60"
                                >
                                    <CheckCircle2 class="h-3 w-3 text-emerald-500" />
                                    {{ row.konfirmasi }}
                                </span>
                                <span v-else class="text-slate-400">-</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        type="button"
                                        title="Edit"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-amber-600 hover:bg-amber-50 hover:border-amber-300 transition"
                                        @click="openEditModal(row)"
                                    >
                                        <Edit3 class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        type="button"
                                        title="Hapus"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-rose-600 hover:bg-rose-50 hover:border-rose-300 transition"
                                        @click="handleDelete(row)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer / Pagination -->
            <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500">
                <div>
                    Menampilkan data ke <strong class="text-slate-700">{{ data.length ? calculateRowNo(0) : 0 }}</strong> -
                    <strong class="text-slate-700">{{ data.length ? calculateRowNo(data.length - 1) : 0 }}</strong> dari
                    <strong class="text-slate-700">{{ pagination.total }}</strong> total data
                </div>

                <Pagination
                    :current-page="pagination.currentPage"
                    :last-page="pagination.lastPage"
                    base-url="/one-power/deviasi-operator/rekap-pemulangan"
                    :query="filterForm"
                />
            </div>
        </div>

        <!-- Add / Edit Modal -->
        <RekapPemulanganModal
            :open="isModalOpen"
            :is-edit="isEditMode"
            :item="selectedItem"
            @close="isModalOpen = false"
            @saved="isModalOpen = false"
        />

        <!-- Import Modal -->
        <RekapPemulanganImportModal
            :open="isImportModalOpen"
            @close="isImportModalOpen = false"
            @imported="isImportModalOpen = false"
        />
    </AppLayout>
</template>
