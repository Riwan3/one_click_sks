<script setup>
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { ChevronRight, FileText, Grid2x2, Search, CheckCircle2 } from 'lucide-vue-next';
import TopNav from './Partials/TopNav.vue';
import BottomNav from './Partials/BottomNav.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    category: { type: String, default: 'Aktivitas Operasional' },
    categories: { type: Array, default: () => [] },
    activities: { type: Array, default: () => [] },
    navItems: { type: Array, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
});

const search = ref('');
const selectedCategory = ref(null);

const filteredActivities = computed(() => {
    return props.activities.filter(item => {
        const matchesCategory = !selectedCategory.value || item.kategori === selectedCategory.value;
        const matchesSearch = !search.value || 
            (item.nama && item.nama.toLowerCase().includes(search.value.toLowerCase())) || 
            (item.kategori && item.kategori.toLowerCase().includes(search.value.toLowerCase()));
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
</script>

<template>
    <Head title="Aktivity - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-24 pt-[84px]">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto flex max-w-md md:max-w-4xl lg:max-w-5xl flex-col gap-4 px-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <FileText class="h-5 w-5 text-slate-700" />
                    <h1 class="m-0 text-xl font-extrabold text-slate-800">Aktivity</h1>
                    <span class="rounded-full bg-blue-500 px-2.5 py-1 text-[0.65rem] font-extrabold uppercase text-white">
                        {{ selectedCategory || category }}
                    </span>
                </div>
                <p class="m-0 mt-1 text-xs text-slate-500">Daftar aktivitas &amp; dokumen prosedur dari database</p>
            </div>

            <!-- Search Bar -->
            <div class="rounded-2xl bg-white p-4 shadow-xs border border-slate-200/80">
                <div class="relative">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari nama atau kategori aktivitas..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                    >
                </div>
            </div>

            <!-- Category Grid Filter -->
            <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wide text-slate-500">
                <span class="flex items-center gap-2">
                    <Grid2x2 class="h-3.5 w-3.5" /> Pilih Kategori Aktivitas:
                </span>
                <button v-if="selectedCategory" @click="selectedCategory = null" class="text-blue-600 hover:underline">
                    Lihat Semua
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <button
                    v-for="item in categories"
                    :key="item"
                    type="button"
                    @click="selectCat(item)"
                    :class="[
                        'flex items-center justify-between rounded-2xl px-5 py-4 text-left text-sm font-bold uppercase shadow-xs border transition cursor-pointer',
                        selectedCategory === item ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-800 border-slate-200/80 hover:bg-blue-50/50 hover:border-blue-300'
                    ]"
                >
                    <span>{{ item }}</span>
                    <ChevronRight :class="['h-4 w-4 shrink-0', selectedCategory === item ? 'text-white' : 'text-slate-400']" />
                </button>
            </div>

            <!-- Activity List from Database -->
            <div class="mt-2 space-y-2">
                <div class="text-xs font-extrabold uppercase tracking-wide text-slate-500">
                    Daftar Aktivitas ({{ filteredActivities.length }})
                </div>

                <div 
                    v-for="act in filteredActivities" 
                    :key="act.id" 
                    class="rounded-2xl bg-white p-4 shadow-xs border border-slate-200/80 flex items-start gap-3"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-extrabold text-blue-600 uppercase tracking-wide mb-0.5">
                            {{ act.kategori || 'General' }}
                        </div>
                        <h4 class="m-0 text-sm font-bold text-slate-800">{{ act.nama }}</h4>
                        <p v-if="act.keterangan" class="m-0 mt-1 text-xs text-slate-500">{{ act.keterangan }}</p>
                    </div>
                </div>

                <div v-if="filteredActivities.length === 0" class="rounded-2xl bg-white p-8 text-center text-slate-400 text-xs border border-slate-200/80">
                    Tidak ada aktivitas yang sesuai dengan filter/pencarian
                </div>
            </div>
        </div>

        <div class="lg:hidden">
            <BottomNav active="aktivity" :items="navItems" :accent-class="accentClass" />
        </div>
    </div>
</template>
