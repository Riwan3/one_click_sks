<script setup>
import { confirmDialog } from '@/utils/confirm';
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
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
    petugas: { type: Object, required: true },
    petugasOptions: { type: Array, required: true },
    formOptions: { type: Object, required: true },
    rows: { type: Array, required: true },
    tally: { type: Object, required: true },
    rosterLookup: { type: Object, required: true },
    docSettings: { type: Object, required: true },
});

const rows = ref(props.rows.map((row) => ({ ...row })));
watch(() => props.rows, (newRows) => {
    rows.value = newRows.map((row) => ({ ...row }));
});

const editingRow = ref(null);
const showModal = ref(false);
const previewSrc = ref(null);

const unitLookup = computed(() => {
    const lookup = { ...props.rosterLookup };
    props.rows.forEach((row) => {
        if (!lookup[row.noUnit]) {
            lookup[row.noUnit] = { nama: row.namaOperator, nrp: row.nrp, gl: row.groupLeader };
        }
    });
    return lookup;
});

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

const saveRow = (data) => {
    router.post('/one-dms/store', {
        id: data.id || null,
        tanggal: props.filters.tanggal,
        shift: props.filters.shift,
        unit: data.noUnit,
        nama_operator: data.namaOperator,
        nrp: data.nrp,
        area: data.area,
        waktu: data.waktu,
        jenis_deviasi: data.jenisDeviasi,
        deviasi_ke: data.deviasiKe,
        temuan: data.temuan,
        evidence_icc: data.evidenceIcc,
        followup_hasil: data.followUp1,
        followup_hasil_2: data.followUp2,
        note_intervensi: data.note,
        sumber: data.sumber,
        group_leader: data.groupLeader,
        status: data.status,
        remark: data.remark,
    }, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
            showModal.value = false;
        },
    });
};

const confirmDelete = async (row) => {
    if (await confirmDialog('Yakin ingin menghapus data DMS ini?')) {
        router.delete(`/one-dms/delete/${row.id}`, {
            preserveScroll: true,
            preserveState: false
        });
    }
};
</script>

<template>
    <Head title="Formulir DMS - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <Toolbar :filters="filters" @add="openAdd" />

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <MetaHeader
                :tanggal="filters.tanggal"
                :shift="filters.shift"
                :shift-label="filters.shiftOptions[filters.shift]"
                :petugas="petugas"
                :petugas-options="petugasOptions"
                :doc-settings="docSettings"
            />

            <div class="mb-5">
                <DmsTable :rows="rows" @edit="openEdit" @delete="confirmDelete" @preview-photo="(src) => (previewSrc = src)" />
            </div>

            <TallySummary :tally="tally" :tanggal="filters.tanggal" :shift-label="filters.shiftOptions[filters.shift]" :tally-title="docSettings.tallyTitle" />
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
