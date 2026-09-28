<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { X, Clock, User } from 'lucide-vue-next';

const props = defineProps({
    rewards: { type: Array, required: true },
    currentUserNrp: { type: String, default: '' },
    showViewAll: { type: Boolean, default: true },
});

const selectedReward = ref(null);
const dismissedRewardIds = ref([]);

const storageKey = computed(() => `oneclick_seen_rewards_${props.currentUserNrp || 'guest'}`);

onMounted(() => {
    try {
        const stored = localStorage.getItem(storageKey.value);
        if (stored) {
            dismissedRewardIds.value = JSON.parse(stored);
        }
    } catch (e) {}
});

const visibleRewards = computed(() => {
    return (props.rewards || []).filter(r => !dismissedRewardIds.value.includes(r.id));
});

const handleRewardClick = (reward) => {
    selectedReward.value = reward;
};

const closeModal = async () => {
    const reward = selectedReward.value;
    selectedReward.value = null;

    if (reward && !dismissedRewardIds.value.includes(reward.id)) {
        dismissedRewardIds.value.push(reward.id);
        try {
            localStorage.setItem(storageKey.value, JSON.stringify(dismissedRewardIds.value));
        } catch (e) {}

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            await fetch(`/rewards/${reward.id}/seen`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
        } catch (e) {}
    }
};

const rewardStyles = {
    safety: { emoji: '⭐', label: 'Safety Award', bg: '#ecfdf5' },
    birthday: { emoji: '🎂', label: 'Birthday', bg: '#fce7f3' },
    achievement: { emoji: '🏆', label: 'Achievement', bg: '#fffbeb' },
    performance: { emoji: '🏅', label: 'Performance', bg: '#eff6ff' },
    reward: { emoji: '🎁', label: 'Reward', bg: '#faf5ff' },
};
</script>

<template>
    <div class="w-full">
        <!-- CARD CONTAINER (Hanya tampil jika ada reward aktif) -->
        <div v-if="visibleRewards.length > 0 || (showViewAll && (!rewards || rewards.length === 0))" class="w-full space-y-1.5">
            <div class="flex items-center justify-between px-1">
                <div class="flex items-center gap-1.5 text-xs font-black text-slate-800 tracking-tight">
                    <span>🎉</span>
                    <span>Oneclick Reward Center</span>
                </div>
                <Link 
                    v-if="showViewAll"
                    href="/rewards" 
                    class="text-[11px] font-bold text-blue-500 hover:text-blue-600 hover:underline"
                >
                    Lihat Semua
                </Link>
            </div>

            <div v-if="visibleRewards.length === 0" class="rounded-2xl border border-slate-200/70 bg-white p-4 text-center text-xs text-slate-400 shadow-2xs">
                Belum ada data reward center.
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="(reward, index) in visibleRewards"
                    :key="reward.id || index"
                    @click="handleRewardClick(reward)"
                    :class="[
                        'flex items-center gap-3.5 rounded-2xl bg-white p-3.5 shadow-2xs border transition hover:shadow-md cursor-pointer select-none',
                        currentUserNrp && reward.recipient_nrp === currentUserNrp 
                            ? 'border-amber-300 ring-2 ring-amber-400/30 bg-amber-50/40' 
                            : 'border-slate-200/80 hover:border-slate-300'
                    ]"
                >
                    <div
                        v-if="reward.image_url"
                        class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-slate-200/80 bg-slate-100"
                    >
                        <img :src="reward.image_url" :alt="reward.title" class="h-full w-full object-cover" />
                    </div>
                    <div
                        v-else
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-2xl shadow-2xs"
                        :style="{ background: (rewardStyles[reward.type] ?? rewardStyles.reward).bg }"
                    >
                        {{ reward.icon || (rewardStyles[reward.type] ?? rewardStyles.reward).emoji }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-sm font-black text-slate-800 leading-snug">{{ reward.title }}</span>
                            <span 
                                v-if="currentUserNrp && reward.recipient_nrp === currentUserNrp" 
                                class="px-1.5 py-0.5 text-[9px] font-extrabold rounded bg-amber-500 text-white tracking-wide uppercase shadow-2xs"
                            >
                                🎉 Untuk Anda
                            </span>
                        </div>
                        <div class="text-xs font-semibold text-slate-400 mt-0.5 truncate">
                            <span v-if="reward.recipient" class="text-amber-600 font-bold">{{ reward.recipient }} &bull; </span>
                            <span>{{ reward.category || (rewardStyles[reward.type] ?? rewardStyles.reward).label }}</span>
                            <span> &bull; {{ reward.date }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DETAIL REWARD MODAL (TELEPORT DI ROOT - TIDAK AKAN TERPENGARUH v-if CARD) -->
        <Teleport to="body">
            <div 
                v-if="selectedReward"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4"
                @click.self="closeModal"
            >
                <div class="w-full max-w-sm sm:max-w-md rounded-[28px] bg-white p-6 shadow-2xl animate-in zoom-in-95 duration-150 relative space-y-4 max-h-[90vh] overflow-y-auto">
                    <!-- Close icon top-right -->
                    <button 
                        type="button" 
                        @click="closeModal"
                        class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 p-1 rounded-full hover:bg-slate-100 transition cursor-pointer"
                    >
                        <X class="w-5 h-5" />
                    </button>

                    <!-- HEADER: Icon & Title -->
                    <div class="text-center pt-1">
                        <div class="inline-flex items-center justify-center gap-2 text-xl font-black text-slate-800">
                            <span class="text-2xl">{{ selectedReward.icon || '🎁' }}</span>
                            <span>{{ selectedReward.title }}</span>
                        </div>
                    </div>

                    <!-- RECIPIENT / CATEGORY CONTAINER -->
                    <div class="rounded-2xl bg-slate-50 border border-slate-100 p-3 text-center">
                        <div class="text-xs font-bold text-slate-800">
                            {{ selectedReward.recipient ? selectedReward.recipient : selectedReward.category }}
                        </div>
                        <div v-if="selectedReward.recipient_nrp" class="text-[11px] text-slate-500 mt-0.5 font-mono">
                            NRP: {{ selectedReward.recipient_nrp }} &bull; {{ selectedReward.category }}
                        </div>
                    </div>

                    <!-- IMAGE PREVIEW -->
                    <div v-if="selectedReward.image_url" class="rounded-2xl overflow-hidden border border-slate-100 max-h-56 bg-slate-50">
                        <img :src="selectedReward.image_url" :alt="selectedReward.title" class="w-full h-full object-cover" />
                    </div>

                    <!-- DETAIL REWARD BOX (Purple Box) -->
                    <div class="rounded-2xl bg-purple-50/70 border border-purple-100 p-4 space-y-1.5 text-left">
                        <div class="flex items-center gap-1.5 text-xs font-black text-purple-700">
                            <span>🎁</span>
                            <span>Detail Reward</span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed m-0 whitespace-pre-line">
                            {{ selectedReward.description || '-' }}
                        </p>
                    </div>

                    <!-- METADATA FOOTER -->
                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-medium px-1">
                        <span class="flex items-center gap-1">
                            <Clock class="w-3.5 h-3.5 text-slate-400" />
                            {{ selectedReward.created_formatted || selectedReward.date }}
                        </span>
                        <span class="flex items-center gap-1">
                            <User class="w-3.5 h-3.5 text-slate-400" />
                            {{ selectedReward.author_name || 'ADMIN' }}
                        </span>
                    </div>

                    <!-- BUTTON TUTUP -->
                    <div class="pt-2 flex justify-center">
                        <button 
                            type="button" 
                            @click="closeModal"
                            class="rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-black text-xs px-10 py-2.5 shadow-md shadow-purple-500/25 transition active:scale-95 cursor-pointer"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
