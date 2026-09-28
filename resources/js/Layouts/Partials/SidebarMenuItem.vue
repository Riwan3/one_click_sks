<script setup>
import { computed, ref, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronDown } from '@lucide/vue';
import { sidebarIcons } from '@/utils/sidebarIcons';

const props = defineProps({
    item: { type: Object, required: true },
    collapsed: { type: Boolean, default: false },
});

const page = usePage();

const hasChildren = computed(() => Array.isArray(props.item.children) && props.item.children.length > 0);

const isChildActive = (item) => {
    if (!item) return false;
    const currentPath = page.url.split('?')[0];
    if (item.href) {
        const targetPath = item.href.split('?')[0];
        if (currentPath === targetPath || (targetPath !== '/' && currentPath.startsWith(targetPath))) {
            return true;
        }
    }
    if (Array.isArray(item.children)) {
        return item.children.some(child => isChildActive(child));
    }
    return false;
};

const hasActiveChild = computed(() => {
    if (!hasChildren.value) return false;
    return props.item.children.some(child => isChildActive(child));
});

const open = ref(hasActiveChild.value);

watch(
    hasActiveChild,
    (active) => {
        if (active) open.value = true;
    },
    { immediate: true }
);

const isActive = computed(() => {
    if (!props.item.href) return false;
    const currentPath = page.url.split('?')[0];
    const targetPath = props.item.href.split('?')[0];
    return currentPath === targetPath;
});

const icon = computed(() => (props.item.icon ? sidebarIcons[props.item.icon] : null));
</script>

<template>
    <div>
        <button
            v-if="hasChildren"
            type="button"
            :class="[
                'flex w-full items-center gap-3 rounded-lg px-4 py-2.5 text-left text-sm font-semibold transition-all duration-150',
                open || hasActiveChild
                    ? 'border border-slate-800 text-slate-800 bg-white shadow-xs'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
            ]"
            @click="open = !open"
        >
            <component :is="icon" v-if="icon" class="h-4 w-4 shrink-0 text-slate-500" />
            <span v-if="!collapsed" class="flex-1">{{ item.label }}</span>
            <ChevronDown v-if="!collapsed" :class="['h-3.5 w-3.5 shrink-0 transition-transform duration-200 text-slate-500', open && 'rotate-180']" />
        </button>

        <a
            v-else-if="item.href && item.external"
            :href="item.href"
            target="_blank"
            class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold no-underline text-slate-600 hover:bg-slate-50 hover:text-slate-900"
        >
            <component :is="icon" v-if="icon" class="h-4 w-4 shrink-0 text-slate-500" />
            <span v-if="!collapsed">{{ item.label }}</span>
        </a>

        <Link
            v-else-if="item.href"
            :href="item.href"
            :class="[
                'flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold no-underline transition-colors',
                isActive ? 'bg-[#EAF2FF] text-[#4A90E2] font-bold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
            ]"
        >
            <component :is="icon" v-if="icon" class="h-4 w-4 shrink-0 text-slate-500" />
            <span v-if="!collapsed">{{ item.label }}</span>
        </Link>

        <span
            v-else
            title="Segera hadir"
            class="flex cursor-not-allowed items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-300"
        >
            <component :is="icon" v-if="icon" class="h-4 w-4 shrink-0 text-slate-300" />
            <span v-if="!collapsed" class="flex-1">{{ item.label }}</span>
            <span v-if="!collapsed && item.badge" class="rounded-full bg-red-500 px-1.5 py-0.5 text-[0.65rem] font-bold text-white">
                {{ item.badge }}
            </span>
        </span>

        <div v-if="hasChildren && open && !collapsed" class="relative mt-1 mb-1.5 ml-5 border-l-2 border-slate-200/90 pl-3 space-y-1">
            <template v-for="(child, index) in item.children" :key="index">
                <Link
                    v-if="child.href"
                    :href="child.href"
                    :class="[
                        'flex items-center gap-2.5 rounded-lg px-2.5 py-2 text-sm font-medium no-underline transition-colors',
                        page.url.split('?')[0] === child.href.split('?')[0]
                            ? 'bg-[#EAF2FF] text-[#4A90E2] font-bold shadow-xs'
                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
                    ]"
                >
                    <component :is="child.icon ? sidebarIcons[child.icon] : null" v-if="child.icon" class="h-4 w-4 shrink-0 text-slate-500" />
                    <span>{{ child.label }}</span>
                </Link>
                <SidebarMenuItem v-else :item="child" :collapsed="collapsed" />
            </template>
        </div>
    </div>
</template>
