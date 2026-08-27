<script setup>
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, UserCheck, UserX } from '@lucide/vue';

defineProps({
    employees: { type: Array, required: true },
    onlyInactive: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle-status', 'delete']);

const statusBadge = {
    FIT: 'bg-emerald-100 text-emerald-700',
    UNFIT: 'bg-red-100 text-red-700',
    INACTIVE: 'bg-slate-200 text-slate-600',
};

const avatarColors = [
    'bg-blue-100 text-blue-700',
    'bg-emerald-100 text-emerald-700',
    'bg-amber-100 text-amber-700',
    'bg-violet-100 text-violet-700',
    'bg-rose-100 text-rose-700',
    'bg-cyan-100 text-cyan-700',
];

const initials = (name) => (name || '?').trim().charAt(0).toUpperCase();

const avatarClass = (name) => {
    const code = (name || '').charCodeAt(0) || 0;
    return avatarColors[code % avatarColors.length];
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[860px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">Foto</th>
                    <th class="px-4 py-3 font-semibold">NRP &amp; Nama</th>
                    <th class="px-4 py-3 font-semibold">Jabatan</th>
                    <th class="px-4 py-3 font-semibold">Masa Kerja</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold">Unit</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="employees.length === 0">
                    <td colspan="7" class="px-4 py-8 text-center text-slate-400">Data tidak ditemukan.</td>
                </tr>
                <tr v-for="row in employees" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle">
                        <div :class="['flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold', avatarClass(row.name)]">
                            {{ initials(row.name) }}
                        </div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                        <div class="text-xs uppercase text-slate-500">{{ row.name }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="text-xs uppercase text-slate-600">{{ row.jabatanSs6 || '-' }}</div>
                        <div class="text-xs text-slate-400">{{ row.lokasiKerja || '-' }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="text-xs font-semibold text-slate-700">{{ row.tenure }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', statusBadge[row.statusKaryawan] ?? 'bg-slate-100 text-slate-600']">
                            {{ row.statusKaryawan }}
                        </span>
                    </td>
                    <td class="px-4 py-3 align-middle text-xs uppercase text-slate-600">{{ row.unit || '-' }}</td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex gap-1.5">
                            <Link :href="`/one-power/employees/${row.id}/edit`" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50">
                                <Pencil class="h-3.5 w-3.5" />
                            </Link>
                            <button
                                type="button"
                                :title="onlyInactive ? 'Aktifkan' : 'Nonaktifkan'"
                                :class="[
                                    'flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50',
                                    onlyInactive ? 'text-emerald-500' : 'text-red-500',
                                ]"
                                @click="emit('toggle-status', row)"
                            >
                                <UserCheck v-if="onlyInactive" class="h-3.5 w-3.5" />
                                <UserX v-else class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                title="Hapus"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-red-500 hover:bg-red-50"
                                @click="emit('delete', row)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
