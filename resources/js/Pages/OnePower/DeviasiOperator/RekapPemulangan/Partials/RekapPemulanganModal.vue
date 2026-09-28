<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { User, Calendar, Clock, Sun, Moon, CheckCircle, X } from '@lucide/vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    isEdit: { type: Boolean, default: false },
    item: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const form = ref({
    id: null,
    nrp: '',
    nama: '',
    jabatan: '',
    tanggal: new Date().toISOString().split('T')[0],
    shift: 'D',
    jam: new Date().toTimeString().slice(0, 5),
    keterangan: '',
    konfirmasi: '',
});

const errors = ref({});
const processing = ref(false);

const searchResults = ref([]);
const showDropdown = ref(false);
let searchTimeout = null;

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        errors.value = {};
        if (props.isEdit && props.item) {
            form.value = {
                id: props.item.id,
                nrp: props.item.nrp || '',
                nama: props.item.nama || '',
                jabatan: props.item.jabatan || '',
                tanggal: props.item.tanggal || new Date().toISOString().split('T')[0],
                shift: props.item.shift || 'D',
                jam: props.item.jam || new Date().toTimeString().slice(0, 5),
                keterangan: props.item.keterangan || props.item.alasan || '',
                konfirmasi: props.item.konfirmasi || '',
            };
        } else {
            form.value = {
                id: null,
                nrp: '',
                nama: '',
                jabatan: '',
                tanggal: new Date().toISOString().split('T')[0],
                shift: 'D',
                jam: new Date().toTimeString().slice(0, 5),
                keterangan: '',
                konfirmasi: '',
            };
        }
    }
});

const onNrpInput = () => {
    clearTimeout(searchTimeout);
    const kw = form.value.nrp.trim();
    if (kw.length < 2) {
        searchResults.value = [];
        showDropdown.value = false;
        return;
    }

    searchTimeout = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/deviasi-operator/rekap-pemulangan/search-employee?keyword=${encodeURIComponent(kw)}`);
            const data = await res.json();
            searchResults.value = data || [];
            showDropdown.value = (data && data.length > 0);
        } catch {
            searchResults.value = [];
            showDropdown.value = false;
        }
    }, 250);
};

const selectEmployee = (emp) => {
    form.value.nrp = emp.nrp;
    form.value.nama = emp.name || emp.nama || '';
    form.value.jabatan = emp.jabatan_ss6 || emp.jabatan || '';
    showDropdown.value = false;
};

const close = () => {
    showDropdown.value = false;
    emit('close');
};

const submit = () => {
    processing.value = true;
    errors.value = {};

    const url = props.isEdit && form.value.id
        ? `/one-power/deviasi-operator/rekap-pemulangan/${form.value.id}`
        : '/one-power/deviasi-operator/rekap-pemulangan';

    const method = props.isEdit && form.value.id ? 'put' : 'post';

    router[method](url, form.value, {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
            close();
            emit('saved');
        },
        onError: (errs) => {
            processing.value = false;
            errors.value = errs || {};
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
                    <span v-if="isEdit" class="text-blue-600">✏️ Edit Data Pemulangan</span>
                    <span v-else class="text-blue-600">➕ Tambah Rekap Pemulangan</span>
                </h5>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="close">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Modal Body -->
            <form @submit.prevent="submit">
                <div class="space-y-4 p-6 text-sm">
                    <!-- NRP with Autocomplete -->
                    <div class="relative">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">
                            NRP Operator <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.nrp"
                                type="text"
                                placeholder="Ketik NRP atau Nama..."
                                autocomplete="off"
                                required
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                @input="onNrpInput"
                            >
                        </div>
                        <p v-if="errors.nrp" class="mt-1 text-xs text-red-600">{{ errors.nrp }}</p>

                        <!-- Autocomplete Dropdown -->
                        <div
                            v-if="showDropdown && searchResults.length"
                            class="absolute left-0 right-0 top-full z-20 mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                        >
                            <div
                                v-for="emp in searchResults"
                                :key="emp.nrp"
                                class="cursor-pointer px-3 py-2 text-xs transition hover:bg-blue-50"
                                @click="selectEmployee(emp)"
                            >
                                <span class="font-bold text-slate-800">{{ emp.nrp }}</span> - {{ emp.name }}
                                <span class="text-slate-400">({{ emp.jabatan_ss6 || 'No Jabatan' }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Nama & Jabatan -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Nama Operator</label>
                            <input
                                v-model="form.nama"
                                type="text"
                                placeholder="Nama operator"
                                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Jabatan</label>
                            <input
                                v-model="form.jabatan"
                                type="text"
                                placeholder="Jabatan"
                                class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                        </div>
                    </div>

                    <!-- Tanggal & Shift -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">
                                Tanggal <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.tanggal"
                                type="date"
                                required
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                            <p v-if="errors.tanggal" class="mt-1 text-xs text-red-600">{{ errors.tanggal }}</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">
                                Shift <span class="text-red-500">*</span>
                            </label>
                            <select
                                v-model="form.shift"
                                required
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                            >
                                <option value="D">Shift D (Day / Siang)</option>
                                <option value="N">Shift N (Night / Malam)</option>
                            </select>
                            <p v-if="errors.shift" class="mt-1 text-xs text-red-600">{{ errors.shift }}</p>
                        </div>
                    </div>

                    <!-- Jam Pemulangan -->
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">
                            Jam Pemulangan <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.jam"
                            type="time"
                            required
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        >
                        <p v-if="errors.jam" class="mt-1 text-xs text-red-600">{{ errors.jam }}</p>
                    </div>

                    <!-- Keterangan / Alasan -->
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">
                            Keterangan / Alasan Pemulangan
                        </label>
                        <textarea
                            v-model="form.keterangan"
                            rows="3"
                            placeholder="Contoh: Mengalami pusing / keluhan fisik di pit / izin keluarga mendesak..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        ></textarea>
                    </div>

                    <!-- Konfirmasi -->
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Konfirmasi</label>
                        <input
                            v-model="form.konfirmasi"
                            type="text"
                            placeholder="Contoh: Disetujui GL Pit / Foreman / Mengetahui..."
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        >
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                    <button
                        type="button"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100"
                        @click="close"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        :disabled="processing"
                        class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60"
                    >
                        {{ processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Simpan Data') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
