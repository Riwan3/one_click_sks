<script setup>
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, Printer, User } from '@lucide/vue';

defineProps({
    items: { type: Array, required: true },
});

defineEmits(['delete']);

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const formatDateIndonesia = (value) => {
    if (! value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${date.getDate()} ${BULAN_INDO[date.getMonth() + 1]} ${date.getFullYear()}`;
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[950px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3.5">No. Surat</th>
                    <th class="px-4 py-3.5">Tanggal / Jam</th>
                    <th class="px-4 py-3.5">Operator</th>
                    <th class="px-4 py-3.5">Unit / Area</th>
                    <th class="px-4 py-3.5">Pengawas</th>
                    <th class="px-4 py-3.5">INPUT</th>
                    <th class="px-4 py-3.5">KETERANGAN</th>
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr v-if="items.length === 0">
                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada data interview.</td>
                </tr>
                <tr v-for="row in items" :key="row.id" class="hover:bg-slate-50/70 transition-colors">
                    <td class="px-4 py-3 align-middle font-mono text-xs font-bold text-slate-800">
                        {{ row.noSurat || '-' }}
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-800 text-sm">{{ formatDateIndonesia(row.tanggal) }}</div>
                        <div class="text-xs text-slate-500">{{ row.jam || '-' }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-800 text-sm">{{ row.nrp || row.nrpOperator || '-' }}</div>
                        <div class="text-xs uppercase text-slate-600">{{ row.nama || row.namaOperator || '-' }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-800 text-sm">{{ row.nomorUnit || '-' }}</div>
                        <div class="text-xs text-slate-500">{{ row.area || '-' }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-800 text-sm">{{ row.nrpPengawas || '-' }}</div>
                        <div class="text-xs uppercase text-slate-600">{{ row.namaPengawas || '-' }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#0d6efd] px-3 py-1 text-xs font-semibold text-white">
                            <User class="h-3 w-3 fill-current" />
                            <span>{{ row.createdByName || '-' }}</span>
                        </span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div v-if="row.updatedByName || row.createdByName" class="flex items-center gap-1.5 text-xs text-slate-600">
                            <Pencil class="h-3.5 w-3.5 text-slate-400" />
                            <span>{{ row.updatedByName || row.createdByName }}</span>
                        </div>
                        <span v-else class="text-xs text-slate-400">-</span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex items-center justify-center gap-1.5">
                            <a
                                :href="`/one-power/pembinaan/interview-fatigue/${row.id}/print`"
                                target="_blank"
                                title="Cetak"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-500 text-emerald-600 hover:bg-emerald-50 transition-colors"
                            >
                                <Printer class="h-4 w-4" />
                            </a>
                            <Link
                                :href="`/one-power/pembinaan/interview-fatigue/${row.id}/edit`"
                                title="Edit"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-blue-500 text-blue-600 hover:bg-blue-50 transition-colors"
                            >
                                <Pencil class="h-4 w-4" />
                            </Link>
                            <button
                                type="button"
                                title="Hapus"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-rose-400 text-rose-500 hover:bg-rose-50 transition-colors"
                                @click="$emit('delete', row)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
