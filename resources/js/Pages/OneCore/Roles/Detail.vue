<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ModuleMenuCard from './Partials/ModuleMenuCard.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    role: { type: Object, required: true },
    moduleMenus: { type: Array, required: true },
});
</script>

<template>
    <Head :title="`Akses Menu - ${role.label} - OneClick`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5">
            <Link href="/one-core/roles" class="mb-2 inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-700">
                <ArrowLeft class="h-3.5 w-3.5" /> Kembali ke Role & Akses
            </Link>
            <h1 class="mb-1 text-xl font-extrabold text-slate-800">Akses Menu — {{ role.label }}</h1>
            <p class="m-0 text-sm text-slate-500">
                Atur menu spesifik yang bisa diakses role ini di dalam tiap modul. Berlaku baik untuk sidebar desktop maupun shortcut mobile.
            </p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <ModuleMenuCard v-for="module in moduleMenus" :key="module.key" :role="role.value" :module="module" />
        </div>
    </AppLayout>
</template>
