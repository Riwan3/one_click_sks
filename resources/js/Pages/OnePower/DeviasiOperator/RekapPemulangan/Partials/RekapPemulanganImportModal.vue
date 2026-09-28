<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { UploadCloud, Download, X, AlertCircle } from '@lucide/vue';

defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'imported']);

const file = ref(null);
const fileInputRef = ref(null);
const processing = ref(false);
const errorMessage = ref('');

const onFileChange = (e) => {
    file.value = e.target.files?.[0] || null;
    errorMessage.value = '';
};

const close = () => {
    file.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
    errorMessage.value = '';
    emit('close');
};

const submitImport = () => {
    if (!file.value) {
        errorMessage.value = 'Silakan pilih file Excel (.xlsx) atau CSV (.csv) terlebih dahulu.';
        return;
    }

    processing.value = true;
    errorMessage.value = '';

    const formData = new FormData();
    formData.append('file', file.value);

    router.post('/one-power/deviasi-operator/rekap-pemulangan/import', formData, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
            close();
            emit('imported');
        },
        onError: (errors) => {
            processing.value = false;
            errorMessage.value = errors.file || 'Terjadi kesalahan saat mengimpor data.';
        },
    });
};
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="close">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                <h5 class="flex items-center gap-2 text-base font-bold text-slate-800">
                    <UploadCloud class="h-5 w-5 text-blue-600" /> Import Data Pemulangan Operator
                </h5>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="close">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 text-sm space-y-4">
                <div class="flex items-center justify-between rounded-xl bg-blue-50/80 p-3.5 text-blue-700">
                    <div class="text-xs">
                        <span class="font-bold">Belum punya template file?</span>
                        <p class="text-blue-600/80 mt-0.5">Unduh template CSV yang sudah disesuaikan dengan sistem.</p>
                    </div>
                    <a
                        href="/one-power/deviasi-operator/rekap-pemulangan/template"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 transition"
                    >
                        <Download class="h-3.5 w-3.5" /> Template
                    </a>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600">
                        File Excel (.xlsx) atau CSV (.csv) <span class="text-red-500">*</span>
                    </label>
                    <input
                        ref="fileInputRef"
                        type="file"
                        accept=".xlsx,.csv"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100 focus:border-blue-500"
                        @change="onFileChange"
                    >
                    <p v-if="errorMessage" class="mt-1 flex items-center gap-1 text-xs text-red-600">
                        <AlertCircle class="h-3.5 w-3.5" /> {{ errorMessage }}
                    </p>
                </div>

                <!-- Susunan Kolom -->
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4 text-xs text-slate-600">
                    <p class="font-bold text-slate-800 mb-2">📋 Susunan Kolom File Excel / CSV:</p>
                    <ol class="list-decimal pl-4 space-y-1 text-slate-500">
                        <li><strong class="text-slate-700">NO</strong> - Nomor urut</li>
                        <li><strong class="text-slate-700">NRP</strong> - NRP Operator</li>
                        <li><strong class="text-slate-700">NAMA</strong> - Nama Operator (otomatis jika kosong)</li>
                        <li><strong class="text-slate-700">JABATAN</strong> - Jabatan Operator (otomatis jika kosong)</li>
                        <li><strong class="text-slate-700">TANGGAL</strong> - Tanggal pemulangan (contoh: <code>2026-09-21</code>)</li>
                        <li><strong class="text-slate-700">SHIFT</strong> - Shift <code>D</code> atau <code>N</code></li>
                        <li><strong class="text-slate-700">JAM PULANG</strong> - Jam pemulangan (contoh: <code>10:30</code>)</li>
                        <li><strong class="text-slate-700">KETERANGAN</strong> - Alasan pemulangan</li>
                        <li><strong class="text-slate-700">KONFIRMASI</strong> - Pengawas / Status konfirmasi</li>
                    </ol>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                <button
                    type="button"
                    class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100"
                    @click="close"
                >
                    Batal
                </button>
                <button
                    type="button"
                    :disabled="processing || !file"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-5 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60"
                    @click="submitImport"
                >
                    <UploadCloud class="h-4 w-4" />
                    {{ processing ? 'Mengimpor Data...' : 'Mulai Import' }}
                </button>
            </div>
        </div>
    </div>
</template>
