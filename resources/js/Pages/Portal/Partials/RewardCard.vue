<script setup>
defineProps({
    rewards: { type: Array, required: true },
});

const rewardStyles = {
    safety: { emoji: '⭐', label: 'Safety Award', bg: '#ecfdf5' },
    birthday: { emoji: '🎂', label: 'Birthday', bg: '#fce7f3' },
    achievement: { emoji: '🏆', label: 'Achievement', bg: '#fffbeb' },
    reward: { emoji: '🎁', label: 'Reward', bg: '#faf5ff' },
};
</script>

<template>
    <div class="w-full rounded-2xl border border-white/45 bg-white/70 p-5 shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md">
        <div class="mb-4 flex items-center justify-between text-base font-extrabold text-slate-800">
            Oneclick Reward Center
            <a href="#" class="text-[0.7rem] font-bold text-blue-500 no-underline">Lihat Semua</a>
        </div>

        <div v-if="rewards.length === 0" class="py-4 text-center text-sm text-slate-500">
            Belum ada oneclick reward center baru.
        </div>

        <div
            v-for="(reward, index) in rewards"
            :key="index"
            class="mb-2 flex cursor-pointer items-center gap-3 rounded-2xl bg-white p-2.5 shadow-[0_2px_8px_rgba(0,0,0,0.05)] transition-all last:mb-0 hover:-translate-y-0.5 hover:shadow-[0_6px_16px_rgba(0,0,0,0.09)]"
        >
            <div
                class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-full text-xl"
                :style="{ background: (rewardStyles[reward.type] ?? rewardStyles.reward).bg }"
            >
                {{ (rewardStyles[reward.type] ?? rewardStyles.reward).emoji }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-bold text-slate-800">{{ reward.title }}</div>
                <div class="text-[0.65rem] text-slate-500">
                    {{ (rewardStyles[reward.type] ?? rewardStyles.reward).label }} &middot; {{ reward.date }}
                </div>
            </div>
        </div>
    </div>
</template>
