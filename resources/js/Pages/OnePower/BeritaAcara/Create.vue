<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import BeritaAcaraForm from './Partials/BeritaAcaraForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    employee_id: null,
    tanggal: '',
    kronologi: '',
    diketahui_oleh_id: null,
    ttd_karyawan: '',
    ttd_atasan: '',
});

const submit = () => form.post('/one-power/pembinaan/berita-acara');
</script>

<template>
    <Head title="Buat Berita Acara - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Buat Berita Acara</h1>
        <BeritaAcaraForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
