<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Search, Calendar, Database, CheckCircle2, ShieldAlert } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    category: { type: String, required: true },
    title: { type: String, required: true },
    filters: { type: Object, required: true },
    records: { type: Object, required: true },
});

const search = ref(props.filters.search || '');
const tanggal = ref(props.filters.tanggal || new Date().toISOString().substring(0, 10));

const handleFilter = () => {
    router.get(`/one-dms/data-icc/${props.category}`, {
        tanggal: tanggal.value,
        q: search.value,
    }, { preserveState: true });
};
</script>

<template>
    <Head :title="`${title} - OneClick`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Header -->
        <div class="mb-6 rounded-3xl p-8 text-white shadow-lg flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #0f172a 0%, #0d9488 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold flex items-center gap-3">
                    <Database class="w-7 h-7 text-emerald-400" />
                    {{ title }}
                </h1>
                <p class="m-0 max-w-xl text-sm text-white/80">
                    Monitoring data ICC ter-sync otomatis dari Formulir DMS yang berstatus Closed.
                </p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="mb-6 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-wrap gap-4 items-center justify-between">
            <form @submit.prevent="handleFilter" class="flex flex-wrap gap-3 items-center w-full md:w-auto">
                <div class="relative min-w-[180px]">
                    <Calendar class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                    <input type="date" v-model="tanggal" @change="handleFilter" class="w-full border rounded-xl pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-slate-800" />
                </div>
                <div class="relative min-w-[240px]">
                    <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
                    <input type="text" v-model="search" placeholder="Cari nama, nrp, unit..." class="w-full border rounded-xl pl-9 pr-3 py-2 text-xs bg-slate-50 dark:bg-slate-800" />
                </div>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow">
                    Filter Data
                </button>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-800/80 text-slate-700 dark:text-slate-300 font-bold border-b">
                        <tr>
                            <th class="p-3">No</th>
                            <th class="p-3">Tanggal</th>
                            <th class="p-3">Shift</th>
                            <th class="p-3">Nama Operator</th>
                            <th class="p-3">NRP</th>
                            <th class="p-3">No. Unit</th>
                            <th class="p-3">Area</th>
                            <th class="p-3" v-if="category !== 'smoking-deviasi'">Waktu</th>
                            <th class="p-3" v-if="category !== 'smoking-deviasi'">Deviasi Ke</th>
                            <th class="p-3" v-if="category === 'smoking-deviasi'">Evidence</th>
                            <th class="p-3" v-if="category === 'smoking-deviasi'">Follow Up</th>
                            <th class="p-3" v-else>Intervensi</th>
                            <th class="p-3">Group Leader</th>
                            <th class="p-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-if="records.data.length === 0">
                            <td colspan="14" class="p-8 text-center text-slate-400">
                                Belum ada record data ICC ter-sync untuk tanggal ini.
                            </td>
                        </tr>
                        <tr v-for="(row, idx) in records.data" :key="row.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-3 font-bold">{{ records.from + idx }}</td>
                            <td class="p-3">{{ row.tanggal }}</td>
                            <td class="p-3"><span class="px-2 py-0.5 bg-slate-200 dark:bg-slate-700 rounded font-semibold">{{ row.shift }}</span></td>
                            <td class="p-3 font-bold text-slate-900 dark:text-white">{{ row.nama_operator || '-' }}</td>
                            <td class="p-3 font-mono text-slate-500">{{ row.nrp || '-' }}</td>
                            <td class="p-3 font-bold text-blue-600 dark:text-blue-400">{{ row.unit || '-' }}</td>
                            <td class="p-3">{{ row.area || '-' }}</td>
                            <td class="p-3" v-if="category !== 'smoking-deviasi'">{{ row.waktu || '-' }}</td>
                            <td class="p-3" v-if="category !== 'smoking-deviasi'"><span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded font-bold">#{{ row.deviasi_ke || 1 }}</span></td>
                            <td class="p-3" v-if="category === 'smoking-deviasi'">{{ row.evidence || '-' }}</td>
                            <td class="p-3" v-if="category === 'smoking-deviasi'">{{ row.fu || '-' }}</td>
                            <td class="p-3" v-else>{{ row.intervensi || '-' }}</td>
                            <td class="p-3">{{ row.group_leader || '-' }}</td>
                            <td class="p-3 text-center">
                                <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-full text-[10px] inline-flex items-center gap-1">
                                    <CheckCircle2 class="w-3 h-3" /> {{ row.status || 'Close' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
