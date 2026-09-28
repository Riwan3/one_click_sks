<script setup>
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, Printer } from '@lucide/vue';

const props = defineProps({
    items: { type: Array, required: true },
    pagination: { type: Object, default: () => ({ currentPage: 1 }) },
    canManage: { type: Boolean, default: true },
});

const emit = defineEmits(['delete']);

const getIndex = (index) => {
    const page = props.pagination?.currentPage || 1;
    return (page - 1) * 10 + index + 1;
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1050px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200 bg-slate-50/70 text-[0.7rem] uppercase tracking-wider text-slate-600 font-bold">
                    <th class="px-3 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">NRP</th>
                    <th class="px-4 py-3">Nama Karyawan</th>
                    <th class="px-4 py-3">Jabatan</th>
                    <th class="px-4 py-3">Jenis Pembinaan</th>
                    <th class="px-4 py-3">Detail</th>
                    <th class="px-4 py-3">Diinput Oleh (Admin)</th>
                    <th class="px-4 py-3">Pengawas</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="items.length === 0">
                    <td colspan="10" class="px-4 py-10 text-center text-slate-400">
                        Data berita acara tidak ditemukan.
                    </td>
                </tr>
                <tr
                    v-for="(row, index) in items"
                    :key="row.id"
                    class="border-b border-slate-100 last:border-0 hover:bg-slate-50/70 transition-colors"
                >
                    <td class="px-3 py-3 align-middle text-center text-xs font-semibold text-slate-500">
                        {{ getIndex(index) }}
                    </td>
                    <td class="px-4 py-3 align-middle text-xs font-medium text-slate-700 whitespace-nowrap">
                        {{ row.tanggal }}
                    </td>
                    <td class="px-4 py-3 align-middle text-xs font-bold text-blue-600 whitespace-nowrap">
                        {{ row.nrp }}
                    </td>
                    <td class="px-4 py-3 align-middle text-xs font-semibold text-slate-800 uppercase">
                        {{ row.nama }}
                    </td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600 uppercase">
                        {{ row.jabatan }}
                    </td>
                    <td class="px-4 py-3 align-middle text-xs whitespace-nowrap">
                        <span class="inline-flex rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700">
                            {{ row.jenisPembinaan || 'Berita Acara' }}
                        </span>
                        <div v-if="row.nomorSurat" class="mt-0.5 text-[11px] font-mono text-slate-400">
                            {{ row.nomorSurat }}
                        </div>
                    </td>
                    <td
                        class="max-w-[240px] truncate px-4 py-3 align-middle text-xs text-slate-600"
                        :title="row.detail || row.kronologi"
                    >
                        {{ row.detail || row.kronologi }}
                    </td>
                    <td class="px-4 py-3 align-middle text-xs font-medium text-slate-700 whitespace-nowrap">
                        {{ row.createdByName || '-' }}
                    </td>
                    <td class="px-4 py-3 align-middle text-xs font-medium text-slate-700 whitespace-nowrap">
                        {{ row.pengawas || row.diketahuiOlehNama || '-' }}
                    </td>
                    <td class="px-4 py-3 align-middle text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <Link
                                :href="`/one-power/pembinaan/berita-acara/${row.id}/print`"
                                title="Cetak"
                                target="_blank"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-slate-600 transition hover:bg-slate-100 hover:text-slate-800 shadow-2xs"
                            >
                                <Printer class="h-3.5 w-3.5" />
                            </Link>
                            <template v-if="canManage">
                                <Link
                                    :href="`/one-power/pembinaan/berita-acara/${row.id}/edit`"
                                    title="Edit"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-600 transition hover:bg-amber-50 hover:text-amber-700 shadow-2xs"
                                >
                                    <Pencil class="h-3.5 w-3.5" />
                                </Link>
                                <button
                                    type="button"
                                    title="Hapus"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-red-600 transition hover:bg-red-50 hover:text-red-700 shadow-2xs"
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
