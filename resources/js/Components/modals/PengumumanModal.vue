<script setup>
import { Megaphone, X, Calendar, ChevronRight, BellRing } from 'lucide-vue-next';

defineProps({
    show: { type: Boolean, default: false },
});

defineEmits(['close']);

const announcements = [
    {
        id: 1,
        title: 'Update Jadwal Roster Bulan Depan',
        date: '26 Aug 2026',
        category: 'Operasional',
        content: 'Silakan cek jadwal roster terbaru di menu One-Power sebelum akhir bulan. Pastikan tidak ada penyesuaian shift yang terlewat.',
        type: 'info',
    },
    {
        id: 2,
        title: 'Reminder Absensi Harian',
        date: '24 Aug 2026',
        category: 'Kehadiran',
        content: 'Pastikan absensi harian diinput sebelum pukul 17:00 setiap harinya untuk validasi ATR dan insentif pimpinan.',
        type: 'warning',
    },
    {
        id: 3,
        title: 'Sistem Berhasil Diperbarui',
        date: '22 Aug 2026',
        category: 'Sistem',
        content: 'Perbaikan performa dan pembaruan tampilan pada modul One-Reporting serta One-Procedure telah selesai dilaksanakan.',
        type: 'success',
    },
    {
        id: 4,
        title: 'Sosialisasi Keselamatan Kerja (SHE)',
        date: '18 Aug 2026',
        category: 'SHE & K3',
        content: 'Wajib menghadiri p5m dan menyimak penyampaian fatigue check secara berkala demi mewujudkan target Zero Accident.',
        type: 'info',
    },
];
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-xs animate-in fade-in duration-200"
        @click.self="$emit('close')"
    >
        <div class="relative w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl border border-slate-100 flex flex-col max-h-[85vh]">
            <!-- HEADER -->
            <div class="flex items-center justify-between p-5 border-b border-slate-100 bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-transparent">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-md shadow-amber-500/20">
                        <Megaphone class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="m-0 text-base font-extrabold text-slate-800 uppercase tracking-tight">Pengumuman Perusahaan</h3>
                        <p class="m-0 text-xs font-medium text-slate-500">Informasi &amp; Edaran Resmi PT. Putra Perkasa Abadi</p>
                    </div>
                </div>

                <button
                    type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition"
                    @click="$emit('close')"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- ANNOUNCEMENT LIST -->
            <div class="p-5 overflow-y-auto space-y-3.5">
                <div
                    v-for="item in announcements"
                    :key="item.id"
                    class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/60 hover:bg-amber-50/40 hover:border-amber-200 transition space-y-2 group"
                >
                    <div class="flex items-center justify-between gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 text-amber-700">
                            {{ item.category }}
                        </span>
                        <span class="flex items-center gap-1 text-[11px] font-semibold text-slate-400">
                            <Calendar class="h-3 w-3" /> {{ item.date }}
                        </span>
                    </div>

                    <h4 class="m-0 text-sm font-bold text-slate-800 group-hover:text-amber-700 transition">
                        {{ item.title }}
                    </h4>

                    <p class="m-0 text-xs text-slate-600 leading-relaxed">
                        {{ item.content }}
                    </p>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="p-4 border-t border-slate-100 bg-slate-50 text-center">
                <button
                    type="button"
                    class="w-full py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs uppercase tracking-wider hover:bg-slate-800 transition"
                    @click="$emit('close')"
                >
                    Tutup Pengumuman
                </button>
            </div>
        </div>
    </div>
</template>
