<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PemanggilanKaryawanForm from './Partials/PemanggilanKaryawanForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    employee_id: null,
    nrp: '',
    nama: '',
    jabatan: '',
    alasan_pemanggilan: '',
    tempat: '',
    jam_pemanggilan: '',
    tanggal_pemanggilan: '',
    nama_manpower: '',
    fatigue_periode: '',
    ttd_pemanggil: '',
    ttd_karyawan: '',
});

const submit = () => form.post('/one-power/pembinaan/pemanggilan-karyawan');
</script>

<template>
    <Head title="Buat Pemanggilan Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Buat Pemanggilan Karyawan</h1>
        <PemanggilanKaryawanForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
