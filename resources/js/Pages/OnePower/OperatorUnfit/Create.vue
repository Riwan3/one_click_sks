<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import OperatorUnfitForm from './Partials/OperatorUnfitForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    nrp: '',
    nama: '',
    perusahaan: 'PT. PUTRA PERKASA ABADI',
    departemen: '',
    contact: '',
    diagnosa: '',
    dokter_rujukan: '',
    tgl_mulai_unfit: '',
    tgl_akhir_unfit: '',
    laporan: '',
    bukti: null,
});

const submit = () => form.post('/one-power/deviasi-operator/unfit');
</script>

<template>
    <Head title="Tambah Data Unfit - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Tambah Data Unfit Karyawan</h1>
        <OperatorUnfitForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
