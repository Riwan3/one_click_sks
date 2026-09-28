<script setup>
import { Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import SidebarMenuItem from './SidebarMenuItem.vue';

defineProps({
    menu: { type: Array, required: true },
    user: { type: Object, required: true },
    collapsed: { type: Boolean, default: false },
    mobileOpen: { type: Boolean, default: false },
});

defineEmits(['close-mobile']);
</script>

<template>
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-50 flex w-[280px] flex-col border-r border-slate-200 bg-white shadow-[0_0_20px_rgba(0,0,0,0.03)] transition-all duration-300 print:hidden',
            collapsed ? 'lg:w-[75px]' : 'lg:w-[280px]',
            mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
        ]"
    >
        <a href="/portal" class="flex items-center justify-center gap-2.5 px-4 py-5 text-slate-800 no-underline">
            <img src="/images/title.png" alt="One Click" class="h-8 w-8 shrink-0">
            <span v-if="!collapsed" class="text-lg font-bold lowercase">one<span class="text-blue-500">click</span></span>
        </a>

        <div v-if="!collapsed" class="mx-4 mb-5 flex items-center gap-3 rounded-xl border border-slate-200 bg-[#F5F7FA] p-3">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full overflow-hidden bg-blue-500 text-lg font-extrabold text-white border border-slate-200 shadow-xs">
                <img v-if="user.foto" :src="user.foto" :alt="user.displayName" class="h-full w-full object-cover" />
                <span v-else>{{ user.displayName ? user.displayName.charAt(0).toUpperCase() : 'U' }}</span>
            </div>
            <div class="min-w-0">
                <div class="truncate text-sm font-bold text-slate-800">{{ user.displayName }}</div>
                <small class="text-xs text-slate-500">{{ user.jabatan }}</small>
            </div>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-4">
            <SidebarMenuItem v-for="(item, index) in menu" :key="index" :item="item" :collapsed="collapsed" />
        </nav>

        <div class="border-t border-slate-100 p-3">
            <Link href="/portal" class="flex items-center justify-center gap-2.5 rounded-lg border-2 border-blue-500 px-4 py-2.5 text-sm font-bold text-blue-500 no-underline hover:bg-blue-50 transition-colors">
                <ArrowLeft class="h-4 w-4 shrink-0" />
                <span v-if="!collapsed">Kembali ke Home</span>
            </Link>
        </div>
    </aside>

    <div v-if="mobileOpen" class="fixed inset-0 z-40 bg-black/40 lg:hidden" @click="$emit('close-mobile')" />
</template>
