<script setup>
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2 } from '@lucide/vue';

defineProps({
    items: { type: Array, required: true },
    canManage: { type: Boolean, default: true },
});

const emit = defineEmits(['delete']);
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">No. Surat</th>
                    <th class="px-4 py-3 font-semibold">Anak Asuh</th>
                    <th class="px-4 py-3 font-semibold">Coach</th>
                    <th class="px-4 py-3 font-semibold">Tanggal</th>
                    <th class="px-4 py-3 font-semibold">Fatigue Level</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="items.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">Data tidak ditemukan.</td>
                </tr>
                <tr v-for="row in items" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle font-semibold text-slate-700">{{ row.nomorSurat }}</td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                        <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.coachNama }}</td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.tanggal }}</td>
                    <td class="px-4 py-3 align-middle">
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', row.fatigueLevel >= 7 ? 'bg-red-100 text-red-700' : row.fatigueLevel >= 4 ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700']">
                            {{ row.fatigueLevel }}
                        </span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div v-if="canManage" class="flex gap-1.5">
                            <Link :href="`/one-power/pembinaan/coaching-counseling/${row.id}/edit`" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50">
                                <Pencil class="h-3.5 w-3.5" />
                            </Link>
                            <button type="button" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-red-500 hover:bg-red-50" @click="emit('delete', row)">
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
