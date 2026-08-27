<script setup>
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, Printer, MessageCircle } from '@lucide/vue';

defineProps({
    suratPeringatans: { type: Array, required: true },
    canManage: { type: Boolean, default: true },
});

const emit = defineEmits(['delete']);

const jenisBadge = {
    'Peringatan Pertama': 'bg-amber-100 text-amber-700',
    'Peringatan Kedua': 'bg-orange-100 text-orange-700',
    'Peringatan Terakhir': 'bg-red-100 text-red-700',
};

const waLink = (row) => {
    const normalized = row.noTelepon.replace(/\D/g, '').replace(/^0/, '62');
    const pesan = `Halo ${row.nama}, Anda menerima ${row.jenisPembinaan} tertanggal ${row.tanggalKejadian}. Mohon segera menghubungi atasan Anda.`;

    return `https://api.whatsapp.com/send/?phone=${normalized}&text=${encodeURIComponent(pesan)}`;
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">No. SP</th>
                    <th class="px-4 py-3 font-semibold">NRP &amp; Nama</th>
                    <th class="px-4 py-3 font-semibold">Jenis</th>
                    <th class="px-4 py-3 font-semibold">Tanggal Kejadian</th>
                    <th class="px-4 py-3 font-semibold">Masa Berlaku</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="suratPeringatans.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">Data tidak ditemukan.</td>
                </tr>
                <tr v-for="row in suratPeringatans" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle text-xs font-semibold text-slate-700">{{ row.nomorSp || '-' }}</td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                        <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', jenisBadge[row.jenisPembinaan] ?? 'bg-slate-100 text-slate-600']">
                            {{ row.jenisPembinaan }}
                        </span>
                    </td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.tanggalKejadian || '-' }}</td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.sanksiMasaBerlaku }} bulan</td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex gap-1.5">
                            <a :href="`/one-power/pembinaan/surat-peringatan/${row.id}/print`" target="_blank" title="Cetak" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50">
                                <Printer class="h-3.5 w-3.5" />
                            </a>
                            <a
                                v-if="row.noTelepon"
                                :href="waLink(row)"
                                target="_blank"
                                title="Kirim WA ke Karyawan"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-emerald-500 hover:bg-emerald-50"
                            >
                                <MessageCircle class="h-3.5 w-3.5" />
                            </a>
                            <template v-if="canManage">
                                <Link :href="`/one-power/pembinaan/surat-peringatan/${row.id}/edit`" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50">
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
                            </template>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
