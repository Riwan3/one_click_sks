<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    tally: { type: Object, required: true },
    tanggal: { type: String, required: true },
    shiftLabel: { type: String, required: true },
    tallyTitle: { type: String, required: true },
});

const title = ref(props.tallyTitle);

const saveTitle = () => {
    router.post('/one-dms/formulir/settings', { tallyTitle: title.value }, { preserveScroll: true, preserveState: true });
};
</script>

<template>
    <div class="rounded-xl border border-emerald-200 p-5" style="background:#c8e6c9;">
        <input
            v-model="title"
            type="text"
            @blur="saveTitle"
            class="mb-2 w-full bg-transparent p-0 text-sm font-bold text-emerald-900 border-none outline-none focus:ring-0"
        >
        <p class="mb-3 text-xs text-emerald-800">
            Tanggal {{ tanggal }} &middot; {{ shiftLabel }}
        </p>

        <div class="mb-3 text-xs font-bold uppercase tracking-wide text-emerald-900">Jenis Deviasi:</div>
        <ul class="mb-3 grid grid-cols-1 gap-y-1 text-sm text-emerald-900">
            <li v-for="(count, jenis) in tally" :key="jenis" class="flex justify-between border-b border-emerald-300/50 py-0.5">
                <span>{{ jenis }}</span>
                <span class="font-bold">{{ count }}</span>
            </li>
        </ul>

        <p class="m-0 text-center text-sm font-bold italic text-emerald-900">Salam Zero Accident Mindset!!!</p>
    </div>
</template>
