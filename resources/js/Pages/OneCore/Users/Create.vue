<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UserForm from './Partials/UserForm.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    roles: { type: Array, required: true },
});

const form = useForm({
    username: '',
    name: '',
    jabatan: '',
    unit: '',
    lokasi: '',
    role: 'operator',
    password: '',
});

const submit = () => form.post('/one-core/users');
</script>

<template>
    <Head title="Tambah User - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Tambah User</h1>
        <UserForm :form="form" :roles="roles" mode="create" :on-submit="submit" />
    </AppLayout>
</template>
