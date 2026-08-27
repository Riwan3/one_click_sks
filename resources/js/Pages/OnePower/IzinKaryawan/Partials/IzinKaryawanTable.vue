<script setup>
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, Check, X, RotateCcw, Ban, Paperclip } from '@lucide/vue';

const props = defineProps({
    izins: { type: Array, required: true },
    isAdmin: { type: Boolean, required: true },
});

const emit = defineEmits(['approve', 'decline', 'reset', 'cancel', 'delete']);

const canCancel = (row) => props.isAdmin || row.isOwner;

const statusBadge = {
    'Awaiting Approval': 'bg-amber-100 text-amber-700',
    Approved: 'bg-emerald-100 text-emerald-700',
    Declined: 'bg-red-100 text-red-700',
    Cancelled: 'bg-slate-200 text-slate-600',
};

const jenisBadge = {
    izin: 'bg-blue-100 text-blue-700',
    sakit: 'bg-pink-100 text-pink-700',
    emergency: 'bg-orange-100 text-orange-700',
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1000px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">No. Surat</th>
                    <th class="px-4 py-3 font-semibold">NRP &amp; Nama</th>
                    <th class="px-4 py-3 font-semibold">Jenis</th>
                    <th class="px-4 py-3 font-semibold">Periode</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold">Bukti</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="izins.length === 0">
                    <td colspan="7" class="px-4 py-8 text-center text-slate-400">Data tidak ditemukan.</td>
                </tr>
                <tr v-for="row in izins" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle text-xs font-semibold text-slate-700">{{ row.noSurat || '-' }}</td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                        <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', jenisBadge[row.jenisIzin] ?? 'bg-slate-100 text-slate-600']">
                            {{ row.jenisIzinLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">
                        <template v-if="row.tglBerangkat">{{ row.tglBerangkat }} &ndash; {{ row.tglKembali || '-' }}</template>
                        <template v-else>{{ row.createdAt }}</template>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', statusBadge[row.status] ?? 'bg-slate-100 text-slate-600']">
                            {{ row.statusLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <a v-if="row.buktiUrl" :href="row.buktiUrl" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:underline">
                            <Paperclip class="h-3.5 w-3.5" /> Lihat
                        </a>
                        <span v-else class="text-xs text-slate-400">-</span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex flex-wrap gap-1.5">
                            <button
                                v-if="isAdmin && row.status === 'Awaiting Approval'"
                                type="button"
                                title="Setujui"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-emerald-500 hover:bg-emerald-50"
                                @click="emit('approve', row)"
                            >
                                <Check class="h-3.5 w-3.5" />
                            </button>
                            <button
                                v-if="isAdmin && row.status === 'Awaiting Approval'"
                                type="button"
                                title="Tolak"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-red-500 hover:bg-red-50"
                                @click="emit('decline', row)"
                            >
                                <X class="h-3.5 w-3.5" />
                            </button>
                            <button
                                v-if="isAdmin && (row.status === 'Approved' || row.status === 'Declined')"
                                type="button"
                                title="Reset ke Menunggu"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50"
                                @click="emit('reset', row)"
                            >
                                <RotateCcw class="h-3.5 w-3.5" />
                            </button>
                            <button
                                v-if="canCancel(row) && row.status !== 'Cancelled'"
                                type="button"
                                title="Batalkan"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50"
                                @click="emit('cancel', row)"
                            >
                                <Ban class="h-3.5 w-3.5" />
                            </button>
                            <Link :href="`/one-power/deviasi-operator/izin-karyawan/${row.id}/edit`" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50">
                                <Pencil class="h-3.5 w-3.5" />
                            </Link>
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
