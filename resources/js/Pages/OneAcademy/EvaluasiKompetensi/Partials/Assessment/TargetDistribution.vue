<script setup>
import { useForm } from '@inertiajs/vue3';
import { Users, Save, Info, Loader2 } from 'lucide-vue-next';
import { ASSESSMENT_BASE_URL } from '@/utils/academyAssessment';

const props = defineProps({
  assessmentId: { type: Number, required: true },
  targetRoles: { type: Array, default: () => [] },
  selectedRoles: { type: Array, default: () => [] },
  canManage: { type: Boolean, default: false },
});

const form = useForm({ target_roles: [...props.selectedRoles] });

const handleSave = () => form.put(`${ASSESSMENT_BASE_URL}/${props.assessmentId}/targets`, { preserveScroll: true });
</script>

<template>
  <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-100 p-4">
      <h6 class="flex items-center gap-2 text-sm font-bold text-slate-800"><Users class="h-4 w-4 text-rose-600" />Target Distribusi</h6>
      <button
        v-if="canManage"
        type="button"
        :disabled="form.processing"
        class="inline-flex items-center gap-1 rounded-full border border-rose-500 px-3 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 disabled:opacity-60"
        @click="handleSave"
      >
        <component :is="form.processing ? Loader2 : Save" :class="['h-3.5 w-3.5', form.processing && 'animate-spin']" />
        {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
      </button>
    </div>
    <div class="max-h-[420px] overflow-y-auto p-4">
      <p v-if="targetRoles.length === 0" class="flex items-center gap-1 text-[13px] text-slate-500">
        <Info class="h-3.5 w-3.5" />Tidak ada jabatan ditemukan di data karyawan.
      </p>
      <label v-for="role in targetRoles" :key="role" class="mb-1 flex items-start gap-2 text-[13px] text-slate-700">
        <input v-model="form.target_roles" type="checkbox" :value="role" :disabled="!canManage" class="mt-0.5 rounded" />
        {{ role }}
      </label>
    </div>
  </div>
</template>
