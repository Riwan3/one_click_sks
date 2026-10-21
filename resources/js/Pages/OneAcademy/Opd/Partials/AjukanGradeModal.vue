<script setup>
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';

const props = defineProps({
  employee: { type: Object, default: null },
  year: { type: Number, required: true },
});

const emit = defineEmits(['close']);

const TERMINS = [1, 2, 3, 4];
const termin = ref(null);
const isProcessing = ref(false);

const usedTermins = computed(() => props.employee?.usedTermins ?? []);
const firstFreeTermin = computed(() => TERMINS.find((t) => !usedTermins.value.includes(t)) ?? null);

watch(() => props.employee, () => { termin.value = firstFreeTermin.value; });

const handleSubmit = () => {
  router.post('/one-academy/opd/pengajuan-grade/ajukan', { nrp: props.employee.nrp, termin: termin.value }, {
    preserveScroll: true,
    onStart: () => { isProcessing.value = true; },
    onFinish: () => { isProcessing.value = false; },
    onSuccess: () => emit('close'),
  });
};
</script>

<template>
  <BaseModal :open="!!employee" title="Ajukan Grade" size="sm" @close="emit('close')">
    <form v-if="employee" class="space-y-4 p-5" @submit.prevent="handleSubmit">
      <div>
        <p class="text-sm font-bold text-slate-800">{{ employee.name }}</p>
        <p class="text-xs text-slate-500">{{ employee.nrp }} &middot; tahun {{ year }}</p>
      </div>

      <div>
        <p class="mb-2 text-xs font-semibold text-slate-600">Pilih termin</p>
        <div class="grid grid-cols-4 gap-2">
          <button
            v-for="t in TERMINS"
            :key="t"
            type="button"
            :disabled="usedTermins.includes(t)"
            :class="[
              'rounded-lg border py-2 text-sm font-bold transition',
              termin === t ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 text-slate-700 hover:bg-slate-50',
              usedTermins.includes(t) && 'cursor-not-allowed bg-emerald-50 text-emerald-600 opacity-70',
            ]"
            @click="termin = t"
          >
            T{{ t }}
          </button>
        </div>
        <p class="mt-2 text-[11px] text-slate-500">Termin hijau sudah diajukan. Satu karyawan hanya sekali per termin dalam satu tahun.</p>
      </div>

      <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
        <button type="button" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700" @click="emit('close')">Batal</button>
        <button type="submit" :disabled="!termin || isProcessing" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white disabled:opacity-50">Ajukan</button>
      </div>
    </form>
  </BaseModal>
</template>
