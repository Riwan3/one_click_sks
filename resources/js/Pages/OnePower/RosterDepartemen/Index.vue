<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    Users, Calendar, Filter, Printer, Search, 
    X, Check, RotateCcw, Edit2, ShieldAlert,
    UploadCloud, FileSpreadsheet, RefreshCw, Save,
    CheckCircle2, AlertCircle, Loader2, FileUp, Plus, Trash2
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Swal from 'sweetalert2';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    daysInMonth: { type: Number, required: true },
    days: { type: Array, required: true },
    rosterGroups: { type: Array, required: true },
    rosterGroupsMap: { type: Object, default: () => ({}) },
    categories: { type: Array, default: () => [] },
    savedGoogleSheetUrls: { type: Object, default: () => ({}) },
    jabatanList: { type: Array, required: true },
    manpowerEmployees: { type: Array, default: () => [] },
    rows: { type: Array, required: true },
    akkuPerHari: { type: Object, required: true },
    akkuTotalHari: { type: Array, required: true },
    codeTypes: { type: Object, required: true },
    grandSummary: { type: Object, required: true },
});

// Filter state
const filterBulan = ref(props.filters.bulan);
const filterTahun = ref(props.filters.tahun);
const filterCategory = ref(props.filters.category || '');
const filterJabatan = ref(props.filters.jabatan || '');
const searchKeyword = ref(props.filters.search || '');
const focusDatesMobile = ref(true);

// Import Modal State & Dynamic Categories
const isImportModalOpen = ref(false);

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('openImport') === '1') {
        isImportModalOpen.value = true;
    }
});
const importActiveTab = ref('sheets'); // 'sheets' | 'file'
const categories = ref(
    props.categories && props.categories.length > 0
        ? JSON.parse(JSON.stringify(props.categories))
        : Object.entries(props.rosterGroupsMap || {}).map(([k, label]) => ({
            key: k,
            label: label,
            url: props.savedGoogleSheetUrls?.[k] || '',
            keywords: '',
            penanggung_jawab_nrp: '',
            penanggung_jawab_nama: '',
            penanggung_jawab_jabatan: '',
        }))
);

const displayRosterGroups = computed(() => {
    if (categories.value && categories.value.length > 0) {
        return categories.value.map(c => c.label).filter(Boolean);
    }
    return props.rosterGroups || [];
});

watch(() => props.categories, (newCats) => {
    if (newCats && Array.isArray(newCats)) {
        categories.value = JSON.parse(JSON.stringify(newCats));
    }
}, { deep: true });

watch(() => props.filters.category, (newCat) => {
    filterCategory.value = newCat || '';
});

watch(displayRosterGroups, (newGroups) => {
    if (filterCategory.value && !newGroups.includes(filterCategory.value)) {
        filterCategory.value = '';
    }
});

const isSavingUrls = ref(false);
const updatingGroup = ref(null);
const importProgressMsg = ref('');
const progressPercent = ref(0);
const groupStatus = ref({});
const importAlert = ref(null);

const handlePicChange = (cat) => {
    const selected = (props.manpowerEmployees || []).find(e => String(e.nrp) === String(cat.penanggung_jawab_nrp));
    if (selected) {
        cat.penanggung_jawab_nama = selected.name;
        cat.penanggung_jawab_jabatan = selected.jabatan;
    } else {
        cat.penanggung_jawab_nama = '';
        cat.penanggung_jawab_jabatan = '';
    }
    saveSheetUrls();
};

const addCategory = () => {
    const newKey = 'CAT_' + Date.now();
    categories.value.push({
        key: newKey,
        label: 'Roster Baru',
        url: '',
        keywords: '',
        penanggung_jawab_nrp: '',
        penanggung_jawab_nama: '',
        penanggung_jawab_jabatan: '',
    });
    // Simpan otomatis ke database agar kategori baru langsung tersimpan
    saveSheetUrls();
};

const removeCategory = async (index) => {
    const cat = categories.value[index];
    const categoryName = cat?.label || 'Kategori ini';

    const result = await Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Apakah Anda yakin ingin menghapus kategori <b>"${categoryName}"</b> dari daftar?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true,
    });

    if (result.isConfirmed) {
        const deletedLabel = cat?.label;
        categories.value.splice(index, 1);

        const isCurrentCategory = filterCategory.value === deletedLabel || props.filters.category === deletedLabel;
        if (isCurrentCategory) {
            filterCategory.value = '';
        }

        saveSheetUrls(isCurrentCategory);

        Swal.fire({
            icon: 'success',
            title: 'Berhasil Dihapus',
            text: `Kategori "${categoryName}" telah dihapus.`,
            timer: 1500,
            showConfirmButton: false,
        });
    }
};

const uploadFile = ref(null);
const uploadCategory = ref('');
const isUploadingFile = ref(false);
const fileInputRef = ref(null);

const onFileSelected = (e) => {
    const files = e.target.files;
    if (files && files.length > 0) {
        uploadFile.value = files[0];
    }
};

const saveSheetUrls = (resetFilter = false) => {
    isSavingUrls.value = true;
    importAlert.value = null;

    const urlsMap = {};
    categories.value.forEach(c => {
        if (c.key && c.url) urlsMap[c.key] = c.url;
    });

    router.post('/one-power/roster-departemen/save-google-sheet-urls', {
        categories: categories.value,
        urls: urlsMap,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isSavingUrls.value = false;
            importAlert.value = { type: 'success', message: 'Daftar nama kategori & URL Google Spreadsheet berhasil disimpan.' };
            if (resetFilter || (filterCategory.value && !categories.value.some(c => c.label === filterCategory.value))) {
                filterCategory.value = '';
                applyFilter();
            }
        },
        onError: () => {
            isSavingUrls.value = false;
            importAlert.value = { type: 'error', message: 'Gagal menyimpan kategori & URL.' };
        }
    });
};

const importSheet = async (cat) => {
    updatingGroup.value = cat.key;
    importAlert.value = null;
    const label = cat.label || 'Roster';
    progressPercent.value = 25;
    importProgressMsg.value = `Menghubungi Google Spreadsheet: ${label}...`;
    groupStatus.value[cat.key] = { status: 'loading' };

    try {
        progressPercent.value = 50;
        importProgressMsg.value = `Mengunduh & memproses matriks roster ${label}...`;
        const res = await fetch('/one-power/roster-departemen/import-google-sheet', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                group: cat.key,
                label: cat.label,
                url: cat.url || '',
            })
        });
        progressPercent.value = 85;
        const data = await res.json();
        if (data.success) {
            progressPercent.value = 100;
            groupStatus.value[cat.key] = { status: 'success', count: data.count };
            importProgressMsg.value = `Selesai! ${label} berhasil diperbarui (${data.count} data).`;
            importAlert.value = {
                type: 'success',
                message: data.message || `Berhasil memperbarui ${data.count} data roster (${label}).`
            };
            router.reload({ preserveScroll: true });
        } else {
            progressPercent.value = 0;
            groupStatus.value[cat.key] = { status: 'error', message: data.error };
            importAlert.value = {
                type: 'error',
                message: data.error || 'Terjadi kesalahan saat mengimpor Google Sheet.'
            };
        }
    } catch (e) {
        progressPercent.value = 0;
        groupStatus.value[cat.key] = { status: 'error', message: 'Koneksi gagal' };
        importAlert.value = { type: 'error', message: 'Gagal terhubung ke server.' };
    } finally {
        updatingGroup.value = null;
    }
};

const importAllSheets = async () => {
    const validCats = categories.value.filter(c => c.url && c.url.trim() !== '');
    if (validCats.length === 0) {
        importAlert.value = { type: 'error', message: 'Belum ada URL Google Spreadsheet yang diisi.' };
        return;
    }

    updatingGroup.value = 'ALL';
    importAlert.value = null;
    progressPercent.value = 5;
    
    let totalImported = 0;
    let successCount = 0;
    const failedGroups = [];

    for (let i = 0; i < validCats.length; i++) {
        const cat = validCats[i];
        const label = cat.label || 'Roster';
        progressPercent.value = Math.round(((i) / validCats.length) * 100);
        importProgressMsg.value = `[${i + 1}/${validCats.length}] Mengimpor ${label}...`;
        groupStatus.value[cat.key] = { status: 'loading' };

        try {
            const res = await fetch('/one-power/roster-departemen/import-google-sheet', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    group: cat.key,
                    label: cat.label,
                    url: cat.url || '',
                })
            });
            const data = await res.json();
            if (data.success) {
                totalImported += (data.count || 0);
                successCount++;
                groupStatus.value[cat.key] = { status: 'success', count: data.count };
            } else {
                failedGroups.push(`${label}: ${data.error || 'Gagal'}`);
                groupStatus.value[cat.key] = { status: 'error', message: data.error || 'Gagal' };
            }
        } catch (err) {
            failedGroups.push(`${label}: Gagal koneksi`);
            groupStatus.value[cat.key] = { status: 'error', message: 'Koneksi gagal' };
        }
    }

    progressPercent.value = 100;
    updatingGroup.value = null;

    if (failedGroups.length === 0) {
        importProgressMsg.value = `Semua ${validCats.length} kategori selesai diimpor (${totalImported.toLocaleString()} data)!`;
        importAlert.value = {
            type: 'success',
            message: `Update Berhasil! Total ${totalImported.toLocaleString()} data roster berhasil disinkronkan dari ${successCount} kategori.`
        };
    } else {
        importProgressMsg.value = 'Proses import selesai dengan beberapa catatan.';
        importAlert.value = {
            type: successCount > 0 ? 'success' : 'error',
            message: `Berhasil import ${totalImported.toLocaleString()} data. Catatan: ${failedGroups.join(' | ')}`
        };
    }

    router.reload({ preserveScroll: true });
};

const submitImportFile = () => {
    if (!uploadFile.value) {
        importAlert.value = { type: 'error', message: 'Silakan pilih file terlebih dahulu.' };
        return;
    }
    isUploadingFile.value = true;
    importAlert.value = null;

    const formData = new FormData();
    formData.append('file', uploadFile.value);
    if (uploadCategory.value) {
        formData.append('category', uploadCategory.value);
    }

    router.post('/one-power/roster-departemen/import-file', formData, {
        preserveScroll: true,
        onSuccess: () => {
            isUploadingFile.value = false;
            uploadFile.value = null;
            if (fileInputRef.value) fileInputRef.value.value = '';
            importAlert.value = { type: 'success', message: 'File roster berhasil diimpor.' };
            router.reload({ preserveScroll: true });
        },
        onError: (errors) => {
            isUploadingFile.value = false;
            importAlert.value = { type: 'error', message: errors.file || 'Gagal mengimpor file.' };
        }
    });
};

// Apply filter via Inertia
const applyFilter = () => {
    router.get('/one-power/roster-departemen', {
        bulan: filterBulan.value,
        tahun: filterTahun.value,
        category: filterCategory.value || undefined,
        jabatan: filterJabatan.value || undefined,
        search: searchKeyword.value || undefined,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const resetJabatan = () => {
    filterJabatan.value = '';
    applyFilter();
};

const printRoster = () => {
    window.print();
};

// Modal Edit Cell
const isEditModalOpen = ref(false);
const editingCell = ref({
    nrp: '',
    nama: '',
    tanggal: '',
    kode: '',
});

const openEditCell = (row, dayObj) => {
    const dayNum = dayObj.day;
    const currentCode = row.days[dayNum] || '';
    editingCell.value = {
        nrp: row.nrp,
        nama: row.nama,
        tanggal: dayObj.date,
        dayNum: dayNum,
        kode: currentCode,
    };
    isEditModalOpen.value = true;
};

const saveEditCell = () => {
    router.post('/one-power/roster-departemen/update-cell', {
        nrp: editingCell.value.nrp,
        tanggal: editingCell.value.tanggal,
        kode: editingCell.value.kode,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            isEditModalOpen.value = false;
        },
    });
};

// Legend Codes & Color Classes
const CODE_COLORS = {
    'D': 'bg-white text-slate-900 border-slate-200 font-bold hover:bg-slate-100',
    'N': 'bg-[#0070c0] text-white border-[#005a9e] font-bold hover:brightness-110',
    'OFF': 'bg-[#dc2626] text-white border-[#b91c1c] font-bold hover:brightness-110',
    'CT': 'bg-[#ffc000] text-slate-900 border-[#e0a800] font-bold hover:brightness-105',
    'ID': 'bg-[#7030a0] text-white border-[#58257f] font-bold hover:brightness-110',
    'IDS': 'bg-[#ff00ff] text-slate-900 border-[#d900d9] font-bold hover:brightness-105',
    'MCU': 'bg-[#00b050] text-white border-[#009242] font-bold hover:brightness-110',
    'FU': 'bg-[#006400] text-white border-[#004d00] font-bold hover:brightness-110',
    'H': 'bg-[#e11d48] text-white border-[#be123c] font-bold hover:brightness-110',
    'TR': 'bg-[#00b0f0] text-slate-900 border-[#0096cc] font-bold hover:brightness-105',
};

const getCodeClass = (kode, isSunday) => {
    if (!kode || kode.trim() === '') {
        return isSunday ? 'bg-amber-50/70 text-slate-300' : 'bg-transparent text-slate-300';
    }
    const upper = kode.toUpperCase().trim();
    return CODE_COLORS[upper] || 'bg-slate-200 text-slate-700 font-bold';
};

const monthNameDisplay = computed(() => {
    return props.filters.monthNames[props.filters.bulan] || '';
});
</script>

<template>
    <Head title="Roster Departemen Produksi - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Breadcrumb & Header (No Print) -->
        <div class="no-print mb-4">
            <nav class="mb-2 text-xs text-slate-500 flex items-center gap-1.5">
                <Link href="/portal" class="text-blue-600 hover:underline">Home</Link>
                <span>&gt;</span>
                <Link href="/one-power" class="text-blue-600 hover:underline">One-Power</Link>
                <span>&gt;</span>
                <span class="text-slate-400">Roster</span>
                <span>&gt;</span>
                <span class="font-bold text-slate-700">Roster Departemen Produksi</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div>
                    <h1 class="text-xl font-black tracking-tight text-slate-900 flex items-center gap-2">
                        <Users class="h-6 w-6 text-blue-600" />
                        Roster Kerja Departemen Produksi
                        <span v-if="filters.category" class="text-blue-600 font-extrabold text-base bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-100">
                            {{ filters.category }}
                        </span>
                        <span v-if="filters.jabatan" class="text-emerald-700 font-bold text-xs bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 flex items-center gap-1">
                            {{ filters.jabatan }}
                            <button @click="resetJabatan" title="Hapus filter jabatan" class="hover:text-red-500">
                                <X class="w-3 h-3" />
                            </button>
                        </span>
                    </h1>
                    <p class="text-xs text-slate-500 mt-1">
                        Matriks & Rekap Roster Kerja Seluruh Karyawan Produksi — Periode <span class="font-bold text-slate-700 uppercase">{{ monthNameDisplay }} {{ filters.tahun }}</span>
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        @click="isImportModalOpen = true"
                        class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs flex items-center gap-1.5 transition active:scale-95 cursor-pointer"
                    >
                        <UploadCloud class="w-4 h-4" />
                        <span>Import Roster</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filter Bar (No Print) -->
        <div class="no-print mb-4 bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-xs">
            <form @submit.prevent="applyFilter" class="flex flex-wrap items-center gap-2.5">
                <!-- Kategori -->
                <div class="flex-1 min-w-[170px]">
                    <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Kategori Roster</label>
                    <select 
                        v-model="filterCategory" 
                        @change="applyFilter"
                        class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50/50 py-2 px-3 focus:bg-white focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Semua Kategori</option>
                        <option v-for="g in displayRosterGroups" :key="g" :value="g">{{ g }}</option>
                    </select>
                </div>

                <!-- Jabatan -->
                <div class="flex-1 min-w-[190px]">
                    <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Jabatan</label>
                    <select 
                        v-model="filterJabatan" 
                        @change="applyFilter"
                        class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50/50 py-2 px-3 focus:bg-white focus:ring-2 focus:ring-blue-500"
                    >
                        <option value="">Semua Jabatan</option>
                        <option v-for="j in jabatanList" :key="j" :value="j">{{ j }}</option>
                    </select>
                </div>

                <!-- Bulan -->
                <div class="w-32">
                    <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Bulan</label>
                    <select 
                        v-model.number="filterBulan" 
                        class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50/50 py-2 px-3 focus:bg-white focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="(name, num) in filters.monthNames" :key="num" :value="Number(num)">{{ name }}</option>
                    </select>
                </div>

                <!-- Tahun -->
                <div class="w-24">
                    <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Tahun</label>
                    <select 
                        v-model.number="filterTahun" 
                        class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-slate-50/50 py-2 px-3 focus:bg-white focus:ring-2 focus:ring-blue-500"
                    >
                        <option v-for="y in filters.years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[11px] font-bold text-slate-500 mb-1 uppercase tracking-wider">Cari Karyawan / NRP</label>
                    <div class="relative">
                        <Search class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input 
                            v-model="searchKeyword" 
                            type="text" 
                            placeholder="Ketik nama atau NRP..."
                            class="w-full text-xs font-medium pl-8 pr-3 py-2 rounded-xl border-slate-200 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="self-end">
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition active:scale-95 flex items-center gap-1.5"
                    >
                        <Filter class="w-3.5 h-3.5" />
                        <span>Tampilkan</span>
                    </button>
                </div>
            </form>

            <!-- Legend Badge (No Print) -->
            <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center gap-1.5 text-[10px]">
                <span class="font-bold text-slate-400 mr-1 uppercase">Keterangan:</span>
                <span class="px-2 py-0.5 rounded border border-slate-300 bg-white font-bold text-slate-800">D = SIANG</span>
                <span class="px-2 py-0.5 rounded bg-[#0070c0] font-bold text-white">N = MALAM</span>
                <span class="px-2 py-0.5 rounded bg-[#dc2626] font-bold text-white">OFF = OFF</span>
                <span class="px-2 py-0.5 rounded bg-[#ffc000] font-bold text-slate-900">CT = CUTI</span>
                <span class="px-2 py-0.5 rounded bg-[#7030a0] font-bold text-white">ID = INDUKSI</span>
                <span class="px-2 py-0.5 rounded bg-[#ff00ff] font-bold text-slate-900">IDS = PERMIT</span>
                <span class="px-2 py-0.5 rounded bg-[#00b050] font-bold text-white">MCU = MCU</span>
                <span class="px-2 py-0.5 rounded bg-[#006400] font-bold text-white">FU = FOLLOW UP</span>
                <span class="px-2 py-0.5 rounded bg-[#e11d48] font-bold text-white">H = DIRUMAHKAN</span>
                <span class="px-2 py-0.5 rounded bg-[#00b0f0] font-bold text-slate-900">TR = TRAINING</span>
            </div>
        </div>

        <!-- Print Header Only (Visible on Print) -->
        <div class="print-only text-center mb-4">
            <h2 class="text-base font-black uppercase text-slate-900">
                ROSTER KERJA {{ filters.category ? filters.category.toUpperCase() : 'DEPARTEMEN PRODUKSI' }}
                {{ filters.jabatan ? '- ' + filters.jabatan.toUpperCase() : '' }}
            </h2>
            <h4 class="text-xs font-bold uppercase text-slate-600">
                PERIODE {{ monthNameDisplay.toUpperCase() }} {{ filters.tahun }}
            </h4>
        </div>

        <!-- Mobile Hint & Focus Toggle (No Print) -->
        <div class="no-print md:hidden flex items-center justify-between gap-2 mb-2.5 px-1 bg-blue-50/80 p-2 rounded-xl border border-blue-100">
            <span class="text-[11px] font-bold text-blue-800 truncate">
                👉 Geser ke kanan untuk lihat tanggal
            </span>
            <button 
                type="button" 
                @click="focusDatesMobile = !focusDatesMobile"
                class="shrink-0 px-2.5 py-1 text-[10px] font-extrabold rounded-lg border transition shadow-xs"
                :class="focusDatesMobile ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-300'"
            >
                {{ focusDatesMobile ? 'Fokus Tanggal (Aktif)' : 'Tampilkan Info Lengkap' }}
            </button>
        </div>

        <!-- Matriks Roster Wrapper -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-6">
            <div class="matrix-scroll-container overflow-x-auto max-h-[650px] relative">
                <table class="table-roster w-full text-[11px] border-collapse">
                    <!-- Table Header -->
                    <thead class="sticky top-0 z-30 bg-slate-100 text-slate-700 shadow-xs select-none">
                        <tr>
                            <th rowspan="2" class="sticky-col sticky-col-1 bg-slate-100 font-extrabold text-left px-2 md:px-3 py-2 border-r border-b border-slate-200">
                                NAMA KARYAWAN
                            </th>
                            <th rowspan="2" class="sticky-col sticky-col-2 bg-slate-100 font-extrabold text-center px-1.5 md:px-2 py-2 border-r border-b border-slate-200" :class="{ 'hidden md:table-cell': focusDatesMobile }">
                                NRP
                            </th>
                            <th rowspan="2" class="sticky-col sticky-col-3 bg-slate-100 font-extrabold text-left px-2 md:px-2.5 py-2 border-r border-b border-slate-200" :class="{ 'hidden md:table-cell': focusDatesMobile }">
                                JABATAN
                            </th>
                            <th :colspan="daysInMonth" class="text-center font-extrabold py-1 border-b border-slate-200 bg-slate-200/60 uppercase tracking-widest text-[10px]">
                                TANGGAL ({{ monthNameDisplay.toUpperCase() }} {{ filters.tahun }})
                            </th>
                        </tr>
                        <tr>
                            <th 
                                v-for="d in days" 
                                :key="d.day"
                                :class="[
                                    'text-center py-1 px-0.5 border-b border-r border-slate-200 font-black min-w-[28px] w-[28px] md:min-w-[30px] md:w-[30px] leading-tight text-[10px]',
                                    d.isSunday ? 'bg-amber-100 text-amber-900' : 'bg-slate-100 text-slate-700'
                                ]"
                            >
                                <div>{{ d.day }}</div>
                                <div class="text-[8px] font-semibold uppercase opacity-80">{{ d.dow }}</div>
                            </th>
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="rows.length === 0">
                            <td :colspan="daysInMonth + (focusDatesMobile ? 1 : 3)" class="text-center py-12 text-slate-400 font-medium">
                                Tidak ada data roster yang sesuai dengan filter yang dipilih.
                            </td>
                        </tr>

                        <tr 
                            v-for="(r, idx) in rows" 
                            :key="r.nrp" 
                            class="hover:bg-blue-50/40 transition-colors group"
                        >
                            <!-- Sticky Cols -->
                            <td class="sticky-col sticky-col-1 bg-white group-hover:bg-blue-50/40 px-2 md:px-3 py-1.5 border-r border-slate-200 font-bold text-slate-900 truncate">
                                {{ r.nama }}
                            </td>
                            <td class="sticky-col sticky-col-2 bg-white group-hover:bg-blue-50/40 px-1.5 md:px-2 py-1.5 border-r border-slate-200 text-center font-mono text-slate-600 text-[10px]" :class="{ 'hidden md:table-cell': focusDatesMobile }">
                                {{ r.nrp }}
                            </td>
                            <td class="sticky-col sticky-col-3 bg-white group-hover:bg-blue-50/40 px-2 md:px-2.5 py-1.5 border-r border-slate-200 text-slate-700 truncate text-[10px]" :class="{ 'hidden md:table-cell': focusDatesMobile }">
                                {{ r.jabatan }}
                            </td>

                            <!-- Day Matrix Cells -->
                            <td 
                                v-for="d in days" 
                                :key="d.day"
                                :class="[
                                    'text-center p-0 border-r border-b border-slate-200 transition relative cursor-pointer select-none',
                                    getCodeClass(r.days[d.day], d.isSunday)
                                ]"
                                @click="openEditCell(r, d)"
                                :title="`${r.nama} (${r.nrp}) - Tgl ${d.day}: ${r.days[d.day] || '-'}`"
                            >
                                <span class="inline-block py-1 px-0.5 text-[10px]">
                                    {{ r.days[d.day] || '' }}
                                </span>
                            </td>
                        </tr>

                        <!-- Separator Row -->
                        <tr v-if="rows.length > 0">
                            <td :colspan="daysInMonth + (focusDatesMobile ? 1 : 3)" class="bg-slate-200 h-1 p-0"></td>
                        </tr>

                        <!-- Akumulasi Harian Footer Rows -->
                        <tr 
                            v-if="rows.length > 0"
                            v-for="(label, code) in codeTypes" 
                            :key="code"
                            class="bg-slate-50 font-semibold text-[10px]"
                        >
                            <td class="sticky-col sticky-col-1 bg-slate-50 px-2 md:px-3 py-1 font-extrabold text-slate-700 border-r border-slate-200">
                                {{ typeof label === 'object' && label !== null ? (label.label || code) : label }}
                            </td>
                            <td class="sticky-col sticky-col-2 bg-slate-50 border-r border-slate-200 text-center font-mono font-bold text-slate-500" :class="{ 'hidden md:table-cell': focusDatesMobile }">
                                {{ code }}
                            </td>
                            <td class="sticky-col sticky-col-3 bg-slate-50 border-r border-slate-200" :class="{ 'hidden md:table-cell': focusDatesMobile }"></td>
                            <td 
                                v-for="d in days" 
                                :key="d.day"
                                :class="[
                                    'text-center py-1 px-0.5 border-r border-b border-slate-200 font-bold',
                                    d.isSunday ? 'bg-amber-100/50' : 'bg-slate-50',
                                    akkuPerHari[code][d.day] > 0 ? 'text-slate-900 font-black' : 'text-slate-300'
                                ]"
                            >
                                {{ akkuPerHari[code][d.day] > 0 ? akkuPerHari[code][d.day] : '' }}
                            </td>
                        </tr>

                        <!-- Total Row -->
                        <tr v-if="rows.length > 0" class="bg-slate-900 text-white font-black text-[10px] sticky bottom-0 z-20">
                            <td class="sticky-col sticky-col-1 bg-slate-900 text-white px-2 md:px-3 py-1.5 border-r border-slate-800 text-left font-black tracking-wider">
                                TOTAL KEHADIRAN
                            </td>
                            <td class="sticky-col sticky-col-2 bg-slate-900 border-r border-slate-800" :class="{ 'hidden md:table-cell': focusDatesMobile }"></td>
                            <td class="sticky-col sticky-col-3 bg-slate-900 border-r border-slate-800" :class="{ 'hidden md:table-cell': focusDatesMobile }"></td>
                            <td 
                                v-for="d in days" 
                                :key="d.day"
                                class="text-center py-1.5 px-0.5 border-r border-slate-800 font-black text-white bg-slate-900"
                            >
                                {{ akkuTotalHari[d.day] > 0 ? akkuTotalHari[d.day] : '' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel Rekap Akumulasi Per Karyawan (No Print) -->
        <div v-if="rows.length > 0" class="no-print bg-white rounded-2xl border border-slate-200/80 shadow-xs p-4 mb-8">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-black text-slate-800 flex items-center gap-1.5">
                    <Calendar class="w-4 h-4 text-blue-600" />
                    Akumulasi Roster Per Karyawan
                </h3>
                <span class="text-xs text-slate-500 font-medium">Total: {{ rows.length }} karyawan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse border border-slate-200">
                    <thead class="bg-slate-800 text-white font-bold uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="p-2.5 border border-slate-700">NAMA KARYAWAN</th>
                            <th class="p-2.5 border border-slate-700 text-center">NRP</th>
                            <th class="p-2.5 border border-slate-700">JABATAN</th>
                            <th class="p-2 border border-slate-700 text-center bg-white text-slate-900 font-black">D (SIANG)</th>
                            <th class="p-2 border border-slate-700 text-center bg-[#0070c0] text-white">N (MALAM)</th>
                            <th class="p-2 border border-slate-700 text-center bg-[#dc2626] text-white">OFF</th>
                            <th class="p-2 border border-slate-700 text-center bg-[#ffc000] text-slate-900">CUTI</th>
                            <th class="p-2 border border-slate-700 text-center bg-[#7030a0] text-white">INDUKSI</th>
                            <th class="p-2 border border-slate-700 text-center bg-[#00b050] text-white">MCU</th>
                            <th class="p-2 border border-slate-700 text-center bg-[#006400] text-white">FU</th>
                            <th class="p-2 border border-slate-700 text-center bg-[#e11d48] text-white">H</th>
                            <th class="p-2 border border-slate-700 text-center bg-[#00b0f0] text-slate-900">TRAINING</th>
                            <th class="p-2.5 border border-slate-700 text-center font-black bg-slate-900 text-white">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-for="r in rows" :key="r.nrp" class="hover:bg-slate-50">
                            <td class="p-2 font-bold text-slate-800 border border-slate-200">{{ r.nama }}</td>
                            <td class="p-2 text-center font-mono text-slate-600 border border-slate-200">{{ r.nrp }}</td>
                            <td class="p-2 text-slate-600 border border-slate-200">{{ r.jabatan }}</td>
                            <td class="p-2 text-center font-bold text-slate-800 border border-slate-200">{{ r.summary.siang || '-' }}</td>
                            <td class="p-2 text-center font-bold text-blue-600 border border-slate-200">{{ r.summary.malam || '-' }}</td>
                            <td class="p-2 text-center font-bold text-red-600 border border-slate-200">{{ r.summary.off || '-' }}</td>
                            <td class="p-2 text-center font-bold text-amber-600 border border-slate-200">{{ r.summary.cuti || '-' }}</td>
                            <td class="p-2 text-center font-bold text-purple-600 border border-slate-200">{{ r.summary.induksi || '-' }}</td>
                            <td class="p-2 text-center font-bold text-emerald-600 border border-slate-200">{{ r.summary.mcu || '-' }}</td>
                            <td class="p-2 text-center font-bold text-green-700 border border-slate-200">{{ r.summary.fu || '-' }}</td>
                            <td class="p-2 text-center font-bold text-rose-600 border border-slate-200">{{ r.summary.h || '-' }}</td>
                            <td class="p-2 text-center font-bold text-cyan-600 border border-slate-200">{{ r.summary.training || '-' }}</td>
                            <td class="p-2 text-center font-black text-slate-900 bg-slate-50 border border-slate-200">{{ r.summary.total }}</td>
                        </tr>
                        <!-- Grand Summary -->
                        <tr class="bg-slate-900 text-white font-black text-xs">
                            <td colspan="3" class="p-2.5 text-center font-black tracking-wider border border-slate-800">
                                GRAND TOTAL
                            </td>
                            <td class="p-2 text-center border border-slate-800 bg-slate-800">{{ grandSummary.siang }}</td>
                            <td class="p-2 text-center border border-slate-800 bg-blue-900 text-blue-200">{{ grandSummary.malam }}</td>
                            <td class="p-2 text-center border border-slate-800 bg-red-900 text-red-200">{{ grandSummary.off }}</td>
                            <td class="p-2 text-center border border-slate-800 bg-amber-900 text-amber-200">{{ grandSummary.cuti }}</td>
                            <td class="p-2 text-center border border-slate-800 bg-purple-900 text-purple-200">{{ grandSummary.induksi }}</td>
                            <td class="p-2 text-center border border-slate-800 bg-emerald-900 text-emerald-200">{{ grandSummary.mcu }}</td>
                            <td class="p-2 text-center border border-slate-800 bg-green-900 text-green-200">{{ grandSummary.fu }}</td>
                            <td class="p-2 text-center border border-slate-800 bg-rose-900 text-rose-200">{{ grandSummary.h }}</td>
                            <td class="p-2 text-center border border-slate-800 bg-cyan-900 text-cyan-200">{{ grandSummary.training }}</td>
                            <td class="p-2.5 text-center border border-slate-800 bg-black text-white font-black">{{ grandSummary.total }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Edit Cell Roster -->
        <div v-if="isEditModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-sm w-full p-5">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-black text-slate-900 flex items-center gap-1.5">
                        <Edit2 class="w-4 h-4 text-blue-600" />
                        Edit Roster Harian
                    </h4>
                    <button @click="isEditModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <div class="text-xs text-slate-600 mb-4 bg-slate-50 p-3 rounded-xl border border-slate-200/60 space-y-1">
                    <div><span class="font-bold">Karyawan:</span> {{ editingCell.nama }}</div>
                    <div><span class="font-bold">NRP:</span> {{ editingCell.nrp }}</div>
                    <div><span class="font-bold">Tanggal:</span> {{ editingCell.tanggal }} (Hari ke-{{ editingCell.dayNum }})</div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih / Masukkan Kode Roster:</label>
                    <input 
                        v-model="editingCell.kode"
                        type="text" 
                        placeholder="Contoh: D, N, OFF, CT..."
                        class="w-full text-sm font-bold uppercase py-2 px-3 rounded-xl border-slate-300 focus:ring-2 focus:ring-blue-500 mb-2"
                    />

                    <!-- Quick buttons -->
                    <div class="grid grid-cols-5 gap-1.5 text-[10px]">
                        <button 
                            type="button"
                            v-for="code in ['D', 'N', 'OFF', 'CT', 'ID', 'IDS', 'MCU', 'FU', 'H', 'TR']" 
                            :key="code"
                            @click="editingCell.kode = code"
                            :class="[
                                'py-1 rounded font-bold border transition',
                                editingCell.kode === code ? 'ring-2 ring-blue-600 scale-105' : 'opacity-85 hover:opacity-100',
                                CODE_COLORS[code]
                            ]"
                        >
                            {{ code }}
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button 
                        type="button" 
                        @click="isEditModalOpen = false"
                        class="px-3 py-1.5 text-xs font-bold text-slate-500 hover:text-slate-700"
                    >
                        Batal
                    </button>
                    <button 
                        type="button" 
                        @click="saveEditCell"
                        class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-xs transition"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Import Roster (Google Sheet & File Upload) -->
        <div 
            v-if="isImportModalOpen" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity"
            @click.self="isImportModalOpen = false"
        >
            <div class="bg-white w-full max-w-5xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col my-auto max-h-[95vh]">
                <!-- Modal Header -->
                <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-2xs">
                            <UploadCloud class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 leading-tight">Import &amp; Sinkronisasi Roster Produksi</h3>
                            <p class="text-[11px] text-slate-500">Sinkronisasi otomatis melalui Google Spreadsheet atau unggah file excel/csv</p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="isImportModalOpen = false"
                        class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-slate-200 bg-white px-5">
                    <button 
                        type="button"
                        @click="importActiveTab = 'sheets'"
                        :class="[
                            'py-2 px-3 text-xs font-bold border-b-2 flex items-center gap-1.5 transition cursor-pointer',
                            importActiveTab === 'sheets' 
                                ? 'border-blue-600 text-blue-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700'
                        ]"
                    >
                        <FileSpreadsheet class="w-3.5 h-3.5" />
                        <span>Google Spreadsheet Sync</span>
                    </button>
                    <button 
                        type="button"
                        @click="importActiveTab = 'file'"
                        :class="[
                            'py-2 px-3 text-xs font-bold border-b-2 flex items-center gap-1.5 transition cursor-pointer',
                            importActiveTab === 'file' 
                                ? 'border-blue-600 text-blue-600' 
                                : 'border-transparent text-slate-500 hover:text-slate-700'
                        ]"
                    >
                        <FileUp class="w-3.5 h-3.5" />
                        <span>Upload File (.xlsx / .csv)</span>
                    </button>
                </div>

                <!-- Modal Body without scrollbar -->
                <div class="p-4 overflow-y-auto [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden flex-1 space-y-2.5">
                    <!-- Alert Status -->
                    <div 
                        v-if="importAlert" 
                        :class="[
                            'p-2.5 rounded-xl border text-xs flex items-start gap-2 transition',
                            importAlert.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'
                        ]"
                    >
                        <CheckCircle2 v-if="importAlert.type === 'success'" class="w-4 h-4 text-emerald-600 mt-0.5 shrink-0" />
                        <AlertCircle v-else class="w-4 h-4 text-rose-600 mt-0.5 shrink-0" />
                        <div class="flex-1 font-medium leading-relaxed">{{ importAlert.message }}</div>
                        <button type="button" @click="importAlert = null" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- Live Animated Progress Bar -->
                    <div 
                        v-if="updatingGroup !== null || isUploadingFile || (progressPercent > 0 && progressPercent < 100)" 
                        class="p-3 rounded-xl bg-blue-50/90 border border-blue-200 shadow-2xs space-y-2"
                    >
                        <div class="flex items-center justify-between text-xs font-bold text-slate-800">
                            <div class="flex items-center gap-2">
                                <Loader2 class="w-3.5 h-3.5 animate-spin text-blue-600 shrink-0" />
                                <span class="text-blue-900 font-extrabold">{{ importProgressMsg || 'Memproses sinkronisasi data roster...' }}</span>
                            </div>
                            <span class="text-xs font-black text-blue-700 font-mono">{{ progressPercent }}%</span>
                        </div>

                        <!-- Progress Bar Track & Fill -->
                        <div class="w-full bg-blue-100 h-2.5 rounded-full overflow-hidden p-0.5 shadow-inner">
                            <div 
                                class="h-full rounded-full bg-gradient-to-r from-blue-600 via-indigo-600 to-emerald-500 transition-all duration-300 relative overflow-hidden"
                                :style="{ width: `${progressPercent}%` }"
                            >
                                <div class="absolute inset-0 bg-white/30 animate-pulse"></div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 1: GOOGLE SPREADSHEET -->
                    <div v-if="importActiveTab === 'sheets'" class="space-y-2.5">
                        <div class="flex items-center justify-between gap-2">
                            <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl px-3 py-1.5 text-[11px] text-amber-900 leading-normal flex items-center gap-2 flex-1">
                                <AlertCircle class="w-3.5 h-3.5 text-amber-600 shrink-0" />
                                <div>
                                    <span class="font-bold">Ketentuan Google Spreadsheet:</span> Link sheet wajib <span class="font-bold underline">Anyone with the link can view</span>. Nama kategori &amp; kata kunci jabatan dapat disesuaikan.
                                </div>
                            </div>

                            <button 
                                type="button" 
                                @click="addCategory"
                                :disabled="updatingGroup !== null || isSavingUrls"
                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-xs transition active:scale-95 cursor-pointer shrink-0 disabled:opacity-50"
                            >
                                <Plus class="w-3.5 h-3.5" />
                                <span>Tambah Kategori</span>
                            </button>
                        </div>

                        <!-- Table of Categories & URLs -->
                        <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                    <tr>
                                        <th class="py-1.5 px-3 w-[150px]">Nama Kategori</th>
                                        <th class="py-1.5 px-3 w-[180px]">Kata Kunci Jabatan (Keywords)</th>
                                        <th class="py-1.5 px-3 w-[200px]">Penanggung Jawab</th>
                                        <th class="py-1.5 px-3">URL Google Spreadsheet</th>
                                        <th class="py-1.5 px-3 text-center w-[170px]">Aksi &amp; Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium">
                                    <tr v-for="(cat, idx) in categories" :key="cat.key" class="hover:bg-slate-50/60 transition">
                                        <td class="py-1.5 px-3">
                                            <input 
                                                type="text" 
                                                v-model="cat.label"
                                                @change="saveSheetUrls()"
                                                placeholder="Nama Kategori..." 
                                                class="w-full text-xs font-bold py-1 px-2.5 rounded-lg border border-blue-200/70 bg-blue-50/40 focus:bg-white focus:ring-2 focus:ring-blue-500 text-blue-900"
                                            />
                                        </td>
                                        <td class="py-1.5 px-3">
                                            <input 
                                                type="text" 
                                                v-model="cat.keywords"
                                                @change="saveSheetUrls()"
                                                placeholder="Pisahkan koma (ex: HD, 785)..." 
                                                class="w-full text-xs py-1 px-2.5 rounded-lg border border-amber-200/70 bg-amber-50/30 focus:bg-white focus:ring-2 focus:ring-amber-500 text-slate-700 font-medium"
                                                title="Kata kunci jabatan yang dipisahkan dengan koma"
                                            />
                                        </td>
                                        <td class="py-1.5 px-3">
                                            <select 
                                                v-model="cat.penanggung_jawab_nrp"
                                                @change="handlePicChange(cat)"
                                                class="w-full text-xs py-1 px-2 rounded-lg border border-slate-200 bg-slate-50/60 focus:bg-white focus:ring-2 focus:ring-blue-500 text-slate-800 font-semibold"
                                                title="Pilih Penanggung Jawab Roster"
                                            >
                                                <option value="">-- Pilih PIC --</option>
                                                <option v-for="emp in manpowerEmployees" :key="emp.nrp" :value="emp.nrp">
                                                    {{ emp.name }}
                                                </option>
                                            </select>
                                        </td>
                                        <td class="py-1.5 px-3">
                                            <input 
                                                type="url" 
                                                v-model="cat.url"
                                                @change="saveSheetUrls()"
                                                placeholder="https://docs.google.com/spreadsheets/d/.../edit?gid=..." 
                                                class="w-full text-xs py-1 px-2.5 rounded-lg border-slate-200 bg-slate-50/40 focus:bg-white focus:ring-2 focus:ring-blue-500 text-slate-700"
                                            />
                                        </td>
                                        <td class="py-1.5 px-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <!-- Status Badge if present -->
                                                <span 
                                                    v-if="groupStatus[cat.key]?.status === 'loading'" 
                                                    class="inline-flex items-center gap-1 text-[10px] text-blue-700 font-bold bg-blue-100/80 px-1.5 py-0.5 rounded border border-blue-200 animate-pulse"
                                                >
                                                    <Loader2 class="w-3 h-3 animate-spin" />
                                                    <span>Proses</span>
                                                </span>
                                                <span 
                                                    v-else-if="groupStatus[cat.key]?.status === 'success'" 
                                                    class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200"
                                                    :title="`Berhasil: ${groupStatus[cat.key]?.count} data`"
                                                >
                                                    <CheckCircle2 class="w-3 h-3 text-emerald-600" />
                                                    <span>{{ groupStatus[cat.key]?.count }} data</span>
                                                </span>
                                                <span 
                                                    v-else-if="groupStatus[cat.key]?.status === 'error'" 
                                                    class="inline-flex items-center gap-1 text-[10px] text-rose-700 font-bold bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200"
                                                    :title="groupStatus[cat.key]?.message"
                                                >
                                                    <AlertCircle class="w-3 h-3 text-rose-600" />
                                                    <span>Gagal</span>
                                                </span>

                                                <button 
                                                    type="button" 
                                                    @click="importSheet(cat)"
                                                    :disabled="updatingGroup !== null || isSavingUrls"
                                                    class="inline-flex items-center gap-1 px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[11px] font-bold shadow-2xs transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer"
                                                    title="Sync / Update kategori ini"
                                                >
                                                    <Loader2 v-if="updatingGroup === cat.key" class="w-3 h-3 animate-spin" />
                                                    <RefreshCw v-else class="w-3 h-3" />
                                                    <span>Update</span>
                                                </button>

                                                <button 
                                                    type="button" 
                                                    @click="removeCategory(idx)"
                                                    :disabled="updatingGroup !== null || isSavingUrls"
                                                    class="p-1 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 transition cursor-pointer disabled:opacity-40"
                                                    title="Hapus baris kategori ini"
                                                >
                                                    <Trash2 class="w-3.5 h-3.5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="categories.length === 0">
                                        <td colspan="4" class="py-4 text-center text-slate-400 italic">
                                            Belum ada kategori roster. Klik "+ Tambah Kategori" di atas untuk menambahkan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 2: UPLOAD FILE -->
                    <div v-if="importActiveTab === 'file'" class="space-y-3">
                        <div class="bg-blue-50/60 border border-blue-200/80 rounded-xl p-2.5 text-xs text-blue-900 leading-relaxed flex items-start gap-2">
                            <FileSpreadsheet class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                            <div>
                                <span class="font-bold">Unggah Dokumen Roster:</span> Format file <span class="font-semibold">.xlsx, .xls, .csv</span> matriks tanggal bulanan standar departemen produksi.
                            </div>
                        </div>

                        <div class="space-y-2.5 bg-slate-50/50 p-3 rounded-xl border border-slate-200">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Target Kategori Roster (Opsional)</label>
                                <select 
                                    v-model="uploadCategory"
                                    class="w-full text-xs font-semibold rounded-xl border-slate-200 bg-white py-1.5 px-3 focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">Otomatis (Sesuai isi spreadsheet)</option>
                                    <option v-for="cat in categories" :key="cat.key" :value="cat.label">{{ cat.label }}</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Pilih File Spreadsheet</label>
                                <input 
                                    ref="fileInputRef"
                                    type="file" 
                                    accept=".xlsx,.xls,.csv"
                                    @change="onFileSelected"
                                    class="w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer border border-slate-200 rounded-xl bg-white p-1 text-slate-600"
                                />
                                <p class="text-[11px] text-slate-400 mt-0.5">Ukuran file maksimal 15MB (.xlsx, .xls, atau .csv).</p>
                            </div>
                        </div>

                        <div class="flex justify-end pt-1">
                            <button 
                                type="button" 
                                @click="submitImportFile"
                                :disabled="!uploadFile || isUploadingFile"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition disabled:opacity-50 cursor-pointer"
                            >
                                <Loader2 v-if="isUploadingFile" class="w-3.5 h-3.5 animate-spin" />
                                <UploadCloud v-else class="w-3.5 h-3.5" />
                                <span>Upload &amp; Proses Roster</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-5 py-2.5 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                    <div v-if="importActiveTab === 'sheets'">
                        <button 
                            type="button" 
                            @click="saveSheetUrls()"
                            :disabled="isSavingUrls || updatingGroup !== null"
                            class="px-3.5 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition disabled:opacity-50 cursor-pointer"
                        >
                            <Loader2 v-if="isSavingUrls" class="w-3.5 h-3.5 animate-spin" />
                            <Save v-else class="w-3.5 h-3.5" />
                            <span>Simpan Kategori &amp; URL</span>
                        </button>
                    </div>
                    <div v-else></div>

                    <div class="flex items-center gap-2">
                        <button 
                            v-if="importActiveTab === 'sheets'"
                            type="button" 
                            @click="importAllSheets"
                            :disabled="updatingGroup !== null || isSavingUrls"
                            class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs flex items-center gap-1.5 transition disabled:opacity-50 cursor-pointer"
                        >
                            <Loader2 v-if="updatingGroup === 'ALL'" class="w-3.5 h-3.5 animate-spin" />
                            <RefreshCw v-else class="w-3.5 h-3.5" />
                            <span>Update Semua Kategori</span>
                        </button>

                        <button 
                            type="button" 
                            @click="isImportModalOpen = false"
                            class="px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-200/60 transition cursor-pointer"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Sticky columns styling */
/* Responsive Sticky Columns */
@media (min-width: 768px) {
    .sticky-col-1 {
        position: sticky;
        left: 0;
        z-index: 15;
        width: 190px;
        min-width: 190px;
        max-width: 190px;
    }
    .sticky-col-2 {
        position: sticky;
        left: 190px;
        z-index: 15;
        width: 85px;
        min-width: 85px;
        max-width: 85px;
    }
    .sticky-col-3 {
        position: sticky;
        left: 275px;
        z-index: 15;
        width: 150px;
        min-width: 150px;
        max-width: 150px;
    }
    thead .sticky-col-1, thead .sticky-col-2, thead .sticky-col-3 {
        z-index: 35;
    }
    tfoot .sticky-col-1, tfoot .sticky-col-2, tfoot .sticky-col-3 {
        z-index: 25;
    }
}

@media (max-width: 767px) {
    .sticky-col-1 {
        position: sticky;
        left: 0;
        z-index: 15;
        width: 110px;
        min-width: 110px;
        max-width: 110px;
        font-size: 10px !important;
    }
    .sticky-col-2, .sticky-col-3 {
        position: static !important;
        left: auto !important;
    }
    thead .sticky-col-1 {
        z-index: 35;
    }
}

/* Print Styling */
.print-only {
    display: none;
}

@media print {
    .no-print {
        display: none !important;
    }
    .print-only {
        display: block !important;
    }
    .matrix-scroll-container {
        max-height: none !important;
        overflow: visible !important;
    }
    table {
        font-size: 8px !important;
    }
    th, td {
        padding: 2px 1px !important;
    }
    @page {
        size: landscape;
        margin: 6mm;
    }
}
</style>
