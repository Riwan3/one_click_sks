<script setup>
import { KeyRound } from '@lucide/vue';

defineProps({
    row: { type: Object, default: null },
});

const emit = defineEmits(['close', 'reset']);
</script>

<template>
    <div v-if="row" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="emit('close')">
        <div class="w-full max-w-sm overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between bg-slate-800 px-6 py-4 text-white">
                <h5 class="flex items-center gap-2 text-base font-bold">
                    <KeyRound class="h-4 w-4" /> Info Password
                </h5>
                <button type="button" class="text-2xl leading-none text-white/80 hover:text-white" @click="emit('close')">&times;</button>
            </div>

            <div class="space-y-3 p-6 text-sm">
                <div>
                    <span class="block text-xs font-semibold uppercase text-slate-400">NRP / Username</span>
                    <span class="font-bold text-slate-800">{{ row.username }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold uppercase text-slate-400">Nama</span>
                    <span class="font-bold text-slate-800">{{ row.name }}</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold uppercase text-slate-400">Status Password</span>
                    <span :class="row.passwordStatus === 'custom' ? 'font-bold text-amber-600' : 'font-bold text-slate-600'">
                        {{ row.passwordStatus === 'custom' ? 'Sudah diubah (custom)' : 'Default (mengikuti NRP)' }}
                    </span>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button type="button" class="text-sm font-bold text-slate-500" @click="emit('close')">Tutup</button>
                <button type="button" class="rounded-lg bg-amber-500 px-4 py-2 text-sm font-bold text-white hover:bg-amber-600" @click="emit('reset', row)">
                    Reset ke Default
                </button>
            </div>
        </div>
    </div>
</template>
