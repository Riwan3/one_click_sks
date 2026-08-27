<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PemulanganKaryawanForm from './Partials/PemulanganKaryawanForm.vue';

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
    departemen: '',
    lokasi: '',
    tanggal: '',
    indikasi_fatigue: '',
    jam_tidur_kurang_4jam: false,
    fatigue_berat: false,
    deskripsi_penyebab: '',
    alamat_pengantaran: '',
    penanggungjawab_pengantaran: '',
    ttd_karyawan: '',
    ttd_pengawas: '',
});

const submit = () => form.post('/one-power/pembinaan/pemulangan-karyawan');
</script>

<template>
    <Head title="Buat Pemulangan Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Buat Pemulangan Karyawan</h1>
        <PemulanganKaryawanForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
