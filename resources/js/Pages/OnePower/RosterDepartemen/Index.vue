<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Users, ArrowRight } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    departments: { type: Array, required: true },
});
</script>

<template>
    <Head title="Roster Departement - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-1 text-xl font-extrabold text-slate-800">Roster Departement</h1>
        <p class="mb-5 text-sm text-slate-500">Pilih departemen/unit untuk melihat roster personal karyawan di dalamnya.</p>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="dept in departments"
                :key="dept.unit"
                :href="`/one-power/roster?search=${encodeURIComponent(dept.unit)}`"
                class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-300 hover:shadow-md"
            >
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-500">
                        <Users class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="font-bold text-slate-700">{{ dept.unit }}</div>
                        <div class="text-xs text-slate-500">{{ dept.total_karyawan }} karyawan</div>
                    </div>
                </div>
                <ArrowRight class="h-4 w-4 text-slate-400" />
            </Link>
            <p v-if="departments.length === 0" class="text-sm text-slate-400">Belum ada data departemen/unit.</p>
        </div>
    </AppLayout>
</template>
