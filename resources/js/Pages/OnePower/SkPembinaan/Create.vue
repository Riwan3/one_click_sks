<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SkPembinaanForm from './Partials/SkPembinaanForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    employee_id: null,
    perusahaan: 'PT. PUTRA PERKASA ABADI',
    tanggal_pelanggaran: '',
    jam_pelanggaran: '',
    lokasi: '',
    berlaku_sampai: '',
    spdk_ref: '',
    dikeluarkan_di: '',
    dikeluarkan_tanggal: '',
    ttd_karyawan: '',
    ttd_atasan: '',
    pengawas_id: null,
    jenis_pelanggaran: '',
    lubang: 0,
    keterangan: '',
});

const submit = () => form.post('/one-power/pembinaan/sk-pembinaan');
</script>

<template>
    <Head title="Buat SK Pembinaan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Buat SK Pembinaan</h1>
        <SkPembinaanForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
