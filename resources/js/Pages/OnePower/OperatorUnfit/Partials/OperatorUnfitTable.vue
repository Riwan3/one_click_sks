<script setup>
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, CalendarCheck, Paperclip } from '@lucide/vue';

defineProps({
    unfits: { type: Array, required: true },
});

const emit = defineEmits(['end-unfit', 'delete']);
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">NRP &amp; Nama</th>
                    <th class="px-4 py-3 font-semibold">Diagnosa</th>
                    <th class="px-4 py-3 font-semibold">Periode Unfit</th>
                    <th class="px-4 py-3 font-semibold">Status</th>
                    <th class="px-4 py-3 font-semibold">Bukti</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="unfits.length === 0">
                    <td colspan="6" class="px-4 py-8 text-center text-slate-400">Data tidak ditemukan.</td>
                </tr>
                <tr v-for="row in unfits" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle">
                        <div class="font-bold text-slate-700">{{ row.nrp }}</div>
                        <div class="text-xs uppercase text-slate-500">{{ row.nama }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="text-xs text-slate-700">{{ row.diagnosa || '-' }}</div>
                        <div class="text-xs text-slate-400">{{ row.dokterRujukan || '-' }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle text-xs text-slate-600">
                        {{ row.tglMulaiUnfit }} &ndash; {{ row.tglAkhirUnfit || 'berlangsung' }}
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', row.isActive ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700']">
                            {{ row.isActive ? 'UNFIT' : 'FIT' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <a v-if="row.buktiUrl" :href="row.buktiUrl" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:underline">
                            <Paperclip class="h-3.5 w-3.5" /> Lihat
                        </a>
                        <span v-else class="text-xs text-slate-400">-</span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex gap-1.5">
                            <button
                                v-if="row.isActive"
                                type="button"
                                title="Akhiri Unfit"
                                class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-emerald-500 hover:bg-emerald-50"
                                @click="emit('end-unfit', row)"
                            >
                                <CalendarCheck class="h-3.5 w-3.5" />
                            </button>
                            <Link :href="`/one-power/deviasi-operator/unfit/${row.id}/edit`" title="Edit" class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 text-amber-500 hover:bg-amber-50">
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
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
