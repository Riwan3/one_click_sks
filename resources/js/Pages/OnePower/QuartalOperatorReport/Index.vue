<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import {
    Trophy, CloudDownload, RefreshCw, Medal, RotateCcw,
    Search, Filter, CalendarDays, ClipboardList, ChevronRight, AlertTriangle
} from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import MultiImportModal from './Partials/MultiImportModal.vue';
import DprDailyDetailModal from './Partials/DprDailyDetailModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    isAdmin: { type: Boolean, required: true },
    isGroupLeader: { type: Boolean, default: false },
    activeTab: { type: String, default: 'operator_report' },
    filters: { type: Object, required: true },
    reportData: { type: Object, required: true },
});

// Filters state
const currentTab = ref(props.activeTab || 'operator_report');
const quartalInput = ref(props.filters.quartal);
const yearInput = ref(props.filters.tahun);
const searchInput = ref(props.filters.search || '');
const kategoriInput = ref(props.filters.kategori || 'ALL');

const allTabs = [
    { key: 'operator_report', label: 'Operator Report (Harian)', periodBadge: 'Harian', icon: ClipboardList, desc: 'Laporan performa & pencapaian target operator per hari' },
    { key: 'monthly_report', label: 'Monthly Report / MOR (Bulanan)', periodBadge: 'Bulanan', icon: CalendarDays, desc: 'Akumulasi performa bulanan operator' },
    { key: 'quartal_report', label: 'Quartal Report (Kuartalan)', periodBadge: 'Per Kuartal', icon: Trophy, desc: 'Ranking kuartal dan perolehan poin MOR' },
];

const tabs = computed(() => (
    props.isGroupLeader ? allTabs.filter((t) => t.key === 'monthly_report') : allTabs
));

const switchTab = (tabKey) => {
    currentTab.value = tabKey;
    submitFilter();
};

const submitFilter = () => {
    router.get('/one-power/quartal-operator-report', {
        tab: currentTab.value,
        quartal: quartalInput.value,
        tahun: yearInput.value,
        search: searchInput.value || undefined,
        kategori: currentTab.value === 'operator_report' ? kategoriInput.value : undefined,
    }, { preserveScroll: true, preserveState: true });
};

// Import Modal
const importModalOpen = ref(false);

// Daily Detail Modal
const detailModalOpen = ref(false);
const selectedOperator = ref({ nrp: '', nama: '' });

const openDetail = (row) => {
    selectedOperator.value = { nrp: row.nrp, nama: row.nama };
    detailModalOpen.value = true;
};

// Recalculate
const recalculateForm = useForm({});
const handleRecalculate = async () => {
    const confirmed = await confirmDialog({
        title: 'Hitung Ulang Performa Kuartal?',
        text: `Sistem akan menghitung ulang seluruh agregat BCM, produktivitas (PDTY), dan poin ranking MOR untuk Kuartal ${quartalInput.value} Tahun ${yearInput.value}.`,
        confirmButtonText: 'Ya, Hitung Ulang!',
        danger: false,
    });
    if (!confirmed) return;

    recalculateForm.transform(() => ({
        quartal: quartalInput.value,
        tahun: yearInput.value
    })).post('/one-power/quartal-operator-report/recalculate', { preserveScroll: true });
};

// Reset
const resetForm = useForm({});
const handleReset = async (resetAll = false) => {
    const msg = resetAll
        ? 'PERINGATAN: Seluruh data QOR & MOR dari semua periode akan DIHAPUS BERSIH!'
        : `Hapus semua data DPR, SAP HM, ATR, dan kalkulasi untuk Kuartal ${quartalInput.value} Tahun ${yearInput.value}?`;

    const confirmed = await confirmDialog({
        title: resetAll ? 'Reset Seluruh Database QOR?' : 'Reset Data Periode?',
        text: msg,
        confirmButtonText: 'Ya, Reset Data!',
        danger: true,
    });
    if (!confirmed) return;

    resetForm.transform(() => ({
        quartal: quartalInput.value,
        tahun: yearInput.value,
        reset_all: resetAll
    })).post('/one-power/quartal-operator-report/reset', { preserveScroll: true });
};

// Helpers for badges
const rankBadgeClass = (rank) => ({
    1: 'bg-amber-400 text-amber-950 font-black ring-4 ring-amber-200/60 shadow',
    2: 'bg-slate-200 text-slate-800 font-bold ring-2 ring-slate-300',
    3: 'bg-orange-300 text-orange-950 font-bold ring-2 ring-orange-200',
}[rank] ?? 'bg-slate-100 text-slate-600 font-semibold');

const rowHighlightClass = (rank) => (rank <= 3 ? 'bg-amber-50/50' : '');

const achievementBadgeClass = (pct) => {
    if (!pct) return 'bg-slate-100 text-slate-500';
    const num = parseFloat(pct);
    if (num >= 100) return 'bg-emerald-100 text-emerald-800 font-bold';
    if (num >= 85) return 'bg-blue-100 text-blue-800 font-semibold';
    return 'bg-rose-100 text-rose-800 font-semibold';
};

const itemsList = computed(() => {
    if (props.reportData && props.reportData.data) {
        return props.reportData.data;
    }
    return Array.isArray(props.reportData) ? props.reportData : [];
});
</script>

<template>
    <Head title="Laporan Performa Operator - OneClick" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <!-- Page Header -->
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="mb-1 flex items-center gap-2.5 text-2xl font-black text-slate-800">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-white shadow-sm">
                        <Trophy class="h-5 w-5" />
                    </div>
                    Laporan Performa Operator
                </h1>
                <p class="m-0 text-sm text-slate-500">
                    Monitoring performa harian (Operator Report), akumulasi bulanan (MOR), dan evaluasi ranking performa kuartal.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-2">
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-emerald-700 transition"
                    @click="importModalOpen = true"
                >
                    <CloudDownload class="h-4 w-4" /> Import Data
                </button>

                <button
                    v-if="isAdmin"
                    type="button"
                    :disabled="recalculateForm.processing"
                    class="flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-blue-700 disabled:opacity-60 transition"
                    @click="handleRecalculate"
                >
                    <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': recalculateForm.processing }" />
                    <span>{{ recalculateForm.processing ? 'Menghitung...' : 'Hitung Ulang' }}</span>
                </button>

                <div v-if="isAdmin" class="relative group">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-2.5 text-xs font-bold text-rose-700 hover:bg-rose-100 transition"
                        @click="handleReset(false)"
                    >
                        <RotateCcw class="h-4 w-4" /> Reset Periode
                    </button>
                </div>
            </div>
        </div>

        <!-- Modern Tabs -->
        <div class="mb-5 grid grid-cols-1" :class="isGroupLeader ? '' : 'md:grid-cols-3'">
            <button
                v-for="t in tabs"
                :key="t.key"
                type="button"
                class="flex items-center gap-3.5 rounded-2xl border p-4 text-left transition-all"
                :class="currentTab === t.key
                    ? 'border-blue-500 bg-blue-50/50 shadow-md ring-1 ring-blue-500/20'
                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/70 shadow-sm'"
                @click="switchTab(t.key)"
            >
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition"
                    :class="currentTab === t.key ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-500'"
                >
                    <component :is="t.icon" class="h-5 w-5" />
                </div>
                <div class="overflow-hidden">
                    <div class="text-sm font-extrabold" :class="currentTab === t.key ? 'text-blue-900' : 'text-slate-800'">
                        {{ t.label }}
                    </div>
                    <div class="text-xs text-slate-500 truncate">{{ t.desc }}</div>
                </div>
            </button>
        </div>

        <!-- Filters Bar -->
        <div class="mb-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <form class="flex flex-wrap items-center gap-3" @submit.prevent="submitFilter">
                <!-- Quartal Select -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500">Kuartal:</span>
                    <select v-model.number="quartalInput" class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white transition" @change="submitFilter">
                        <option :value="1">Kuartal 1 (Jan - Mar)</option>
                        <option :value="2">Kuartal 2 (Apr - Jun)</option>
                        <option :value="3">Kuartal 3 (Jul - Sep)</option>
                        <option :value="4">Kuartal 4 (Okt - Des)</option>
                    </select>
                </div>

                <!-- Year Select -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500">Tahun:</span>
                    <select v-model.number="yearInput" class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white transition" @change="submitFilter">
                        <option v-for="y in filters.years" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>

                <!-- Kategori Filter (Operator Report Only) -->
                <div v-if="currentTab === 'operator_report'" class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-500">Kategori:</span>
                    <select v-model="kategoriInput" class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-semibold text-slate-800 outline-none focus:border-blue-500 focus:bg-white transition" @change="submitFilter">
                        <option value="ALL">Semua (OB + Coal)</option>
                        <option value="OB">Hanya OB</option>
                        <option value="COAL">Hanya Coal</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="relative flex-1 min-w-[200px]">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Cari NRP, Nama, Unit..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-3 py-2 text-xs text-slate-800 outline-none focus:border-blue-500 focus:bg-white transition"
                    />
                </div>

                <button type="submit" class="flex items-center gap-1.5 rounded-xl bg-slate-800 px-4 py-2 text-xs font-bold text-white hover:bg-slate-900 transition">
                    <Filter class="h-3.5 w-3.5" /> Terapkan Filter
                </button>
            </form>
        </div>

        <!-- ================= TAB 1: OPERATOR REPORT ================= -->
        <div v-if="currentTab === 'operator_report'" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[900px] text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center">No</th>
                        <th class="px-4 py-3.5">NRP / Nama Operator</th>
                        <th class="px-4 py-3.5">Jabatan</th>
                        <th class="px-4 py-3.5">Kode Unit</th>
                        <th class="px-4 py-3.5">Loader Terakhir</th>
                        <th class="px-4 py-3.5 text-right">Rata Jarak (m)</th>
                        <th class="px-4 py-3.5 text-right">Total Rit</th>
                        <th class="px-4 py-3.5 text-right text-amber-600">Total BCM (OB)</th>
                        <th class="px-4 py-3.5 text-right text-slate-700">Total TON (Coal)</th>
                        <th class="px-4 py-3.5 text-center">Achiev PDTY</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="itemsList.length === 0">
                        <td colspan="10" class="px-4 py-12 text-center text-slate-400">
                            Belum ada data untuk periode ini. Silakan klik tombol <strong>Import Data</strong> untuk mengunggah file.
                        </td>
                    </tr>
                    <tr
                        v-for="(row, idx) in itemsList"
                        :key="row.nrp"
                        class="hover:bg-blue-50/40 cursor-pointer transition group"
                        @click="openDetail(row)"
                    >
                        <td class="px-4 py-3 text-center text-slate-400 font-medium">{{ idx + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-extrabold text-blue-600 group-hover:underline flex items-center gap-1">
                                {{ row.nama || row.nrp }}
                                <ChevronRight class="h-3.5 w-3.5 opacity-0 group-hover:opacity-100 transition" />
                            </div>
                            <div class="text-[11px] font-mono text-slate-400">NRP: {{ row.nrp }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600 font-medium">{{ row.jabatan || '-' }}</td>
                        <td class="px-4 py-3 text-slate-700 font-bold">{{ row.kode_unit || '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ row.loader || '-' }}</td>
                        <td class="px-4 py-3 text-right text-slate-600">{{ row.avg_jarak ? Number(row.avg_jarak).toLocaleString('id-ID') : '-' }}</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-800">{{ Number(row.total_rit || 0).toLocaleString('id-ID') }}</td>
                        <td class="px-4 py-3 text-right font-black text-amber-600">
                            {{ Number(row.total_bcm || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}
                        </td>
                        <td class="px-4 py-3 text-right font-black text-slate-700">
                            {{ Number(row.total_ton || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="rounded-full px-2.5 py-1 text-[10px]" :class="achievementBadgeClass(row.achievement_pct)">
                                {{ row.achievement_pct ? `${Number(row.achievement_pct).toFixed(1)}%` : '-' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ================= TAB 2: MONTHLY REPORT ================= -->
        <div v-else-if="currentTab === 'monthly_report'" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[800px] text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center">No</th>
                        <th class="px-4 py-3.5">NRP / Operator</th>
                        <th class="px-4 py-3.5">Jabatan</th>
                        <th class="px-4 py-3.5 text-center">Bulan Ke</th>
                        <th class="px-4 py-3.5 text-center">Hari Aktif</th>
                        <th class="px-4 py-3.5 text-right">Total Ritase</th>
                        <th class="px-4 py-3.5 text-right text-amber-600">Total BCM (OB)</th>
                        <th class="px-4 py-3.5 text-right text-slate-700">Total TON (Coal)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="itemsList.length === 0">
                        <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                            Tidak ada data rekaman bulanan untuk periode ini.
                        </td>
                    </tr>
                    <tr v-for="(row, idx) in itemsList" :key="idx" class="hover:bg-slate-50/60">
                        <td class="px-4 py-3 text-center text-slate-400 font-medium">{{ idx + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-800">{{ row.nama || row.nrp }}</div>
                            <div class="text-[11px] font-mono text-slate-400">{{ row.nrp }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ row.jabatan || '-' }}</td>
                        <td class="px-4 py-3 text-center font-bold text-blue-600">Bulan {{ row.bulan }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ row.active_days }} hari</td>
                        <td class="px-4 py-3 text-right font-bold text-slate-800">{{ Number(row.total_rit || 0).toLocaleString('id-ID') }}</td>
                        <td class="px-4 py-3 text-right font-black text-amber-600">{{ Number(row.total_bcm || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}</td>
                        <td class="px-4 py-3 text-right font-black text-slate-700">{{ Number(row.total_ton || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- ================= TAB 3: QUARTAL REPORT ================= -->
        <div v-else-if="currentTab === 'quartal_report'" class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="w-full min-w-[950px] text-left text-xs">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center">Rank</th>
                        <th class="px-4 py-3.5">NRP / Nama Operator</th>
                        <th class="px-4 py-3.5">Kategori / Jabatan</th>
                        <th class="px-4 py-3.5 text-center">Grup Alat</th>
                        <th class="px-4 py-3.5 text-right text-blue-700 font-black">Total Poin MOR</th>
                        <th class="px-4 py-3.5 text-right">ATR Poin</th>
                        <th class="px-4 py-3.5 text-right">Timesheet Poin</th>
                        <th class="px-4 py-3.5 text-right">HM Poin</th>
                        <th class="px-4 py-3.5 text-right">PDTY Poin</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="itemsList.length === 0">
                        <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                            Belum ada ranking kuartal untuk periode ini. Pastikan data sudah diimport, lalu klik tombol <strong>Hitung Ulang</strong>.
                        </td>
                    </tr>
                    <tr
                        v-for="row in itemsList"
                        :key="row.id || row.nrp"
                        class="hover:bg-amber-50/30 transition"
                        :class="rowHighlightClass(row.ranking)"
                    >
                        <td class="px-4 py-3 text-center align-middle">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full text-xs font-black" :class="rankBadgeClass(row.ranking)">
                                <Medal v-if="row.ranking <= 3" class="h-4 w-4" />
                                <template v-else>{{ row.ranking }}</template>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-extrabold text-slate-800">{{ row.nama || row.employee?.name || row.nrp }}</div>
                            <div class="text-[11px] font-mono text-slate-400">NRP: {{ row.nrp || row.employee?.nrp }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-slate-700">{{ row.kategori || row.jabatan || '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ row.jabatan || '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span v-if="row.grup" class="rounded px-2 py-0.5 text-[10px] font-extrabold bg-slate-100 text-slate-700">
                                {{ row.grup }}
                            </span>
                            <span v-else class="text-slate-400">-</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="text-base font-black text-blue-700">
                                {{ row.total_poin !== undefined ? Number(row.total_poin).toFixed(2) : (row.bcm_total ? Number(row.bcm_total).toLocaleString('id-ID') : '-') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right text-slate-700 font-semibold">
                            <div>{{ row.atr_point !== undefined ? Number(row.atr_point).toFixed(2) : '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ row.atr_persen ? `${row.atr_persen}%` : '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-right text-slate-700 font-semibold">
                            <div>{{ row.ts_point !== undefined ? Number(row.ts_point).toFixed(2) : '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ row.ts_persen ? `${row.ts_persen}%` : '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-right text-slate-700 font-semibold">
                            <div>{{ row.hm_point !== undefined ? Number(row.hm_point).toFixed(2) : '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ row.hm_persen ? `${row.hm_persen}%` : '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-right text-slate-700 font-semibold">
                            <div>{{ row.pdty_point !== undefined ? Number(row.pdty_point).toFixed(2) : '-' }}</div>
                            <div class="text-[10px] text-slate-400">{{ row.pdty_persen ? `${Number(row.pdty_persen).toFixed(1)}%` : '' }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar if available -->
        <div v-if="reportData && reportData.links && reportData.links.length > 3" class="mt-4 flex items-center justify-between">
            <div class="text-xs text-slate-500">
                Menampilkan {{ reportData.from || 0 }} - {{ reportData.to || 0 }} dari {{ reportData.total || 0 }} data
            </div>
            <div class="flex gap-1">
                <button
                    v-for="(link, lIdx) in reportData.links"
                    :key="lIdx"
                    :disabled="!link.url || link.active"
                    class="rounded-lg px-3 py-1.5 text-xs font-semibold"
                    :class="link.active ? 'bg-blue-600 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 disabled:opacity-40'"
                    v-html="link.label"
                    @click="router.get(link.url, {}, { preserveScroll: true })"
                />
            </div>
        </div>

        <!-- Modals -->
        <MultiImportModal :open="importModalOpen" @close="importModalOpen = false" />
        <DprDailyDetailModal
            :open="detailModalOpen"
            :nrp="selectedOperator.nrp"
            :nama="selectedOperator.nama"
            :quartal="quartalInput"
            :tahun="yearInput"
            @close="detailModalOpen = false"
        />
    </AppLayout>
</template>
