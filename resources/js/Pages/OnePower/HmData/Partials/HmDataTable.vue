<script setup>
import { Pencil, Trash2 } from '@lucide/vue';

defineProps({
    hmData: { type: Array, required: true },
});

const emit = defineEmits(['edit', 'delete']);
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[1000px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">Tanggal / Shift</th>
                    <th class="px-4 py-3 font-semibold">NRP / Nama</th>
                    <th class="px-4 py-3 font-semibold">Unit / Dept</th>
                    <th class="px-4 py-3 font-semibold">HM Start / Stop</th>
                    <th class="px-4 py-3 font-semibold text-blue-600">Diff</th>
                    <th class="px-4 py-3 font-semibold">Activity</th>
                    <th class="px-4 py-3 font-semibold">Parking</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="hmData.length === 0">
                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada data HM.</td>
                </tr>
                <tr v-for="row in hmData" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-700">{{ row.tanggal }}</div>
                        <span class="inline-block rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[0.65rem] font-semibold text-slate-500">SHIFT {{ row.shift }}</span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-blue-600">{{ row.nrp }}</div>
                        <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold uppercase text-slate-700">{{ row.unit }}</div>
                        <div class="text-xs text-slate-400">{{ row.dept || '-' }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-500">
                        {{ row.hmStart.toFixed(2) }} - {{ row.hmStop.toFixed(2) }}
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span class="text-base font-bold text-blue-600">{{ row.hmDiff.toFixed(2) }}</span>
                    </td>
                    <td class="max-w-[150px] truncate px-4 py-3 align-middle text-xs text-slate-600" :title="row.activity">
                        {{ row.activity || '-' }}
                    </td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-500">{{ row.parking || '-' }}</td>
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
