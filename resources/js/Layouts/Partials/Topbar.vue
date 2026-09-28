<script setup>
import { computed } from 'vue';
import { Menu, ArrowLeft } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import TopbarActions from '@/Components/shared/TopbarActions.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    collapsed: { type: Boolean, default: false },
    hideSidebar: { type: Boolean, default: false },
    backUrl: { type: String, default: '/portal/data' },
});

defineEmits(['toggle-sidebar']);

const isAdmin = computed(() => {
    const role = String(props.user?.role || '').toLowerCase();
    const jabatan = String(props.user?.jabatan || '').toLowerCase();
    return role === 'admin' || role === 'admin_produksi' || role === 'management' || jabatan.includes('admin');
});

const goBack = () => {
    if (window.history.length > 2) {
        window.history.back();
    } else {
        router.visit(props.backUrl || '/portal/data');
    }
};
</script>

<template>
    <nav
        :class="[
            'fixed inset-x-0 top-0 z-30 flex h-[68px] items-center gap-3 border-b border-white/45 bg-white/70 px-4 shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md transition-all duration-300 print:hidden lg:px-6',
            hideSidebar ? 'left-0' : (collapsed ? 'lg:left-[75px]' : 'lg:left-[280px]'),
        ]"
    >
        <!-- Button Kembali (Hanya muncul untuk selain admin) -->
        <button 
            v-if="!isAdmin"
            type="button" 
            @click="goBack"
            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-extrabold transition shrink-0 border border-slate-200/80 shadow-2xs"
            title="Kembali"
        >
            <ArrowLeft class="h-4 w-4 text-slate-600" />
            <span class="hidden xs:inline">Kembali</span>
        </button>

        <!-- Toggle Sidebar (Burger Bar) - Hanya muncul untuk role Admin -->
        <button 
            v-if="!hideSidebar && isAdmin"
            type="button" 
            class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 shrink-0" 
            @click="$emit('toggle-sidebar')"
            title="Menu Sidebar"
        >
            <Menu class="h-5 w-5" />
        </button>

        <h5 class="m-0 hidden text-xs md:text-sm font-bold uppercase tracking-wide text-slate-800 sm:block">
            Sistem <span class="text-slate-800">one</span><span class="text-blue-500">click</span>
        </h5>

        <div class="ml-auto flex items-center gap-2">
            <TopbarActions :user="user" :notif-counts="notifCounts" />
        </div>
    </nav>
</template>
