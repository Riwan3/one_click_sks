<script setup>
import { ref, computed, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ManpowerBoardPreview from './Partials/ManpowerBoardPreview.vue';
import FleetBoardPreview from './Partials/FleetBoardPreview.vue';
import { Sliders, Image as ImageIcon, Users, Truck, RefreshCw, X, ZoomIn } from '@lucide/vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    filters: { type: Object, required: true },
    settingBoards: { type: Object, default: null }, // gambar upload lama: tidak lagi ditampilkan, prop dipertahankan agar tidak jatuh ke atribut DOM
    manpowerPreview: { type: Object, default: null },
    fleetPreview: { type: Object, default: null },
});

const filterSite = props.filters?.site || 'ADW';
const filterDate = ref(props.filters?.date || new Date().toISOString().split('T')[0]);
const filterShift = ref(props.filters?.shift || 'D');

const applyFilter = () => {
    router.get('/one-setting/setting-board', {
        site: filterSite,
        date: filterDate.value,
        shift: filterShift.value,
    }, { preserveState: true, replace: true });
};

const reload = () => {
    router.reload({ only: ['manpowerPreview', 'fleetPreview'] });
};

const boardConfig = {
    manpower: {
        label: 'Papan Setting Manpower Departement Produksi',
        icon: Users,
        preview: ManpowerBoardPreview,
        emptyTitle: 'Belum Ada Papan Manpower',
        emptyName: 'manpower',
        createPath: '/one-setting/manpower',
        createLabel: 'Buat di Setting Manpower',
    },
    fleet: {
        label: 'Papan Setting Fleet Dept Produksi',
        icon: Truck,
        preview: FleetBoardPreview,
        emptyTitle: 'Belum Ada Papan Fleet',
        emptyName: 'fleet',
        createPath: '/one-setting/fleet',
        createLabel: 'Buat di Setting Fleet',
    },
};

const previews = computed(() => ({ manpower: props.manpowerPreview, fleet: props.fleetPreview }));
const createHref = (cfg) => `${cfg.createPath}?date=${filterDate.value}&shift=${filterShift.value}`;

const shiftLabel = computed(() => (filterShift.value === 'N' ? 'Shift 2 Malam' : 'Shift 1 Siang'));

// HD Zoom modal
const zoomModal = reactive({ open: false, type: null, title: '', scale: 1, x: 0, y: 0 });
let pinchStartDistance = 0;
let pinchStartScale = 1;
let isPanning = false;
let panStart = { x: 0, y: 0 };

const SHEET_VIEWPORT_RATIO = 0.82;
const sheetMaxHeight = computed(() => Math.round(window.innerHeight * SHEET_VIEWPORT_RATIO));

const openSheetZoom = (type) => {
    zoomModal.open = true;
    zoomModal.type = type;
    zoomModal.title = boardConfig[type].label;
    zoomModal.scale = 1;
    zoomModal.x = 0;
    zoomModal.y = 0;
};

const closeZoom = () => {
    zoomModal.open = false;
};

const clampScale = (s) => Math.min(6, Math.max(1, s));

const onWheelZoom = (event) => {
    event.preventDefault();
    const delta = event.deltaY > 0 ? -0.2 : 0.2;
    zoomModal.scale = clampScale(zoomModal.scale + delta);
    if (zoomModal.scale === 1) { zoomModal.x = 0; zoomModal.y = 0; }
};

const distanceBetween = (touches) => {
    const [a, b] = touches;
    return Math.hypot(a.clientX - b.clientX, a.clientY - b.clientY);
};

const onTouchStart = (event) => {
    if (event.touches.length === 2) {
        pinchStartDistance = distanceBetween(event.touches);
        pinchStartScale = zoomModal.scale;
    } else if (event.touches.length === 1 && zoomModal.scale > 1) {
        isPanning = true;
        panStart = { x: event.touches[0].clientX - zoomModal.x, y: event.touches[0].clientY - zoomModal.y };
    }
};

const onTouchMove = (event) => {
    if (event.touches.length === 2 && pinchStartDistance) {
        event.preventDefault();
        const ratio = distanceBetween(event.touches) / pinchStartDistance;
        zoomModal.scale = clampScale(pinchStartScale * ratio);
    } else if (event.touches.length === 1 && isPanning) {
        event.preventDefault();
        zoomModal.x = event.touches[0].clientX - panStart.x;
        zoomModal.y = event.touches[0].clientY - panStart.y;
    }
};

const onTouchEnd = () => {
    pinchStartDistance = 0;
    isPanning = false;
};

// Drag-pan pakai mouse (desktop) — padanan touch pan di atas untuk ponsel/tablet.
const onMouseDown = (event) => {
    if (zoomModal.scale <= 1) return;
    isPanning = true;
    panStart = { x: event.clientX - zoomModal.x, y: event.clientY - zoomModal.y };
};

const onMouseMove = (event) => {
    if (!isPanning) return;
    zoomModal.x = event.clientX - panStart.x;
    zoomModal.y = event.clientY - panStart.y;
};

const onMouseUp = () => {
    isPanning = false;
};

const onDoubleClickZoom = () => {
    zoomModal.scale = zoomModal.scale > 1 ? 1 : 3;
    zoomModal.x = 0;
    zoomModal.y = 0;
};
</script>

<template>
    <Head title="Setting Board - One-Setting" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 mb-1">
                        <span>Operation Settings</span>
                        <span>/</span>
                        <span class="text-slate-500">Setting Board</span>
                    </div>
                    <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
                        <Sliders class="w-6 h-6 text-sky-500" />
                        Papan Setting Manpower &amp; Fleet
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">Tampilan visual resolusi tinggi (HD) dengan navigasi multi-site &amp; cubit zoom untuk Group Leader</p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 text-xs font-bold rounded-xl bg-slate-50 text-slate-600 border border-slate-200">{{ filterDate }} ({{ shiftLabel }})</span>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Tanggal</label>
                    <input type="date" v-model="filterDate" @change="applyFilter" class="px-3 py-2 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Shift</label>
                    <select v-model="filterShift" @change="applyFilter" class="px-3 py-2 text-xs font-medium border border-slate-300 rounded-xl bg-slate-50/50">
                        <option value="D">Shift 1 (Siang / Day)</option>
                        <option value="N">Shift 2 (Malam / Night)</option>
                    </select>
                </div>

                <button type="button" @click="reload" class="ml-auto px-3.5 py-2 text-xs font-bold rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition flex items-center gap-1.5">
                    <RefreshCw class="w-3.5 h-3.5" /> Muat Ulang
                </button>
            </div>

            <!-- Petunjuk -->
            <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-800 text-xs flex items-start gap-2">
                <ZoomIn class="w-4 h-4 shrink-0 mt-0.5" />
                <p class="m-0">
                    <span class="font-bold">Petunjuk:</span> Klik atau ketuk salah satu papan setting di bawah untuk membuka mode HD Zoom.
                    Di ponsel/tablet, gunakan <span class="font-bold">cubit 2 jari (pinch-to-zoom)</span> untuk memperbesar hingga 600% tanpa pecah.
                </p>
            </div>

            <!-- Boards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div v-for="(cfg, type) in boardConfig" :key="type" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            {{ filterDate }} &bull; {{ shiftLabel.toUpperCase() }}
                        </p>
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                            <component :is="cfg.icon" class="w-4 h-4 text-sky-500" />
                            {{ cfg.label }}
                        </h3>
                    </div>

                    <div class="relative min-h-[420px] flex items-center justify-center bg-gradient-to-br from-slate-900 via-slate-950 to-black overflow-hidden">
                        <button v-if="previews[type]" type="button" class="block w-full cursor-zoom-in" @click="openSheetZoom(type)">
                            <component :is="cfg.preview" :preview="previews[type]" />
                        </button>

                        <div v-else class="text-center space-y-3 py-10">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-800 text-slate-500 flex items-center justify-center">
                                <ImageIcon class="w-7 h-7" />
                            </div>
                            <h4 class="text-sm font-bold text-slate-300">{{ cfg.emptyTitle }}</h4>
                            <p class="text-xs text-slate-500 max-w-xs mx-auto">
                                Setting {{ cfg.emptyName }} tanggal <strong class="text-slate-300">{{ filterDate }}</strong> Shift <strong class="text-slate-300">{{ filterShift }}</strong> belum dibuat.
                            </p>
                            <Link
                                :href="createHref(cfg)"
                                class="px-4 py-2 text-xs font-bold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition inline-flex items-center gap-1.5"
                            >
                                {{ cfg.createLabel }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- HD Zoom Modal -->
        <div
            v-if="zoomModal.open"
            class="fixed inset-0 z-50 flex flex-col select-none overflow-hidden bg-black"
            @click.self="closeZoom"
        >
            <div class="flex items-center justify-between p-4 text-white">
                <h3 class="text-sm font-bold">{{ zoomModal.title }}</h3>
                <button type="button" class="p-2 rounded-xl hover:bg-white/10 transition" @click="closeZoom">
                    <X class="w-5 h-5" />
                </button>
            </div>
            <div
                class="flex-1 overflow-hidden flex items-center justify-center touch-none"
                :class="zoomModal.scale > 1 ? 'cursor-grab active:cursor-grabbing' : ''"
                @wheel="onWheelZoom"
                @touchstart="onTouchStart"
                @touchmove="onTouchMove"
                @touchend="onTouchEnd"
                @mousedown="onMouseDown"
                @mousemove="onMouseMove"
                @mouseup="onMouseUp"
                @mouseleave="onMouseUp"
                @dblclick="onDoubleClickZoom"
            >
                <div
                    v-if="zoomModal.type"
                    class="w-[95vw] rounded-lg shadow-2xl transition-transform duration-100 ease-out"
                    :style="{ transform: `translate(${zoomModal.x}px, ${zoomModal.y}px) scale(${zoomModal.scale})` }"
                >
                    <component :is="boardConfig[zoomModal.type].preview" :preview="previews[zoomModal.type]" :contain-height="sheetMaxHeight" />
                </div>
            </div>
            <p class="text-center text-[11px] text-slate-300 pb-3">Scroll / cubit 2 jari untuk zoom &middot; Geser (drag) untuk lihat bagian lain &middot; Klik dua kali untuk reset &middot; Zoom: {{ Math.round(zoomModal.scale * 100) }}%</p>
        </div>
    </AppLayout>
</template>
