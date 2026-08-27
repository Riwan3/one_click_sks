<script setup>
import { CloudDownload } from '@lucide/vue';

defineProps({
    open: { type: Boolean, default: false },
    form: { type: Object, required: true },
});

const emit = defineEmits(['close', 'submit']);

const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const handleSubmit = () => emit('submit');
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-emerald-600 px-6 py-4 text-white">
                <h5 class="flex items-center gap-2 text-base font-bold">
                    <CloudDownload class="h-4 w-4" /> Import Roster Absensi Bulanan
                </h5>
                <button type="button" class="text-2xl leading-none text-white/80 hover:text-white" @click="emit('close')">&times;</button>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="space-y-4 p-6">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Bulan</label>
                            <select v-model.number="form.bulan" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-500">
                                <option v-for="(name, idx) in months" :key="idx" :value="idx + 1">{{ name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">Tahun</label>
                            <input v-model.number="form.tahun" type="number" min="2000" max="2100" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-bold uppercase text-slate-500">File Excel (.xlsx, .xls, .csv)</label>
                        <input
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-emerald-500"
                            @change="form.file = $event.target.files[0]"
                        >
                        <p v-if="form.errors.file" class="mt-1 text-xs text-red-600">{{ form.errors.file }}</p>
                    </div>

                    <p class="text-xs text-slate-500">
                        Kolom wajib: NRP, lalu 15 kode kehadiran (D, N, IO, NC, L, T, O, S, I, A, K, P, R, H, NR).
                        Kolom Name/Dept/Jabatan/Posisi/Comp/ATR opsional. Bulan &amp; tahun berlaku untuk seluruh baris
                        pada file yang diupload. Data yang sudah ada untuk NRP + bulan + tahun yang sama akan ditimpa
                        (upsert). Baris dengan NRP kosong atau tidak terdaftar di Data Karyawan akan dilewati.
                    </p>

                    <a
                        href="/one-power/personal-data/attendance/template"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:underline"
                    >
                        <CloudDownload class="h-3.5 w-3.5" /> Download Template Excel
                    </a>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 px-6 py-4">
                    <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-50" @click="emit('close')">Batal</button>
                    <button type="submit" :disabled="form.processing || !form.file" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-60">
                        {{ form.processing ? 'Mengimport...' : 'Mulai Import' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
