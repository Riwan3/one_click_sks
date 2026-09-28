<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { CalendarRange, Printer, ChevronLeft, ChevronRight, ChevronDown, Eye, Check, Loader2, Sparkles, UserCheck, UploadCloud, X } from '@lucide/vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    isAdmin: { type: Boolean, required: true },
    filters: { type: Object, required: true },
    employee: { type: Object, default: null },
    days: { type: Array, default: () => [] },
    employeeOptions: { type: Array, default: () => [] },
    penanggungJawab: { type: Object, default: null },
});

const isOperator = computed(() => {
    return Boolean(
        props.user?.isOperator ||
        String(props.user?.role || '').toLowerCase() === 'operator' ||
        String(props.user?.jabatan || '').toLowerCase().includes('operator')
    );
});

const isGroupLeader = computed(() => {
    const role = String(props.user?.role || '').toLowerCase();
    const jabatan = String(props.user?.jabatan || '').toUpperCase();
    return role === 'group-leader' || role === 'groupleader' || role === 'gl' || jabatan.includes('GROUP LEADER');
});

const todayString = computed(() => {
    const d = new Date();
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
});

const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

const KODE_STYLES = {
    d: 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
    n: 'bg-blue-50 text-blue-700 border-blue-200/80',
    off: 'bg-rose-50 text-rose-700 border-rose-200/80',
    ct: 'bg-amber-50 text-amber-800 border-amber-200/80',
    h: 'bg-purple-50 text-purple-700 border-purple-200/80',
    id: 'bg-violet-50 text-violet-700 border-violet-200/80',
    ids: 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200/80',
    mcu: 'bg-teal-50 text-teal-700 border-teal-200/80',
    fu: 'bg-lime-50 text-lime-800 border-lime-200/80',
    tr: 'bg-cyan-50 text-cyan-700 border-cyan-200/80',
};

const kodeClass = (kode) => KODE_STYLES[(kode || '').toLowerCase()] ?? 'bg-slate-50 text-slate-700 border-slate-200';

const KODE_TEXT_COLORS = {
    'n': 'text-blue-600 font-extrabold',
    'd': 'text-emerald-600 font-extrabold',
    'off': 'text-red-600 font-extrabold',
    'ct': 'text-amber-600 font-extrabold',
    'id': 'text-purple-600 font-extrabold',
    'ids': 'text-fuchsia-600 font-extrabold',
    'mcu': 'text-teal-600 font-extrabold',
    'fu': 'text-lime-600 font-extrabold',
    'h': 'text-rose-600 font-extrabold',
    'tr': 'text-cyan-600 font-extrabold',
};

const kodeTextColor = (kode) => KODE_TEXT_COLORS[(kode || '').toLowerCase()] ?? 'text-slate-900 font-extrabold';

const summaryCounts = computed(() => {
    const res = { d: 0, n: 0, ct: 0, off: 0, other: 0 };
    for (const d of props.days) {
        const k = (d.kode || '').toLowerCase();
        if (k === 'd') res.d++;
        else if (k === 'n') res.n++;
        else if (k === 'ct') res.ct++;
        else if (k === 'off') res.off++;
        else if (k) res.other++;
    }
    return res;
});

const monthInput = ref(props.filters.bulan);
const yearInput = ref(props.filters.tahun);

const employeeQuery = ref(props.employee ? `${props.employee.nrp} - ${props.employee.nama}` : '');
const employeeResults = ref([]);
const showEmployeeResults = ref(false);
const selectedNrp = ref(props.filters.nrp);
let searchTimer = null;

// Visibilitas Bulan (Admin Only)
const showVisibilityDropdown = ref(false);
const visibleMonthsState = ref(
    Array.isArray(props.filters.visibleMonths) && props.filters.visibleMonths.length
        ? [...props.filters.visibleMonths]
        : [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
);
const isSavingVisibility = ref(false);
const saveSuccessMsg = ref('');

watch(() => props.filters.visibleMonths, (newVal) => {
    if (Array.isArray(newVal)) {
        visibleMonthsState.value = [...newVal];
    }
});

const allMonthOptions = computed(() => {
    return props.filters.allMonthNames || props.filters.monthNames || {};
});

const selectAllMonths = () => {
    visibleMonthsState.value = Object.keys(allMonthOptions.value).map(Number);
};

const clearAllMonths = () => {
    visibleMonthsState.value = [];
};

const saveVisibleMonths = () => {
    isSavingVisibility.value = true;
    saveSuccessMsg.value = '';
    router.post('/one-power/roster-personal/visible-months', {
        months: visibleMonthsState.value,
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            saveSuccessMsg.value = 'Visibilitas berhasil disimpan!';
            setTimeout(() => {
                saveSuccessMsg.value = '';
                showVisibilityDropdown.value = false;
            }, 1200);
        },
        onError: () => {
            alert('Gagal menyimpan visibilitas bulan.');
        },
        onFinish: () => {
            isSavingVisibility.value = false;
        },
    });
};

const monthLabel = computed(() => (props.filters.allMonthNames || props.filters.monthNames)[props.filters.bulan] ?? '');

const tanggalCetak = computed(() => {
    const d = new Date();
    const day = d.getDate();
    const monthNames = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    const month = monthNames[d.getMonth()] || 'September';
    const year = d.getFullYear();
    return `${day} ${month} ${year}`;
});

const goTo = (nrp, bulan, tahun) => {
    router.get('/one-power/roster-personal', {
        nrp: nrp || undefined,
        bulan,
        tahun,
    }, { preserveScroll: true, preserveState: true });
};

const submitFilter = () => {
    goTo(selectedNrp.value, monthInput.value, yearInput.value);
};

const prevMonth = () => {
    let m = monthInput.value - 1;
    let y = yearInput.value;
    if (m < 1) { m = 12; y -= 1; }
    monthInput.value = m;
    yearInput.value = y;
    goTo(selectedNrp.value, m, y);
};

const nextMonth = () => {
    let m = monthInput.value + 1;
    let y = yearInput.value;
    if (m > 12) { m = 1; y += 1; }
    monthInput.value = m;
    yearInput.value = y;
    goTo(selectedNrp.value, m, y);
};

const onEmployeeFocus = () => {
    showEmployeeResults.value = true;
    if (employeeResults.value.length === 0) {
        onEmployeeQueryInput();
    }
};

const onEmployeeQueryInput = () => {
    clearTimeout(searchTimer);
    const keyword = employeeQuery.value.trim();
    searchTimer = setTimeout(async () => {
        try {
            const response = await fetch(`/one-power/roster-personal/search-employee?keyword=${encodeURIComponent(keyword)}`);
            employeeResults.value = await response.json();
            showEmployeeResults.value = true;
        } catch (e) {
            // ignore
        }
    }, 200);
};

const clearEmployeeSearch = () => {
    employeeQuery.value = '';
    onEmployeeQueryInput();
};

const pickEmployee = (emp) => {
    selectedNrp.value = emp.nrp;
    employeeQuery.value = `${emp.nrp} - ${emp.name}`;
    showEmployeeResults.value = false;
    goTo(emp.nrp, monthInput.value, yearInput.value);
};

const signerName = ref('Radhitya HS');
const signerTitle = ref('Group Leader Manpower');

watch(
    () => props.penanggungJawab,
    (pj) => {
        if (pj && pj.nama) {
            signerName.value = pj.nama;
            signerTitle.value = pj.jabatan || 'Group Leader Manpower';
        } else {
            try {
                const savedName = localStorage.getItem('roster_signer_name');
                const savedTitle = localStorage.getItem('roster_signer_title');
                if (savedName) signerName.value = savedName;
                if (savedTitle) signerTitle.value = savedTitle;
            } catch (e) {
                // ignore
            }
        }
    },
    { immediate: true }
);

const saveSignerSettings = () => {
    try {
        localStorage.setItem('roster_signer_name', signerName.value);
        localStorage.setItem('roster_signer_title', signerTitle.value);
    } catch (e) {
        // ignore
    }
};

const handlePrint = () => {
    const params = new URLSearchParams({
        nrp: selectedNrp.value || props.filters.nrp || '',
        bulan: monthInput.value,
        tahun: yearInput.value,
        signer_name: signerName.value,
        signer_title: signerTitle.value,
    });
    window.open(`/one-power/roster-personal/print?${params.toString()}`, '_blank');
};
</script>

<template>
    <Head :title="(isOperator ? 'Roster Saya' : 'Roster Personal') + ' - OneClick'" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" :back-url="isGroupLeader || isOperator ? '/portal/data' : undefined">
        <div class="mx-auto max-w-3xl">
            <!-- Header Title -->
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between print:hidden">
                <div>
                    <h1 class="mb-0.5 flex items-center gap-2 text-xl font-black text-slate-800 tracking-tight">
                        <CalendarRange class="h-5 w-5 text-blue-500" />
                        {{ isOperator ? 'Roster Saya' : 'Roster Personal' }}
                    </h1>
                    <p class="m-0 text-xs font-medium text-slate-500">
                        {{ isAdmin ? 'Pilih karyawan untuk melihat roster kalender bulanannya.' : 'Roster kalender bulanan Anda.' }}
                    </p>
                </div>
            </div>

            <!-- Filter & Navigation Bar (V1 toolbar) -->
            <div class="mb-4 rounded-xl border border-slate-200/90 bg-white p-3 shadow-xs print:hidden">
                <form class="flex flex-wrap items-center gap-2" @submit.prevent="submitFilter">
                    <!-- Admin Search / Employee Picker -->
                    <div v-if="isAdmin" class="relative w-full sm:w-80 min-w-[220px] flex-1">
                        <div class="relative flex items-center">
                            <input
                                v-model="employeeQuery"
                                type="text"
                                autocomplete="off"
                                placeholder="Pilih / Cari Karyawan..."
                                class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-3 pr-14 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition shadow-xs"
                                @input="onEmployeeQueryInput"
                                @focus="onEmployeeFocus"
                            >
                            <div class="absolute right-2 flex items-center gap-1">
                                <button
                                    v-if="employeeQuery"
                                    type="button"
                                    class="text-slate-400 hover:text-slate-600 p-0.5 rounded cursor-pointer"
                                    title="Bersihkan"
                                    @click="clearEmployeeSearch"
                                >
                                    <X class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    type="button"
                                    class="text-slate-400 hover:text-slate-600 p-0.5 rounded cursor-pointer"
                                    title="Tampilkan Daftar Karyawan"
                                    @click="showEmployeeResults = !showEmployeeResults; if (showEmployeeResults) onEmployeeFocus();"
                                >
                                    <ChevronDown class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- Backdrop to close dropdown -->
                        <div v-if="showEmployeeResults" class="fixed inset-0 z-30" @click="showEmployeeResults = false" />

                        <!-- Dropdown List of Employees -->
                        <div
                            v-if="showEmployeeResults"
                            class="absolute left-0 z-40 mt-1 max-h-72 w-full min-w-[280px] sm:min-w-[340px] overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-2xl"
                        >
                            <div class="sticky top-0 bg-slate-50 px-3 py-1.5 border-b border-slate-100 flex items-center justify-between text-[11px] text-slate-500 font-bold">
                                <span>DAFTAR KARYAWAN</span>
                                <span>{{ (employeeResults.length || employeeOptions.length) }} Karyawan</span>
                            </div>

                            <button
                                v-for="emp in (employeeResults.length > 0 ? employeeResults : employeeOptions)"
                                :key="emp.nrp"
                                type="button"
                                class="block w-full px-3 py-2 text-left text-xs hover:bg-blue-50 transition border-b border-slate-100 last:border-0 cursor-pointer"
                                :class="selectedNrp === emp.nrp ? 'bg-blue-50/80 font-bold' : ''"
                                @click="pickEmployee(emp)"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-900">{{ emp.nrp }} - {{ emp.name }}</span>
                                    <span v-if="selectedNrp === emp.nrp" class="text-blue-600 font-black text-xs">✓</span>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5 text-[10px] text-slate-500">
                                    <span v-if="emp.jabatan_ss6">{{ emp.jabatan_ss6 }}</span>
                                    <span v-if="emp.unit" class="font-semibold text-slate-700 bg-slate-100 px-1 rounded">{{ emp.unit }}</span>
                                </div>
                            </button>

                            <div v-if="employeeResults.length === 0 && employeeOptions.length === 0" class="p-4 text-center text-xs text-slate-400">
                                Tidak ada data karyawan.
                            </div>
                        </div>
                    </div>

                    <!-- Month Nav Controls -->
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            class="flex h-8.5 w-8.5 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100 transition active:scale-95 cursor-pointer"
                            title="Bulan Sebelumnya"
                            @click="prevMonth"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </button>
                        <select
                            v-model.number="monthInput"
                            class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 outline-none focus:border-blue-500"
                            @change="submitFilter"
                        >
                            <option v-for="(name, num) in filters.monthNames" :key="num" :value="Number(num)">{{ name }}</option>
                        </select>
                        <select
                            v-model.number="yearInput"
                            class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-bold text-slate-700 outline-none focus:border-blue-500"
                            @change="submitFilter"
                        >
                            <option v-for="y in filters.years" :key="y" :value="y">{{ y }}</option>
                        </select>
                        <button
                            type="button"
                            class="flex h-8.5 w-8.5 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100 transition active:scale-95 cursor-pointer"
                            title="Bulan Berikutnya"
                            @click="nextMonth"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>

                    <button
                        type="submit"
                        class="rounded-lg bg-blue-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-blue-700 transition cursor-pointer"
                    >
                        Tampilkan
                    </button>

                    <!-- Akses Bulan (Khusus Admin) -->
                    <div v-if="isAdmin" class="relative">
                        <button
                            type="button"
                            class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer"
                            @click="showVisibilityDropdown = !showVisibilityDropdown"
                        >
                            <Eye class="h-3.5 w-3.5 text-blue-600" />
                            <span>Akses Bulan</span>
                            <span
                                v-if="visibleMonthsState.length < 12"
                                class="rounded-full bg-blue-600 px-1.5 py-0.5 text-[9px] font-black text-white"
                            >
                                {{ visibleMonthsState.length }}
                            </span>
                        </button>

                        <div v-if="showVisibilityDropdown" class="fixed inset-0 z-30" @click="showVisibilityDropdown = false" />

                        <div v-if="showVisibilityDropdown" class="absolute right-0 z-40 mt-2 w-72 rounded-xl border border-slate-200 bg-white p-4 shadow-xl">
                            <div class="mb-2 border-b border-slate-100 pb-2">
                                <h4 class="text-xs font-bold uppercase text-slate-800">Akses Bulan Non-Admin</h4>
                                <p class="text-[11px] text-slate-400">Pilih bulan yang dapat dibuka oleh operator.</p>
                            </div>
                            <div class="mb-2 flex items-center justify-between text-[11px]">
                                <button type="button" class="font-bold text-blue-600 cursor-pointer" @click="selectAllMonths">Pilih Semua</button>
                                <button type="button" class="font-bold text-red-500 cursor-pointer" @click="clearAllMonths">Kosongkan</button>
                            </div>
                            <div class="grid grid-cols-2 gap-1 max-h-48 overflow-y-auto py-1 pr-1">
                                <label
                                    v-for="(name, num) in allMonthOptions"
                                    :key="num"
                                    class="flex items-center gap-2 rounded-lg border border-slate-100 px-2 py-1.5 text-xs text-slate-700 cursor-pointer"
                                    :class="visibleMonthsState.includes(Number(num)) ? 'bg-blue-50 border-blue-200 font-bold text-blue-800' : ''"
                                >
                                    <input type="checkbox" :value="Number(num)" v-model="visibleMonthsState" class="rounded text-blue-600" />
                                    <span>{{ name }}</span>
                                </label>
                            </div>
                            <div class="mt-3 border-t border-slate-100 pt-2">
                                <button
                                    type="button"
                                    :disabled="isSavingVisibility"
                                    class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white shadow-xs hover:bg-blue-700 disabled:opacity-50 cursor-pointer"
                                    @click="saveVisibleMonths"
                                >
                                    <Loader2 v-if="isSavingVisibility" class="h-3.5 w-3.5 animate-spin" />
                                    <Check v-else class="h-3.5 w-3.5" />
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol Import Roster Link Google Sheet (Khusus Admin) -->
                    <a
                        v-if="isAdmin"
                        href="/one-power/roster-departemen?openImport=1"
                        class="flex items-center gap-1.5 rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100 transition cursor-pointer"
                        title="Buka Pengaturan Import Link Google Spreadsheet di Roster Departemen"
                    >
                        <UploadCloud class="h-3.5 w-3.5 text-blue-600" />
                        <span>Import Link Roster</span>
                    </a>

                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white hover:bg-emerald-700 transition cursor-pointer"
                        @click="handlePrint"
                    >
                        <Printer class="h-3.5 w-3.5" />
                        <span>Print</span>
                    </button>
                </form>
            </div>

            <!-- Summary Chips -->
            <div v-if="employee && days.length" class="mb-4 grid grid-cols-2 sm:grid-cols-4 gap-2 print:hidden">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-2.5 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase text-emerald-700">Day (Siang)</div>
                        <div class="text-base font-black text-emerald-900">{{ summaryCounts.d }} <span class="text-[11px] font-normal">Hari</span></div>
                    </div>
                    <span class="rounded-md bg-emerald-200/80 px-2 py-0.5 text-xs font-extrabold text-emerald-800">D</span>
                </div>
                <div class="rounded-xl border border-blue-200 bg-blue-50/70 p-2.5 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase text-blue-700">Night (Malam)</div>
                        <div class="text-base font-black text-blue-900">{{ summaryCounts.n }} <span class="text-[11px] font-normal">Hari</span></div>
                    </div>
                    <span class="rounded-md bg-blue-200/80 px-2 py-0.5 text-xs font-extrabold text-blue-800">N</span>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-2.5 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase text-amber-700">Cuti (CT)</div>
                        <div class="text-base font-black text-amber-900">{{ summaryCounts.ct }} <span class="text-[11px] font-normal">Hari</span></div>
                    </div>
                    <span class="rounded-md bg-amber-200/80 px-2 py-0.5 text-xs font-extrabold text-amber-800">CT</span>
                </div>
                <div class="rounded-xl border border-rose-200 bg-rose-50/70 p-2.5 flex items-center justify-between">
                    <div>
                        <div class="text-[10px] font-bold uppercase text-rose-700">Off / Libur</div>
                        <div class="text-base font-black text-rose-900">{{ summaryCounts.off }} <span class="text-[11px] font-normal">Hari</span></div>
                    </div>
                    <span class="rounded-md bg-rose-200/80 px-2 py-0.5 text-xs font-extrabold text-rose-800">OFF</span>
                </div>
            </div>

            <!-- Empty states -->
            <div v-if="isAdmin && !employee" class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-center text-xs font-bold text-blue-700 print:hidden">
                Silakan cari dan pilih karyawan terlebih dahulu.
            </div>

            <div v-else-if="!employee" class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-center text-xs font-bold text-amber-700 print:hidden">
                Data karyawan tidak ditemukan.
            </div>

            <!-- THE OFFICIAL V1 ROSTER CARD -->
            <div v-else class="mx-auto max-w-2xl rounded-xl border border-slate-300 bg-white p-6 shadow-sm print:border-0 print:p-0 print:shadow-none">
                <!-- Header Logos -->
                <div class="mb-4 flex items-center justify-between gap-4 border-b pb-3">
                    <div class="flex items-center gap-2.5">
                        <img src="/images/images.png" alt="Safe & Strong" class="h-10 sm:h-11 object-contain">
                        <div class="leading-tight">
                            <div class="text-xs sm:text-sm font-black tracking-tight text-slate-900">SAFE & STRONG</div>
                            <div class="text-[8px] sm:text-[9px] font-bold text-slate-600 tracking-wider">OPERATIONAL PERFORMANCE</div>
                        </div>
                    </div>
                    <div>
                        <img src="/images/logo_synergy.png" alt="Synergy" class="h-10 sm:h-12 object-contain">
                    </div>
                </div>

                <!-- Banner Judul Dokumen -->
                <div class="mb-3 border-y-2 border-slate-900 py-1.5 text-center text-xs font-black uppercase tracking-wider text-slate-900">
                    ROSTER KARYAWAN DEPT. PRODUKSI - PPA JOBSITE ADARO INDONESIA
                </div>

                <!-- Info Karyawan & Unit Box (V1 Style) -->
                <div class="mb-3 flex items-start justify-between gap-4">
                    <div class="space-y-1 text-xs">
                        <div class="flex">
                            <span class="w-20 font-bold text-slate-800">NAMA</span>
                            <span class="w-4 font-bold text-slate-800">:</span>
                            <span class="font-bold text-slate-900">{{ employee.nama || '-' }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-20 font-bold text-slate-800">NRP</span>
                            <span class="w-4 font-bold text-slate-800">:</span>
                            <span class="font-bold text-slate-900">{{ employee.nrp || '-' }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-20 font-bold text-slate-800">JABATAN</span>
                            <span class="w-4 font-bold text-slate-800">:</span>
                            <span class="font-semibold text-slate-800">{{ employee.jabatan || '-' }}</span>
                        </div>
                    </div>
                    <div class="min-w-36 border-2 border-slate-900 text-center rounded-xs overflow-hidden">
                        <div class="border-b-2 border-slate-900 bg-white px-4 py-0.5 text-xs font-black text-slate-900">
                            UNIT
                        </div>
                        <div class="flex min-h-10 items-center justify-center px-4 py-1 text-base font-black text-slate-900">
                            {{ employee.unit || '-' }}
                        </div>
                    </div>
                </div>

                <!-- Periode Header -->
                <div class="mb-3 border border-slate-900 bg-white py-1 text-center text-xs font-extrabold text-slate-900 uppercase">
                    PERIODE {{ monthLabel.toUpperCase() }} {{ filters.tahun }}
                </div>

                <!-- Roster Table (Full V1 Official Table) -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-slate-900 text-xs">
                        <thead>
                            <tr class="bg-slate-200 text-slate-900">
                                <th class="border border-slate-900 px-1 py-1 font-bold text-center w-10">NO</th>
                                <th class="border border-slate-900 px-2 py-1 font-bold text-center w-24">HARI</th>
                                <th class="border border-slate-900 px-2 py-1 font-bold text-center w-40">TANGGAL</th>
                                <th class="border border-slate-900 px-2 py-1 font-bold text-center w-20">ROSTER</th>
                                <th class="border border-slate-900 px-2 py-1 font-bold text-center">KETERANGAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="d in days"
                                :key="d.tanggal"
                                :class="d.dayOfWeek === 0 ? 'bg-[#fef9c3]' : 'bg-white'"
                                :style="d.dayOfWeek === 0 ? 'background-color: #fef9c3;' : ''"
                            >
                                <td class="border border-slate-900 px-1 py-0.5 text-center font-medium">{{ d.day }}</td>
                                <td class="border border-slate-900 px-2 py-0.5 text-center font-medium">
                                    {{ DAY_NAMES[d.dayOfWeek] }}
                                </td>
                                <td class="border border-slate-900 px-2 py-0.5 text-center font-medium">
                                    {{ String(d.day).padStart(2, '0') }} {{ monthLabel }} {{ filters.tahun }}
                                </td>
                                <td class="border border-slate-900 px-1 py-0.5 text-center font-extrabold" :class="kodeTextColor(d.kode)">
                                    {{ d.kode || '' }}
                                </td>
                                <td class="border border-slate-900 px-2 py-0.5 text-center font-medium">
                                    {{ d.keterangan || '' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Section (V1 Note, Slogans & Signatures) -->
                <div class="mt-4 pt-2 text-xs">
                    <div class="flex items-start justify-between gap-4">
                        <!-- Left notes & slogan -->
                        <div class="space-y-2">
                            <div>
                                <div class="font-extrabold text-red-600">**Note</div>
                                <div class="text-[11px] text-slate-800 font-medium">
                                    - Update roster per hari harap menyesuaikan dengan SS6.
                                </div>
                            </div>
                            <div class="pt-1 text-xs font-black leading-tight">
                                <div style="color: #16a34a;">Saya Sehat, Saya Selamat,</div>
                                <div style="color: #dc2626;">Saya Semangat, Saya Manfaat!!!!!</div>
                            </div>
                            <div class="pt-2">
                                <img src="/images/logo_safe_strong.png" alt="SAFE & STRONG" class="h-9 sm:h-10 object-contain">
                            </div>
                        </div>

                        <!-- Right signature block -->
                        <div class="text-right shrink-0 min-w-[180px] sm:min-w-[200px]">
                            <div class="text-slate-900 font-medium">Mengetahui,</div>
                            <div class="text-slate-900 font-medium mt-0.5">Tanjung, {{ tanggalCetak }}</div>
                            <div class="h-14 sm:h-16"></div>
                            <div class="inline-flex flex-col items-end">
                                <input
                                    v-model="signerName"
                                    type="text"
                                    class="text-right font-bold text-slate-900 text-sm border-b border-dashed border-transparent hover:border-slate-400 focus:border-blue-500 focus:bg-blue-50/50 outline-none transition px-1 py-0.5 rounded cursor-text w-48 sm:w-56"
                                    title="Klik untuk mengubah nama penandatangan"
                                    placeholder="Nama Penandatangan"
                                    @input="saveSignerSettings"
                                >
                                <input
                                    v-model="signerTitle"
                                    type="text"
                                    class="text-right text-xs italic text-slate-800 border-b border-dashed border-transparent hover:border-slate-400 focus:border-blue-500 focus:bg-blue-50/50 outline-none transition px-1 py-0.5 rounded cursor-text w-48 sm:w-56 mt-0.5"
                                    title="Klik untuk mengubah jabatan"
                                    placeholder="Jabatan"
                                    @input="saveSignerSettings"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
