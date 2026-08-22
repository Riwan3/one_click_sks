<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmployeeForm from './Partials/EmployeeForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    statuses: { type: Array, required: true },
});

const form = useForm({
    nrp: '',
    name: '',
    jabatan_ss6: '',
    jabatan_roster: '',
    status_karyawan: 'FIT',
    unit: '',
    lokasi_kerja: '',
    efektif_bekerja: '',
    email: '',
    no_telepon: '',
    input_here: '',
});

const submit = () => form.post('/one-power/employees');
</script>

<template>
    <Head title="Tambah Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Tambah Karyawan Baru</h1>
        <EmployeeForm :form="form" :statuses="statuses" :on-submit="submit" />
    </AppLayout>
</template>
