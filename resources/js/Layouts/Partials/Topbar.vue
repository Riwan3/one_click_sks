<script setup>
import { Menu } from '@lucide/vue';
import TopbarActions from '@/Components/shared/TopbarActions.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    collapsed: { type: Boolean, default: false },
});

defineEmits(['toggle-sidebar']);
</script>

<template>
    <nav
        :class="[
            'fixed inset-x-0 top-0 z-30 flex h-[68px] items-center gap-4 border-b border-white/45 bg-white/70 px-4 shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md transition-all duration-300 print:hidden lg:px-6',
            collapsed ? 'lg:left-[75px]' : 'lg:left-[280px]',
        ]"
    >
        <button type="button" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100" @click="$emit('toggle-sidebar')">
            <Menu class="h-5 w-5" />
        </button>

        <h5 class="m-0 hidden text-sm font-bold uppercase tracking-wide text-slate-800 md:block">
            Sistem <span class="text-slate-800">one</span><span class="text-blue-500">click</span>
        </h5>

        <div class="ml-auto">
            <TopbarActions :user="user" :notif-counts="notifCounts" />
        </div>
    </nav>
</template>
