<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PaktaIntegritasForm from './Partials/PaktaIntegritasForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const form = useForm({
    employee_id: null,
    tanggal: '',
    tempat: 'Tanjung',
    pernyataan: '',
    atasan_nama: '',
    mengetahui_nama: '',
    ttd_karyawan: '',
    ttd_atasan: '',
    ttd_mengetahui: '',
});

const submit = () => form.post('/one-power/pembinaan/pakta-integritas');
</script>

<template>
    <Head title="Buat Pakta Integritas - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Buat Pakta Integritas</h1>
        <PaktaIntegritasForm :form="form" :on-submit="submit" />
    </AppLayout>
</template>
