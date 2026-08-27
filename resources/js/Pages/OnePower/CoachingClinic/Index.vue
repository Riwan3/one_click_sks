<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Lightbulb, Search } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    search: { type: String, default: '' },
    items: { type: Array, required: true },
});

const searchInput = ref(props.search);
const submitFilter = () => {
    router.get('/one-power/coaching-clinic', { search: searchInput.value }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <Head title="Coaching Clinic - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <h1 class="mb-1 flex items-center gap-2 text-xl font-extrabold text-slate-800"><Lightbulb class="h-5 w-5 text-blue-500" /> Data Coaching Karyawan (Coaching Clinic V1)</h1>
        <p class="mb-5 text-sm text-slate-500">Riwayat sesi coaching &amp; counseling anak asuh.</p>

        <form class="mb-4 flex flex-wrap gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-md flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari NRP atau nama..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600">Filter</button>
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[800px] text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 font-semibold">No. Surat</th>
                        <th class="px-4 py-3 font-semibold">Anak Asuh</th>
                        <th class="px-4 py-3 font-semibold">Coach</th>
                        <th class="px-4 py-3 font-semibold">Tanggal</th>
                        <th class="px-4 py-3 font-semibold">Fatigue Level</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="items.length === 0"><td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada data.</td></tr>
                    <tr v-for="row in items" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                        <td class="px-4 py-3 align-middle font-semibold text-slate-700">{{ row.nomorSurat }}</td>
                        <td class="px-4 py-3 align-middle">
                            <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                            <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                        </td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.coachNama }}</td>
                        <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.tanggal }}</td>
                        <td class="px-4 py-3 align-middle">
                            <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', row.fatigueLevel >= 7 ? 'bg-red-100 text-red-700' : row.fatigueLevel >= 4 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700']">{{ row.fatigueLevel }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
