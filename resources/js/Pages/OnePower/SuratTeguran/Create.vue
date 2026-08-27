<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SuratTeguranForm from './Partials/SuratTeguranForm.vue';

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
    tanggal_kejadian: '',
    tempat_kejadian: '',
    jenis_absen: false,
    jenis_perilaku: false,
    jenis_ceroboh: false,
    jenis_hasil_kerja: false,
    jenis_menolak_perintah: false,
    jenis_pelanggaran_peraturan: false,
    penjelasan_singkat: '',
    kejadian_tanggal: '',
    kejadian_jam: '',
    kejadian_lokasi: '',
    dikeluarkan_di: '',
    dikeluarkan_tanggal: '',
    ttd_karyawan: '',
    ttd_atasan: '',
    ttd_atasan_atasan: '',
    catatan: '',
});

const submit = () => form.post('/one-power/pembinaan/surat-teguran');
</script>

<template>
    <Head title="Buat Surat Teguran - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Buat Surat Teguran</h1>
        <SuratTeguranForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
