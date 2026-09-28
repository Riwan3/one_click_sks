<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, Printer, MessageCircle } from '@lucide/vue';
import { Image as ImageIcon } from 'lucide-vue-next';
import PhotoPreviewModal from '@/Components/ui/PhotoPreviewModal.vue';

defineProps({
    suratPeringatans: { type: Array, required: true },
    canManage: { type: Boolean, default: true },
});

const emit = defineEmits(['delete']);

const activePhotoModal = ref(null);
const viewPhoto = (row) => {
    activePhotoModal.value = row;
};

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const formatDateIndonesia = (value) => {
    if (! value) return '-';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${date.getDate()} ${BULAN_INDO[date.getMonth() + 1]} ${date.getFullYear()}`;
};

const getBadgeStyle = (jenis) => {
    const text = String(jenis || '');
    if (text.includes('Pertama')) {
        return 'bg-amber-400 text-slate-900 font-bold';
    }
    if (text.includes('Kedua')) {
        return 'bg-orange-500 text-white font-bold';
    }
    if (text.includes('Terakhir')) {
        return 'bg-red-600 text-white font-bold';
    }
    return 'bg-slate-500 text-white font-bold';
};

const waLink = (row) => {
    if (!row.noTelepon) return '#';
    const normalized = row.noTelepon.replace(/\D/g, '').replace(/^0/, '62');
    const tgl = row.tanggalKejadian || row.dikeluarkanTanggal || '';
    const pesan = `Halo ${row.nama}, saya ingin berdiskusi terkait Surat Peringatan (${row.jenisPembinaan}) yang Anda terima pada tanggal ${tgl}.`;

    return `https://api.whatsapp.com/send/?phone=${normalized}&text=${encodeURIComponent(pesan)}&type=phone_number&app_absent=0`;
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1300px] text-left text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-900 text-[0.7rem] uppercase tracking-wider text-white">
                    <th class="ps-3 py-3 font-semibold">NO SURAT</th>
                    <th class="px-3 py-3 font-semibold">TANGGAL</th>
                    <th class="px-3 py-3 font-semibold">NRP</th>
                    <th class="px-3 py-3 font-semibold">NAMA KARYAWAN</th>
                    <th class="px-3 py-3 font-semibold">JABATAN</th>
                    <th class="px-3 py-3 font-semibold">JENIS PEMBINAAN</th>
                    <th class="px-3 py-3 font-semibold">DETAIL</th>
                    <th class="px-3 py-3 font-semibold">KODE</th>
                    <th class="px-3 py-3 font-semibold">DIINPUT OLEH</th>
                    <th class="px-3 py-3 font-semibold">PENGAWAS</th>
                    <th class="px-3 py-3 font-semibold">KETERANGAN</th>
                    <th class="px-3 py-3 font-semibold">KOREKSI ALPA</th>
                    <th class="px-3 py-3 font-semibold text-center">FOTO</th>
                    <th class="pe-3 py-3 text-center font-semibold">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr v-if="suratPeringatans.length === 0">
                    <td colspan="14" class="px-4 py-8 text-center text-slate-400">Belum ada data Surat Peringatan.</td>
                </tr>
                <tr v-for="row in suratPeringatans" :key="row.id" class="hover:bg-slate-50/70 transition-colors">
                    <td class="ps-3 py-3 align-middle font-mono font-bold text-slate-800 text-[11px] whitespace-nowrap">
                        {{ row.nomorSp || '-' }}
                    </td>
                    <td class="px-3 py-3 align-middle font-semibold text-slate-700 whitespace-nowrap">
                        {{ formatDateIndonesia(row.dikeluarkanTanggal || row.tanggalKejadian) }}
                    </td>
                    <td class="px-3 py-3 align-middle font-bold text-slate-800">
                        {{ row.nrp || '-' }}
                    </td>
                    <td class="px-3 py-3 align-middle font-bold text-slate-800 uppercase">
                        {{ row.nama || '-' }}
                    </td>
                    <td class="px-3 py-3 align-middle text-slate-600">
                        {{ row.jabatan || '-' }}
                    </td>
                    <td class="px-3 py-3 align-middle whitespace-nowrap">
                        <span :class="['rounded px-2.5 py-1 text-[11px]', getBadgeStyle(row.jenisPembinaan)]">
                            {{ row.jenisPembinaan }}
                        </span>
                    </td>
                    <td class="px-3 py-3 align-middle max-w-[180px]">
                        <div class="truncate text-slate-600" :title="row.detailPelanggaran">
                            {{ row.detailPelanggaran || '-' }}
                        </div>
                    </td>
                    <td class="px-3 py-3 align-middle font-semibold text-slate-700">
                        {{ row.kodePelanggaran || '-' }}
                    </td>
                    <td class="px-3 py-3 align-middle text-slate-500 whitespace-nowrap">
                        {{ row.adminName || row.createdByName || '-' }}
                    </td>
                    <td class="px-3 py-3 align-middle text-slate-700 whitespace-nowrap">
                        {{ row.pengawasName || '-' }}
                    </td>
                    <td class="px-3 py-3 align-middle">
                        <div v-if="row.updatedByName" class="flex items-center gap-1 text-[11px] text-slate-500">
                            <Pencil class="h-3 w-3 text-slate-400" />
                            <span>{{ row.updatedByName }}</span>
                        </div>
                        <span v-else class="text-slate-400">-</span>
                    </td>
                    <td class="px-3 py-3 align-middle text-slate-600">
                        {{ row.koreksiAlpa || '-' }}
                    </td>
                    <td class="px-3 py-3 align-middle text-center whitespace-nowrap">
                        <button
                            v-if="row.foto"
                            type="button"
                            @click="viewPhoto(row)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-[11px] border border-blue-200 transition shadow-2xs cursor-pointer"
                            title="Lihat foto bukti SP"
                        >
                            <ImageIcon class="w-3 h-3 text-blue-600" />
                            <span>Lihat</span>
                        </button>
                        <span v-else class="text-slate-300">-</span>
                    </td>
                    <td class="pe-3 py-3 align-middle">
                        <div class="flex items-center justify-center gap-1.5">
                            <a
                                :href="`/one-power/pembinaan/surat-peringatan/${row.id}/print`"
                                target="_blank"
                                title="Cetak SP"
                                class="flex h-7 w-7 items-center justify-center rounded-md border border-emerald-500 text-emerald-600 hover:bg-emerald-50 transition-colors"
                            >
                                <Printer class="h-3.5 w-3.5" />
                            </a>
                            <a
                                v-if="row.noTelepon"
                                :href="waLink(row)"
                                target="_blank"
                                :title="`Kirim WA ke Karyawan: ${row.nama}`"
                                class="flex h-7 w-7 items-center justify-center rounded-md bg-emerald-600 text-white hover:bg-emerald-700 transition-colors"
                            >
                                <MessageCircle class="h-3.5 w-3.5" />
                            </a>
                            <template v-if="canManage">
                                <Link
                                    :href="`/one-power/pembinaan/surat-peringatan/${row.id}/edit`"
                                    title="Edit SP"
                                    class="flex h-7 w-7 items-center justify-center rounded-md border border-blue-500 text-blue-600 hover:bg-blue-50 transition-colors"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                </Link>
                                <button
                                    type="button"
                                    title="Hapus"
                                    class="flex h-7 w-7 items-center justify-center rounded-md border border-rose-400 text-rose-500 hover:bg-rose-50 transition-colors"
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

    <!-- Photo Preview Modal -->
    <PhotoPreviewModal
        :open="Boolean(activePhotoModal)"
        :src="activePhotoModal?.foto"
        :title="`Foto Bukti SP • ${activePhotoModal?.nomorSp || ''}`"
        :subtitle="`${activePhotoModal?.nrp || ''} - ${activePhotoModal?.nama || ''}`"
        @close="activePhotoModal = null"
    />
</template>
