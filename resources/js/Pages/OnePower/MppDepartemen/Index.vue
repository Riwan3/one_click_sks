<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { FileBarChart2, Settings } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    isAdmin: { type: Boolean, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    departments: { type: Array, required: true },
    staff: { type: Array, required: true },
    pengawas: { type: Array, required: true },
    totals: { type: Object, required: true },
    settings: { type: Object, required: true },
    filters: { type: Object, required: true },
});

const targetForm = useForm({
    target_1: props.settings.mpp_target_1,
    target_2: props.settings.mpp_target_2,
});

const submitTarget = () => {
    targetForm.post(route('one-power.mpp-departemen.update-target'));
};

const achClass = (ach) => (ach >= 100 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700');

const showEmployeesUrl = (categoryNama) =>
    route('mpp-departemen.show-employees', { department: categoryNama });
</script>

<template>
    <Head title="Analisis MPP - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800">
            <FileBarChart2 class="h-5 w-5 text-blue-500" /> Analisis Manpower Planning (MPP)
        </h1>
        <p class="mb-5 text-sm text-slate-500">Perbandingan Index/Plan MP vs Actual MP untuk kategori Staff dan Pengawas.</p>

        <!-- SETTINGS FORM (Admin only) -->
        <div v-if="isAdmin" class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-3">
                <Settings class="h-4 w-4 text-blue-500" />
                <h2 class="text-xs font-bold uppercase tracking-wide text-slate-600">Pengaturan Target % MPP (Global)</h2>
            </div>
            <form class="grid gap-4 p-4 sm:grid-cols-[1fr_1fr_auto]" @submit.prevent="submitTarget">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Target 1 (%)</label>
                    <input
                        v-model="targetForm.target_1"
                        type="text"
                        placeholder="Contoh: 2.7"
                        class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <p v-if="targetForm.errors.target_1" class="mt-1 text-xs text-red-600">{{ targetForm.errors.target_1 }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-600">Target 2 (%)</label>
                    <input
                        v-model="targetForm.target_2"
                        type="text"
                        placeholder="Contoh: 2.9"
                        class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    />
                    <p v-if="targetForm.errors.target_2" class="mt-1 text-xs text-red-600">{{ targetForm.errors.target_2 }}</p>
                </div>
                <div class="flex items-end">
                    <button
                        type="submit"
                        :disabled="targetForm.processing"
                        class="w-full rounded-md bg-red-700 px-4 py-2 text-sm font-bold uppercase tracking-wide text-white shadow-sm hover:bg-red-800 disabled:opacity-60 sm:w-auto"
                    >
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        <!-- TABEL MPP STAFF -->
        <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-bold text-blue-600">Analisis MPP Staff</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-[0.7rem] uppercase tracking-wide text-slate-500">
                            <th class="w-12 px-4 py-3 text-center font-semibold">No</th>
                            <th class="px-4 py-3 font-semibold">Kategori Jabatan</th>
                            <th class="px-4 py-3 text-center font-semibold">Index / Plan MP</th>
                            <th class="px-4 py-3 text-center font-semibold">Actual MP</th>
                            <th class="px-4 py-3 text-center font-semibold">ACH (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="staff.length === 0"><td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada kategori Staff.</td></tr>
                        <tr v-for="(row, index) in staff" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                            <td class="px-4 py-3 text-center align-middle text-slate-500">{{ index + 1 }}</td>
                            <td class="px-4 py-3 align-middle font-bold text-slate-700">
                                <Link :href="showEmployeesUrl(row.nama)" class="hover:text-blue-600 hover:underline">{{ row.nama }}</Link>
                            </td>
                            <td class="px-4 py-3 text-center align-middle text-slate-600">{{ row.plan_mp }}</td>
                            <td class="px-4 py-3 text-center align-middle font-bold text-slate-700">
                                <Link :href="showEmployeesUrl(row.nama)" class="hover:text-blue-600 hover:underline">{{ row.actual_mp }}</Link>
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="achClass(row.ach)">{{ row.ach }}%</span>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="staff.length > 0">
                        <tr class="bg-slate-100 font-bold text-slate-700">
                            <td colspan="2" class="px-4 py-3">TOTAL STAFF</td>
                            <td class="px-4 py-3 text-center">{{ totals.staff.plan_mp }}</td>
                            <td class="px-4 py-3 text-center">{{ totals.staff.actual_mp }}</td>
                            <td class="px-4 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- TABEL MPP PENGAWAS -->
        <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-bold text-blue-600">Analisis MPP Pengawas</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-[0.7rem] uppercase tracking-wide text-slate-500">
                            <th class="w-12 px-4 py-3 text-center font-semibold">No</th>
                            <th class="px-4 py-3 font-semibold">Kategori Jabatan</th>
                            <th class="px-4 py-3 text-center font-semibold">Index Pengawas</th>
                            <th class="px-4 py-3 text-center font-semibold">MP Aktual</th>
                            <th class="px-4 py-3 text-center font-semibold">ACH (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="pengawas.length === 0"><td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada kategori Pengawas.</td></tr>
                        <tr v-for="(row, index) in pengawas" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                            <td class="px-4 py-3 text-center align-middle text-slate-500">{{ index + 1 }}</td>
                            <td class="px-4 py-3 align-middle font-bold text-slate-700">
                                <Link :href="showEmployeesUrl(row.nama)" class="hover:text-blue-600 hover:underline">{{ row.nama }}</Link>
                            </td>
                            <td class="px-4 py-3 text-center align-middle text-slate-600">{{ row.plan_mp }}</td>
                            <td class="px-4 py-3 text-center align-middle font-bold text-slate-700">
                                <Link :href="showEmployeesUrl(row.nama)" class="hover:text-blue-600 hover:underline">{{ row.actual_mp }}</Link>
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold" :class="achClass(row.ach)">{{ row.ach }}%</span>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot v-if="pengawas.length > 0">
                        <tr class="bg-slate-100 font-bold text-slate-700">
                            <td colspan="2" class="px-4 py-3">TOTAL PENGAWAS</td>
                            <td class="px-4 py-3 text-center">{{ totals.pengawas.plan_mp }}</td>
                            <td class="px-4 py-3 text-center">{{ totals.pengawas.actual_mp }}</td>
                            <td class="px-4 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- RINGKASAN UNIT / DEPARTEMEN (secondary panel) -->
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3">
                <h2 class="text-sm font-bold text-slate-600">Ringkasan Karyawan per Unit</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[600px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-[0.7rem] uppercase tracking-wide text-slate-500">
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
        </div>
    </AppLayout>
</template>
