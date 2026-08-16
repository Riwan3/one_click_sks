<script setup>
import { ImageOff, Pencil } from '@lucide/vue';

defineProps({
    rows: { type: Array, required: true },
});

const emit = defineEmits(['edit', 'preview-photo']);

const statusStyles = {
    Open: 'bg-red-100 text-red-700',
    'In Progress': 'bg-orange-100 text-orange-700',
    Closed: 'bg-emerald-100 text-emerald-700',
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1400px] text-left text-xs">
            <thead>
                <tr class="border-b border-slate-100 uppercase tracking-wide text-slate-500" style="background:#1e5e3f10;">
                    <th class="px-3 py-3 font-semibold">No</th>
                    <th class="px-3 py-3 font-semibold">Nama Operator</th>
                    <th class="px-3 py-3 font-semibold">NRP</th>
                    <th class="px-3 py-3 font-semibold">No. Unit</th>
                    <th class="px-3 py-3 font-semibold">Area</th>
                    <th class="px-3 py-3 font-semibold">Waktu</th>
                    <th class="px-3 py-3 font-semibold">Jenis Deviasi</th>
                    <th class="px-3 py-3 font-semibold">Deviasi Ke</th>
                    <th class="px-3 py-3 font-semibold">Temuan</th>
                    <th class="px-3 py-3 font-semibold">Evidence ICC</th>
                    <th class="px-3 py-3 font-semibold">FU Intervensi 1</th>
                    <th class="px-3 py-3 font-semibold">FU Intervensi 2</th>
                    <th class="px-3 py-3 font-semibold">Note</th>
                    <th class="px-3 py-3 font-semibold">Sumber</th>
                    <th class="px-3 py-3 font-semibold">Group Leader</th>
                    <th class="px-3 py-3 font-semibold">Status</th>
                    <th class="px-3 py-3 font-semibold no-print">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, index) in rows" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ index + 1 }}</td>
                    <td class="px-3 py-2.5 align-middle font-semibold text-slate-700">{{ row.namaOperator }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ row.nrp }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ row.noUnit }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ row.area }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ row.waktu }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-700">{{ row.jenisDeviasi }}</td>
                    <td class="px-3 py-2.5 align-middle text-center text-slate-500">{{ row.deviasiKe }}</td>
                    <td class="px-3 py-2.5 align-middle">
                        <span class="rounded-full bg-sky-100 px-2 py-0.5 font-semibold text-sky-700">{{ row.temuan }}</span>
                    </td>
                    <td v-for="field in ['evidenceIcc', 'followUp1', 'followUp2']" :key="field" class="px-3 py-2.5 align-middle">
                        <button
                            v-if="row[field]"
                            type="button"
                            class="h-10 w-14 overflow-hidden rounded border border-slate-200"
                            @click="emit('preview-photo', row[field])"
                        >
                            <img :src="row[field]" class="h-full w-full object-cover">
                        </button>
                        <span v-else class="flex h-10 w-14 items-center justify-center rounded border border-dashed border-slate-200 text-slate-300">
                            <ImageOff class="h-4 w-4" />
                        </span>
                    </td>
                    <td class="max-w-[140px] truncate px-3 py-2.5 align-middle text-slate-500" :title="row.note">{{ row.note }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ row.sumber }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ row.groupLeader }}</td>
                    <td class="px-3 py-2.5 align-middle">
                        <span :class="['rounded-full px-2 py-0.5 font-semibold', statusStyles[row.status]]">{{ row.status }}</span>
                    </td>
                    <td class="px-3 py-2.5 align-middle no-print">
                        <button type="button" class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100" @click="emit('edit', row)">
                            <Pencil class="h-3.5 w-3.5" />
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
