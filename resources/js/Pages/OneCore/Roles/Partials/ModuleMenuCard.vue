<script setup>
import { useForm } from '@inertiajs/vue3';
import { Save } from '@lucide/vue';

const props = defineProps({
    role: { type: String, required: true },
    module: { type: Object, required: true },
});

const form = useForm({
    menu_keys: props.module.menus.filter((m) => m.enabled).map((m) => m.menuKey),
});

const submit = () => form.put(`/one-core/roles/${props.role}/menu/${props.module.key}`, { preserveScroll: true });
const checkAll = () => { form.menu_keys = props.module.menus.map((m) => m.menuKey); };
const uncheckAll = () => { form.menu_keys = []; };
</script>

<template>
    <form class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-slate-800">{{ module.title }}</h2>
            <div v-if="module.menus.length" class="flex shrink-0 gap-2 text-[0.65rem] font-semibold">
                <button type="button" class="text-blue-600 hover:underline" @click="checkAll">Pilih Semua</button>
                <button type="button" class="text-slate-500 hover:underline" @click="uncheckAll">Hapus Semua</button>
            </div>
        </div>

        <div class="mb-4 flex flex-col gap-1">
            <label
                v-for="item in module.menus"
                :key="item.menuKey"
                class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs text-slate-600 hover:bg-slate-50"
                :style="{ paddingLeft: `${8 + item.depth * 16}px` }"
            >
                <input v-model="form.menu_keys" type="checkbox" :value="item.menuKey" class="h-3.5 w-3.5 rounded border-slate-300">
                {{ item.label }}
            </label>
            <p v-if="module.menus.length === 0" class="px-2 py-1.5 text-xs text-slate-400">Belum ada menu terdaftar untuk modul ini.</p>
        </div>

        <button
            type="submit"
            :disabled="form.processing"
            class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-600 disabled:opacity-60"
        >
            <Save class="h-3.5 w-3.5" /> {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
        </button>
    </form>
</template>
