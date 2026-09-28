<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { X, UploadCloud, FileSpreadsheet, Download, AlertCircle, Loader2 } from 'lucide-vue-next';

const props = defineProps({
    open: Boolean,
});

const emit = defineEmits(['close']);

const activeTab = ref('dpr_ob'); // 'dpr_ob' | 'dpr_coal' | 'sap_hm' | 'atr'

const tabs = [
    { key: 'dpr_ob', label: 'DPR OB', desc: 'Data ritase & BCM Overburden harian' },
    { key: 'dpr_coal', label: 'DPR Coal', desc: 'Data ritase & TON Batubara harian' },
    { key: 'sap_hm', label: 'SAP HM', desc: 'Data jam operasi (Hour Time) per unit dari SAP' },
    { key: 'atr', label: 'ATR Kehadiran', desc: 'Data presensi/kehadiran harian operator' },
];

const form = useForm({
    file: null,
    type: 'dpr_ob',
});

const fileInputRef = ref(null);

const handleSelectTab = (key) => {
    activeTab.value = key;
    form.type = key;
    form.file = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
};

const handleFileChange = (e) => {
    const files = e.target.files;
    if (files && files.length > 0) {
        form.file = files[0];
    }
};

const submit = () => {
    if (!form.file) return;
    form.type = activeTab.value;
    form.post('/one-power/quartal-operator-report/import', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            if (fileInputRef.value) fileInputRef.value.value = '';
            emit('close');
        },
    });
};

const downloadTemplate = () => {
    window.location.href = `/one-power/quartal-operator-report/template?type=${activeTab.value}`;
};
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="w-full max-w-xl rounded-2xl bg-white shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div class="flex items-center gap-2">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                        <UploadCloud class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Import Data QOR</h3>
                        <p class="text-xs text-slate-500">Pilih jenis data yang ingin diunggah (CSV / Excel)</p>
                    </div>
                </div>
                <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="emit('close')">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Tab Navigation -->
            <div class="grid grid-cols-4 border-b border-slate-100 bg-slate-50/70 p-1.5 gap-1 text-xs font-semibold">
                <button
                    v-for="t in tabs"
                    :key="t.key"
                    type="button"
                    class="rounded-lg py-2 px-2 text-center transition-all"
                    :class="activeTab === t.key ? 'bg-white text-blue-600 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-800 hover:bg-white/50'"
                    @click="handleSelectTab(t.key)"
                >
                    {{ t.label }}
                </button>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submit" class="p-6 space-y-4">
                <div class="rounded-lg bg-blue-50/60 p-3 text-xs text-blue-900 flex items-start gap-2">
                    <AlertCircle class="h-4 w-4 shrink-0 text-blue-600 mt-0.5" />
                    <div>
                        <strong>{{ tabs.find(t => t.key === activeTab)?.label }}:</strong>
                        {{ tabs.find(t => t.key === activeTab)?.desc }}.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Pilih File (XLSX, XLS, CSV)</label>
                    <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 p-6 text-center hover:border-blue-400 transition-colors bg-slate-50/40">
                        <FileSpreadsheet class="h-10 w-10 text-slate-400 mb-2" />
                        <input
                            ref="fileInputRef"
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            class="text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                            @change="handleFileChange"
                        />
                        <p v-if="form.file" class="mt-2 text-xs font-semibold text-emerald-600">
                            File terpilih: {{ form.file.name }}
                        </p>
                    </div>
                    <p v-if="form.errors.file" class="mt-1 text-xs text-red-500">{{ form.errors.file }}</p>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-blue-600 hover:underline"
                        @click="downloadTemplate"
                    >
                        <Download class="h-3.5 w-3.5" /> Unduh Template Format
                    </button>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                            @click="emit('close')"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            :disabled="!form.file || form.processing"
                            class="flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            <Loader2 v-if="form.processing" class="h-3.5 w-3.5 animate-spin" />
                            <span>{{ form.processing ? 'Mengunggah...' : 'Upload & Proses' }}</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
