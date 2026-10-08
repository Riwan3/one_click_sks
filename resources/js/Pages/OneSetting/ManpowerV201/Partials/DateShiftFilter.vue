<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import { SHIFT_OPTION_LABEL } from '@/utils/manpowerV201';

const props = defineProps({
  url: { type: String, required: true },
  filters: { type: Object, required: true }, // { date, shift }
});

const form = reactive({ date: props.filters.date, shift: props.filters.shift });

const handleSubmit = () => router.get(props.url, { ...form });
</script>

<template>
  <form class="grid grid-cols-2 items-end gap-2 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm md:grid-cols-[14rem_14rem_auto]" @submit.prevent="handleSubmit">
    <FormInput v-model="form.date" type="date" label="TANGGAL" required />
    <FormSelect v-model="form.shift" label="SHIFT">
      <option v-for="(label, code) in SHIFT_OPTION_LABEL" :key="code" :value="code">{{ label }}</option>
    </FormSelect>
    <button type="submit" class="col-span-2 flex items-center justify-center gap-1.5 rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900 md:col-span-1">
      <Search class="h-4 w-4" /> Tampilkan
    </button>
  </form>
</template>
