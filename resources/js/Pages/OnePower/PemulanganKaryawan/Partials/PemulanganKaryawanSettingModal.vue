<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Lock, X } from 'lucide-vue-next';

const props = defineProps({
    open: { type: Boolean, default: false },
    settings: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['close']);

const form = useForm({
    siteCode: props.settings?.siteCode || 'ADW',
    lokasi: props.settings?.lokasi || 'Tabalong',
    noDokumen: props.settings?.noDokumen || 'PPA-ADRO-F-SHE-29F',
    revisi: props.settings?.revisi || '0',
    tglEfektif: props.settings?.tglEfektif || '2023-05-18',
});

watch(() => props.settings, (newVal) => {
    if (newVal) {
        form.siteCode = newVal.siteCode || 'ADW';
        form.lokasi = newVal.lokasi || 'Tabalong';
        form.noDokumen = newVal.noDokumen || 'PPA-ADRO-F-SHE-29F';
        form.revisi = newVal.revisi || '0';
        form.tglEfektif = newVal.tglEfektif || '2023-05-18';
    }
}, { deep: true });

const submit = () => {
    form.post('/one-power/pembinaan/pemulangan-karyawan/settings', {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-6 py-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                        <Lock class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Kunci Format Dokumen &amp; No. Surat</h3>
                        <p class="text-xs text-slate-500">Nilai yang dikunci otomatis diterapkan pada seluruh pemulangan karyawan</p>
                    </div>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
                    @click="emit('close')"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Modal Body -->
            <form @submit.prevent="submit">
                <div class="max-h-[75vh] space-y-4 overflow-y-auto p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Kode Site (No. Surat) <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.siteCode"
                                type="text"
                                placeholder="Contoh: ADW, BIB, MHU"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm uppercase outline-none focus:border-blue-500"
                                required
                            >
                            <p class="mt-1 text-[11px] text-slate-400">.../PK/PPA/<b>{{ form.siteCode || 'SITE' }}</b>/PRO/...</p>
                            <p v-if="form.errors.siteCode" class="mt-1 text-xs text-red-600">{{ form.errors.siteCode }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Lokasi TTD (Kota)
                            </label>
                            <input
                                v-model="form.lokasi"
                                type="text"
                                placeholder="Contoh: Tabalong, Wara"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                            <p class="mt-1 text-[11px] text-slate-400">Contoh: <b>{{ form.lokasi || 'Tabalong' }}</b>, [Tanggal]</p>
                            <p v-if="form.errors.lokasi" class="mt-1 text-xs text-red-600">{{ form.errors.lokasi }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                            No. Dokumen <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.noDokumen"
                            type="text"
                            placeholder="PPA-ADRO-F-SHE-29F"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            required
                        >
                        <p v-if="form.errors.noDokumen" class="mt-1 text-xs text-red-600">{{ form.errors.noDokumen }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Revisi
                            </label>
                            <input
                                v-model="form.revisi"
                                type="text"
                                placeholder="0"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                            <p v-if="form.errors.revisi" class="mt-1 text-xs text-red-600">{{ form.errors.revisi }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Tanggal Efektif
                            </label>
                            <input
                                v-model="form.tglEfektif"
                                type="date"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                            <p v-if="form.errors.tglEfektif" class="mt-1 text-xs text-red-600">{{ form.errors.tglEfektif }}</p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-xs text-amber-800">
                        <p class="font-semibold text-amber-900">Perhatian:</p>
                        <p class="mt-0.5">
                            Mengubah <strong>Kode Site</strong> akan memperbarui nomor surat seluruh data Pemulangan Karyawan secara otomatis.
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                    <button
                        type="button"
                        class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100"
                        @click="emit('close')"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-50"
                    >
                        <Lock class="h-4 w-4" />
                        {{ form.processing ? 'Menyimpan...' : 'Kunci & Terapkan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
