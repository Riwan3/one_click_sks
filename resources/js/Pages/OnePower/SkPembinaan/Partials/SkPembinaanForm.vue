<script setup>
import { ref, watch, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Search, RefreshCw, X, ShieldAlert, FileText, UserCheck, Image as ImageIcon, Eye, Trash2, Camera } from 'lucide-vue-next';
import SignaturePad from '@/Components/ui/SignaturePad.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
    isEdit: { type: Boolean, default: false },
});

// Employee Search
const employeeResults = ref([]);
const showEmployeeResults = ref(false);
const employeeQuery = ref('');
let employeeSearchTimer = null;
const selectedEmployee = ref(
    props.form.employee_id
        ? {
              id: props.form.employee_id,
              name: props.form.nama,
              nrp: props.form.nrp,
              jabatan_ss6: props.form.jabatan,
              unit: props.form.unit,
          }
        : null
);

watch(employeeQuery, (keyword) => {
    clearTimeout(employeeSearchTimer);
    if (!keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    employeeSearchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/pembinaan/sk-pembinaan/search-employee?keyword=${encodeURIComponent(keyword)}`);
            employeeResults.value = await res.json();
            showEmployeeResults.value = true;
        } catch (e) {
            console.error(e);
        }
    }, 250);
});

const pickEmployee = (emp) => {
    props.form.employee_id = emp.id;
    props.form.nama = emp.name;
    props.form.nrp = emp.nrp;
    props.form.jabatan = emp.jabatan_ss6;
    props.form.unit = emp.unit;
    selectedEmployee.value = emp;
    showEmployeeResults.value = false;
    employeeQuery.value = '';
};

const clearEmployee = () => {
    props.form.employee_id = null;
    props.form.nama = '';
    props.form.nrp = '';
    props.form.jabatan = '';
    props.form.unit = '';
    selectedEmployee.value = null;
};

// Supervisor / Pengawas Search
const pengawasResults = ref([]);
const showPengawasResults = ref(false);
const pengawasQuery = ref('');
let pengawasSearchTimer = null;
const selectedPengawas = ref(
    props.form.pengawas_id
        ? { id: props.form.pengawas_id, name: props.form.pengawasName }
        : null
);

watch(pengawasQuery, (keyword) => {
    clearTimeout(pengawasSearchTimer);
    if (!keyword || keyword.length < 2) {
        pengawasResults.value = [];
        return;
    }
    pengawasSearchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/pembinaan/sk-pembinaan/search-employee?keyword=${encodeURIComponent(keyword)}`);
            pengawasResults.value = await res.json();
            showPengawasResults.value = true;
        } catch (e) {
            console.error(e);
        }
    }, 250);
});

const pickPengawas = (emp) => {
    props.form.pengawas_id = emp.id;
    props.form.pengawasName = emp.name;
    selectedPengawas.value = emp;
    showPengawasResults.value = false;
    pengawasQuery.value = '';
};

const clearPengawas = () => {
    props.form.pengawas_id = null;
    props.form.pengawasName = '';
    selectedPengawas.value = null;
};

// SPDK Search
const spdkResults = ref([]);
const showSpdkResults = ref(false);
const spdkQuery = ref(props.form.spdk_ref || '');
let spdkSearchTimer = null;

watch(spdkQuery, (keyword) => {
    clearTimeout(spdkSearchTimer);
    if (!keyword || keyword.length < 2) {
        spdkResults.value = [];
        return;
    }
    spdkSearchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/pembinaan/sk-pembinaan/search-spdk?keyword=${encodeURIComponent(keyword)}`);
            spdkResults.value = await res.json();
            showSpdkResults.value = true;
        } catch (e) {
            console.error(e);
        }
    }, 250);
});

const pickSpdk = (spdk) => {
    props.form.spdk_ref = spdk.no_pelanggaran;
    props.form.keterangan = spdk.jenis_pelanggaran;
    spdkQuery.value = spdk.no_pelanggaran;
    
    // Extract lubang number if available in sanksi or lubang field
    if (typeof spdk.lubang === 'number') {
        props.form.lubang = spdk.lubang;
    } else if (spdk.sanksi) {
        const match = String(spdk.sanksi).match(/\d+/);
        if (match) {
            props.form.lubang = parseInt(match[0], 10);
        }
    }
    showSpdkResults.value = false;
};

// Auto Nomor Surat
const fetchNextNumber = async () => {
    if (!props.form.dikeluarkan_tanggal) return;
    try {
        const params = new URLSearchParams({
            dikeluarkan_tanggal: props.form.dikeluarkan_tanggal,
            perusahaan: props.form.perusahaan || '',
        });
        const res = await fetch(`/one-power/pembinaan/sk-pembinaan/next-number?${params}`);
        const data = await res.json();
        if (data.nomorSurat) {
            props.form.nomor_surat = data.nomorSurat;
        }
    } catch (e) {
        console.error(e);
    }
};

// Sync Berlaku Sampai (+1 year)
const syncBerlakuSampai = () => {
    if (!props.form.dikeluarkan_tanggal) return;
    const tgl = new Date(props.form.dikeluarkan_tanggal);
    if (!isNaN(tgl.getTime())) {
        tgl.setFullYear(tgl.getFullYear() + 1);
        props.form.berlaku_sampai = tgl.toISOString().split('T')[0];
    }
    fetchNextNumber();
};

const changeLubang = (delta) => {
    const current = parseInt(props.form.lubang, 10) || 0;
    props.form.lubang = Math.max(0, current + delta);
};

// Photo Upload State & Methods
const fileInputRef = ref(null);
const previewUrl = ref(null);
const previewModalOpen = ref(false);
const previewModalSrc = ref('');

const handleFileSelect = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) {
        alert('Ukuran foto terlalu besar. Maksimal 10MB.');
        e.target.value = '';
        return;
    }

    props.form.foto = file;
    if (props.isEdit) {
        props.form.hapus_foto = false;
    }

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }
    previewUrl.value = URL.createObjectURL(file);
};

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const removeSelectedFile = () => {
    props.form.foto = null;
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
        previewUrl.value = null;
    }
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const markDeleteExistingFoto = () => {
    props.form.hapus_foto = true;
    removeSelectedFile();
};

const undoDeleteExistingFoto = () => {
    props.form.hapus_foto = false;
};

const openImageModal = (url) => {
    previewModalSrc.value = url;
    previewModalOpen.value = true;
};

onMounted(() => {
    if (!props.isEdit && !props.form.nomor_surat) {
        fetchNextNumber();
    }
});
</script>

<template>
    <div class="max-w-4xl rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-4 bg-gradient-to-r from-blue-700 to-indigo-900 text-white flex items-center justify-between">
            <h2 class="text-base font-bold flex items-center gap-2">
                <ShieldAlert class="h-5 w-5" />
                {{ isEdit ? 'Edit SK Pembinaan & Sanksi Pelubangan' : 'Formulir SK Pembinaan & Sanksi Pelubangan' }}
            </h2>
            <div class="text-xs text-blue-100 font-medium">
                One-Click Pembinaan
            </div>
        </div>

        <form class="p-6 space-y-6" @submit.prevent="onSubmit">
            <!-- ROW 1: PERUSAHAAN & NOMOR SURAT -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Perusahaan <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="form.perusahaan"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500 font-semibold text-slate-800"
                        @change="fetchNextNumber"
                    >
                        <option value="PT. PUTRA PERKASA ABADI">PT. PUTRA PERKASA ABADI</option>
                        <option value="PT. ADARO INDONESIA">PT. ADARO INDONESIA</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Nomor Surat <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input
                            v-model="form.nomor_surat"
                            type="text"
                            placeholder="Otomatis..."
                            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-bold text-blue-700 outline-none focus:border-blue-500"
                            readonly
                        >
                        <button
                            type="button"
                            class="flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 transition-colors shadow-2xs"
                            title="Generate Ulang Nomor Surat"
                            @click="fetchNextNumber"
                        >
                            <RefreshCw class="h-3.5 w-3.5" /> Auto
                        </button>
                    </div>
                    <p v-if="form.errors.nomor_surat" class="mt-1 text-xs text-red-600">{{ form.errors.nomor_surat }}</p>
                </div>
            </div>

            <!-- ROW 2: CARI KARYAWAN -->
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Cari Karyawan <span class="text-red-500">*</span>
                </label>

                <!-- Search Input with Dropdown -->
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="employeeQuery"
                        type="text"
                        placeholder="Ketik NRP atau nama karyawan..."
                        class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                        autocomplete="off"
                        @focus="showEmployeeResults = true"
                    >
                    <div
                        v-if="showEmployeeResults && employeeResults.length"
                        class="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl"
                    >
                        <button
                            v-for="emp in employeeResults"
                            :key="emp.id"
                            type="button"
                            class="block w-full border-b border-slate-50 px-3 py-2 text-left text-sm hover:bg-blue-50/70 transition-colors"
                            @click="pickEmployee(emp)"
                        >
                            <span class="font-bold text-slate-800">{{ emp.nrp }}</span> &ndash;
                            <span class="font-semibold text-slate-700 uppercase">{{ emp.name }}</span>
                            <span class="text-xs text-slate-500 block">{{ emp.jabatan_ss6 || '-' }} ({{ emp.unit || '-' }})</span>
                        </button>
                    </div>
                </div>
                <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>

                <!-- Selected Employee Card -->
                <div
                    v-if="selectedEmployee"
                    class="mt-2.5 flex items-center justify-between rounded-xl border border-blue-200 bg-blue-50/60 p-3"
                >
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-white font-bold text-xs">
                            <UserCheck class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-800 uppercase">
                                {{ selectedEmployee.nrp }} &ndash; {{ selectedEmployee.name }}
                            </div>
                            <div class="text-xs text-slate-600">
                                {{ selectedEmployee.jabatan_ss6 || selectedEmployee.jabatan || '-' }} &bull; {{ selectedEmployee.unit || '-' }}
                            </div>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors"
                        title="Ganti Karyawan"
                        @click="clearEmployee"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- ROW 3: TANGGAL PELANGGARAN, JAM, LOKASI -->
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tanggal Pelanggaran <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.tanggal_pelanggaran"
                        type="date"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                        required
                    >
                    <p v-if="form.errors.tanggal_pelanggaran" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal_pelanggaran }}</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Jam
                    </label>
                    <input
                        v-model="form.jam_pelanggaran"
                        type="time"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                    <p v-if="form.errors.jam_pelanggaran" class="mt-1 text-xs text-red-600">{{ form.errors.jam_pelanggaran }}</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Lokasi
                    </label>
                    <input
                        v-model="form.lokasi"
                        type="text"
                        placeholder="Lokasi Kejadian"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                    <p v-if="form.errors.lokasi" class="mt-1 text-xs text-red-600">{{ form.errors.lokasi }}</p>
                </div>
            </div>

            <!-- ROW 4: JENIS PELANGGARAN -->
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Jenis Pelanggaran <span class="text-red-500">*</span>
                </label>
                <textarea
                    v-model="form.jenis_pelanggaran"
                    rows="2"
                    placeholder="Sebutkan jenis pelanggaran..."
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                    required
                />
                <p v-if="form.errors.jenis_pelanggaran" class="mt-1 text-xs text-red-600">{{ form.errors.jenis_pelanggaran }}</p>
            </div>

            <!-- ROW 5: SESUAI DENGAN SPDK -->
            <div class="relative">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Sesuai Dengan SPDK (Pasal Pelanggaran)
                </label>
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="spdkQuery"
                        type="text"
                        placeholder="Ketik nomor pasal atau jenis pelanggaran SPDK..."
                        class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                        autocomplete="off"
                        @focus="showSpdkResults = true"
                    >
                    <div
                        v-if="showSpdkResults && spdkResults.length"
                        class="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl"
                    >
                        <button
                            v-for="spdk in spdkResults"
                            :key="spdk.id"
                            type="button"
                            class="block w-full border-b border-slate-50 px-3 py-2 text-left text-sm hover:bg-blue-50/70 transition-colors"
                            @click="pickSpdk(spdk)"
                        >
                            <span class="font-bold text-blue-600">{{ spdk.no_pelanggaran }}</span>
                            <span v-if="spdk.sanksi" class="ml-2 inline-flex items-center rounded bg-amber-100 px-1.5 py-0.2 text-[10px] font-bold text-amber-800">
                                {{ spdk.sanksi }}
                            </span>
                            <div class="text-xs text-slate-600 mt-0.5 line-clamp-2">{{ spdk.jenis_pelanggaran }}</div>
                        </button>
                    </div>
                </div>
                <div class="mt-1 text-[11px] text-slate-500">
                    Nomor pasal yang dipilih akan tampil di baris &ldquo;Sesuai Dengan SPDK&rdquo; pada cetakan Berita Acara.
                </div>
                <p v-if="form.errors.spdk_ref" class="mt-1 text-xs text-red-600">{{ form.errors.spdk_ref }}</p>
            </div>

            <!-- ROW 6: KETERANGAN PASAL (OTOMATIS) -->
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Keterangan Pasal (Otomatis)
                </label>
                <textarea
                    v-model="form.keterangan"
                    rows="2"
                    readonly
                    placeholder="Akan terisi otomatis saat memilih SPDK..."
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 outline-none"
                />
            </div>

            <!-- ROW 7: LUBANG SANKSI & BERLAKU SAMPAI -->
            <div class="grid gap-4 sm:grid-cols-12">
                <div class="sm:col-span-4">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Lubang Sanksi
                    </label>
                    <div class="flex rounded-lg border border-slate-200 bg-white overflow-hidden">
                        <button
                            type="button"
                            class="flex w-11 items-center justify-center bg-slate-50 text-slate-700 font-bold hover:bg-slate-100 active:bg-slate-200 transition-colors"
                            @click="changeLubang(-1)"
                        >
                            &minus;
                        </button>
                        <input
                            v-model.number="form.lubang"
                            type="number"
                            min="0"
                            class="w-full text-center font-bold text-slate-800 outline-none py-2 text-sm"
                        >
                        <button
                            type="button"
                            class="flex w-11 items-center justify-center bg-slate-50 text-slate-700 font-bold hover:bg-slate-100 active:bg-slate-200 transition-colors"
                            @click="changeLubang(1)"
                        >
                            +
                        </button>
                    </div>
                    <p v-if="form.errors.lubang" class="mt-1 text-xs text-red-600">{{ form.errors.lubang }}</p>
                </div>

                <div class="sm:col-span-8">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Berlaku Sampai <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.berlaku_sampai"
                        type="date"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                        required
                    >
                    <p v-if="form.errors.berlaku_sampai" class="mt-1 text-xs text-red-600">{{ form.errors.berlaku_sampai }}</p>
                </div>
            </div>

            <!-- ROW 8: DIKELUARKAN DI & DIKELUARKAN TANGGAL -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Dikeluarkan di
                    </label>
                    <input
                        v-model="form.dikeluarkan_di"
                        type="text"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                    <p v-if="form.errors.dikeluarkan_di" class="mt-1 text-xs text-red-600">{{ form.errors.dikeluarkan_di }}</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Dikeluarkan Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.dikeluarkan_tanggal"
                        type="date"
                        class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                        required
                        @change="syncBerlakuSampai"
                    >
                    <p v-if="form.errors.dikeluarkan_tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.dikeluarkan_tanggal }}</p>
                </div>
            </div>

            <!-- ROW 9: PENGAWAS / ATASAN LANGSUNG -->
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Pengawas / Atasan Langsung
                </label>
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="pengawasQuery"
                        type="text"
                        placeholder="Cari nama pengawas..."
                        class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                        autocomplete="off"
                        @focus="showPengawasResults = true"
                    >
                    <div
                        v-if="showPengawasResults && pengawasResults.length"
                        class="absolute z-20 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl"
                    >
                        <button
                            v-for="emp in pengawasResults"
                            :key="emp.id"
                            type="button"
                            class="block w-full border-b border-slate-50 px-3 py-2 text-left text-sm hover:bg-blue-50/70 transition-colors"
                            @click="pickPengawas(emp)"
                        >
                            <span class="font-bold text-slate-800">{{ emp.nrp }}</span> &ndash;
                            <span class="font-semibold text-slate-700 uppercase">{{ emp.name }}</span>
                            <span class="text-xs text-slate-500 block">{{ emp.jabatan_ss6 || '-' }}</span>
                        </button>
                    </div>
                </div>
                <div
                    v-if="selectedPengawas"
                    class="mt-2.5 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-2.5"
                >
                    <div class="text-sm font-semibold text-slate-800">
                        {{ selectedPengawas.name }} <span v-if="selectedPengawas.nrp" class="text-xs text-slate-500">({{ selectedPengawas.nrp }})</span>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1 text-slate-400 hover:text-red-600 transition-colors"
                        title="Hapus Pengawas"
                        @click="clearPengawas"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <p v-if="form.errors.pengawas_id" class="mt-1 text-xs text-red-600">{{ form.errors.pengawas_id }}</p>
            </div>

            <!-- ROW 10: UPLOAD FOTO BUKTI / DOKUMENTASI PELANGGARAN -->
            <div class="space-y-2 pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Foto Bukti Pelanggaran / Mine Permit
                    </label>
                    <span class="text-[11px] text-slate-400">Opsional &bull; Format: JPG, PNG, WEBP (Maks. 10MB)</span>
                </div>

                <!-- Hidden native file input -->
                <input
                    ref="fileInputRef"
                    type="file"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="hidden"
                    @change="handleFileSelect"
                />

                <!-- When NO new file is selected and NO active existing photo -->
                <div
                    v-if="!form.foto && (!form.existing_foto || form.hapus_foto)"
                    class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-2xl p-6 bg-slate-50/60 hover:bg-blue-50/30 transition-all cursor-pointer group"
                    @click="triggerFileInput"
                >
                    <div class="p-3 bg-white rounded-full shadow-2xs border border-slate-200 group-hover:scale-110 group-hover:text-blue-600 transition text-slate-400">
                        <Camera class="w-6 h-6" />
                    </div>
                    <p class="text-sm font-semibold text-slate-700 mt-2">
                        Klik untuk memilih atau upload foto bukti
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Dokumentasi pelanggaran atau kondisi fisik mine permit
                    </p>
                    <div v-if="isEdit && form.hapus_foto" class="mt-3 inline-flex items-center gap-2 px-3 py-1 bg-amber-50 border border-amber-200 text-amber-700 text-xs rounded-lg font-medium">
                        <span>Foto tersimpan akan dihapus saat disimpan.</span>
                        <button type="button" @click.stop="undoDeleteExistingFoto" class="font-bold underline hover:text-amber-900">
                            Urungkan
                        </button>
                    </div>
                </div>

                <!-- When a NEW file is selected -->
                <div
                    v-else-if="form.foto"
                    class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-2xl border border-blue-200 bg-blue-50/50"
                >
                    <div class="flex items-center gap-3.5 w-full sm:w-auto">
                        <div class="relative group cursor-pointer shrink-0" @click="openImageModal(previewUrl)">
                            <img
                                :src="previewUrl"
                                alt="Preview Foto"
                                class="w-16 h-16 object-cover rounded-xl border border-blue-300 shadow-xs"
                            />
                            <div class="absolute inset-0 bg-slate-900/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white">
                                <Eye class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-blue-600 text-white uppercase tracking-wider">
                                    Foto Baru
                                </span>
                                <span class="text-xs text-slate-700 font-semibold truncate max-w-[200px]">
                                    {{ form.foto.name }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ (form.foto.size / 1024).toFixed(1) }} KB &bull; Klik untuk melihat preview besar
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                        <button
                            type="button"
                            @click="openImageModal(previewUrl)"
                            class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs"
                        >
                            <Eye class="w-3.5 h-3.5 text-blue-600" /> Lihat Foto
                        </button>
                        <button
                            type="button"
                            @click="triggerFileInput"
                            class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs"
                        >
                            Ganti
                        </button>
                        <button
                            type="button"
                            @click="removeSelectedFile"
                            class="p-1.5 bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 transition shadow-2xs"
                            title="Hapus foto baru"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- When in EDIT mode and EXISTING foto is present -->
                <div
                    v-else-if="form.existing_foto && !form.hapus_foto"
                    class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-2xl border border-slate-200 bg-slate-50/70"
                >
                    <div class="flex items-center gap-3.5 w-full sm:w-auto">
                        <div class="relative group cursor-pointer shrink-0" @click="openImageModal(form.existing_foto)">
                            <img
                                :src="form.existing_foto"
                                alt="Foto Tersimpan"
                                class="w-16 h-16 object-cover rounded-xl border border-slate-300 shadow-xs"
                            />
                            <div class="absolute inset-0 bg-slate-900/40 rounded-xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition text-white">
                                <Eye class="w-5 h-5" />
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-600 text-white uppercase tracking-wider">
                                    Foto Tersimpan
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1">
                                Klik gambar atau "Lihat Foto" untuk melihat ukuran penuh
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                        <button
                            type="button"
                            @click="openImageModal(form.existing_foto)"
                            class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs"
                        >
                            <Eye class="w-3.5 h-3.5 text-blue-600" /> Lihat Foto
                        </button>
                        <button
                            type="button"
                            @click="triggerFileInput"
                            class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-xl border border-slate-200 transition flex items-center gap-1.5 shadow-2xs"
                        >
                            Ganti Foto
                        </button>
                        <button
                            type="button"
                            @click="markDeleteExistingFoto"
                            class="p-1.5 bg-white hover:bg-rose-50 text-slate-400 hover:text-rose-600 rounded-xl border border-slate-200 transition shadow-2xs"
                            title="Hapus foto tersimpan"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <p v-if="form.errors.foto" class="mt-1 text-xs text-rose-600 font-medium">
                    {{ form.errors.foto }}
                </p>
            </div>

            <!-- ROW 11: TANDA TANGAN (TTD Karyawan & TTD Atasan) -->
            <div class="grid gap-4 sm:grid-cols-2 pt-2 border-t border-slate-100">
                <SignaturePad
                    v-model="form.ttd_karyawan"
                    label="Tanda Tangan Karyawan"
                />
                <SignaturePad
                    v-model="form.ttd_atasan"
                    label="Tanda Tangan Atasan Langsung"
                />
            </div>

            <!-- FOOTER ACTIONS -->
            <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                <Link
                    href="/one-power/pembinaan/sk-pembinaan"
                    class="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition-colors"
                >
                    Kembali
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex items-center gap-1.5 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                    {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Update Data SK' : 'Simpan & Cetak SK') }}
                </button>
            </div>
        </form>

        <!-- LIGHTBOX IMAGE PREVIEW MODAL -->
        <div
            v-if="previewModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs"
            @click.self="previewModalOpen = false"
        >
            <div class="relative max-w-4xl max-h-[90vh] bg-white rounded-2xl overflow-hidden shadow-2xl flex flex-col">
                <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 bg-slate-50">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <ImageIcon class="w-4 h-4 text-blue-600" />
                        Preview Foto Bukti Pelanggaran
                    </h3>
                    <div class="flex items-center gap-2">
                        <a
                            :href="previewModalSrc"
                            target="_blank"
                            class="text-xs font-semibold text-blue-600 hover:underline px-2 py-1"
                        >
                            Buka Tab Baru &nearr;
                        </a>
                        <button
                            type="button"
                            @click="previewModalOpen = false"
                            class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-200/60 transition"
                        >
                            <X class="w-5 h-5" />
                        </button>
                    </div>
                </div>
                <div class="p-4 overflow-auto flex items-center justify-center bg-slate-900/5 max-h-[calc(90vh-60px)]">
                    <img :src="previewModalSrc" alt="Full Preview" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-sm" />
                </div>
            </div>
        </div>
    </div>
</template>
