<script setup>
import RosterCellEditor from './RosterCellEditor.vue';

defineProps({
    rows: { type: Array, required: true },
});
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.65rem] uppercase tracking-wide text-slate-500">
                    <th class="sticky left-0 z-10 min-w-[220px] bg-white px-4 py-3 font-semibold">Karyawan</th>
                    <th v-for="day in (rows[0]?.days ?? [])" :key="day.tanggal" class="min-w-[36px] px-1 py-3 text-center font-semibold">
                        {{ day.tanggal.slice(8, 10) }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="rows.length === 0">
                    <td :colspan="32" class="px-4 py-8 text-center text-slate-400">Belum ada karyawan dengan kategori roster.</td>
                </tr>
                <tr v-for="row in rows" :key="row.employeeId" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="sticky left-0 z-10 bg-white px-4 py-2 align-middle">
                        <div class="font-bold text-slate-700">{{ row.nama }}</div>
                        <div class="text-xs text-slate-400">{{ row.nrp }} &middot; {{ row.jabatanRoster || row.jabatan }}</div>
                    </td>
                    <td v-for="day in row.days" :key="day.tanggal" class="px-1 py-1 text-center align-middle">
                        <RosterCellEditor :employee-id="row.employeeId" :tanggal="day.tanggal" :kode="day.kode" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
