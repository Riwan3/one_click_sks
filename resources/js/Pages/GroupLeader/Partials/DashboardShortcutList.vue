<script setup>
import { Link } from '@inertiajs/vue3';
import { UserCheck, UserRoundX } from '@lucide/vue';
import { moduleIcons } from '@/utils/moduleTheme';

defineProps({
    shortcuts: { type: Array, required: true },
});

const icons = { ...moduleIcons, UserCheck, UserRoundX };
</script>

<template>
    <div class="flex flex-col gap-2">
        <Link
            v-for="item in shortcuts"
            :key="item.label"
            :href="item.href"
            :class="[
                'flex items-center gap-2.5 rounded-xl px-4 py-3 text-sm font-bold uppercase no-underline shadow-sm',
                item.active ? 'bg-blue-500 text-white' : 'bg-white text-slate-700',
            ]"
        >
            <component :is="icons[item.icon]" class="h-4 w-4" />
            {{ item.label }}
        </Link>
    </div>
</template>
