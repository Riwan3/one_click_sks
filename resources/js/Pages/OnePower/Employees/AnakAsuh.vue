<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Users, Search, ArrowLeft, PlusCircle, Printer, UserMinus, ShieldCheck } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    gl: { type: Object, required: true },
    search: { type: String, default: '' },
    employees: { type: Array, required: true },
});

const glData = computed(() => props.gl?.data ?? props.gl ?? {});
const isGroupLeader = computed(() => props.user?.role === 'group-leader');

const searchInput = ref(props.search);

const submitFilter = () => {
    router.get(`/one-power/employees/${glData.value.id}/anak-asuh`, { search: searchInput.value }, { preserveScroll: true, preserveState: true });
};

const handleRemoveAsuh = async (emp) => {
    if (! await confirmDialog(`Lepas karyawan "${emp.name}" dari anak asuh GL ${glData.value.name}?`)) return;
    router.delete(`/one-power/employees/${emp.id}/remove-asuh`, { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Anak Asuh - ${glData.name || 'Group Leader'}`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" :back-url="isGroupLeader ? '/portal/data' : '/one-power/employees/list-gl'">
        <!-- Header Banner -->
        <div class="mb-5 rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3.5">
                    <!-- Tombol Kembali khusus Admin (jika bukan GL) -->
                    <Link v-if="!isGroupLeader" href="/one-power/employees/list-gl" class="mt-0.5 rounded-xl border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 transition" title="Kembali">
                        <ArrowLeft class="h-5 w-5" />
                    </Link>

                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 font-bold">
                        <Users class="h-6 w-6" />
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="m-0 text-lg sm:text-xl font-black text-slate-800 tracking-tight">
                                {{ glData.name || 'Group Leader' }}
                            </h1>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100/80 px-2.5 py-0.5 text-xs font-bold text-emerald-800">
                                <ShieldCheck class="h-3.5 w-3.5" /> {{ employees.length }} Anak Asuh
                            </span>
                        </div>
                        <p class="mt-1 mb-0 text-xs sm:text-sm text-slate-500 font-medium">
                            NRP: <span class="font-mono text-slate-700">{{ glData.nrp || '-' }}</span>
                            <span class="mx-1.5 text-slate-300">•</span>
                            <span>{{ glData.jabatanSs6 || glData.jabatanRoster || 'Group Leader' }}</span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link :href="`/one-power/employees/${glData.id}/select-anak-asuh`" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 shadow-sm shadow-emerald-200">
                        <PlusCircle class="h-4 w-4" /> Tambah Anak Asuh
                    </Link>
                </div>
            </div>
        </div>

        <!-- Filter Search Bar -->
        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-sm flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari NRP atau nama anak asuh..." class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </div>
            <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700 transition">Cari</button>
        </form>

        <!-- TAMPILAN DESKTOP: Table (md:block) -->
        <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
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
                            <span :class="['inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold border', emp.statusKaryawan === 'FIT' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200']">
                                {{ emp.statusKaryawan }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <Link :href="`/one-power/employees/${emp.id}/print`" target="_blank" class="flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1.5 text-xs font-bold text-blue-600 hover:bg-blue-100 transition" title="Cetak Profil / CV">
                                    <Printer class="h-3.5 w-3.5" /> Cetak Profil
                                </Link>
                                <button type="button" class="flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1.5 text-xs font-bold text-rose-600 hover:bg-rose-100 transition" title="Lepas Anak Asuh" @click="handleRemoveAsuh(emp)">
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

        <!-- TAMPILAN MOBILE: Card List (md:hidden) -->
        <div class="space-y-3 md:hidden">
            <div v-for="emp in employees" :key="emp.id" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="font-black text-slate-800 text-sm leading-snug">{{ emp.name }}</div>
                        <div class="text-xs font-mono text-slate-400 mt-0.5">NRP: {{ emp.nrp }}</div>
                    </div>
                    <span :class="['inline-flex shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold border', emp.statusKaryawan === 'FIT' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200']">
                        {{ emp.statusKaryawan }}
                    </span>
                </div>

                <div class="mt-3 pt-2.5 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Jabatan</span>
                        <span class="font-semibold text-slate-700">{{ emp.jabatanSs6 || emp.jabatanRoster || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Unit / Lokasi</span>
                        <span class="font-medium text-slate-700">{{ emp.unit || '-' }} / {{ emp.lokasiKerja || '-' }}</span>
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <Link :href="`/one-power/employees/${emp.id}/print`" target="_blank" class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-blue-50 py-2 px-3 text-xs font-bold text-blue-600 hover:bg-blue-100 transition shadow-sm">
                        <Printer class="h-3.5 w-3.5" /> Cetak Profil
                    </Link>
                    <button type="button" class="flex-1 flex items-center justify-center gap-1.5 rounded-xl bg-rose-50 py-2 px-3 text-xs font-bold text-rose-600 hover:bg-rose-100 transition shadow-sm" @click="handleRemoveAsuh(emp)">
                        <UserMinus class="h-3.5 w-3.5" /> Lepas Asuh
                    </button>
                </div>
            </div>

            <div v-if="employees.length === 0" class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-400 text-sm">
                Belum ada anak asuh yang terdaftar untuk Group Leader ini.
            </div>
        </div>
    </AppLayout>
</template>
