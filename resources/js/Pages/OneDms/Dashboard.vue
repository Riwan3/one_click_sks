<script setup>
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Toolbar from './Partials/Toolbar.vue';
import MetaHeader from './Partials/MetaHeader.vue';
import DmsTable from './Partials/DmsTable.vue';
import TallySummary from './Partials/TallySummary.vue';
import DmsFormModal from './Partials/DmsFormModal.vue';
import PhotoPreviewModal from './Partials/PhotoPreviewModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    petugasOptions: { type: Array, required: true },
    formOptions: { type: Object, required: true },
    rows: { type: Array, required: true },
    tally: { type: Object, required: true },
});

const rows = ref(props.rows.map((row) => ({ ...row })));
const editingRow = ref(null);
const showModal = ref(false);
const previewSrc = ref(null);

const unitLookup = computed(() => Object.fromEntries(
    rows.value.map((row) => [row.noUnit, { nama: row.namaOperator, nrp: row.nrp }]),
));

const tally = computed(() => {
    const counts = Object.fromEntries(Object.keys(props.tally).map((key) => [key, 0]));
    rows.value.forEach((row) => {
        if (counts[row.jenisDeviasi] !== undefined) counts[row.jenisDeviasi]++;
    });
    return counts;
});

const openAdd = () => {
    editingRow.value = null;
    showModal.value = true;
};

const openEdit = (row) => {
    editingRow.value = row;
    showModal.value = true;
};

// TODO: kirim ke backend begitu logic simpan data DMS siap — untuk sekarang hanya mengubah state lokal.
const saveRow = (data) => {
    if (data.id) {
        const index = rows.value.findIndex((row) => row.id === data.id);
        if (index !== -1) rows.value[index] = { ...data };
    } else {
        rows.value.push({ ...data, id: Math.max(0, ...rows.value.map((row) => row.id)) + 1 });
    }
    showModal.value = false;
};
</script>

<template>
    <Head title="One-DMS - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <Toolbar :filters="filters" @add="openAdd" />

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <MetaHeader :tanggal="filters.tanggal" :shift-label="filters.shiftOptions[filters.shift]" :petugas-options="petugasOptions" />

            <div class="mb-5">
                <DmsTable :rows="rows" @edit="openEdit" @preview-photo="(src) => (previewSrc = src)" />
            </div>

            <TallySummary :tally="tally" :tanggal="filters.tanggal" :shift-label="filters.shiftOptions[filters.shift]" />
        </div>

        <DmsFormModal
            :row="editingRow"
            :open="showModal"
            :form-options="formOptions"
            :unit-lookup="unitLookup"
            @close="showModal = false"
            @save="saveRow"
        />
        <PhotoPreviewModal :src="previewSrc" @close="previewSrc = null" />
    </AppLayout>
</template>
