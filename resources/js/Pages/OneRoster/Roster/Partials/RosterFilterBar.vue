<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import { Funnel } from 'lucide-vue-next';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';

const props = defineProps({
  filters: { type: Object, required: true },
  groups: { type: Array, required: true },
});

const STATUS_OPTIONS = [['draft', 'Draft'], ['published', 'Published'], ['archived', 'Archived']];

const form = reactive({ ...props.filters });

const handleApply = () => router.get('/one-roster/roster', form, { preserveScroll: true });
const handleReset = () => router.get('/one-roster/roster');
</script>

<template>
  <form class="grid grid-cols-2 items-end gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4 lg:grid-cols-[1fr_1fr_1.4fr_1fr_auto]" @submit.prevent="handleApply">
    <FormInput v-model="form.date_from" label="Periode mulai" type="date" />
    <FormInput v-model="form.date_to" label="Periode akhir" type="date" />
    <FormSelect v-model="form.roster_group" label="Roster Group">
      <option value="">Semua</option>
      <option v-for="group in groups" :key="group" :value="group">{{ group }}</option>
    </FormSelect>
    <FormSelect v-model="form.status" label="Status">
      <option value="">Semua</option>
      <option v-for="[value, label] in STATUS_OPTIONS" :key="value" :value="value">{{ label }}</option>
    </FormSelect>
    <div class="col-span-2 flex gap-2 md:col-span-4 lg:col-span-1">
      <button type="submit" class="flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-blue-700"><Funnel class="h-4 w-4" />Terapkan</button>
      <button type="button" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50" @click="handleReset">Reset</button>
    </div>
  </form>
</template>
