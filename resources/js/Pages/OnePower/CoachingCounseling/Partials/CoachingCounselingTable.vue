<script setup>
import { Link } from '@inertiajs/vue3';
import { Pencil, Trash2, FileText, FilePlus, Image, Printer, Clock, MapPin, User } from 'lucide-vue-next';

defineProps({
    items: { type: Array, required: true },
    canManage: { type: Boolean, default: true },
});

const emit = defineEmits(['delete']);

const formatIndoDate = (dateStr) => {
    if (!dateStr) return '-';
    try {
        const parts = String(dateStr).split('-');
        if (parts.length === 3) {
            const months = [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ];
            const day = parseInt(parts[2], 10);
            const month = months[parseInt(parts[1], 10) - 1];
            const year = parts[0];
            return `${day} ${month} ${year}`;
        }
        return dateStr;
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <div>
        <!-- Empty State -->
        <div v-if="items.length === 0" class="rounded-2xl border border-slate-200/80 bg-white p-8 sm:p-12 text-center shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-500">
                <FileText class="h-7 w-7" />
            </div>
            <h3 class="mt-3 text-base font-bold text-slate-800">Belum Ada Data Coaching &amp; Counseling</h3>
            <p class="mt-1 text-xs sm:text-sm text-slate-500 max-w-sm mx-auto">
                Tidak ditemukan data pembinaan pada rentang tanggal atau kriteria pencarian saat ini.
            </p>
            <div v-if="canManage" class="mt-4">
                <Link
                    href="/one-power/pembinaan/coaching-counseling/create"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 text-xs sm:text-sm font-bold text-white hover:bg-blue-700 shadow-sm transition"
                >
                    <FilePlus class="h-4 w-4" /> Buat Coaching &amp; Counseling Baru
                </Link>
            </div>
        </div>

        <template v-else>
            <!-- DESKTOP VIEW: Table (hidden on mobile, visible on md+) -->
            <div class="hidden md:block overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full min-w-[1250px] text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50/70 text-xs font-semibold text-slate-600">
                            <th class="px-3.5 py-3 whitespace-nowrap">No. Dokumen</th>
                            <th class="px-3.5 py-3 whitespace-nowrap">Tanggal</th>
                            <th class="px-3.5 py-3 whitespace-nowrap">NRP / Nama</th>
                            <th class="px-3.5 py-3 whitespace-nowrap">Jabatan / Dept</th>
                            <th class="px-3.5 py-3 whitespace-nowrap">Lokasi / Waktu</th>
                            <th class="px-3 py-3 text-center whitespace-nowrap">Materi</th>
                            <th class="px-3 py-3 text-center whitespace-nowrap">Level</th>
                            <th class="px-3.5 py-3 whitespace-nowrap">Input</th>
                            <th class="px-3.5 py-3 whitespace-nowrap">Keterangan</th>
                            <th class="px-3 py-3 text-center whitespace-nowrap">File</th>
                            <th class="px-3.5 py-3 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in items"
                            :key="row.id"
                            class="border-b border-slate-100 last:border-0 hover:bg-slate-50/60 transition-colors"
                        >
                            <!-- No. Dokumen -->
                            <td class="px-3.5 py-3 align-middle text-xs font-medium text-slate-700 whitespace-nowrap">
                                {{ row.nomorSurat || '-' }}
                            </td>

                            <!-- Tanggal -->
                            <td class="px-3.5 py-3 align-middle whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <a
                                        :href="`/one-power/pembinaan/coaching-counseling/${row.id}/print-result`"
                                        target="_blank"
                                        class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded bg-slate-800 text-white hover:bg-slate-900 transition shadow-2xs"
                                        title="Cetak Berkas / Hasil"
                                    >
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                    </a>
                                    <span class="text-xs font-medium text-slate-700">{{ formatIndoDate(row.tanggal) }}</span>
                                </div>
                            </td>

                            <!-- NRP / Nama -->
                            <td class="px-3.5 py-3 align-middle whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        :class="[
                                            'inline-block h-2.5 w-2.5 shrink-0 rounded-full',
                                            (row.statusKaryawan === 'FIT' || !row.statusKaryawan) ? 'bg-emerald-500' : 'bg-amber-400'
                                        ]"
                                        :title="row.statusKaryawan || 'FIT'"
                                    ></span>
                                    <span class="font-bold text-slate-800 text-xs">{{ row.nrp || '-' }}</span>
                                </div>
                                <div class="text-[11px] font-semibold uppercase text-slate-600 leading-tight">
                                    {{ row.nama || '-' }}
                                </div>
                            </td>

                            <!-- Jabatan / Dept -->
                            <td class="px-3.5 py-3 align-middle whitespace-nowrap">
                                <div class="font-bold text-xs uppercase text-slate-800">{{ row.jabatan || '-' }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ row.unit || '-' }}</div>
                            </td>

                            <!-- Lokasi / Waktu -->
                            <td class="px-3.5 py-3 align-middle whitespace-nowrap">
                                <div class="font-bold text-xs uppercase text-slate-800">{{ row.lokasi || '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ row.waktuPertemuan || '-' }}</div>
                            </td>

                            <!-- Materi -->
                            <td class="px-3 py-3 align-middle text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <span
                                        v-if="row.materiPekerjaan && row.materiPekerjaan != '0'"
                                        class="inline-flex h-5 w-5 items-center justify-center rounded bg-rose-600 text-[10px] font-black text-white shadow-2xs"
                                        title="Pekerjaan"
                                    >
                                        W
                                    </span>
                                    <span
                                        v-if="row.materiPribadi && row.materiPribadi != '0'"
                                        class="inline-flex h-5 w-5 items-center justify-center rounded bg-indigo-600 text-[10px] font-black text-white shadow-2xs"
                                        title="Pribadi"
                                    >
                                        P
                                    </span>
                                    <span
                                        v-if="row.materiKeluarga && row.materiKeluarga != '0'"
                                        class="inline-flex h-5 w-5 items-center justify-center rounded bg-emerald-600 text-[10px] font-black text-white shadow-2xs"
                                        title="Keluarga"
                                    >
                                        K
                                    </span>
                                    <span
                                        v-if="row.materiLingkungan && row.materiLingkungan != '0'"
                                        class="inline-flex h-5 w-5 items-center justify-center rounded bg-amber-500 text-[10px] font-black text-white shadow-2xs"
                                        title="Lingkungan"
                                    >
                                        L
                                    </span>
                                    <span
                                        v-if="!row.materiPekerjaan && !row.materiPribadi && !row.materiKeluarga && !row.materiLingkungan"
                                        class="text-xs text-slate-400"
                                    >
                                        -
                                    </span>
                                </div>
                            </td>

                            <!-- Level -->
                            <td class="px-3 py-3 align-middle text-center whitespace-nowrap text-xs text-slate-600">
                                <span v-if="row.fatigueLevel && row.fatigueLevel > 0" class="font-bold text-slate-700">
                                    {{ row.fatigueLevel }}
                                </span>
                                <span v-else class="text-slate-400">-</span>
                            </td>

                            <!-- Input -->
                            <td class="px-3.5 py-3 align-middle whitespace-nowrap">
                                <span class="text-xs font-semibold text-blue-600 hover:underline cursor-default">
                                    {{ row.coachNama || row.createdByName || '-' }}
                                </span>
                            </td>

                            <!-- Keterangan -->
                            <td class="px-3.5 py-3 align-middle text-xs text-slate-600 max-w-[140px] truncate" :title="row.rekomendasi || '-'">
                                {{ row.rekomendasi || '-' }}
                            </td>

                            <!-- File -->
                            <td class="px-3 py-3 align-middle text-center whitespace-nowrap">
                                <a
                                    v-if="row.fileBukti"
                                    :href="row.fileBukti"
                                    target="_blank"
                                    title="Lihat Foto Bukti"
                                    class="inline-flex items-center justify-center text-emerald-600 hover:text-emerald-700 transition"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                </a>
                                <span v-else class="text-xs text-slate-400">-</span>
                            </td>

                            <!-- Aksi (Deskripsi + Action Buttons) -->
                            <td class="px-3.5 py-3 align-middle whitespace-nowrap">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="max-w-[200px] truncate text-xs text-slate-600" :title="row.deskripsi || '-'">
                                        {{ row.deskripsi || '-' }}
                                    </span>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <a
                                            :href="`/one-power/pembinaan/coaching-counseling/${row.id}/print-form`"
                                            title="Cetak Form (PPA-ADRO-F-HCGA-17)"
                                            target="_blank"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-emerald-400 bg-white text-emerald-600 hover:bg-emerald-50 transition shadow-2xs"
                                        >
                                            <FileText class="h-3.5 w-3.5" />
                                        </a>
                                        <template v-if="canManage">
                                            <Link
                                                :href="`/one-power/pembinaan/coaching-counseling/${row.id}/edit`"
                                                title="Edit"
                                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-blue-400 bg-white text-blue-600 hover:bg-blue-50 transition shadow-2xs"
                                            >
                                                <Pencil class="h-3.5 w-3.5" />
                                            </Link>
                                            <button
                                                type="button"
                                                title="Hapus"
                                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-red-300 bg-white text-red-500 hover:bg-red-50 transition shadow-2xs"
                                                @click="emit('delete', row)"
                                            >
                                                <Trash2 class="h-3.5 w-3.5" />
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- MOBILE VIEW: Touch-Friendly Card List (md:hidden) -->
            <div class="space-y-3 md:hidden">
                <div
                    v-for="row in items"
                    :key="row.id"
                    class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <!-- Card Top: Nomor Surat & Status Karyawan -->
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <span class="inline-flex items-center rounded-lg bg-blue-50 px-2 py-0.5 font-mono text-[11px] font-bold text-blue-700 border border-blue-100">
                                {{ row.nomorSurat || '-' }}
                            </span>
                            <div class="mt-1 text-xs font-semibold text-slate-500">
                                {{ formatIndoDate(row.tanggal) }}
                            </div>
                        </div>
                        <span
                            :class="[
                                'inline-flex shrink-0 rounded-full px-2.5 py-0.5 text-xs font-bold border',
                                (row.statusKaryawan === 'FIT' || !row.statusKaryawan)
                                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                    : 'bg-amber-50 text-amber-700 border-amber-200'
                            ]"
                        >
                            {{ row.statusKaryawan || 'FIT' }}
                        </span>
                    </div>

                    <!-- Karyawan (Anak Asuh) -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100">
                        <div class="font-black text-slate-800 text-sm leading-snug">
                            {{ row.nama || '-' }}
                        </div>
                        <div class="text-xs font-mono text-slate-400 mt-0.5">
                            NRP: {{ row.nrp || '-' }}
                        </div>
                    </div>

                    <!-- Detail Grid -->
                    <div class="mt-2.5 grid grid-cols-2 gap-2 text-xs bg-slate-50/70 p-2.5 rounded-xl border border-slate-100">
                        <div>
                            <span class="text-slate-400 block text-[10px]">Jabatan / Unit</span>
                            <span class="font-semibold text-slate-700 block truncate">{{ row.jabatan || '-' }}</span>
                            <span class="text-slate-500 font-mono text-[11px]">{{ row.unit || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Lokasi / Waktu</span>
                            <span class="font-semibold text-slate-700 block truncate">{{ row.lokasi || '-' }}</span>
                            <span class="text-slate-500 text-[11px]">{{ row.waktuPertemuan || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Coach / Pembina</span>
                            <span class="font-semibold text-blue-600 block truncate">{{ row.coachNama || row.createdByName || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Fatigue Level</span>
                            <span v-if="row.fatigueLevel && row.fatigueLevel > 0" class="font-bold text-slate-700">Level {{ row.fatigueLevel }}</span>
                            <span v-else class="text-slate-400">-</span>
                        </div>
                    </div>

                    <!-- Materi Tags -->
                    <div class="mt-2.5 flex items-center gap-1.5 flex-wrap">
                        <span class="text-[11px] text-slate-400 font-medium">Materi:</span>
                        <span v-if="row.materiPekerjaan && row.materiPekerjaan != '0'" class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-[11px] font-bold text-rose-700 border border-rose-200">
                            Pekerjaan
                        </span>
                        <span v-if="row.materiPribadi && row.materiPribadi != '0'" class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700 border border-indigo-200">
                            Pribadi
                        </span>
                        <span v-if="row.materiKeluarga && row.materiKeluarga != '0'" class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200">
                            Keluarga
                        </span>
                        <span v-if="row.materiLingkungan && row.materiLingkungan != '0'" class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-bold text-amber-700 border border-amber-200">
                            Lingkungan
                        </span>
                        <span v-if="!row.materiPekerjaan && !row.materiPribadi && !row.materiKeluarga && !row.materiLingkungan" class="text-xs text-slate-400">
                            -
                        </span>
                    </div>

                    <!-- Catatan / Rekomendasi jika ada -->
                    <div v-if="row.rekomendasi || row.deskripsi" class="mt-2.5 text-xs text-slate-600 bg-amber-50/50 p-2 rounded-lg border border-amber-100/80">
                        <span class="font-semibold text-amber-800">Catatan: </span>
                        <span>{{ row.rekomendasi || row.deskripsi }}</span>
                    </div>

                    <!-- Bottom Action Buttons -->
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between gap-1.5">
                        <div class="flex items-center gap-1.5">
                            <a
                                :href="`/one-power/pembinaan/coaching-counseling/${row.id}/print-result`"
                                target="_blank"
                                class="flex items-center gap-1 rounded-xl bg-slate-800 px-2.5 py-1.5 text-xs font-bold text-white hover:bg-slate-900 transition shadow-2xs"
                                title="Cetak Berkas / Hasil"
                            >
                                <FileText class="h-3.5 w-3.5" /> Berkas
                            </a>
                            <a
                                :href="`/one-power/pembinaan/coaching-counseling/${row.id}/print-form`"
                                target="_blank"
                                class="flex items-center gap-1 rounded-xl border border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition shadow-2xs"
                                title="Cetak Form (PPA-ADRO-F-HCGA-17)"
                            >
                                Form
                            </a>
                            <a
                                v-if="row.fileBukti"
                                :href="row.fileBukti"
                                target="_blank"
                                class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 transition"
                                title="Lihat Foto Bukti"
                            >
                                Bukti
                            </a>
                        </div>

                        <div v-if="canManage" class="flex items-center gap-1.5">
                            <Link
                                :href="`/one-power/pembinaan/coaching-counseling/${row.id}/edit`"
                                class="flex h-8 w-8 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 text-blue-600 hover:bg-blue-100 transition shadow-2xs"
                                title="Edit"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                            </Link>
                            <button
                                type="button"
                                class="flex h-8 w-8 items-center justify-center rounded-xl border border-rose-200 bg-rose-50 text-rose-600 hover:bg-rose-100 transition shadow-2xs"
                                title="Hapus"
                                @click="emit('delete', row)"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
