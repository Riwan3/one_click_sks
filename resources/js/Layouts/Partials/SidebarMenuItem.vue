<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown } from '@lucide/vue';
import { sidebarIcons } from '@/utils/sidebarIcons';

const props = defineProps({
    item: { type: Object, required: true },
    collapsed: { type: Boolean, default: false },
});

const page = usePage();
const open = ref(false);

const hasChildren = computed(() => Array.isArray(props.item.children) && props.item.children.length > 0);
const isActive = computed(() => !!props.item.href && page.url === props.item.href);
const icon = computed(() => (props.item.icon ? sidebarIcons[props.item.icon] : null));
</script>

<template>
    <div>
        <button
            v-if="hasChildren"
            type="button"
            class="flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-left text-sm font-semibold text-slate-500 hover:bg-slate-50"
            @click="open = !open"
        >
            <component :is="icon" v-if="icon" class="h-4 w-4 shrink-0" />
            <span v-if="!collapsed" class="flex-1">{{ item.label }}</span>
            <ChevronDown v-if="!collapsed" :class="['h-3.5 w-3.5 shrink-0 transition-transform', open && 'rotate-180']" />
        </button>

        <Link
            v-else-if="item.href"
            :href="item.href"
            :class="[
                'flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold no-underline',
                isActive ? 'bg-[#EAF2FF] text-[#4A90E2]' : 'text-slate-500 hover:bg-slate-50',
            ]"
        >
            <component :is="icon" v-if="icon" class="h-4 w-4 shrink-0" />
            <span v-if="!collapsed">{{ item.label }}</span>
        </Link>

        <span
            v-else
            title="Segera hadir"
            class="flex cursor-not-allowed items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-300"
        >
            <component :is="icon" v-if="icon" class="h-4 w-4 shrink-0" />
            <span v-if="!collapsed" class="flex-1">{{ item.label }}</span>
            <span v-if="!collapsed && item.badge" class="rounded-full bg-red-500 px-1.5 py-0.5 text-[0.65rem] font-bold text-white">
                {{ item.badge }}
            </span>
        </span>

        <div v-if="hasChildren && open && !collapsed" class="ml-4 border-l border-slate-100 pl-2">
            <SidebarMenuItem v-for="(child, index) in item.children" :key="index" :item="child" :collapsed="collapsed" />
        </div>
    </div>
</template>
