<script setup>
import { ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import EmployeeForm from './Partials/EmployeeForm.vue';
import { roleFromJabatan } from '@/utils/employeeRole';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    statuses: { type: Array, required: true },
    roles: { type: Array, required: true },
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
    role: roleFromJabatan(''),
});

const isRoleChosenManually = ref(false);

const submit = () => form.post('/one-power/employees');

watch(() => form.jabatan_ss6, (jabatan) => {
    if (isRoleChosenManually.value) return;
    form.role = roleFromJabatan(jabatan);
});
</script>

<template>
    <Head title="Tambah Karyawan - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Tambah Karyawan Baru</h1>
        <EmployeeForm
            :form="form"
            :statuses="statuses"
            :roles="roles"
            :on-submit="submit"
            @role-changed="isRoleChosenManually = true"
        />
    </AppLayout>
</template>
