<script setup>
import TopbarActions from '@/Components/shared/TopbarActions.vue';

defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
});
</script>

<template>
    <nav class="fixed inset-x-0 top-0 z-40 flex h-[68px] items-center bg-white px-6 shadow-sm lg:px-10">
        <a href="/portal" class="mr-4 flex items-center gap-2 text-slate-800 no-underline">
            <img src="/images/title.png" alt="One Click" class="h-8 w-8">
            <span class="text-lg font-bold lowercase">one<span class="text-blue-500">click</span></span>
        </a>

        <div class="ml-auto">
            <TopbarActions :user="user" :notif-counts="notifCounts" />
        </div>
    </nav>
</template>
