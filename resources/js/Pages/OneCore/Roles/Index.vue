<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import AddRoleForm from './Partials/AddRoleForm.vue';
import RoleCard from './Partials/RoleCard.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    roles: { type: Array, required: true },
});
</script>

<template>
    <Head title="Role & Akses - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5">
            <h1 class="mb-1 text-xl font-extrabold text-slate-800">Role &amp; Akses Modul</h1>
            <p class="m-0 text-sm text-slate-500">
                Atur modul apa saja yang bisa diakses tiap role, dan apakah role tersebut melihat tampilan mobile atau desktop setelah login.
            </p>
        </div>

        <AddRoleForm />

        <div class="grid gap-4 lg:grid-cols-2">
            <RoleCard v-for="role in roles" :key="role.role" :role="role" />
        </div>
    </AppLayout>
</template>
