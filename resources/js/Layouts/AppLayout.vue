<script setup>
import { ref } from 'vue';
import Sidebar from './Partials/Sidebar.vue';
import Topbar from './Partials/Topbar.vue';
import FlashMessage from '@/Components/ui/FlashMessage.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
});

const collapsed = ref(false);
const mobileOpen = ref(false);

const toggleSidebar = () => {
    if (window.matchMedia('(min-width: 1024px)').matches) {
        collapsed.value = !collapsed.value;
    } else {
        mobileOpen.value = !mobileOpen.value;
    }
};
</script>

<template>
    <div class="min-h-screen bg-[#F5F7FA]">
        <Sidebar
            :menu="menu"
            :user="user"
            :collapsed="collapsed"
            :mobile-open="mobileOpen"
            @close-mobile="mobileOpen = false"
        />

        <div :class="collapsed ? 'lg:ml-[75px]' : 'lg:ml-[280px]'" class="print:ml-0 transition-all duration-300">
            <Topbar :user="user" :notif-counts="notifCounts" :collapsed="collapsed" @toggle-sidebar="toggleSidebar" />

            <main class="p-4 pt-[84px] lg:p-6 lg:pt-[92px] print:p-0">
                <FlashMessage />
                <slot />
            </main>
        </div>
    </div>
</template>
