<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, Printer, User } from '@lucide/vue';
import { Image as ImageIcon } from 'lucide-vue-next';
import PhotoPreviewModal from '@/Components/ui/PhotoPreviewModal.vue';

defineProps({
    items: { type: Array, required: true },
    canManage: { type: Boolean, default: false },
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
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1000px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/70 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="ps-4 py-3.5">Tanggal</th>
                    <th class="px-4 py-3.5">No. Dokumen</th>
                    <th class="px-4 py-3.5">Karyawan</th>
                    <th class="px-4 py-3.5">Jabatan / Unit</th>
                    <th class="px-4 py-3.5">Atasan Langsung</th>
                    <th class="px-4 py-3.5">INPUT</th>
                    <th class="px-4 py-3.5">KETERANGAN</th>
                    <th class="px-4 py-3.5">Tempat</th>
                    <th class="px-4 py-3.5 text-center">Foto Bukti</th>
                    <th class="pe-4 py-3.5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr v-if="items.length === 0">
                    <td colspan="10" class="px-4 py-8 text-center text-slate-400">Belum ada data pakta integritas.</td>
                </tr>
                <tr v-for="row in items" :key="row.id" class="hover:bg-slate-50/70 transition-colors">
                    <td class="ps-4 py-3 align-middle font-bold text-blue-600">
                        {{ formatDateIndonesia(row.tanggal) }}
                    </td>
                    <td class="px-4 py-3 align-middle font-mono text-xs font-bold text-slate-800">
                        {{ row.nomorSurat || '-' }}
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                <User class="h-4 w-4" />
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-sm">{{ row.nama || '-' }}</div>
                                <div class="text-xs text-slate-500">{{ row.nrp || '-' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-800 text-sm">{{ row.jabatan || '-' }}</div>
                        <div class="text-xs text-slate-500">{{ row.unit || '-' }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div v-if="row.atasanNama" class="font-bold text-slate-800 text-sm">{{ row.atasanNama }}</div>
                        <span v-else class="inline-block rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-500">N/A</span>
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
                    <td class="px-4 py-3 align-middle text-sm text-slate-700">
                        {{ row.tempat || 'Tanjung' }}
                    </td>
                    <td class="px-4 py-3 align-middle text-center whitespace-nowrap">
                        <button
                            v-if="row.foto"
                            type="button"
                            @click="viewPhoto(row)"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-xs border border-blue-200 transition shadow-2xs cursor-pointer"
                            title="Lihat foto bukti pakta"
                        >
                            <ImageIcon class="w-3.5 h-3.5 text-blue-600" />
                            <span>Lihat Foto</span>
                        </button>
                        <span v-else class="text-xs text-slate-300">-</span>
                    </td>
                    <td class="pe-4 py-3 align-middle">
                        <div class="flex items-center justify-center gap-1.5">
                            <a
                                :href="`/one-power/pembinaan/pakta-integritas/${row.id}/print`"
                                target="_blank"
                                title="Cetak Pakta"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-500 text-emerald-600 hover:bg-emerald-50 transition-colors"
                            >
                                <Printer class="h-4 w-4" />
                            </a>
                            <Link
                                v-if="canManage"
                                :href="`/one-power/pembinaan/pakta-integritas/${row.id}/edit`"
                                title="Edit"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-blue-500 text-blue-600 hover:bg-blue-50 transition-colors"
                            >
                                <Pencil class="h-4 w-4" />
                            </Link>
                            <button
                                v-if="canManage"
                                type="button"
                                title="Hapus"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-rose-400 text-rose-500 hover:bg-rose-50 transition-colors"
                                @click="emit('delete', row)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
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
        :title="`Foto Bukti Pakta Integritas • ${activePhotoModal?.nomorSurat || ''}`"
        :subtitle="`${activePhotoModal?.nrp || ''} - ${activePhotoModal?.nama || ''}`"
        @close="activePhotoModal = null"
    />
</template>
