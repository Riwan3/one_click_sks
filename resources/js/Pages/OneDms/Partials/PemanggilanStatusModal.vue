<script setup>
import { Link } from '@inertiajs/vue3';
import { X, Search, Megaphone } from 'lucide-vue-next';

const props = defineProps({
    open: Boolean,
    operator: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const formatTanggal = (value) => {
    if (!value) return '-';
    return new Date(value).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
};
</script>

<template>
    <div v-if="open && operator" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
        <div class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 p-5">
                <h5 class="flex items-center gap-2 text-base font-extrabold text-slate-800 dark:text-white">
                    <Megaphone class="w-4 h-4 text-sky-500" /> Status Pemanggilan
                </h5>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('close')">
                    <X class="w-5 h-5" />
                </button>
            </div>

            <div class="p-5">
                <p class="text-base font-black text-slate-900 dark:text-white">{{ operator.nama_operator }}</p>
                <p class="mb-4 text-xs text-slate-500">NRP {{ operator.nrp }} &middot; {{ operator.total_cases }} kasus</p>

                <div v-if="operator.pemanggilanJumlah > 0" class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-900/20 dark:text-emerald-300">
                    <p class="font-bold">✓ Sudah dilakukan pemanggilan.</p>
                    <p class="mt-1 text-xs">Jumlah pemanggilan: <span class="font-bold">{{ operator.pemanggilanJumlah }} kali</span></p>
                    <p class="text-xs">Terakhir: <span class="font-bold">{{ formatTanggal(operator.pemanggilanTerakhir) }}</span></p>
                </div>
                <div v-else class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-900 dark:bg-amber-900/20 dark:text-amber-300">
                    <p class="font-bold">Belum pernah dilakukan pemanggilan.</p>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3">
                    <button type="button" class="rounded-xl bg-slate-100 dark:bg-slate-800 px-4 py-2.5 text-sm font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200" @click="emit('close')">
                        TUTUP
                    </button>
                    <Link
                        :href="`/one-power/pembinaan/pemanggilan-karyawan?search=${encodeURIComponent(operator.nrp)}`"
                        class="flex items-center gap-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 px-4 py-2.5 text-sm font-bold text-white no-underline"
                    >
                        <Search class="w-4 h-4" /> LIHAT DI PEMANGGILAN
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
