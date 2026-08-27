<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: [Number, null], default: null },
    initialLabel: { type: String, default: '' },
    error: { type: String, default: '' },
    placeholder: { type: String, default: 'Cari NRP atau nama karyawan...' },
});

const emit = defineEmits(['update:modelValue']);

const query = ref(props.initialLabel);
const results = ref([]);
const showResults = ref(false);
let searchTimer = null;

watch(query, (keyword) => {
    clearTimeout(searchTimer);
    if (! keyword || keyword.length < 2) {
        results.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/coaching-counseling/search-employee?keyword=${encodeURIComponent(keyword)}`);
        results.value = await response.json();
        showResults.value = true;
    }, 300);
});

const pick = (employee) => {
    emit('update:modelValue', employee.id);
    query.value = `${employee.nrp} - ${employee.name}`;
    showResults.value = false;
};
</script>

<template>
    <div class="relative">
        <input
            v-model="query"
            type="text"
            autocomplete="off"
            :placeholder="placeholder"
            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
            @focus="showResults = true"
        >
        <div v-if="showResults && results.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
            <button
                v-for="employee in results"
                :key="employee.id"
                type="button"
                class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                @click="pick(employee)"
            >
                <span class="font-semibold text-slate-700">{{ employee.nrp }}</span>
                <span class="text-slate-500"> &ndash; {{ employee.name }}</span>
            </button>
        </div>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>
