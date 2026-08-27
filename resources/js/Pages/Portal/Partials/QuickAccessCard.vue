<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    CalendarCheck, FileText, UserCheck, ClipboardCheck,
    BookOpen, BarChart, Phone, Megaphone, Trophy
} from 'lucide-vue-next';
import NomorPentingModal from '@/Components/modals/NomorPentingModal.vue';

defineProps({
    quickAccess: { type: Array, required: true },
});

const showNomorPenting = ref(false);

const icons = {
    CalendarCheck, FileText, UserCheck, ClipboardCheck,
    BookOpen, BarChart, Phone, Megaphone, Trophy
};

const handleItemClick = (item, e) => {
    if (item.action === 'nomor-penting') {
        e.preventDefault();
        showNomorPenting.value = true;
    }
};
</script>

<template>
    <div class="w-full rounded-2xl border border-white/45 bg-white/70 p-5 shadow-[0_4px_20px_rgba(74,144,226,0.06)] backdrop-blur-md">
        <h3 class="mb-2.5 flex items-center gap-1.5 text-sm font-extrabold uppercase tracking-wide text-slate-800">
            <span class="inline-block h-3.5 w-3.5 rounded bg-blue-500"></span>
            Quick Access
        </h3>
        <div class="grid grid-cols-4 gap-2">
            <template v-for="(item, index) in quickAccess" :key="index">
                <button
                    v-if="item.action === 'nomor-penting'"
                    type="button"
                    @click="showNomorPenting = true"
                    class="flex flex-col items-center gap-1.5 rounded-xl border border-white/60 bg-white/80 p-2.5 text-center no-underline shadow-[0_2px_12px_rgba(74,144,226,0.04)] transition-all hover:-translate-y-0.5 hover:bg-white hover:shadow-[0_8px_24px_rgba(74,144,226,0.12)] cursor-pointer"
                >
                    <component :is="icons[item.icon] || icons.Phone" class="h-[1.2rem] w-[1.2rem] text-sky-500" />
                    <span class="text-[0.65rem] font-bold leading-tight text-slate-800">{{ item.label }}</span>
                </button>

                <Link
                    v-else
                    :href="item.url || '#'"
                    class="flex flex-col items-center gap-1.5 rounded-xl border border-white/60 bg-white/80 p-2.5 text-center no-underline shadow-[0_2px_12px_rgba(74,144,226,0.04)] transition-all hover:-translate-y-0.5 hover:bg-white hover:shadow-[0_8px_24px_rgba(74,144,226,0.12)]"
                >
                    <component :is="icons[item.icon] || icons.CalendarCheck" class="h-[1.2rem] w-[1.2rem] text-blue-500" />
                    <span class="text-[0.65rem] font-bold leading-tight text-slate-800">{{ item.label }}</span>
                </Link>
            </template>
        </div>

        <!-- NOMOR PENTING MODAL -->
        <NomorPentingModal :show="showNomorPenting" @close="showNomorPenting = false" />
    </div>
</template>
