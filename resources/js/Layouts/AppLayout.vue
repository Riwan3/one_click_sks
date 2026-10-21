<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Sidebar from './Partials/Sidebar.vue';
import Topbar from './Partials/Topbar.vue';
import FlashMessage from '@/Components/ui/FlashMessage.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { 
        type: Object, 
        default: () => ({ signature: 0, notification: 0, message: 0 }) 
    },
    menu: { type: Array, required: true },
    hideSidebar: { type: Boolean, default: false },
    backUrl: { type: String, default: '/portal/data' },
});

const isAdmin = computed(() => {
    const role = String(props.user?.role || '').toLowerCase();
    const jabatan = String(props.user?.jabatan || '').toLowerCase();
    return ['admin', 'superadmin', 'admin_produksi', 'management'].includes(role) || jabatan.includes('admin');
});

const page = usePage();
const isRosterViewer = computed(() => !!props.user?.canViewRosterV2 && page.url.startsWith('/one-roster'));

const effectiveHideSidebar = computed(() => props.hideSidebar || !(isAdmin.value || isRosterViewer.value));

const STORAGE_KEY = 'sidebar-collapsed';

const readStoredCollapsed = () => {
    try {
        return localStorage.getItem(STORAGE_KEY) === '1';
    } catch {
        return false;
    }
};

const collapsed = ref(readStoredCollapsed());
const mobileOpen = ref(false);

const toggleSidebar = () => {
    if (! window.matchMedia('(min-width: 1024px)').matches) {
        mobileOpen.value = !mobileOpen.value;
        return;
    }

    collapsed.value = !collapsed.value;
    try {
        localStorage.setItem(STORAGE_KEY, collapsed.value ? '1' : '0');
    } catch {
        // penyimpanan browser diblokir: pilihan hanya berlaku selama halaman terbuka
    }
};
</script>

<template>
    <div class="min-h-screen bg-[#F5F7FA]">
        <Sidebar
            v-if="!effectiveHideSidebar"
            :menu="menu"
            :user="user"
            :collapsed="collapsed"
            :mobile-open="mobileOpen"
            @close-mobile="mobileOpen = false"
        />

        <div :class="effectiveHideSidebar ? 'ml-0' : (collapsed ? 'lg:ml-[75px]' : 'lg:ml-[280px]')" class="print:ml-0 transition-all duration-300">
            <Topbar 
                :user="user" 
                :notif-counts="notifCounts" 
                :collapsed="collapsed" 
                :hide-sidebar="effectiveHideSidebar"
                :back-url="backUrl"
                @toggle-sidebar="toggleSidebar" 
            />

            <main class="p-4 pt-[84px] lg:p-6 lg:pt-[92px] print:p-0">
                <FlashMessage />
                <slot />
            </main>
        </div>
    </div>
</template>
