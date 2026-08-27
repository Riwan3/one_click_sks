<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, Search, ArrowLeft, PlusCircle, Printer, UserMinus } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    gl: { type: Object, required: true },
    search: { type: String, default: '' },
    employees: { type: Array, required: true },
});

const searchInput = ref(props.search);

const submitFilter = () => {
    router.get(`/one-power/employees/${props.gl.id}/anak-asuh`, { search: searchInput.value }, { preserveScroll: true, preserveState: true });
};

const handleRemoveAsuh = (emp) => {
    if (! confirm(`Lepas karyawan "${emp.name}" dari anak asuh GL ${props.gl.name}?`)) return;
    router.delete(`/one-power/employees/${emp.id}/remove-asuh`, { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Anak Asuh - ${gl.name}`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <Link href="/one-power/employees/list-gl" class="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="mb-1 text-xl font-extrabold text-slate-800 flex items-center gap-2">
                        <Users class="h-6 w-6 text-emerald-600" />
                        Data Anak Asuh: {{ gl.name }}
                    </h1>
                    <p class="m-0 text-sm text-slate-500">NRP: {{ gl.nrp }} | {{ gl.jabatanSs6 || 'Group Leader' }} (Total {{ employees.length }} Anak Asuh)</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="`/one-power/employees/${gl.id}/select-anak-asuh`" class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-bold text-white transition hover:bg-emerald-700 shadow-sm">
                    <PlusCircle class="h-4 w-4" /> Tambah Anak Asuh
                </Link>
            </div>
        </div>

        <!-- Filter -->
        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-xs flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari NRP atau nama anak asuh..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-emerald-500">
            </div>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700 transition">Filter</button>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold">Karyawan (Anak Asuh)</th>
                        <th class="px-4 py-3 font-bold">Jabatan</th>
                        <th class="px-4 py-3 font-bold">Unit / Lokasi</th>
                        <th class="px-4 py-3 font-bold text-center">Status</th>
                        <th class="px-4 py-3 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="emp in employees" :key="emp.id" class="hover:bg-slate-50/80 transition">
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-800">{{ emp.name }}</div>
                            <div class="text-xs font-mono text-slate-400">NRP: {{ emp.nrp }}</div>
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-700">
                            {{ emp.jabatanSs6 || emp.jabatanRoster || '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-600">
                            <div>{{ emp.unit || '-' }}</div>
                            <div class="text-slate-400">{{ emp.lokasiKerja || '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200">
                                {{ emp.statusKaryawan }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <Link :href="`/one-power/employees/${emp.id}/print`" target="_blank" class="flex items-center gap-1 rounded-md bg-blue-50 px-2.5 py-1.5 text-xs font-bold text-blue-600 hover:bg-blue-100 transition" title="Cetak Profil / CV">
                                    <Printer class="h-3.5 w-3.5" /> Cetak Profil
                                </Link>
                                <button type="button" class="flex items-center gap-1 rounded-md bg-rose-50 px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-100 transition" title="Lepas Anak Asuh" @click="handleRemoveAsuh(emp)">
                                    <UserMinus class="h-3.5 w-3.5" /> Lepas
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="employees.length === 0">
                        <td colspan="5" class="px-4 py-12 text-center text-slate-400">
                            Belum ada anak asuh yang terdaftar untuk Group Leader ini.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
