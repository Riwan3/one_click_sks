<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    employeeId: { type: Number, required: true },
    tanggal: { type: String, required: true },
    kode: { type: String, default: null },
});

const editing = ref(false);
const value = ref(props.kode ?? '');
const saving = ref(false);

const startEdit = () => {
    value.value = props.kode ?? '';
    editing.value = true;
};

const save = () => {
    saving.value = true;
    router.post('/one-power/roster/cell', {
        employee_id: props.employeeId,
        tanggal: props.tanggal,
        kode: value.value.trim() || null,
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => { saving.value = false; editing.value = false; },
    });
};

const cancel = () => { editing.value = false; };
</script>

<template>
    <div
        v-if="!editing"
        class="flex h-8 w-9 cursor-pointer items-center justify-center rounded text-[0.7rem] font-bold hover:ring-1 hover:ring-blue-300"
        :class="kode ? 'bg-blue-50 text-blue-700' : 'text-slate-300'"
        @click="startEdit"
    >
        {{ kode || '-' }}
    </div>
    <input
        v-else
        v-model="value"
        type="text"
        autofocus
        :disabled="saving"
        class="h-8 w-9 rounded border border-blue-400 text-center text-[0.7rem] font-bold outline-none"
        @keyup.enter="save"
        @keyup.esc="cancel"
        @blur="save"
    >
</template>
