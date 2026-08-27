<script setup>
import { Head } from '@inertiajs/vue3';
import { FileBarChart2 } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    departments: { type: Array, required: true },
});
</script>

<template>
    <Head title="MPP Departement - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800"><FileBarChart2 class="h-5 w-5 text-blue-500" /> MPP Departement</h1>
        <p class="mb-5 text-sm text-slate-500">Ringkasan Manpower Planning per departemen/unit.</p>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[600px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">Departemen / Unit</th>
                        <th class="px-4 py-3 font-semibold">Total Karyawan</th>
                        <th class="px-4 py-3 font-semibold">Fit</th>
                        <th class="px-4 py-3 font-semibold">Rasio Fit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="departments.length === 0"><td colspan="4" class="px-4 py-8 text-center text-slate-400">Belum ada data.</td></tr>
                    <tr v-for="dept in departments" :key="dept.unit" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                        <td class="px-4 py-3 align-middle font-bold text-slate-700">{{ dept.unit }}</td>
                        <td class="px-4 py-3 align-middle text-slate-600">{{ dept.total }}</td>
                        <td class="px-4 py-3 align-middle text-slate-600">{{ dept.total_fit }}</td>
                        <td class="px-4 py-3 align-middle">
                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                {{ dept.total > 0 ? Math.round((dept.total_fit / dept.total) * 100) : 0 }}%
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
