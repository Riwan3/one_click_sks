<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { CalendarRange, CloudDownload, Search } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import RosterTable from './Partials/RosterTable.vue';
import RosterImportModal from './Partials/RosterImportModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    rows: { type: Array, required: true },
});

const monthInput = ref(props.filters.bulan);
const yearInput = ref(props.filters.tahun);
const searchInput = ref(props.filters.search);
const categoryInput = ref(props.filters.kategori);

const submitFilter = () => {
    router.get('/one-power/roster', {
        bulan: monthInput.value,
        tahun: yearInput.value,
        search: searchInput.value,
        kategori: categoryInput.value,
    }, { preserveScroll: true, preserveState: true });
};

const importModalOpen = ref(false);
const importForm = useForm({ file: null });

const submitImport = () => {
    importForm.post('/one-power/roster/import', {
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
    <Head title="Roster Bulanan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800">
                    <CalendarRange class="h-5 w-5 text-blue-500" /> Roster Bulanan
                </h1>
                <p class="m-0 text-sm text-slate-500">Klik sel untuk mengubah kode roster harian karyawan.</p>
            </div>
            <button type="button" class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-700" @click="importModalOpen = true">
                <CloudDownload class="h-4 w-4" /> Import Excel
            </button>
        </div>

        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <select v-model.number="monthInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option v-for="(name, num) in filters.monthNames" :key="num" :value="Number(num)">{{ name }}</option>
            </select>
            <select v-model.number="yearInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option v-for="y in filters.years" :key="y" :value="y">{{ y }}</option>
            </select>
            <select v-model="categoryInput" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option :value="null">Semua Kategori</option>
                <option v-for="opt in filters.kategoriOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
            <div class="relative w-full max-w-xs flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari NRP atau nama..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <RosterTable :rows="rows" />

        <RosterImportModal :open="importModalOpen" :form="importForm" @close="importModalOpen = false" @submit="submitImport" />
    </AppLayout>
</template>
