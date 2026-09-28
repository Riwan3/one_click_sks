<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    Bell, 
    Download, 
    MessageCircle, 
    Share, 
    SquarePlus, 
    CheckCircle2, 
    MoreVertical, 
    X 
} from 'lucide-vue-next';
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

// PWA Install State & Modals
const deferredPrompt = ref(null);
const isInstalled = ref(false);
const isIOS = ref(false);
const showIosModal = ref(false);
const showBrowserModal = ref(false);

const unreadMessageCount = ref(props.notifCounts?.message || 0);

watch(() => props.notifCounts?.message, (newVal) => {
    if (typeof newVal === 'number') unreadMessageCount.value = newVal;
});

const checkIsStandalone = () => {
    if (typeof window === 'undefined') return false;
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true ||
        localStorage.getItem('pwa-installed') === '1'
    );
};

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

const handleAppInstalled = () => {
    localStorage.setItem('pwa-installed', '1');
    isInstalled.value = true;
    deferredPrompt.value = null;
};

onMounted(() => {
    isIOS.value = typeof navigator !== 'undefined' && /iphone|ipad|ipod/i.test(navigator.userAgent) && !window.MSStream;
    isInstalled.value = checkIsStandalone();

    document.addEventListener('click', closeIfOutside);
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.addEventListener('appinstalled', handleAppInstalled);

    checkUnreadMessages();
    unreadInterval = setInterval(checkUnreadMessages, 10000);
});

onUnmounted(() => {
    document.removeEventListener('click', closeIfOutside);
    window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    window.removeEventListener('appinstalled', handleAppInstalled);
    if (unreadInterval) clearInterval(unreadInterval);
});

const promptInstall = () => {
    if (isIOS.value) {
        showIosModal.value = true;
        return;
    }

    if (deferredPrompt.value) {
        deferredPrompt.value.prompt();
        deferredPrompt.value.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                localStorage.setItem('pwa-installed', '1');
                isInstalled.value = true;
            }
            deferredPrompt.value = null;
        });
    } else {
        showBrowserModal.value = true;
    }
};

const handleLogout = () => router.post('/logout');
</script>

<template>
    <nav class="fixed inset-x-0 top-0 z-40 flex h-[68px] items-center justify-between bg-white px-4 shadow-sm">
        <a href="/portal" class="flex items-center gap-2 text-slate-800 no-underline">
            <img src="/images/title.png" alt="One Click" class="h-8 w-8 object-contain">
            <span class="text-base font-extrabold uppercase tracking-wide">one <span class="text-blue-500">click</span></span>
        </a>

        <div class="flex items-center gap-2">
            <!-- PWA Install Button (Hidden if already in standalone app) -->
            <button 
                v-if="!isInstalled"
                type="button" 
                @click="promptInstall"
                class="flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700 hover:bg-slate-200 transition cursor-pointer shadow-2xs"
            >
                <Download class="h-3.5 w-3.5 text-slate-600" /> Install
            </button>

            <!-- Notification Bell Button -->
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

            <!-- Internal Message Button -->
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

            <!-- Profile / Avatar Dropdown -->
            <div ref="menuRef" class="relative">
                <button 
                    type="button" 
                    class="flex h-9 w-9 items-center justify-center rounded-full overflow-hidden bg-blue-500 text-sm font-bold text-white cursor-pointer border border-slate-200 shadow-2xs transition hover:opacity-90" 
                    @click="showMenu = !showMenu"
                >
                    <img v-if="user.foto" :src="user.foto" :alt="user.displayName" class="h-full w-full object-cover" />
                    <span v-else>{{ user.displayName ? user.displayName.charAt(0) : 'U' }}</span>
                </button>
                <div v-if="showMenu" class="absolute right-0 mt-2 w-52 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-xl z-50 animate-in fade-in zoom-in-95 duration-150">
                    <div class="p-3 border-b border-slate-100 bg-slate-50">
                        <div class="font-bold text-xs text-slate-800 truncate uppercase">{{ user.displayName }}</div>
                        <div class="text-[11px] text-slate-400 truncate">{{ user.jabatan || 'OPERATOR' }}</div>
                    </div>
                    <div class="py-1">
                        <a href="/profil" class="block w-full px-4 py-2 text-left text-xs font-bold text-slate-700 hover:bg-slate-50 no-underline">Profil Saya</a>
                        <button type="button" class="w-full px-4 py-2 text-left text-xs font-bold text-red-600 hover:bg-red-50 cursor-pointer" @click="handleLogout">Keluar</button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- ===== PWA INSTALL: iOS GUIDE MODAL (BOTTOM SHEET PARITY V1) ===== -->
    <div 
        v-if="showIosModal" 
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4"
        @click.self="showIosModal = false"
    >
        <div class="w-full max-w-md rounded-t-[28px] sm:rounded-3xl bg-white p-6 pb-8 shadow-2xl animate-in slide-in-from-bottom-5 duration-200">
            <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-slate-200"></div>

            <div class="text-center mb-5">
                <div class="text-[0.65rem] font-extrabold uppercase tracking-widest text-slate-400 mb-1">PWA</div>
                <h3 class="m-0 text-lg font-black text-slate-800">Install One Click</h3>
                <p class="m-0 mt-1 text-xs text-slate-500">
                    Buka di <strong>Safari</strong>, lalu ikuti langkah berikut:
                </p>
            </div>

            <div class="space-y-3 mb-6">
                <!-- Step 1 -->
                <div class="flex items-center gap-3.5 rounded-2xl bg-slate-50 p-3.5 border border-slate-100">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-xs">
                        <Share class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-800">Langkah 1</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Tap ikon <strong>Share [↑]</strong> di toolbar Safari
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex items-center gap-3.5 rounded-2xl bg-slate-50 p-3.5 border border-slate-100">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-xs">
                        <SquarePlus class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-800">Langkah 2</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Pilih <strong>"Add to Home Screen"</strong>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex items-center gap-3.5 rounded-2xl bg-slate-50 p-3.5 border border-slate-100">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs">
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-800">Langkah 3</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Tap <strong>"Add"</strong> — aplikasi siap di Home Screen!
                        </div>
                    </div>
                </div>
            </div>

            <button 
                type="button"
                @click="showIosModal = false"
                class="w-full rounded-2xl bg-slate-900 py-3.5 text-xs font-black uppercase tracking-wider text-white shadow-md hover:bg-slate-800 transition cursor-pointer"
            >
                Tutup
            </button>
        </div>
    </div>

    <!-- ===== PWA INSTALL: ANDROID / BROWSER GUIDE MODAL (BOTTOM SHEET) ===== -->
    <div 
        v-if="showBrowserModal" 
        class="fixed inset-0 z-50 flex items-end justify-center bg-black/60 backdrop-blur-xs p-0 sm:p-4"
        @click.self="showBrowserModal = false"
    >
        <div class="w-full max-w-md rounded-t-[28px] sm:rounded-3xl bg-white p-6 pb-8 shadow-2xl animate-in slide-in-from-bottom-5 duration-200">
            <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-slate-200"></div>

            <div class="text-center mb-5">
                <div class="text-[0.65rem] font-extrabold uppercase tracking-widest text-slate-400 mb-1">PWA</div>
                <h3 class="m-0 text-lg font-black text-slate-800">Install One Click</h3>
                <p class="m-0 mt-1 text-xs text-slate-500">
                    Buka di <strong>Chrome / Browser</strong>, lalu ikuti langkah berikut:
                </p>
            </div>

            <div class="space-y-3 mb-6">
                <!-- Step 1 -->
                <div class="flex items-center gap-3.5 rounded-2xl bg-slate-50 p-3.5 border border-slate-100">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-xs">
                        <MoreVertical class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-800">Langkah 1</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Tap ikon menu <strong>titik tiga (⋮)</strong> di sudut atas browser
                        </div>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="flex items-center gap-3.5 rounded-2xl bg-slate-50 p-3.5 border border-slate-100">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-xs">
                        <Download class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-800">Langkah 2</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Pilih <strong>"Install Aplikasi"</strong> atau <strong>"Tambahkan ke Layar Utama"</strong>
                        </div>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="flex items-center gap-3.5 rounded-2xl bg-slate-50 p-3.5 border border-slate-100">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs">
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="text-xs font-extrabold text-slate-800">Langkah 3</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Konfirmasi <strong>"Install"</strong> — app siap digunakan tanpa browser frame!
                        </div>
                    </div>
                </div>
            </div>

            <button 
                type="button"
                @click="showBrowserModal = false"
                class="w-full rounded-2xl bg-slate-900 py-3.5 text-xs font-black uppercase tracking-wider text-white shadow-md hover:bg-slate-800 transition cursor-pointer"
            >
                Tutup
            </button>
        </div>
    </div>

    <!-- Modals -->
    <NotificationModal :show="showNotifModal" @close="showNotifModal = false" />
    <PesanModal :show="showPesanModal" @close="showPesanModal = false" />
</template>
