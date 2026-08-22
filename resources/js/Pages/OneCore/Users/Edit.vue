<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import UserForm from './Partials/UserForm.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    roles: { type: Array, required: true },
    targetUser: { type: Object, required: true },
});

const form = useForm({
    username: props.targetUser.username,
    name: props.targetUser.name,
    jabatan: props.targetUser.jabatan ?? '',
    unit: props.targetUser.unit ?? '',
    lokasi: props.targetUser.lokasi ?? '',
    role: props.targetUser.role,
    password: '',
});

const submit = () => form.put(`/one-core/users/${props.targetUser.id}`);
</script>

<template>
    <Head title="Edit User - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-5 text-xl font-extrabold text-slate-800">Edit User</h1>
        <UserForm :form="form" :roles="roles" mode="edit" :on-submit="submit" />
    </AppLayout>
</template>
