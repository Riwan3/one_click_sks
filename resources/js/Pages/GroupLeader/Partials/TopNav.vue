<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Bell, Download, MessageCircle } from '@lucide/vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
});

const showMenu = ref(false);
const menuRef = ref(null);

const closeIfOutside = (event) => {
    if (menuRef.value && !menuRef.value.contains(event.target)) showMenu.value = false;
};

onMounted(() => document.addEventListener('click', closeIfOutside));
onUnmounted(() => document.removeEventListener('click', closeIfOutside));

const handleLogout = () => router.post('/logout');
</script>

<template>
    <nav class="fixed inset-x-0 top-0 z-40 flex h-[68px] items-center justify-between bg-white px-4 shadow-sm">
        <a href="/portal" class="flex items-center gap-2 text-slate-800 no-underline">
            <img src="/images/title.png" alt="One Click" class="h-8 w-8">
            <span class="text-base font-extrabold uppercase tracking-wide">one <span class="text-blue-500">click</span></span>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" class="hidden items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-400 sm:flex">
                <Download class="h-3.5 w-3.5" /> Install
            </button>
            <button type="button" class="relative flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                <Bell class="h-4 w-4" />
                <span v-if="notifCounts.notification > 0" class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-blue-500 text-[0.6rem] font-black text-white">{{ notifCounts.notification }}</span>
            </button>
            <button type="button" class="relative flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                <MessageCircle class="h-4 w-4" />
                <span v-if="notifCounts.message > 0" class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-blue-500 text-[0.6rem] font-black text-white">{{ notifCounts.message }}</span>
            </button>

            <div ref="menuRef" class="relative">
                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-500 text-sm font-bold text-white" @click="showMenu = !showMenu">
                    {{ user.displayName.charAt(0) }}
                </button>
                <div v-if="showMenu" class="absolute right-0 mt-2 w-44 overflow-hidden rounded-xl border border-slate-100 bg-white py-2 shadow-xl">
                    <button type="button" class="w-full px-4 py-2 text-left text-sm font-bold text-red-600 hover:bg-red-50" @click="handleLogout">Keluar</button>
                </div>
            </div>
        </div>
    </nav>
</template>
