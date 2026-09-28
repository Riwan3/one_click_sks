<script setup>
import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { X, Bell } from 'lucide-vue-next';
import { CERTIFICATE_STATUS_OPTIONS } from '@/utils/procedureCertificate';

const props = defineProps({
  filters: { type: Object, required: true },
  assessments: { type: Array, default: () => [] },
  total: { type: Number, default: 0 },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['filters-changed']);

const form = reactive({ ...props.filters, assessment_id: props.filters.assessment_id || '' });

let searchTimer = null;

const hasActiveFilter = computed(() => Object.values(props.filters).some((v) => v !== '' && v !== 0));

const fieldClass = 'rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20';
const labelClass = 'mb-1 block text-xs font-semibold text-slate-700';

const applyFilters = () => emit('filters-changed', { ...form });

const handleSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(applyFilters, 450);
};

const handleReset = () => {
  Object.keys(form).forEach((key) => { form[key] = ''; });
  applyFilters();
};

const handleReminders = () => router.post('/one-procedure/evaluasi/certificates/reminders', {}, { preserveScroll: true });
</script>

<template>
  <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
    <div class="flex flex-wrap items-end gap-2">
      <div>
        <label :class="labelClass">Assessment</label>
        <select v-model="form.assessment_id" :class="[fieldClass, 'w-full sm:w-56']" @change="applyFilters">
          <option value="">Semua Assessment</option>
          <option v-for="a in assessments" :key="a.id" :value="a.id">{{ a.title }}</option>
        </select>
      </div>
      <div>
        <label :class="labelClass">Status</label>
        <select v-model="form.status" :class="[fieldClass, 'w-40']" @change="applyFilters">
          <option value="">Semua Status</option>
          <option v-for="s in CERTIFICATE_STATUS_OPTIONS" :key="s.value" :value="s.value">{{ s.label }}</option>
        </select>
      </div>
      <div>
        <label :class="labelClass">Tanggal Terbit</label>
        <div class="flex gap-1">
          <input v-model="form.date_from" type="date" :class="fieldClass" @change="applyFilters" />
          <input v-model="form.date_to" type="date" :class="fieldClass" @change="applyFilters" />
        </div>
      </div>
      <div>
        <label :class="labelClass">Cari NRP / Nama</label>
        <input v-model="form.search" type="text" placeholder="NRP / Nama..." :class="[fieldClass, 'w-44']" @input="handleSearchInput" />
      </div>
      <button
        v-if="hasActiveFilter"
        type="button"
        class="inline-flex items-center gap-1 rounded-full border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"
        @click="handleReset"
      >
        <X class="h-3.5 w-3.5" />Reset
      </button>
      <button
        v-if="canManage"
        type="button"
        class="ml-auto inline-flex items-center gap-1 rounded-full border border-amber-500 px-3 py-1.5 text-xs font-semibold text-amber-600 hover:bg-amber-50"
        @click="handleReminders"
      >
        <Bell class="h-3.5 w-3.5" />Kirim Reminder
      </button>
    </div>
    <div class="mt-2 text-xs text-slate-500">{{ total }} sertifikat ditemukan</div>
  </div>
</template>
