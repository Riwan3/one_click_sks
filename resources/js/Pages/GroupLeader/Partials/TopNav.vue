<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Bell, Download, MessageCircle } from 'lucide-vue-next';
import NotificationModal from '@/Components/modals/NotificationModal.vue';
import PesanModal from '@/Components/modals/PesanModal.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, default: () => ({ signature: 0, notification: 0, message: 0 }) },
});

const showMenu = ref(false);
const menuRef = ref(null);
const showNotifModal = ref(false);
const showPesanModal = ref(false);
const deferredPrompt = ref(null);
const unreadMessageCount = ref(props.notifCounts?.message || 0);

watch(() => props.notifCounts?.message, (newVal) => {
    if (typeof newVal === 'number') unreadMessageCount.value = newVal;
});

const checkUnreadMessages = async () => {
    try {
        const res = await fetch('/internal-messages/unread-count');
        const data = await res.json();
        if (data && typeof data.unread_count === 'number') {
            unreadMessageCount.value = data.unread_count;
        }
    } catch (e) {
        // silent
    }
};

let unreadInterval = null;

const closeIfOutside = (event) => {
    if (menuRef.value && !menuRef.value.contains(event.target)) showMenu.value = false;
};

const handleBeforeInstallPrompt = (e) => {
    e.preventDefault();
    deferredPrompt.value = e;
};

onMounted(() => {
    document.addEventListener('click', closeIfOutside);
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    checkUnreadMessages();
    unreadInterval = setInterval(checkUnreadMessages, 10000);
});

onUnmounted(() => {
    document.removeEventListener('click', closeIfOutside);
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    if (unreadInterval) clearInterval(unreadInterval);
});

const promptInstall = () => {
    if (deferredPrompt.value) {
        deferredPrompt.value.prompt();
        deferredPrompt.value.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                deferredPrompt.value = null;
            }
        });
    } else {
        alert('Untuk menginstall aplikasi OneClick, gunakan menu "Add to Home Screen" atau "Install Application" pada pilihan browser Anda.');
    }
};

const handleLogout = () => router.post('/logout');
</script>

<template>
    <nav class="fixed inset-x-0 top-0 z-40 flex h-[68px] items-center justify-between bg-white px-4 shadow-sm">
        <a href="/portal" class="flex items-center gap-2 text-slate-800 no-underline">
            <img src="/images/title.png" alt="One Click" class="h-8 w-8">
            <span class="text-base font-extrabold uppercase tracking-wide">one <span class="text-blue-500">click</span></span>
        </a>

        <div class="flex items-center gap-2">
            <button 
                type="button" 
                @click="promptInstall"
                class="flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-200 transition cursor-pointer"
            >
                <Download class="h-3.5 w-3.5" /> Install
            </button>

            <button 
                type="button" 
                @click="showNotifModal = true"
                class="relative flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition cursor-pointer"
                title="Notifikasi Sistem"
            >
                <Bell class="h-4 w-4" />
                <span v-if="notifCounts && notifCounts.notification > 0" class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-blue-500 text-[0.6rem] font-black text-white">
                    {{ notifCounts.notification }}
                </span>
            </button>

            <button 
                type="button" 
                @click="showPesanModal = true"
                class="relative flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition cursor-pointer"
                title="Pesan Internal"
            >
                <MessageCircle class="h-4 w-4" />
                <span v-if="unreadMessageCount > 0" class="absolute -right-1 -top-1 flex h-4 w-4 items-center justify-center rounded-full bg-blue-500 text-[0.6rem] font-black text-white">
                    {{ unreadMessageCount }}
                </span>
            </button>

            <div ref="menuRef" class="relative">
                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-500 text-sm font-bold text-white cursor-pointer" @click="showMenu = !showMenu">
                    {{ user.displayName.charAt(0) }}
                </button>
                <div v-if="showMenu" class="absolute right-0 mt-2 w-44 overflow-hidden rounded-xl border border-slate-100 bg-white py-2 shadow-xl z-50">
                    <a href="/profil" class="block w-full px-4 py-2 text-left text-sm font-bold text-slate-700 hover:bg-slate-50 no-underline">Profil Saya</a>
                    <button type="button" class="w-full px-4 py-2 text-left text-sm font-bold text-red-600 hover:bg-red-50 cursor-pointer" @click="handleLogout">Keluar</button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Modals -->
    <NotificationModal :show="showNotifModal" @close="showNotifModal = false" />
    <PesanModal :show="showPesanModal" @close="showPesanModal = false" />
</template>
