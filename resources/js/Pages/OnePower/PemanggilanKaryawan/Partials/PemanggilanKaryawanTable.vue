<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, Printer, MessageCircle, Coffee, FileText, ExternalLink, Image as ImageIcon } from 'lucide-vue-next';
import PhotoPreviewModal from '@/Components/ui/PhotoPreviewModal.vue';

const props = defineProps({
    items: { type: Array, required: true },
    canManage: { type: Boolean, default: true },
    currentPage: { type: Number, default: 1 },
    perPage: { type: Number, default: 10 },
});

const emit = defineEmits(['delete']);

const activePhotoModal = ref(null);
const viewPhoto = (row) => {
    activePhotoModal.value = row;
};

const BULAN_INDO = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

const formatDateIndonesia = (value) => {
    if (! value) return '-';
    if (typeof value === 'string') {
        const parts = value.split('T')[0].split(' ')[0].split('-');
        if (parts.length === 3) {
            const year = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10);
            const day = parseInt(parts[2], 10);
            if (!isNaN(day) && !isNaN(month) && !isNaN(year) && BULAN_INDO[month]) {
                return `${day} ${BULAN_INDO[month]} ${year}`;
            }
        }
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return `${date.getDate()} ${BULAN_INDO[date.getMonth() + 1]} ${date.getFullYear()}`;
};

const formatJam = (value) => {
    if (! value) return '-';
    const str = String(value).trim();
    if (str.includes(':')) {
        const parts = str.split(':');
        return `${parts[0].padStart(2, '0')}:${parts[1].padStart(2, '0')}`;
    }
    return str;
};

const waLink = (row) => {
    let phone = (row.noTelepon || '').replace(/\D/g, '');
    if (phone.startsWith('0')) {
        phone = '62' + phone.slice(1);
    } else if (phone && !phone.startsWith('62')) {
        phone = '62' + phone;
    }

    const printLink = `${window.location.origin}/one-power/pembinaan/pemanggilan-karyawan/${row.id}/print`;
    const text = `*Pemanggilan Karyawan*\n\n` +
        `Nama : ${row.nama || '-'}\n` +
        `NRP : ${row.nrp || '-'}\n` +
        `Jabatan : ${row.jabatan || '-'}\n` +
        `Alasan Pemanggilan : ${row.alasanPemanggilan || '-'}\n` +
        `Tempat : ${row.tempat || '-'}\n` +
        `Jam : ${formatJam(row.jamPemanggilan)} WITA\n\n` +
        `Link Form Pemanggilan:\n${printLink}\n\n` +
        `Manpower Dept Produksi`;

    return `https://wa.me/${phone}?text=${encodeURIComponent(text)}`;
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1100px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50 text-[0.72rem] font-bold uppercase tracking-wider text-slate-700">
                    <th class="ps-4 py-3 text-center" style="width: 40px;">#</th>
                    <th class="px-3 py-3">No. Surat</th>
                    <th class="px-3 py-3">Tanggal</th>
                    <th class="px-3 py-3">NRP</th>
                    <th class="px-3 py-3">Nama Karyawan</th>
                    <th class="px-3 py-3">Jabatan</th>
                    <th class="px-3 py-3">Alasan Pemanggilan</th>
                    <th class="px-3 py-3">Tempat</th>
                    <th class="px-3 py-3">Jam</th>
                    <th class="px-3 py-3">TTD / Manpower</th>
                    <th class="px-3 py-3 text-center">Eviden</th>
                    <th class="px-3 py-3 text-center">Foto</th>
                    <th class="pe-4 py-3 text-center" style="width: 130px;">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr v-if="items.length === 0">
                    <td colspan="13" class="px-4 py-12 text-center text-slate-400">
                        Belum ada data pemanggilan karyawan.
                    </td>
                </tr>
                <tr
                    v-for="(row, idx) in items"
                    :key="row.id"
                    class="transition-colors hover:bg-slate-50/70"
                >
                    <!-- # -->
                    <td class="ps-4 py-3 align-middle text-center text-xs font-semibold text-slate-500">
                        {{ (currentPage - 1) * perPage + idx + 1 }}
                    </td>

                    <!-- No. Surat -->
                    <td class="px-3 py-3 align-middle whitespace-nowrap">
                        <span class="font-bold text-slate-800 text-xs tracking-tight">{{ row.noSurat || '-' }}</span>
                    </td>

                    <!-- Tanggal Pemanggilan -->
                    <td class="px-3 py-3 align-middle text-xs font-semibold text-slate-700 whitespace-nowrap">
                        {{ formatDateIndonesia(row.tanggalPemanggilan) }}
                    </td>

                    <!-- NRP -->
                    <td class="px-3 py-3 align-middle whitespace-nowrap">
                        <span class="inline-flex rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">
                            {{ row.nrp || '-' }}
                        </span>
                    </td>

                    <!-- Nama Karyawan (+ Fatigue badge) -->
                    <td class="px-3 py-3 align-middle">
                        <div class="font-bold text-slate-800 uppercase">{{ row.nama }}</div>
                        <div v-if="row.fatiguePeriode" class="mt-0.5">
                            <span class="inline-flex items-center gap-1 rounded bg-amber-100 px-1.5 py-0.5 text-[11px] font-bold text-amber-800">
                                <Coffee class="h-3 w-3 text-amber-600" />
                                Fatigue {{ row.fatiguePeriode === 'tahun' ? '1 Thn' : '1 Bln' }}
                            </span>
                        </div>
                    </td>

                    <!-- Jabatan -->
                    <td class="px-3 py-3 align-middle text-xs font-medium text-slate-700">
                        {{ row.jabatan || '-' }}
                    </td>

                    <!-- Alasan Pemanggilan -->
                    <td class="max-w-[200px] truncate px-3 py-3 align-middle text-xs text-slate-600" :title="row.alasanPemanggilan">
                        {{ row.alasanPemanggilan || '-' }}
                    </td>

                    <!-- Tempat -->
                    <td class="px-3 py-3 align-middle text-xs text-slate-600 whitespace-nowrap">
                        {{ row.tempat || '-' }}
                    </td>

                    <!-- Jam -->
                    <td class="px-3 py-3 align-middle text-xs font-semibold text-slate-700 whitespace-nowrap">
                        {{ formatJam(row.jamPemanggilan) }} WITA
                    </td>

                    <!-- TTD / Manpower -->
                    <td class="px-3 py-3 align-middle text-xs">
                        <div class="font-semibold text-slate-800">{{ row.ttdPemanggilan || '-' }}</div>
                        <div class="text-[11px] text-slate-500">{{ row.namaManpower || '' }}</div>
                    </td>

                    <!-- Eviden -->
                    <td class="px-3 py-3 align-middle text-center whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1.5">
                            <a
                                v-if="row.eviden1"
                                :href="row.eviden1"
                                target="_blank"
                                class="inline-flex items-center gap-1 rounded border border-sky-300 bg-sky-50 px-2 py-0.5 text-[11px] font-bold text-sky-700 hover:bg-sky-100"
                                title="Lihat Eviden 1"
                            >
                                <FileText class="h-3 w-3" /> E1
                            </a>
                            <a
                                v-if="row.eviden2"
                                :href="row.eviden2"
                                target="_blank"
                                class="inline-flex items-center gap-1 rounded border border-sky-300 bg-sky-50 px-2 py-0.5 text-[11px] font-bold text-sky-700 hover:bg-sky-100"
                                title="Lihat Eviden 2"
                            >
                                <FileText class="h-3 w-3" /> E2
                            </a>
                            <span v-if="!row.eviden1 && !row.eviden2" class="text-xs text-slate-400">-</span>
                        </div>
                    </td>

                    <!-- FOTO -->
                    <td class="px-3 py-3 align-middle text-center whitespace-nowrap">
                        <button
                            v-if="row.foto"
                            type="button"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors shadow-xs"
                            @click="viewPhoto(row)"
                        >
                            <ImageIcon class="w-3.5 h-3.5 text-blue-600" />
                            <span>Lihat Foto</span>
                        </button>
                        <span v-else class="text-xs text-slate-300">-</span>
                    </td>

                    <!-- AKSI -->
                    <td class="pe-4 py-3 align-middle text-center whitespace-nowrap">
                        <div class="flex items-center justify-center gap-1.5">
                            <!-- WhatsApp Button -->
                            <a
                                :href="waLink(row)"
                                target="_blank"
                                :title="`Kirim WhatsApp ke ${row.nama}`"
                                class="flex h-7 w-7 items-center justify-center rounded-md bg-emerald-600 text-white hover:bg-emerald-700 transition-colors"
                            >
                                <MessageCircle class="h-3.5 w-3.5" />
                            </a>

                            <!-- Print Button -->
                            <a
                                :href="`/one-power/pembinaan/pemanggilan-karyawan/${row.id}/print`"
                                target="_blank"
                                title="Cetak Form Pemanggilan"
                                class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-300 text-slate-700 hover:bg-slate-100 transition-colors"
                            >
                                <Printer class="h-3.5 w-3.5" />
                            </a>

                            <!-- Edit & Delete (if canManage) -->
                            <template v-if="canManage">
                                <Link
                                    :href="`/one-power/pembinaan/pemanggilan-karyawan/${row.id}/edit`"
                                    title="Edit"
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
        :title="`Foto Dokumen Pemanggilan • ${activePhotoModal?.noSurat || ''}`"
        :subtitle="`${activePhotoModal?.nrp || ''} - ${activePhotoModal?.nama || ''}`"
        @close="activePhotoModal = null"
    />
</template>
