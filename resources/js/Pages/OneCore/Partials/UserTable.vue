<script setup>
import { Pencil, Trash2, KeyRound } from '@lucide/vue';

defineProps({
    users: { type: Array, required: true },
});

const emit = defineEmits(['show-password']);

const roleBadge = {
    admin: 'bg-red-100 text-red-700',
    pimpinan: 'bg-emerald-100 text-emerald-700',
    'group leader': 'bg-blue-100 text-blue-700',
    helper: 'bg-sky-100 text-sky-700',
    trafficman: 'bg-amber-100 text-amber-700',
    tamu: 'bg-slate-800 text-white',
};

const badgeFor = (role) => roleBadge[role] ?? 'bg-slate-100 text-slate-600';
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[800px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">Username / NRP</th>
                    <th class="px-4 py-3 font-semibold">Nama Lengkap</th>
                    <th class="px-4 py-3 font-semibold">Jabatan</th>
                    <th class="px-4 py-3 font-semibold">Unit / Lokasi</th>
                    <th class="px-4 py-3 font-semibold">Role Akses</th>
                    <th class="px-4 py-3 font-semibold">Password</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="users.length === 0">
                    <td colspan="7" class="px-4 py-8 text-center text-slate-400">Tidak ada user yang cocok dengan pencarian.</td>
                </tr>
                <tr v-for="row in users" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle font-semibold text-slate-700">{{ row.username }}</td>
                    <td class="px-4 py-3 align-middle text-slate-700">{{ row.name }}</td>
                    <td class="px-4 py-3 align-middle text-slate-500">{{ row.jabatan }}</td>
                    <td class="px-4 py-3 align-middle text-slate-500">{{ row.unit }}</td>
                    <td class="px-4 py-3 align-middle">
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold capitalize', badgeFor(row.role)]">{{ row.role }}</span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <button
                            type="button"
                            :class="[
                                'flex items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs font-semibold',
                                row.passwordStatus === 'custom' ? 'border-amber-300 text-amber-600' : 'border-slate-200 text-slate-500',
                            ]"
                            @click="emit('show-password', row)"
                        >
                            <KeyRound class="h-3.5 w-3.5" />
                            {{ row.passwordStatus === 'custom' ? 'Custom' : 'Default' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex gap-1.5">
                            <button type="button" title="Edit (segera hadir)" class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-amber-500">
                                <Pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                v-if="row.username !== 'admin'"
                                type="button"
                                title="Hapus (segera hadir)"
                                class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 text-red-500"
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
