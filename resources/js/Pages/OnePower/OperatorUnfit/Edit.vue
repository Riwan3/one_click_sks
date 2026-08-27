<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import OperatorUnfitForm from './Partials/OperatorUnfitForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    targetUnfit: { type: Object, required: true },
});

const form = useForm({
    nrp: props.targetUnfit.nrp,
    nama: props.targetUnfit.nama,
    perusahaan: props.targetUnfit.perusahaan,
    departemen: props.targetUnfit.departemen ?? '',
    contact: props.targetUnfit.contact ?? '',
    diagnosa: props.targetUnfit.diagnosa ?? '',
    dokter_rujukan: props.targetUnfit.dokterRujukan ?? '',
    tgl_mulai_unfit: props.targetUnfit.tglMulaiUnfit,
    tgl_akhir_unfit: props.targetUnfit.tglAkhirUnfit ?? '',
    laporan: props.targetUnfit.laporan ?? '',
    bukti: null,
});

const submit = () => form
    .transform((data) => ({ ...data, _method: 'put' }))
    .post(`/one-power/deviasi-operator/unfit/${props.targetUnfit.id}`);
</script>

<template>
    <Head title="Edit Data Unfit - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Data Unfit Karyawan</h1>
        <OperatorUnfitForm :form="form" :existing-bukti-url="targetUnfit.buktiUrl" :on-submit="submit" />
    </AppLayout>
</template>
