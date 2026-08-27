<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { UserPlus, Search, ArrowLeft, CheckSquare, Square } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    gl: { type: Object, required: true },
    search: { type: String, default: '' },
    unassignedEmployees: { type: Array, required: true },
});

const searchInput = ref(props.search);
const selectedIds = ref([]);

const toggleSelectAll = () => {
    if (selectedIds.value.length === props.unassignedEmployees.length) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.unassignedEmployees.map(e => e.id);
    }
};

const isAllSelected = computed(() => {
    return props.unassignedEmployees.length > 0 && selectedIds.value.length === props.unassignedEmployees.length;
});

const submitFilter = () => {
    router.get(`/one-power/employees/${props.gl.id}/select-anak-asuh`, { search: searchInput.value }, { preserveScroll: true, preserveState: true });
};

const form = useForm({
    gl_id: props.gl.id,
    employee_ids: [],
});

const submitAssign = () => {
    if (selectedIds.value.length === 0) return;
    form.gl_id = props.gl.id;
    form.employee_ids = selectedIds.value;
    form.post('/one-power/employees/bulk-store-asuh', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Pilih Anak Asuh - ${gl.name}`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <Link href="/one-power/employees/list-gl" class="rounded-lg border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 transition">
                    <ArrowLeft class="h-5 w-5" />
                </Link>
                <div>
                    <h1 class="mb-1 text-xl font-extrabold text-slate-800 flex items-center gap-2">
                        <UserPlus class="h-6 w-6 text-emerald-600" />
                        Pilih Anak Asuh untuk {{ gl.name }}
                    </h1>
                    <p class="m-0 text-sm text-slate-500">NRP: {{ gl.nrp }} | {{ gl.jabatanSs6 || 'Group Leader' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" :disabled="selectedIds.length === 0 || form.processing" class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:opacity-50 shadow-sm" @click="submitAssign">
                    <UserPlus class="h-4 w-4" />
                    <span>Tambahkan ({{ selectedIds.length }}) Karyawan</span>
                </button>
            </div>
        </div>

        <!-- Filter -->
        <form class="mb-4 flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
            <div class="relative w-full max-w-xs flex-1">
                <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <input v-model="searchInput" type="text" placeholder="Cari NRP atau nama karyawan..." class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-emerald-500">
            </div>
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700 transition">Filter</button>
        </form>

        <!-- Unassigned Employees Table -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3 font-bold w-12 text-center">
                            <button type="button" class="text-slate-500 hover:text-emerald-600" @click="toggleSelectAll">
                                <CheckSquare v-if="isAllSelected" class="h-5 w-5 text-emerald-600" />
                                <Square v-else class="h-5 w-5" />
                            </button>
                        </th>
                        <th class="px-4 py-3 font-bold">Karyawan</th>
                        <th class="px-4 py-3 font-bold">Jabatan</th>
                        <th class="px-4 py-3 font-bold">Unit / Lokasi</th>
                        <th class="px-4 py-3 font-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="emp in unassignedEmployees" :key="emp.id" class="hover:bg-slate-50/80 transition cursor-pointer" @click="selectedIds.includes(emp.id) ? selectedIds = selectedIds.filter(id => id !== emp.id) : selectedIds.push(emp.id)">
                        <td class="px-4 py-3 text-center">
                            <input type="checkbox" :value="emp.id" :checked="selectedIds.includes(emp.id)" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @click.stop>
                        </td>
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
                    </tr>
                    <tr v-if="unassignedEmployees.length === 0">
                        <td colspan="5" class="px-4 py-12 text-center text-slate-400">
                            Tidak ada karyawan tanpa Group Leader yang ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
