<script setup>
defineProps({
    rows: { type: Array, required: true },
});

const statusStyles = {
    Aktif: 'bg-emerald-100 text-emerald-700',
    Draft: 'bg-slate-200 text-slate-600',
    Review: 'bg-amber-100 text-amber-700',
};
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[800px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">Kode</th>
                    <th class="px-4 py-3 font-semibold">Nama Prosedur</th>
                    <th class="px-4 py-3 font-semibold">Kategori</th>
                    <th class="px-4 py-3 font-semibold">Update Terakhir</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="rows.length === 0">
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada data yang cocok dengan pencarian.</td>
                </tr>
                <tr v-for="row in rows" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle text-slate-500">{{ row.kodeProsedur }}</td>
                    <td class="px-4 py-3 align-middle font-semibold text-slate-700">{{ row.namaProsedur }}</td>
                    <td class="px-4 py-3 align-middle text-slate-500">{{ row.kategori }}</td>
                    <td class="px-4 py-3 align-middle text-slate-500">{{ row.tanggalUpdate }}</td>
                    <td class="px-4 py-3 align-middle">
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', statusStyles[row.status]]">{{ row.status }}</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
