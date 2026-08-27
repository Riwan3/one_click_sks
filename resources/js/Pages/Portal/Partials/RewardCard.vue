<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    rewards: { type: Array, required: true },
});

const rewardStyles = {
    safety: { emoji: '⭐', label: 'Safety Award', bg: '#ecfdf5' },
    birthday: { emoji: '🎂', label: 'Birthday', bg: '#fce7f3' },
    achievement: { emoji: '🏆', label: 'Achievement', bg: '#fffbeb' },
    performance: { emoji: '🏅', label: 'Performance', bg: '#eff6ff' },
    reward: { emoji: '🎁', label: 'Reward', bg: '#faf5ff' },
};
</script>

<template>
    <div class="w-full rounded-2xl border border-white/45 bg-white/70 p-5 shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md">
        <div class="mb-4 flex items-center justify-between text-base font-extrabold text-slate-800">
            Oneclick Reward Center
            <Link href="/rewards" class="text-[0.7rem] font-bold text-blue-500 hover:underline">Lihat Semua</Link>
        </div>

        <div v-if="rewards.length === 0" class="py-4 text-center text-sm text-slate-500">
            Belum ada data reward center.
        </div>

        <div
            v-for="(reward, index) in rewards"
            :key="reward.id || index"
            class="mb-2 flex items-center gap-3 rounded-2xl bg-white p-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.05)] transition-all last:mb-0 hover:-translate-y-0.5 hover:shadow-[0_6px_16px_rgba(0,0,0,0.09)]"
        >
            <div
                class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-full text-xl shadow-2xs"
                :style="{ background: (rewardStyles[reward.type] ?? rewardStyles.reward).bg }"
            >
                {{ reward.icon || (rewardStyles[reward.type] ?? rewardStyles.reward).emoji }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-bold text-slate-800 leading-snug">{{ reward.title }}</div>
                <div class="text-[0.65rem] font-semibold text-slate-500">
                    {{ reward.category || (rewardStyles[reward.type] ?? rewardStyles.reward).label }} &middot; {{ reward.date }}
                </div>
            </div>
        </div>
    </div>
</template>
