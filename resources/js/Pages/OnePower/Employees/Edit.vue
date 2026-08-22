<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmployeeForm from './Partials/EmployeeForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    statuses: { type: Array, required: true },
    targetEmployee: { type: Object, required: true },
});

const form = useForm({
    nrp: props.targetEmployee.nrp,
    name: props.targetEmployee.name,
    jabatan_ss6: props.targetEmployee.jabatanSs6 ?? '',
    jabatan_roster: props.targetEmployee.jabatanRoster ?? '',
    status_karyawan: props.targetEmployee.statusKaryawan,
    unit: props.targetEmployee.unit ?? '',
    lokasi_kerja: props.targetEmployee.lokasiKerja ?? '',
    efektif_bekerja: props.targetEmployee.efektifBekerja ?? '',
    email: props.targetEmployee.email ?? '',
    no_telepon: props.targetEmployee.noTelepon ?? '',
    input_here: props.targetEmployee.inputHere ?? '',
});

const submit = () => form.put(`/one-power/employees/${props.targetEmployee.id}`);
</script>

<template>
    <Head title="Edit Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit Data Karyawan</h1>
        <EmployeeForm :form="form" :statuses="statuses" :on-submit="submit" />
    </AppLayout>
</template>
