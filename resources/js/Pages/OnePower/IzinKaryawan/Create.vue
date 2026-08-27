<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import IzinKaryawanForm from './Partials/IzinKaryawanForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    isMedic: { type: Boolean, default: false },
});

const form = useForm({
    jenis_izin: props.isMedic ? 'sakit' : 'izin',
    nama: '',
    nrp: '',
    jabatan: '',
    departemen: '',
    alasan: '',
    tujuan: '',
    alamat: '',
    tgl_berangkat: '',
    tgl_kembali: '',
    no_telepon: '',
    keluhan: '',
    hasil_td: '',
    hasil_rr: '',
    hasil_t: '',
    hasil_p: '',
    hasil_spo: '',
    diagnosa: '',
    terapi: '',
    keterangan_status: 1,
    rujuk_ke: '',
    rumah_sakit_rujukan: '',
    dokter_rujukan: '',
    dipulangkan_tidur: false,
    file_bukti: null,
});

const submit = () => form.post('/one-power/deviasi-operator/izin-karyawan');
</script>

<template>
    <Head title="Ajukan Izin Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Ajukan Izin Karyawan</h1>
        <IzinKaryawanForm :form="form" :on-submit="submit" :is-medic="isMedic" />
    </AppLayout>
</template>
