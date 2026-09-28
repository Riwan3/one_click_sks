<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { 
    ChevronRight, 
    FileText, 
    Grid2x2, 
    Search, 
    CheckCircle2, 
    ShieldAlert, 
    AlertTriangle, 
    Info, 
    BookOpen, 
    X,
    Eye,
    Calendar,
    Award
} from 'lucide-vue-next';
import TopNav from './Partials/TopNav.vue';
import BottomNav from './Partials/BottomNav.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    category: { type: String, default: 'Aktivitas Operasional' },
    categories: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    isOperator: { type: Boolean, default: false },
    navItems: { type: Array, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
});

const search = ref('');
const selectedCategory = ref(null);
const activeModalDoc = ref(null);

const filteredActivities = computed(() => {
    return props.activities.filter(item => {
        const matchesCategory = !selectedCategory.value || item.kategori === selectedCategory.value;
        const matchesSearch = !search.value || 
            (item.nama && item.nama.toLowerCase().includes(search.value.toLowerCase())) || 
            (item.kategori && item.kategori.toLowerCase().includes(search.value.toLowerCase())) ||
            (item.keterangan && item.keterangan.toLowerCase().includes(search.value.toLowerCase()));
        return matchesCategory && matchesSearch;
    });
});

const selectCat = (cat) => {
    if (selectedCategory.value === cat) {
        selectedCategory.value = null;
    } else {
        selectedCategory.value = cat;
    }
};

const openDocModal = (doc) => {
    activeModalDoc.value = doc;
};

const closeDocModal = () => {
    activeModalDoc.value = null;
};

const getRiskBadge = (risk) => {
    switch (risk) {
        case 'critical':
            return { label: 'CRITICAL', class: 'bg-rose-100 text-rose-700 border-rose-200' };
        case 'high':
            return { label: 'HIGH RISK', class: 'bg-amber-100 text-amber-700 border-amber-200' };
        case 'low':
            return { label: 'LOW RISK', class: 'bg-emerald-100 text-emerald-700 border-emerald-200' };
        default:
            return { label: 'STANDARD', class: 'bg-slate-100 text-slate-700 border-slate-200' };
    }
};
</script>

<template>
    <Head title="Aktivity - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-24 pt-[84px]">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto flex max-w-md md:max-w-4xl lg:max-w-5xl flex-col gap-4 px-4">
            <!-- Header Banner -->
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <FileText class="h-5 w-5" :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'" />
                    <h1 class="m-0 text-xl font-extrabold text-slate-800">Aktivity</h1>
                    <span 
                        class="rounded-full px-2.5 py-1 text-[0.65rem] font-extrabold uppercase text-white shadow-xs"
                        :class="isOperator ? 'bg-[#7B112C]' : 'bg-blue-600'"
                    >
                        {{ selectedCategory || category }}
                    </span>
                </div>
                <p class="m-0 mt-1 text-xs text-slate-500">
                    Daftar dokumen SOP, instruksi kerja &amp; aktivitas operasional lapangan
                </p>
            </div>

            <!-- Search Bar -->
            <div class="rounded-2xl bg-white p-3.5 shadow-xs border border-slate-200/80">
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama atau kategori aktivitas..."
                        :class="[
                            'w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm outline-none transition',
                            isOperator ? 'focus:border-[#7B112C] focus:bg-white' : 'focus:border-blue-500 focus:bg-white'
                        ]"
                    >
                </div>
            </div>

            <!-- Category Grid Filter -->
            <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wide text-slate-500">
                <span class="flex items-center gap-2">
                    <Grid2x2 class="h-3.5 w-3.5" /> Pilih Kategori Aktivitas:
                </span>
                <button 
                    v-if="selectedCategory" 
                    @click="selectedCategory = null" 
                    :class="[
                        'font-bold hover:underline cursor-pointer',
                        isOperator ? 'text-[#7B112C]' : 'text-blue-600'
                    ]"
                >
                    Lihat Semua
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                <button
                    v-for="item in categories"
                    :key="item"
                    type="button"
                    @click="selectCat(item)"
                    :class="[
                        'flex items-center justify-between rounded-2xl px-4 py-3.5 text-left text-xs font-bold uppercase shadow-xs border transition cursor-pointer',
                        selectedCategory === item 
                            ? (isOperator ? 'bg-[#7B112C] text-white border-[#7B112C] shadow-sm shadow-[#7B112C]/20' : 'bg-blue-600 text-white border-blue-600 shadow-sm') 
                            : (isOperator ? 'bg-white text-slate-800 border-slate-200/80 hover:bg-rose-50/40 hover:border-rose-300' : 'bg-white text-slate-800 border-slate-200/80 hover:bg-blue-50/50 hover:border-blue-300')
                    ]"
                >
                    <span class="truncate pr-2">{{ item }}</span>
                    <ChevronRight :class="['h-4 w-4 shrink-0', selectedCategory === item ? 'text-white' : 'text-slate-400']" />
                </button>
            </div>

            <!-- Activity List from Database -->
            <div class="mt-2 space-y-2.5">
                <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wide text-slate-500">
                    <span>Daftar Aktivitas &amp; Dokumen ({{ filteredActivities.length }})</span>
                    <span v-if="selectedCategory" class="text-[0.7rem] font-medium lowercase text-slate-400">
                        filter: {{ selectedCategory }}
                    </span>
                </div>

                <div 
                    v-for="act in filteredActivities" 
                    :key="act.id" 
                    @click="openDocModal(act)"
                    class="group rounded-2xl bg-white p-4 shadow-xs border border-slate-200/80 flex items-start gap-3 hover:border-slate-300 hover:shadow-sm transition cursor-pointer"
                >
                    <div 
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl transition"
                        :class="isOperator ? 'bg-rose-50 text-[#7B112C] group-hover:bg-[#7B112C] group-hover:text-white' : 'bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white'"
                    >
                        <BookOpen class="h-5 w-5" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap mb-1">
                            <span 
                                class="text-[0.68rem] font-extrabold uppercase tracking-wide"
                                :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'"
                            >
                                {{ act.kategori || 'General' }}
                            </span>
                            <span v-if="act.revision" class="rounded bg-slate-100 px-1.5 py-0.5 text-[0.62rem] font-bold text-slate-600">
                                {{ act.revision }}
                            </span>
                            <span 
                                v-if="act.risk_level" 
                                class="rounded px-1.5 py-0.5 text-[0.62rem] font-extrabold border"
                                :class="getRiskBadge(act.risk_level).class"
                            >
                                {{ getRiskBadge(act.risk_level).label }}
                            </span>
                        </div>

                        <h4 class="m-0 text-sm font-bold text-slate-800 leading-snug group-hover:text-slate-900">
                            {{ act.nama }}
                        </h4>

                        <p v-if="act.keterangan" class="m-0 mt-1 line-clamp-2 text-xs text-slate-500">
                            {{ act.keterangan }}
                        </p>
                    </div>

                    <div class="shrink-0 self-center">
                        <div 
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 group-hover:bg-slate-100 group-hover:text-slate-600 transition"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="filteredActivities.length === 0" class="rounded-2xl bg-white p-8 text-center text-slate-400 text-xs border border-slate-200/80">
                    <Info class="mx-auto h-8 w-8 text-slate-300 mb-2" />
                    Tidak ada aktivitas atau dokumen SOP yang sesuai dengan filter/pencarian.
                </div>
            </div>
        </div>

        <!-- Detail Modal Dokumen / SOP -->
        <div 
            v-if="activeModalDoc" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs"
            @click.self="closeDocModal"
        >
            <div class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 animate-in fade-in zoom-in-95 duration-150">
                <!-- Modal Header -->
                <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <div 
                            class="flex h-11 w-11 items-center justify-center rounded-2xl"
                            :class="isOperator ? 'bg-rose-50 text-[#7B112C]' : 'bg-blue-50 text-blue-600'"
                        >
                            <BookOpen class="h-6 w-6" />
                        </div>
                        <div>
                            <span 
                                class="text-[0.7rem] font-extrabold uppercase tracking-wide"
                                :class="isOperator ? 'text-[#7B112C]' : 'text-blue-600'"
                            >
                                {{ activeModalDoc.kategori }}
                            </span>
                            <h3 class="m-0 text-base font-extrabold text-slate-800 leading-snug">
                                {{ activeModalDoc.nama }}
                            </h3>
                        </div>
                    </div>
                    <button 
                        @click="closeDocModal"
                        class="rounded-full p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="py-4 space-y-3.5">
                    <!-- Badges Row -->
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 font-bold text-slate-700">
                            <Award class="h-3.5 w-3.5 text-slate-500" />
                            {{ activeModalDoc.revision || 'Standar Operasional' }}
                        </span>
                        <span 
                            v-if="activeModalDoc.risk_level"
                            class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 font-extrabold border"
                            :class="getRiskBadge(activeModalDoc.risk_level).class"
                        >
                            <ShieldAlert class="h-3.5 w-3.5" />
                            {{ getRiskBadge(activeModalDoc.risk_level).label }}
                        </span>
                        <span v-if="activeModalDoc.effective_date" class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2.5 py-1 text-slate-600 font-medium">
                            <Calendar class="h-3.5 w-3.5 text-slate-400" />
                            Efektif: {{ activeModalDoc.effective_date }}
                        </span>
                    </div>

                    <!-- Description Box -->
                    <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200/80">
                        <div class="text-[0.7rem] font-extrabold uppercase tracking-wide text-slate-500 mb-1.5">
                            Ringkasan Instruksi &amp; Prosedur Kerja:
                        </div>
                        <p class="m-0 text-xs text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ activeModalDoc.keterangan || 'Tidak ada keterangan tambahan pada dokumen ini.' }}
                        </p>
                    </div>

                    <!-- Operator Compliance Notice -->
                    <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-3 text-xs text-amber-800 flex items-start gap-2.5">
                        <AlertTriangle class="h-4 w-4 shrink-0 text-amber-600 mt-0.5" />
                        <div>
                            <span class="font-bold">Kepatuhan K3 &amp; SOP Lapangan:</span> Seluruh operator wajib mematuhi standar instruksi kerja ini demi keselamatan bersama dan pencapaian target zero incident.
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="border-t border-slate-100 pt-4 flex gap-2">
                    <button 
                        type="button"
                        @click="closeDocModal"
                        class="w-full rounded-xl py-3 text-xs font-bold text-white transition shadow-sm cursor-pointer"
                        :class="isOperator ? 'bg-[#7B112C] hover:bg-[#630e23]' : 'bg-blue-600 hover:bg-blue-700'"
                    >
                        Saya Mengerti &amp; Siap Menerapkan
                    </button>
                </div>
            </div>
        </div>

        <div class="lg:hidden">
            <BottomNav active="aktivity" :items="navItems" :accent-class="accentClass" />
        </div>
    </div>
</template>
