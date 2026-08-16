<script setup>
defineProps({
    rows: { type: Array, required: true },
});

const statusStyles = {
    Compliant: 'bg-emerald-100 text-emerald-700',
    'Non-Compliant': 'bg-red-100 text-red-700',
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[900px] text-left text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-violet-500/5 uppercase tracking-wide text-slate-500">
                    <th class="px-3 py-3 font-semibold">No</th>
                    <th class="px-3 py-3 font-semibold">Tanggal</th>
                    <th class="px-3 py-3 font-semibold">No. Unit</th>
                    <th class="px-3 py-3 font-semibold">Nama Operator</th>
                    <th class="px-3 py-3 font-semibold">Group Leader</th>
                    <th class="px-3 py-3 font-semibold">Item Compliance</th>
                    <th class="px-3 py-3 font-semibold">Status</th>
                    <th class="px-3 py-3 font-semibold">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, index) in rows" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ index + 1 }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ row.tanggal }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ row.noUnit }}</td>
                    <td class="px-3 py-2.5 align-middle font-semibold text-slate-700">{{ row.namaOperator }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-500">{{ row.groupLeader }}</td>
                    <td class="px-3 py-2.5 align-middle text-slate-700">{{ row.itemCompliance }}</td>
                    <td class="px-3 py-2.5 align-middle">
                        <span :class="['rounded-full px-2 py-0.5 font-semibold', statusStyles[row.status]]">{{ row.status }}</span>
                    </td>
                    <td class="max-w-[220px] truncate px-3 py-2.5 align-middle text-slate-500" :title="row.keterangan">{{ row.keterangan }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
