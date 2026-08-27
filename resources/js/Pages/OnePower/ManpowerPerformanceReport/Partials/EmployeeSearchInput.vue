<script setup>
import { ref, watch } from 'vue';
import { Search } from '@lucide/vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'select']);

const query = ref(props.modelValue);
const results = ref([]);
const showResults = ref(false);
let searchTimer = null;

watch(() => props.modelValue, (value) => {
    query.value = value;
});

watch(query, (keyword) => {
    emit('update:modelValue', keyword);
    clearTimeout(searchTimer);
    if (! keyword || keyword.length < 2) {
        results.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/personal-data/mpr/search-employee?keyword=${encodeURIComponent(keyword)}`);
        results.value = await response.json();
        showResults.value = true;
    }, 300);
});

const pick = (employee) => {
    query.value = employee.nrp;
    showResults.value = false;
    emit('select', employee);
};
</script>

<template>
    <div class="relative">
        <div class="relative">
            <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            <input
                v-model="query"
                type="text"
                autocomplete="off"
                placeholder="Cari NRP atau nama karyawan..."
                class="w-full rounded-lg border border-slate-200 py-2 pl-9 pr-3 text-sm outline-none focus:border-blue-500"
                @focus="showResults = true"
                @keyup.enter="$emit('select', { nrp: query })"
            >
        </div>
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
    </div>
</template>
