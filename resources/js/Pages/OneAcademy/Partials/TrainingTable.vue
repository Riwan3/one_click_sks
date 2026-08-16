<script setup>
import { CheckCircle2, PlayCircle, CalendarCheck, Hourglass, XCircle, FilePlus2, Pencil, Printer } from '@lucide/vue';

defineProps({
    submissions: { type: Array, required: true },
});

const emit = defineEmits(['edit']);

const badgeStyles = {
    'Selesai': { icon: CheckCircle2, class: 'bg-emerald-100 text-emerald-700' },
    'Lulus': { icon: CheckCircle2, class: 'bg-emerald-100 text-emerald-700' },
    'Sedang Berlangsung': { icon: PlayCircle, class: 'bg-amber-100 text-amber-700' },
    'Menunggu Jadwal': { icon: CalendarCheck, class: 'bg-sky-100 text-sky-700' },
    'Menunggu Approval': { icon: Hourglass, class: 'bg-slate-100 text-slate-600' },
    'Dibatalkan': { icon: XCircle, class: 'bg-red-100 text-red-700' },
};

const badgeFor = (tahapan) => badgeStyles[tahapan] ?? { icon: FilePlus2, class: 'bg-blue-100 text-blue-700' };
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full min-w-[900px] text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-[0.7rem] uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3 font-semibold">No</th>
                    <th class="px-4 py-3 font-semibold">Tanggal</th>
                    <th class="px-4 py-3 font-semibold">Jenis Training</th>
                    <th class="px-4 py-3 font-semibold">Type Unit / Program</th>
                    <th class="px-4 py-3 font-semibold">Peserta</th>
                    <th class="px-4 py-3 font-semibold">Status Tahapan</th>
                    <th class="px-4 py-3 font-semibold">Catatan</th>
                    <th class="px-4 py-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="submissions.length === 0">
                    <td colspan="8" class="px-4 py-8 text-center text-slate-400">Tidak ada data yang cocok dengan filter.</td>
                </tr>
                <tr v-for="(row, index) in submissions" :key="row.id" class="border-b border-slate-50 last:border-0 hover:bg-slate-50/60">
                    <td class="px-4 py-3 align-middle text-slate-500">{{ index + 1 }}</td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-medium text-slate-700">{{ row.tanggal }}</div>
                        <div class="text-xs text-slate-400">{{ row.jam }} WIB</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span class="rounded-full bg-slate-800 px-2.5 py-1 text-xs font-semibold text-white">{{ row.jenisPengajuan }}</span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <div class="font-semibold text-slate-700">{{ row.typeUnit }}</div>
                        <div class="text-xs text-slate-400">Pengaju: {{ row.diajukanNama }}</div>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ row.totalPeserta }}</span>
                    </td>
                    <td class="px-4 py-3 align-middle">
                        <span :class="['inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold', badgeFor(row.tahapan).class]">
                            <component :is="badgeFor(row.tahapan).icon" class="h-3.5 w-3.5" />
                            {{ row.tahapan }}
                        </span>
                    </td>
                    <td class="max-w-[180px] truncate px-4 py-3 align-middle text-slate-500" :title="row.catatan">{{ row.catatan }}</td>
                    <td class="px-4 py-3 align-middle">
                        <div class="flex gap-1.5">
                            <button
                                type="button"
                                title="Update Tahapan"
                                class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100"
                                @click="emit('edit', row)"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                title="Print Pengajuan (segera hadir)"
                                class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg bg-slate-100 text-slate-400"
                            >
                                <Printer class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
