<script setup>
import { Link } from '@inertiajs/vue3';
import { Eye, Trash2, Check, X, RotateCcw, Ban, FileText } from '@lucide/vue';

const props = defineProps({
    izins: { type: Array, required: true },
    isAdmin: { type: Boolean, required: true },
    isEmergency: { type: Boolean, default: false },
});

const emit = defineEmits(['approve', 'decline', 'reset', 'cancel', 'delete']);

const canCancel = (row) => props.isAdmin || row.isOwner;

const statusBadge = {
    'Awaiting Approval': 'bg-amber-100 text-amber-800 border border-amber-200',
    Approved: 'bg-emerald-100 text-emerald-800 border border-emerald-200',
    Declined: 'bg-red-100 text-red-800 border border-red-200',
    Cancelled: 'bg-slate-200 text-slate-700 border border-slate-300',
};

const statusText = {
    'Awaiting Approval': 'Menunggu',
    Approved: 'Disetujui',
    Declined: 'Ditolak',
    Cancelled: 'Dibatalkan',
};

const jenisBadge = {
    izin: 'bg-blue-100 text-blue-800 border border-blue-200',
    sakit: 'bg-red-100 text-red-800 border border-red-200',
    emergency: 'bg-purple-100 text-purple-800 border border-purple-200',
};
</script>

<template>
    <!-- Mobile Card View (sm:hidden) -->
    <div class="space-y-3 sm:hidden">
        <div v-if="izins.length === 0" class="rounded-xl border border-dashed border-slate-200 bg-white p-5 text-center text-slate-400">
            <FileText class="mx-auto mb-1.5 h-6 w-6 text-slate-300" />
            <p class="text-xs font-semibold">
                {{ isEmergency ? 'Tidak ada data emergency.' : 'Tidak ada data izin atau sakit.' }}
            </p>
        </div>

        <div
            v-for="row in izins"
            :key="row.id"
            class="rounded-xl border border-slate-200 bg-white p-4 shadow-xs"
        >
            <div class="flex items-start justify-between gap-2 border-b border-slate-100 pb-2.5">
                <div>
                    <span class="text-xs font-mono font-bold text-slate-800">{{ row.noSurat || '-' }}</span>
                    <div class="mt-1 flex items-center gap-1.5">
                        <span :class="['rounded px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider', jenisBadge[row.jenisIzin] ?? 'bg-slate-100 text-slate-600']">
                            {{ row.jenisIzinLabel }}
                        </span>
                    </div>
                </div>
                <div class="text-right">
                    <span :class="['rounded px-2 py-0.5 text-[10px] font-bold tracking-wider', statusBadge[row.status] ?? 'bg-slate-100 text-slate-600']">
                        {{ statusText[row.status] || row.statusLabel }}
                    </span>
                    <div v-if="row.approverName && (row.status === 'Approved' || row.status === 'Declined')" class="mt-1 text-[10px] text-slate-400 leading-tight">
                        {{ row.status === 'Approved' ? 'Disetujui oleh:' : 'Ditolak oleh:' }}<br>
                        <strong class="text-slate-600 font-semibold">{{ row.approverName }}</strong>
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-1 text-xs">
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-slate-400">Nama:</span>
                    <span class="font-bold text-slate-800">{{ row.nama }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-slate-400">NRP:</span>
                    <span class="font-mono text-slate-700">{{ row.nrp }}</span>
                </div>
                <div class="flex items-center justify-between text-slate-600">
                    <span class="text-slate-400">Tanggal:</span>
                    <span class="text-slate-700">{{ row.createdAt }}</span>
                </div>
                <div v-if="row.buktiUrl" class="flex items-center justify-between pt-1 text-slate-600">
                    <span class="text-slate-400">Bukti:</span>
                    <a :href="row.buktiUrl" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:underline">
                        <FileText class="h-3.5 w-3.5" /> Buka Bukti
                    </a>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-end gap-1.5 border-t border-slate-100 pt-2.5">
                <Link
                    :href="`/one-power/deviasi-operator/izin-karyawan/${row.id}/edit`"
                    title="Lihat"
                    class="inline-flex items-center gap-1 rounded border border-blue-200 px-2 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-50"
                >
                    <Eye class="h-3.5 w-3.5" /> Lihat
                </Link>

                <template v-if="row.status === 'Awaiting Approval'">
                    <button
                        v-if="isAdmin"
                        type="button"
                        title="Setujui"
                        class="inline-flex items-center gap-1 rounded border border-emerald-300 px-2 py-1 text-xs font-semibold text-emerald-600 hover:bg-emerald-50"
                        @click="emit('approve', row)"
                    >
                        <Check class="h-3.5 w-3.5" /> Setujui
                    </button>
                    <button
                        v-if="isAdmin"
                        type="button"
                        title="Tolak"
                        class="inline-flex items-center gap-1 rounded border border-red-300 px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50"
                        @click="emit('decline', row)"
                    >
                        <X class="h-3.5 w-3.5" /> Tolak
                    </button>
                    <button
                        v-if="canCancel(row)"
                        type="button"
                        title="Batalkan"
                        class="inline-flex items-center gap-1 rounded border border-amber-300 px-2 py-1 text-xs font-semibold text-amber-600 hover:bg-amber-50"
                        @click="emit('cancel', row)"
                    >
                        <Ban class="h-3.5 w-3.5" /> Batalkan
                    </button>
                </template>

                <button
                    v-if="row.status === 'Declined' && isAdmin"
                    type="button"
                    title="Reset ke Menunggu"
                    class="inline-flex items-center gap-1 rounded border border-slate-300 px-2 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                    @click="emit('reset', row)"
                >
                    <RotateCcw class="h-3.5 w-3.5" /> Reset
                </button>

                <button
                    v-if="row.status === 'Cancelled' && (row.isOwner || isAdmin)"
                    type="button"
                    title="Hapus"
                    class="inline-flex items-center gap-1 rounded border border-red-300 px-2 py-1 text-xs font-semibold text-red-600 hover:bg-red-50"
                    @click="emit('delete', row)"
                >
                    <Trash2 class="h-3.5 w-3.5" /> Hapus
                </button>
            </div>
        </div>
    </div>

    <!-- Desktop Table View (hidden sm:block) -->
    <div class="hidden sm:block overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-xs">
        <table class="w-full min-w-[900px] text-left text-sm align-middle">
            <thead class="bg-slate-50 border-b border-slate-200 text-[0.75rem] uppercase tracking-wider text-slate-600 font-bold">
                <tr class="text-center">
                    <th class="px-3 py-3 w-36">Aksi</th>
                    <th class="px-3 py-3 text-left">No Surat</th>
                    <th v-if="!isEmergency" class="px-3 py-3">Jenis</th>
                    <th class="px-3 py-3 text-left">Nama</th>
                    <th class="px-3 py-3 text-left">NRP</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Tanggal</th>
                    <th class="px-3 py-3 w-16">Bukti</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr v-if="izins.length === 0">
                    <td :colspan="isEmergency ? 7 : 8" class="px-4 py-8 text-center text-slate-400 text-xs">
                        {{ isEmergency ? 'Tidak ada data emergency.' : 'Tidak ada data izin atau sakit.' }}
                    </td>
                </tr>
                <tr v-for="row in izins" :key="row.id" class="hover:bg-slate-50/70 transition-colors">
                    <!-- Aksi -->
                    <td class="px-3 py-2.5 text-center">
                        <div class="inline-flex items-center justify-center gap-1">
                            <Link
                                :href="`/one-power/deviasi-operator/izin-karyawan/${row.id}/edit`"
                                title="Lihat"
                                class="inline-flex h-7 w-7 items-center justify-center rounded border border-blue-300 text-blue-600 hover:bg-blue-50 transition"
                            >
                                <Eye class="h-3.5 w-3.5" />
                            </Link>

                            <template v-if="row.status === 'Awaiting Approval'">
                                <button
                                    v-if="isAdmin"
                                    type="button"
                                    title="Setujui"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded border border-emerald-300 text-emerald-600 hover:bg-emerald-50 transition"
                                    @click="emit('approve', row)"
                                >
                                    <Check class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    v-if="isAdmin"
                                    type="button"
                                    title="Tolak"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded border border-red-300 text-red-600 hover:bg-red-50 transition"
                                    @click="emit('decline', row)"
                                >
                                    <X class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    v-if="canCancel(row)"
                                    type="button"
                                    title="Batalkan"
                                    class="inline-flex h-7 w-7 items-center justify-center rounded border border-amber-300 text-amber-600 hover:bg-amber-50 transition"
                                    @click="emit('cancel', row)"
                                >
                                    <Ban class="h-3.5 w-3.5" />
                                </button>
                            </template>

                            <button
                                v-if="row.status === 'Declined' && isAdmin"
                                type="button"
                                title="Reset ke Menunggu"
                                class="inline-flex h-7 w-7 items-center justify-center rounded border border-slate-300 text-slate-600 hover:bg-slate-50 transition"
                                @click="emit('reset', row)"
                            >
                                <RotateCcw class="h-3.5 w-3.5" />
                            </button>

                            <button
                                v-if="row.status === 'Cancelled' && (row.isOwner || isAdmin)"
                                type="button"
                                title="Hapus"
                                class="inline-flex h-7 w-7 items-center justify-center rounded border border-red-300 text-red-600 hover:bg-red-50 transition"
                                @click="emit('delete', row)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>

                    <!-- No Surat -->
                    <td class="px-3 py-2.5 text-xs font-mono font-semibold text-slate-700 whitespace-nowrap">
                        {{ row.noSurat || '-' }}
                    </td>

                    <!-- Jenis (if not Emergency table) -->
                    <td v-if="!isEmergency" class="px-3 py-2.5 text-center whitespace-nowrap">
                        <span :class="['inline-block rounded px-2.5 py-0.5 text-xs font-bold uppercase', jenisBadge[row.jenisIzin] ?? 'bg-slate-100 text-slate-600']">
                            {{ row.jenisIzinLabel }}
                        </span>
                    </td>

                    <!-- Nama -->
                    <td class="px-3 py-2.5 text-xs font-semibold text-slate-800">
                        {{ row.nama || '-' }}
                    </td>

                    <!-- NRP -->
                    <td class="px-3 py-2.5 text-xs font-mono text-slate-600 whitespace-nowrap">
                        {{ row.nrp || '-' }}
                    </td>

                    <!-- Status -->
                    <td class="px-3 py-2.5 text-center whitespace-nowrap">
                        <span :class="['inline-block rounded px-2.5 py-0.5 text-xs font-bold', statusBadge[row.status] ?? 'bg-slate-100 text-slate-600']">
                            {{ statusText[row.status] || row.statusLabel }}
                        </span>
                        <div v-if="row.approverName && (row.status === 'Approved' || row.status === 'Declined')" class="mt-1 text-[11px] text-slate-400 leading-tight">
                            {{ row.status === 'Approved' ? 'Disetujui oleh:' : 'Ditolak oleh:' }}<br>
                            <strong class="text-slate-700 font-semibold">{{ row.approverName }}</strong>
                        </div>
                    </td>

                    <!-- Tanggal -->
                    <td class="px-3 py-2.5 text-center text-xs text-slate-600 whitespace-nowrap">
                        {{ row.createdAt }}
                    </td>

                    <!-- Bukti -->
                    <td class="px-3 py-2.5 text-center whitespace-nowrap">
                        <a
                            v-if="row.buktiUrl"
                            :href="row.buktiUrl"
                            target="_blank"
                            class="inline-flex h-7 w-7 items-center justify-center rounded border border-blue-200 text-blue-600 hover:bg-blue-50 transition"
                            title="Buka Bukti"
                        >
                            <FileText class="h-3.5 w-3.5" />
                        </a>
                        <span v-else class="text-xs text-slate-400">-</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
