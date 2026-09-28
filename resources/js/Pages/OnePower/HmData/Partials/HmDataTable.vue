<script setup>
import { Pencil, Trash2, Gauge, MapPin, Activity, Calendar } from '@lucide/vue';

defineProps({
    hmData: { type: Array, required: true },
    isOperator: { type: Boolean, default: false },
});

const emit = defineEmits(['edit', 'delete']);
</script>

<template>
    <div>
        <!-- Empty State -->
        <div v-if="hmData.length === 0" class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center shadow-xs">
            <div class="mx-auto mb-2.5 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                <Gauge class="h-6 w-6 stroke-[1.8]" />
            </div>
            <p class="m-0 text-sm font-extrabold text-slate-700">Belum Ada Data HM</p>
            <p class="m-0 mt-1 text-xs text-slate-400">Data penggunaan jam kerja alat pada rentang tanggal ini belum ditemukan.</p>
        </div>

        <template v-else>
            <!-- Mobile Cards View (Muncul di layar kecil / mobile) -->
            <div class="sm:hidden space-y-3">
                <div
                    v-for="row in hmData"
                    :key="row.id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs"
                >
                    <!-- Header Kartu -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-black text-slate-800">{{ row.tanggal }}</span>
                            <span class="inline-flex rounded-md bg-blue-50 border border-blue-200/60 px-2 py-0.5 text-[10px] font-black text-blue-700">
                                SHIFT {{ row.shift }}
                            </span>
                        </div>
                        <span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-black text-slate-800 uppercase tracking-tight">
                            {{ row.unit || '-' }}
                        </span>
                    </div>

                    <!-- HM Stat Box -->
                    <div class="mt-3 grid grid-cols-2 gap-2 rounded-xl bg-slate-50/80 p-2.5 border border-slate-100">
                        <div>
                            <span class="text-[9px] font-black uppercase tracking-wider text-slate-400">HM START &rarr; STOP</span>
                            <div class="text-xs font-black text-slate-700 mt-0.5">
                                {{ row.hmStart.toFixed(2) }} &ndash; {{ row.hmStop.toFixed(2) }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[9px] font-black uppercase tracking-wider text-blue-500">TOTAL DIFERENSIAL</span>
                            <div class="text-sm font-black text-blue-600 mt-0.5">
                                +{{ row.hmDiff.toFixed(2) }} <span class="text-[10px] font-bold">Jam</span>
                            </div>
                        </div>
                    </div>

                    <!-- Metadata Aktivitas & Parkir -->
                    <div class="mt-2.5 flex flex-col gap-1 text-xs">
                        <div v-if="row.activity" class="flex items-center justify-between text-slate-600">
                            <span class="text-[11px] text-slate-400">Aktivitas:</span>
                            <span class="font-bold text-slate-700 truncate max-w-[200px]">{{ row.activity }}</span>
                        </div>
                        <div v-if="row.parking" class="flex items-center justify-between text-slate-600">
                            <span class="text-[11px] text-slate-400">Parkir:</span>
                            <span class="font-semibold text-slate-600 truncate max-w-[200px]">{{ row.parking }}</span>
                        </div>
                        <div v-if="!isOperator && row.nama" class="flex items-center justify-between text-slate-500 pt-1 border-t border-slate-100">
                            <span class="text-[11px] text-slate-400">Operator:</span>
                            <span class="font-bold text-slate-700">{{ row.nrp }} &ndash; {{ row.nama }}</span>
                        </div>
                    </div>

                    <!-- Action Buttons jika bukan operator -->
                    <div v-if="!isOperator" class="mt-3 flex justify-end gap-2 border-t border-slate-100 pt-2.5">
                        <button type="button" class="flex items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700" @click="emit('edit', row)">
                            <Pencil class="h-3.5 w-3.5" /> Edit
                        </button>
                        <button type="button" class="flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700" @click="emit('delete', row)">
                            <Trash2 class="h-3.5 w-3.5" /> Hapus
                        </button>
                    </div>
                </div>
            </div>

            <!-- Desktop Table View (Tampil di Tablet / Desktop) -->
            <div class="hidden sm:block overflow-x-auto rounded-2xl border border-slate-200/80 bg-white shadow-xs">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500 bg-slate-50/70">
                            <th class="px-4 py-3 font-semibold">Tanggal / Shift</th>
                            <th v-if="!isOperator" class="px-4 py-3 font-semibold">NRP / Nama</th>
                            <th class="px-4 py-3 font-semibold">Unit / Dept</th>
                            <th class="px-4 py-3 font-semibold">HM Start / Stop</th>
                            <th class="px-4 py-3 font-semibold text-blue-600">Diff</th>
                            <th class="px-4 py-3 font-semibold">Activity</th>
                            <th class="px-4 py-3 font-semibold">Parking</th>
                            <th v-if="!isOperator" class="px-4 py-3 font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="row in hmData" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                            <td class="px-4 py-3 align-middle">
                                <div class="font-bold text-slate-700">{{ row.tanggal }}</div>
                                <span class="inline-block rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[0.65rem] font-semibold text-slate-500">SHIFT {{ row.shift }}</span>
                            </td>
                            <td v-if="!isOperator" class="px-4 py-3 align-middle">
                                <div class="font-bold text-blue-600">{{ row.nrp }}</div>
                                <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <div class="font-bold uppercase text-slate-700">{{ row.unit }}</div>
                                <div class="text-xs text-slate-400">{{ row.dept || '-' }}</div>
                            </td>
                            <td class="px-4 py-3 align-middle text-xs text-slate-500">
                                {{ row.hmStart.toFixed(2) }} &ndash; {{ row.hmStop.toFixed(2) }}
                            </td>
                            <td class="px-4 py-3 align-middle">
                                <span class="text-base font-bold text-blue-600">+{{ row.hmDiff.toFixed(2) }}</span>
                            </td>
                            <td class="max-w-[150px] truncate px-4 py-3 align-middle text-xs text-slate-600" :title="row.activity">
                                {{ row.activity || '-' }}
                            </td>
                            <td class="px-4 py-3 align-middle text-xs text-slate-500">{{ row.parking || '-' }}</td>
                            <td v-if="!isOperator" class="px-4 py-3 align-middle">
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
    </div>
</template>
