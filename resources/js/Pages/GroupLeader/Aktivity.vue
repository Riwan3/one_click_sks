<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { ChevronRight, FileText, Grid2x2, Search } from '@lucide/vue';
import TopNav from './Partials/TopNav.vue';
import BottomNav from './Partials/BottomNav.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    category: { type: String, required: true },
    categories: { type: Array, required: true },
    navItems: { type: Array, required: true },
    accentClass: { type: String, default: 'text-blue-500' },
});

const search = ref('');
</script>

<template>
    <Head title="Aktivity - OneClick" />

    <div class="min-h-screen bg-slate-100 pb-24 pt-[84px]">
        <TopNav :user="user" :notif-counts="notifCounts" />

        <div class="mx-auto flex max-w-md flex-col gap-4 px-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <FileText class="h-5 w-5 text-slate-700" />
                    <h1 class="m-0 text-xl font-extrabold text-slate-800">Aktivity</h1>
                    <span class="rounded-full bg-blue-500 px-2.5 py-1 text-[0.65rem] font-extrabold uppercase text-white">{{ category.replace('_', ' ') }}</span>
                </div>
                <p class="m-0 mt-1 text-xs text-slate-500">Daftar dokumen aktivity {{ category }} yang tersedia.</p>
            </div>

            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <div class="relative mb-3">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Cari judul atau kode dokumen..."
                        class="w-full rounded-xl border border-slate-100 bg-slate-50 py-3 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                    >
                </div>
                <button type="button" class="w-full rounded-xl bg-blue-500 py-3 text-sm font-extrabold uppercase text-white hover:bg-blue-600">Filter</button>
            </div>

            <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wide text-slate-500">
                <Grid2x2 class="h-3.5 w-3.5" /> Pilih Kategori Aktivitas:
            </div>

            <div class="flex flex-col gap-3">
                <button
                    v-for="item in categories"
                    :key="item"
                    type="button"
                    class="flex items-center justify-between rounded-2xl bg-white px-5 py-4 text-left text-sm font-bold uppercase text-slate-800 shadow-sm"
                >
                    {{ item }}
                    <ChevronRight class="h-4 w-4 shrink-0 text-slate-300" />
                </button>
            </div>
        </div>

        <BottomNav active="aktivity" :items="navItems" :accent-class="accentClass" />
    </div>
</template>
