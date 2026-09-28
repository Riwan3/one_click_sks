<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, Printer, Image as ImageIcon, Eye, X } from 'lucide-vue-next';

defineProps({
    skPembinaans: { type: Array, required: true },
    canManage: { type: Boolean, default: true },
});

const emit = defineEmits(['delete']);

const statusBadge = {
    Aktif: 'bg-red-100 text-red-700',
    Selesai: 'bg-slate-200 text-slate-600',
};

const activePhotoModal = ref(null);

const viewPhoto = (row) => {
    activePhotoModal.value = row;
};

const closePhotoModal = () => {
    activePhotoModal.value = null;
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[960px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">No. Surat</th>
                    <th class="px-4 py-3 font-semibold">NRP &amp; Nama</th>
                    <th class="px-4 py-3 font-semibold">Lubang</th>
                    <th class="px-4 py-3 font-semibold">Berlaku Sampai</th>
                    <th class="px-4 py-3 font-semibold text-center">Foto Bukti</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="skPembinaans.length === 0">
                    <td colspan="7" class="px-4 py-8 text-center text-slate-400">Data tidak ditemukan.</td>
                </tr>
                <tr v-for="row in skPembinaans" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle text-xs font-semibold text-slate-700">{{ row.nomorSurat || '-' }}</td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                        <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle text-xs font-semibold text-slate-700">{{ row.lubang }}</td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.berlakuSampai || '-' }}</td>
                    
                    <!-- Foto Bukti -->
                    <td class="px-4 py-3 align-middle text-center">
                        <button
                            v-if="row.foto"
                            type="button"
                            @click="viewPhoto(row)"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold text-xs border border-blue-200 transition shadow-2xs cursor-pointer"
                            title="Lihat foto bukti pelanggaran"
                        >
                            <ImageIcon class="w-3.5 h-3.5 text-blue-600" />
                            <span>Lihat Foto</span>
                        </button>
                        <span v-else class="text-xs text-slate-300">-</span>
                    </td>

                    <td class="px-4 py-3 align-middle">
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', statusBadge[row.statusPembinaan] ?? 'bg-slate-100 text-slate-600']">
                            {{ row.statusPembinaan }}
                        </span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex gap-1.5">
                            <a :href="`/one-power/pembinaan/sk-pembinaan/${row.id}/print`" target="_blank" title="Cetak" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50">
                                <Printer class="h-3.5 w-3.5" />
                            </a>
                            <template v-if="canManage">
                                <Link :href="`/one-power/pembinaan/sk-pembinaan/${row.id}/edit`" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50">
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

    <!-- MODAL PREVIEW FOTO BUKTI -->
    <div 
        v-if="activePhotoModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
        @click.self="closePhotoModal"
    >
        <div class="relative max-w-3xl w-full bg-white rounded-2xl overflow-hidden shadow-2xl flex flex-col">
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 bg-slate-50">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <ImageIcon class="w-4 h-4 text-blue-600" />
                        Foto Bukti Pelanggaran &bull; {{ activePhotoModal.nomorSurat || 'SK Pembinaan' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ activePhotoModal.nrp }} - {{ activePhotoModal.nama }} ({{ activePhotoModal.tanggalPelanggaran || '-' }})
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a
                        :href="activePhotoModal.foto"
                        target="_blank"
                        class="text-xs font-semibold text-blue-600 hover:underline px-2.5 py-1 rounded-lg hover:bg-blue-50"
                    >
                        Buka Tab Baru &nearr;
                    </a>
                    <button
                        type="button"
                        @click="closePhotoModal"
                        class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200/60 transition"
                    >
                        <X class="w-5 h-5" />
                    </button>
                </div>
            </div>
            <div class="p-4 overflow-auto flex items-center justify-center bg-slate-900/5 max-h-[75vh]">
                <img 
                    :src="activePhotoModal.foto" 
                    :alt="`Bukti ${activePhotoModal.nomorSurat}`" 
                    class="max-w-full max-h-[70vh] object-contain rounded-xl shadow-sm border border-slate-200 bg-white" 
                />
            </div>
        </div>
    </div>
</template>
