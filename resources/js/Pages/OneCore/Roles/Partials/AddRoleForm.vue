<script setup>
import { useForm } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';

const form = useForm({
    code: '',
    label: '',
    scope: 'site',
});

const submit = () => form.post('/one-core/roles', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
});
</script>

<template>
    <form class="mb-4 flex flex-wrap items-end gap-2 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4" @submit.prevent="submit">
        <div class="flex-1">
            <label class="mb-1 block text-xs font-semibold text-slate-600">Kode Role</label>
            <input v-model="form.code" type="text" placeholder="mis. supervisor" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            <p v-if="form.errors.code" class="mt-1 text-xs text-red-600">{{ form.errors.code }}</p>
        </div>
        <div class="flex-1">
            <label class="mb-1 block text-xs font-semibold text-slate-600">Nama Tampilan</label>
            <input v-model="form.label" type="text" placeholder="mis. Supervisor" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            <p v-if="form.errors.label" class="mt-1 text-xs text-red-600">{{ form.errors.label }}</p>
        </div>
        <div class="flex-1">
            <label class="mb-1 block text-xs font-semibold text-slate-600">Tipe Role</label>
            <select v-model="form.scope" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <option value="site">Site (operasional, khusus site ini)</option>
                <option value="control_center">Control Center (akses lintas fungsi)</option>
            </select>
            <p v-if="form.errors.scope" class="mt-1 text-xs text-red-600">{{ form.errors.scope }}</p>
        </div>
        <button
            type="submit"
            :disabled="form.processing"
            class="flex items-center gap-1.5 rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60"
        >
            <Plus class="h-4 w-4" /> {{ form.processing ? 'Menambahkan...' : 'Tambah Role' }}
        </button>
    </form>
</template>
