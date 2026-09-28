<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { UserPlus, Search, ArrowLeft, CheckSquare, Square, Check, Users, ShieldCheck } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    gl: { type: Object, required: true },
    search: { type: String, default: '' },
    unassignedEmployees: { type: Array, required: true },
});

const glData = computed(() => props.gl?.data ?? props.gl ?? {});
const isGroupLeader = computed(() => props.user?.role === 'group-leader');

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

const toggleEmployee = (id) => {
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter(item => item !== id);
    } else {
        selectedIds.value.push(id);
    }
};

const submitFilter = () => {
    router.get(`/one-power/employees/${glData.value.id}/select-anak-asuh`, { search: searchInput.value }, { preserveScroll: true, preserveState: true });
};

const form = useForm({
    gl_id: null,
    employee_ids: [],
});

const submitAssign = () => {
    if (selectedIds.value.length === 0) return;
    form.gl_id = glData.value.id;
    form.employee_ids = selectedIds.value;
    form.post('/one-power/employees/bulk-store-asuh', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Pilih Anak Asuh - ${glData.name || 'Group Leader'}`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" :back-url="`/one-power/employees/${glData.id}/anak-asuh`">
        <div class="pb-24 md:pb-6">
            <!-- Header Banner -->
            <div class="mb-5 rounded-2xl border border-slate-200/80 bg-white p-4 sm:p-5 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3.5">
                        <!-- Back button only for non-GL (GL uses topbar back button) -->
                        <Link v-if="!isGroupLeader" :href="`/one-power/employees/${glData.id}/anak-asuh`" class="mt-0.5 rounded-xl border border-slate-200 p-2 text-slate-600 hover:bg-slate-50 transition" title="Kembali">
                            <ArrowLeft class="h-5 w-5" />
                        </Link>

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 font-bold">
                            <UserPlus class="h-6 w-6" />
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="m-0 text-lg sm:text-xl font-black text-slate-800 tracking-tight">
                                    Pilih Anak Asuh
                                </h1>
                                <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                                    GL: {{ glData.name || 'Group Leader' }}
                                </span>
                            </div>
                            <p class="mt-1 mb-0 text-xs sm:text-sm text-slate-500 font-medium">
                                NRP: <span class="font-mono text-slate-700">{{ glData.nrp || '-' }}</span>
                                <span class="mx-1.5 text-slate-300">•</span>
                                <span>{{ glData.jabatanSs6 || glData.jabatanRoster || 'Group Leader' }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- Desktop Header Action Button -->
                    <div class="hidden sm:flex items-center gap-2">
                        <button type="button" :disabled="selectedIds.length === 0 || form.processing" class="flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:opacity-50 shadow-sm shadow-emerald-200" @click="submitAssign">
                            <UserPlus class="h-4 w-4" />
                            <span>Tambahkan ({{ selectedIds.length }}) Karyawan</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Toolbar: Filter & Select All Toggle -->
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <form class="flex items-center gap-2 flex-1 max-w-md" @submit.prevent="submitFilter">
                    <div class="relative w-full">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input v-model="searchInput" type="text" placeholder="Cari NRP atau nama karyawan..." class="w-full rounded-xl border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
                    </div>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700 transition">Cari</button>
                </form>

                <div class="flex items-center justify-between sm:justify-end gap-2 text-xs sm:text-sm font-semibold text-slate-600">
                    <button type="button" class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50 transition shadow-sm" @click="toggleSelectAll">
                        <CheckSquare v-if="isAllSelected" class="h-4 w-4 text-emerald-600" />
                        <Square v-else class="h-4 w-4 text-slate-400" />
                        <span>{{ isAllSelected ? 'Batalkan Semua' : 'Pilih Semua' }}</span>
                    </button>
                    <span class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                        {{ selectedIds.length }} / {{ unassignedEmployees.length }} dipilih
                    </span>
                </div>
            </div>

            <!-- DESKTOP VIEW: Table (hidden on mobile, visible on md and up) -->
            <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
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
                        <tr v-for="emp in unassignedEmployees" :key="emp.id" :class="['transition cursor-pointer', selectedIds.includes(emp.id) ? 'bg-emerald-50/60 hover:bg-emerald-50' : 'hover:bg-slate-50/80']" @click="toggleEmployee(emp.id)">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" :value="emp.id" :checked="selectedIds.includes(emp.id)" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @click.stop="toggleEmployee(emp.id)">
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
                                <span :class="['inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold border', emp.statusKaryawan === 'FIT' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200']">
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

            <!-- MOBILE VIEW: Touch-friendly Card List (md:hidden) -->
            <div class="space-y-2.5 md:hidden">
                <div
                    v-for="emp in unassignedEmployees"
                    :key="emp.id"
                    :class="[
                        'rounded-2xl border p-3.5 transition cursor-pointer active:scale-[0.99] select-none',
                        selectedIds.includes(emp.id)
                            ? 'border-emerald-500 bg-emerald-50/50 shadow-sm ring-1 ring-emerald-500'
                            : 'border-slate-200 bg-white hover:border-slate-300 shadow-sm'
                    ]"
                    @click="toggleEmployee(emp.id)"
                >
                    <div class="flex items-start gap-3">
                        <!-- Custom Checkbox Icon -->
                        <div class="mt-0.5 shrink-0">
                            <div
                                :class="[
                                    'flex h-6 w-6 items-center justify-center rounded-lg border transition',
                                    selectedIds.includes(emp.id)
                                        ? 'border-emerald-600 bg-emerald-600 text-white'
                                        : 'border-slate-300 bg-white text-transparent'
                                ]"
                            >
                                <Check class="h-4 w-4 stroke-[3]" />
                            </div>
                        </div>

                        <!-- Employee Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="font-black text-slate-800 text-sm leading-snug truncate">
                                    {{ emp.name }}
                                </div>
                                <span :class="['shrink-0 inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold border', emp.statusKaryawan === 'FIT' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200']">
                                    {{ emp.statusKaryawan }}
                                </span>
                            </div>
                            <div class="text-xs font-mono text-slate-400 mt-0.5">
                                NRP: {{ emp.nrp }}
                            </div>

                            <div class="mt-2 pt-2 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs">
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Jabatan</span>
                                    <span class="font-semibold text-slate-700 leading-tight block truncate">
                                        {{ emp.jabatanSs6 || emp.jabatanRoster || '-' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[10px]">Unit / Lokasi</span>
                                    <span class="font-medium text-slate-600 leading-tight block truncate">
                                        {{ emp.unit || '-' }} / {{ emp.lokasiKerja || '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="unassignedEmployees.length === 0" class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-400 text-sm">
                    Tidak ada karyawan tanpa Group Leader yang ditemukan.
                </div>
            </div>

            <!-- Floating Bottom Action Bar for Mobile -->
            <div v-if="selectedIds.length > 0" class="fixed bottom-4 inset-x-4 z-40 md:hidden">
                <button
                    type="button"
                    :disabled="form.processing"
                    class="w-full flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 py-3.5 px-5 text-sm font-black text-white shadow-xl shadow-emerald-900/25 active:scale-[0.98] transition hover:bg-emerald-700"
                    @click="submitAssign"
                >
                    <UserPlus class="h-5 w-5" />
                    <span>Tambahkan ({{ selectedIds.length }}) Anak Asuh</span>
                </button>
            </div>
        </div>
    </AppLayout>
</template>
