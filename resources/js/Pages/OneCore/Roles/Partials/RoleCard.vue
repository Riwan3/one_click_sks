<script setup>
import { confirmDialog } from '@/utils/confirm';
import { Link, router, useForm } from '@inertiajs/vue3';
import { Smartphone, Monitor, Save, ListChecks, Trash2 } from '@lucide/vue';

const props = defineProps({
    role: { type: Object, required: true },
});

const form = useForm({
    is_mobile: props.role.isMobile,
    module_keys: props.role.modules.filter((m) => m.enabled).map((m) => m.key),
});

const submit = () => form.put(`/one-core/roles/${props.role.role}`, { preserveScroll: true });
const checkAll = () => { form.module_keys = props.role.modules.map((m) => m.key); };
const uncheckAll = () => { form.module_keys = []; };

const destroyRole = async () => {
    if (! await confirmDialog(`Hapus role "${props.role.label}"? User yang masih memakai role ini harus dipindahkan dulu.`)) return;
    router.delete(`/one-core/roles/${props.role.role}`, { preserveScroll: true });
};
</script>

<template>
    <form class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
        <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h2 class="text-base font-extrabold text-slate-800">{{ role.label }}</h2>
                <span
                    class="rounded-full px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-wide"
                    :class="role.scope === 'control_center' ? 'bg-violet-100 text-violet-600' : 'bg-slate-100 text-slate-500'"
                >
                    {{ role.scope === 'control_center' ? 'Control Center' : 'Site' }}
                </span>
            </div>
            <div class="flex items-center gap-3">
                <label class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-slate-600">
                    <component :is="form.is_mobile ? Smartphone : Monitor" class="h-4 w-4 text-slate-400" />
                    {{ form.is_mobile ? 'Tampilan Mobile' : 'Tampilan Desktop' }}
                    <span class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors" :class="form.is_mobile ? 'bg-blue-500' : 'bg-slate-300'">
                        <input v-model="form.is_mobile" type="checkbox" class="peer absolute inset-0 h-full w-full cursor-pointer opacity-0">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform" :class="form.is_mobile ? 'translate-x-4' : 'translate-x-0.5'"></span>
                    </span>
                </label>
                <button
                    v-if="!role.isSystem"
                    type="button"
                    title="Hapus role"
                    class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 text-red-500 hover:bg-red-50"
                    @click="destroyRole"
                >
                    <Trash2 class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>

        <div class="mb-4">
            <div class="mb-2 flex items-center justify-between">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Modul yang bisa diakses
                    <span class="font-normal normal-case text-slate-400">
                        — {{ form.is_mobile ? 'tampil sebagai shortcut di beranda mobile' : 'tampil sebagai kartu di Home Portal' }}
                    </span>
                </p>
                <div class="flex shrink-0 gap-2 text-[0.65rem] font-semibold">
                    <button type="button" class="text-blue-600 hover:underline" @click="checkAll">Pilih Semua</button>
                    <button type="button" class="text-slate-500 hover:underline" @click="uncheckAll">Hapus Semua</button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3">
                <label v-for="module in role.modules" :key="module.key" class="flex items-center gap-1.5 rounded-lg border border-slate-100 px-2 py-1.5 text-xs text-slate-600 hover:bg-slate-50">
                    <input v-model="form.module_keys" type="checkbox" :value="module.key" class="h-3.5 w-3.5 rounded border-slate-300">
                    {{ module.title }}
                </label>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="submit"
                :disabled="form.processing"
                class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-3 py-1.5 text-xs font-bold text-white hover:bg-blue-600 disabled:opacity-60"
            >
                <Save class="h-3.5 w-3.5" /> {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <Link
                :href="`/one-core/roles/${role.role}`"
                class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50"
            >
                <ListChecks class="h-3.5 w-3.5" /> Atur Menu per Modul
            </Link>
        </div>
    </form>
</template>
