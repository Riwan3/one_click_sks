<script setup>
import { ImageOff, Pencil, Trash2 } from '@lucide/vue';

defineProps({
    rows: { type: Array, required: true },
});

const emit = defineEmits(['edit', 'preview-photo', 'delete']);

const statusStyles = {
    Open: 'bg-red-100 text-red-700',
    'In Progress': 'bg-orange-100 text-orange-700',
    Closed: 'bg-emerald-100 text-emerald-700',
};
</script>

<template>
    <div class="print-scroll-table overflow-x-auto bg-white shadow-sm">
        <table class="w-full min-w-[1400px] border-collapse border border-slate-300 text-center text-[10px] md:text-xs">
            <thead>
                <tr class="bg-[#0F5132] text-white">
                    <th class="border border-slate-300 p-2 font-semibold uppercase">No</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">NAMA OPERATOR</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">NRP</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">No. Unit</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Area</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Waktu</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Jenis Deviasi</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Deviasi Ke</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Temuan</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Evidence ICC</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Follow UP Hasil Intervensi</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Follow UP Hasil Intervensi 2</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Note/Tindak Lanjut (Intervensi)</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Sumber</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Group Leader</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Status</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase">Remark</th>
                    <th class="border border-slate-300 p-2 font-semibold uppercase no-print">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, index) in rows" :key="row.id" class="hover:bg-slate-50/60">
                    <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ index + 1 }}</td>
                    <td class="border border-slate-300 p-2 align-middle font-semibold text-slate-800 text-left">{{ row.namaOperator }}</td>
                    <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ row.nrp }}</td>
                    <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ row.noUnit }}</td>
                    <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ row.area }}</td>
                    <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ row.waktu }}</td>
                    <td class="border border-slate-300 p-2 align-middle text-slate-800">{{ row.jenisDeviasi }}</td>
                    <td class="border border-slate-300 p-2 align-middle text-center text-slate-700">{{ row.deviasiKe }}</td>
                    <td class="border border-slate-300 p-2 align-middle">
                        {{ row.temuan }}
                    </td>
                    <td v-for="field in ['evidenceIcc', 'followUp1', 'followUp2']" :key="field" class="border border-slate-300 p-2 align-middle">
                        <button
                            v-if="row[field]"
                            type="button"
                            class="mx-auto h-10 w-14 overflow-hidden rounded border border-slate-200"
                            @click="emit('preview-photo', row[field])"
                        >
                            <img :src="row[field]" class="h-full w-full object-cover">
                        </button>
                        <span v-else class="mx-auto flex h-10 w-14 items-center justify-center rounded border border-dashed border-slate-200 text-slate-300">
                            <ImageOff class="h-4 w-4" />
                        </span>
                    </td>
                    <td class="border border-slate-300 p-2 align-middle text-slate-700 text-left max-w-[140px] truncate" :title="row.note">{{ row.note }}</td>
                    <td class="border border-slate-300 p-2 align-middle text-slate-700">{{ row.sumber }}</td>
                    <td class="border border-slate-300 p-2 align-middle text-slate-700 text-left">{{ row.groupLeader }}</td>
                    <td class="border border-slate-300 p-2 align-middle">
                        <span :class="['rounded-full px-2 py-0.5 font-semibold text-[10px]', statusStyles[row.status]]">{{ row.status }}</span>
                    </td>
                    <td class="border border-slate-300 p-2 align-middle text-slate-700 text-left">{{ row.remark }}</td>
                    <td class="border border-slate-300 p-2 align-middle no-print">
                        <div class="flex items-center justify-center gap-1.5">
                            <button type="button" class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100" title="Edit" @click="emit('edit', row)">
                                <Pencil class="h-3.5 w-3.5" />
                            </button>
                            <button type="button" class="flex h-7 w-7 items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100" title="Hapus" @click="emit('delete', row)">
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
                <tr v-if="rows.length === 0">
                    <td colspan="18" class="border border-slate-300 p-4 text-center text-slate-500">Tidak ada data untuk tanggal dan shift ini.</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
