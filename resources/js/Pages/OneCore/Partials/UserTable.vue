<script setup>
import { router } from '@inertiajs/vue3';
import { KeyRound, Trash2 } from '@lucide/vue';

defineProps({
    users: { type: Array, required: true },
    roles: { type: Array, required: true },
});

const emit = defineEmits(['show-password', 'delete-user']);

const changeRole = (row, event) => {
    router.put(`/one-core/users/${row.id}/role`, { role: event.target.value }, { preserveScroll: true });
};
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
                    <td colspan="7"class="px-4 py-8 text-center text-slate-400">Tidak ada user yang cocok dengan pencarian.</td>
                </tr>
                <tr v-for="row in users" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle font-semibold text-slate-700">{{ row.username }}</td>
                    <td class="px-4 py-3 align-middle text-slate-700">{{ row.name }}</td>
                    <td class="px-4 py-3 align-middle text-slate-500">{{ row.jabatan }}</td>
                    <td class="px-4 py-3 align-middle">
                        <div class="text-slate-600">{{ row.unit }}</div>
                        <div class="text-xs text-slate-400">{{ row.lokasi }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <select
                            :value="row.role"
                            class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-semibold capitalize outline-none focus:border-blue-500"
                            @change="changeRole(row, $event)"
                        >
                            <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                        </select>
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
                        <button
                            type="button"
                            title="Hapus user"
                            class="rounded-lg border border-red-200 p-1.5 text-red-500 hover:bg-red-50"
                            @click="emit('delete-user', row)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
