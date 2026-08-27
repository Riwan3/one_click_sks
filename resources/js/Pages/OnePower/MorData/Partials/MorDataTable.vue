<script setup>
import { Pencil, Trash2 } from '@lucide/vue';

defineProps({
    items: { type: Array, required: true },
});

const emit = defineEmits(['edit', 'delete']);
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1000px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">Ranking</th>
                    <th class="px-4 py-3 font-semibold">NRP / Nama</th>
                    <th class="px-4 py-3 font-semibold">ATR</th>
                    <th class="px-4 py-3 font-semibold">Timesheet</th>
                    <th class="px-4 py-3 font-semibold">HM Ratio</th>
                    <th class="px-4 py-3 font-semibold">Fuel</th>
                    <th class="px-4 py-3 font-semibold text-blue-600">Total Poin</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="items.length === 0">
                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada data MOR untuk periode ini.</td>
                </tr>
                <tr v-for="row in items" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700">{{ row.ranking ?? '-' }}</span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                        <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.atrPersen.toFixed(1) }}% ({{ row.atrPoint.toFixed(1) }})</td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.timesheetPersen.toFixed(1) }}%</td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.hmRatio.toFixed(1) }}%</td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">{{ row.fuelPersen.toFixed(1) }}%</td>
                    <td class="px-4 py-3 align-middle">
                        <span class="text-base font-bold text-blue-600">{{ row.totalPoin.toFixed(1) }}</span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex gap-1.5">
                            <button type="button" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50" @click="emit('edit', row)">
                                <Pencil class="h-3.5 w-3.5" />
                            </button>
                            <button type="button" title="Hapus" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-red-500 hover:bg-red-50" @click="emit('delete', row)">
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
