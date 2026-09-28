<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import { 
    Megaphone, 
    X, 
    Calendar, 
    User, 
    ChevronLeft, 
    ChevronRight, 
    CheckCircle2, 
    Maximize2, 
    ZoomIn, 
    Tag 
} from 'lucide-vue-next';

const props = defineProps({
    show: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const announcements = ref([]);
const loading = ref(false);
const autoShow = ref(false);
const currentSlide = ref(0);
const countdown = ref(0);
const showZoomImage = ref(false);
let timer = null;

const sessionId = ref('');

const isVisible = computed(() => props.show || autoShow.value);

const currentAnnouncement = computed(() => {
    if (!announcements.value || announcements.value.length === 0) return null;
    return announcements.value[currentSlide.value] || announcements.value[0];
});

const fetchActiveAnnouncements = async () => {
    try {
        loading.value = true;
        const res = await fetch('/pengumuman/active');
        if (res.ok) {
            const data = await res.json();
            if (data.session_id) {
                sessionId.value = data.session_id;
            }
            announcements.value = Array.isArray(data) ? data : (data.announcements || []);
            checkAutoPopup();
        }
    } catch (e) {
        // silent catch
    } finally {
        loading.value = false;
    }
};

const checkAutoPopup = () => {
    if (!announcements.value || announcements.value.length === 0) return;
    
    // Jangan auto-popup jika user sedang berada di halaman kelola pengumuman
    if (window.location.pathname.startsWith('/pengumuman')) return;

    const sid = sessionId.value || 'current';

    // Cek apakah ada pengumuman yang belum dilihat pada sesi login ini
    const hasUnseen = announcements.value.some((ann) => {
        const sessionKey = `oneclick_seen_session_${sid}_${ann.id}`;
        return !localStorage.getItem(sessionKey);
    });

    if (hasUnseen) {
        autoShow.value = true;
        startCountdown();
    }
};

const startCountdown = () => {
    countdown.value = 3;
    if (timer) clearInterval(timer);
    timer = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            clearInterval(timer);
            timer = null;
        }
    }, 1000);
};

const markAnnouncementSeen = async (announcementId) => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        await fetch(`/pengumuman/${announcementId}/seen`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        });
    } catch (e) {
        // silent catch
    }
};

const nextSlide = () => {
    const current = currentAnnouncement.value;
    if (current) {
        const sid = sessionId.value || 'current';
        localStorage.setItem(`oneclick_seen_session_${sid}_${current.id}`, '1');
        if (current.visibility_mode === 'once') {
            markAnnouncementSeen(current.id);
        }
    }

    if (currentSlide.value < announcements.value.length - 1) {
        currentSlide.value++;
    } else {
        handleClose();
    }
};

const prevSlide = () => {
    if (currentSlide.value > 0) {
        currentSlide.value--;
    }
};

const handleClose = () => {
    if (announcements.value && announcements.value.length > 0) {
        const sid = sessionId.value || 'current';
        announcements.value.forEach((ann) => {
            // Tandai sudah dilihat pada sesi login ini
            localStorage.setItem(`oneclick_seen_session_${sid}_${ann.id}`, '1');

            // Jika modenya 'once' (sekali lihat), simpan permanen ke database
            if (ann.visibility_mode === 'once') {
                markAnnouncementSeen(ann.id);
            }
        });
    }
    autoShow.value = false;
    showZoomImage.value = false;
    emit('close');
    if (timer) clearInterval(timer);
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        if (announcements.value.length === 0) {
            fetchActiveAnnouncements();
        }
        currentSlide.value = 0;
    }
});

onMounted(() => {
    fetchActiveAnnouncements();
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <!-- 1. POPUP MODAL PENGUMUMAN -->
    <Teleport to="body">
        <div
            v-if="isVisible && currentAnnouncement"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/70 backdrop-blur-sm animate-in fade-in duration-200"
            @click.self="handleClose"
        >
            <div 
                class="relative w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-[0_25px_60px_-15px_rgba(0,0,0,0.3)] border border-slate-100 flex flex-col max-h-[92vh] animate-in zoom-in-95 duration-200"
            >
                <!-- HEADER (OneClick Brand Gradient) -->
                <div 
                    class="flex items-center justify-between px-6 py-4.5 text-white border-b border-white/10"
                    style="background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);"
                >
                    <div class="flex items-center gap-3.5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/15 backdrop-blur-md shadow-inner text-white">
                            <Megaphone class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="m-0 text-base font-black uppercase tracking-wider text-white">Pengumuman Resmi</h3>
                                <span v-if="announcements.length > 1" class="text-[11px] font-extrabold px-2.5 py-0.5 rounded-full bg-white/20 text-white backdrop-blur-xs">
                                    {{ currentSlide + 1 }} dari {{ announcements.length }}
                                </span>
                            </div>
                            <p class="m-0 text-xs font-medium text-blue-100/90">PT Putra Perkasa Abadi</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="flex h-8.5 w-8.5 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition hover:scale-105 active:scale-95"
                        title="Tutup Modal"
                        @click="handleClose"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- BODY -->
                <div class="p-6 overflow-y-auto space-y-4 max-h-[68vh] divide-y divide-slate-100/80">
                    <!-- TOP INFO (CATEGORY, DATE, AUTHOR) -->
                    <div class="flex flex-wrap items-center justify-between gap-2.5 pb-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/80 shadow-2xs">
                            <Tag class="h-3 w-3" />
                            {{ currentAnnouncement.type || 'Pengumuman' }}
                        </span>

                        <div class="flex items-center gap-3 text-xs text-slate-500 font-medium">
                            <span class="inline-flex items-center gap-1.5">
                                <Calendar class="h-3.5 w-3.5 text-slate-400" />
                                {{ currentAnnouncement.date }}
                            </span>
                            <span v-if="currentAnnouncement.author_name" class="inline-flex items-center gap-1.5 font-semibold text-slate-700">
                                <User class="h-3.5 w-3.5 text-slate-400" />
                                {{ currentAnnouncement.author_name }}
                            </span>
                        </div>
                    </div>

                    <!-- TITLE & CONTENT -->
                    <div class="pt-3 space-y-2">
                        <h4 class="m-0 text-xl font-black text-slate-900 tracking-tight leading-snug">
                            {{ currentAnnouncement.title }}
                        </h4>

                        <div class="text-[13px] text-slate-600 leading-relaxed whitespace-pre-wrap font-normal">
                            {{ currentAnnouncement.content }}
                        </div>
                    </div>

                    <!-- BANNER / POSTER IMAGE -->
                    <div v-if="currentAnnouncement.image_url" class="pt-4">
                        <div 
                            class="group relative overflow-hidden rounded-2xl border border-slate-200/90 shadow-sm bg-slate-50 cursor-pointer transition-all hover:shadow-md"
                            @click="showZoomImage = true"
                        >
                            <img
                                :src="currentAnnouncement.image_url"
                                :alt="currentAnnouncement.title"
                                class="w-full max-h-80 object-cover object-center transition-transform duration-300 group-hover:scale-[1.015]"
                            />
                            <!-- Zoom Overlay Hint -->
                            <div class="absolute inset-0 flex items-center justify-center bg-slate-950/20 opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-[1px]">
                                <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/90 backdrop-blur-md text-slate-900 text-xs font-bold shadow-lg">
                                    <ZoomIn class="h-4 w-4 text-blue-600" />
                                    Klik untuk memperbesar gambar
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="p-4 px-6 border-t border-slate-100 bg-slate-50/90 flex items-center justify-between gap-3">
                    <!-- Slide Navigation if multiple -->
                    <template v-if="announcements.length > 1">
                        <button
                            type="button"
                            :disabled="currentSlide === 0"
                            :class="[
                                'px-4 py-2.5 rounded-xl border font-bold text-xs flex items-center gap-1.5 transition',
                                currentSlide === 0 
                                    ? 'border-slate-200 text-slate-400 bg-slate-100/60 cursor-not-allowed' 
                                    : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100 shadow-2xs cursor-pointer'
                            ]"
                            @click="prevSlide"
                        >
                            <ChevronLeft class="h-4 w-4" /> Sebelumnya
                        </button>

                        <!-- Indicator Dots -->
                        <div class="flex items-center gap-1.5">
                            <span 
                                v-for="(_, idx) in announcements" 
                                :key="idx"
                                :class="[
                                    'h-2 rounded-full transition-all duration-300',
                                    idx === currentSlide ? 'w-6 bg-blue-600' : 'w-2 bg-slate-300'
                                ]"
                            />
                        </div>

                        <button
                            type="button"
                            :disabled="countdown > 0"
                            :class="[
                                'py-2.5 px-5 rounded-xl font-bold text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 shadow-sm',
                                countdown > 0
                                    ? 'bg-slate-300 text-slate-500 cursor-not-allowed'
                                    : 'bg-gradient-to-r from-blue-700 to-blue-600 hover:from-blue-800 hover:to-blue-700 text-white cursor-pointer shadow-blue-600/25 active:scale-98'
                            ]"
                            @click="nextSlide"
                        >
                            <template v-if="countdown > 0">
                                Harap baca ({{ countdown }}s)
                            </template>
                            <template v-else-if="currentSlide < announcements.length - 1">
                                Informasi Selanjutnya <ChevronRight class="h-4 w-4" />
                            </template>
                            <template v-else>
                                <CheckCircle2 class="h-4 w-4" /> Mengerti &amp; Tutup
                            </template>
                        </button>
                    </template>

                    <!-- Single Announcement Full Width Button -->
                    <template v-else>
                        <button
                            type="button"
                            :disabled="countdown > 0"
                            :class="[
                                'w-full py-3 px-6 rounded-xl font-black text-xs uppercase tracking-wider transition flex items-center justify-center gap-2 shadow-md',
                                countdown > 0
                                    ? 'bg-slate-300 text-slate-500 cursor-not-allowed'
                                    : 'bg-gradient-to-r from-[#1e3a5f] to-[#2563eb] hover:from-[#172d4a] hover:to-[#1d4ed8] text-white cursor-pointer shadow-blue-900/20 active:scale-[0.99]'
                            ]"
                            @click="handleClose"
                        >
                            <template v-if="countdown > 0">
                                Harap baca terlebih dahulu... ({{ countdown }}s)
                            </template>
                            <template v-else>
                                <CheckCircle2 class="h-4 w-4" /> MENGERTI &amp; TUTUP PENGUMUMAN
                            </template>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- 2. FULLSCREEN LIGHTBOX UNTUK POSTER/GAMBAR -->
        <div
            v-if="showZoomImage && currentAnnouncement && currentAnnouncement.image_url"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-md animate-in fade-in duration-150"
            @click.self="showZoomImage = false"
        >
            <div class="relative max-w-5xl w-full max-h-[92vh] flex flex-col items-center">
                <button
                    type="button"
                    class="absolute -top-12 right-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition"
                    @click="showZoomImage = false"
                >
                    <X class="h-4 w-4" /> Tutup Pratinjau
                </button>
                <img
                    :src="currentAnnouncement.image_url"
                    :alt="currentAnnouncement.title"
                    class="max-w-full max-h-[86vh] object-contain rounded-2xl shadow-2xl border border-white/10"
                />
                <p class="text-white/80 text-xs font-medium mt-3 text-center">
                    {{ currentAnnouncement.title }} &bull; PT Putra Perkasa Abadi
                </p>
            </div>
        </div>
    </Teleport>
</template>
