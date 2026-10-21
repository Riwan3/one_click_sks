<script setup>
import { computed, ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import { useEmployeeSearch } from '@/composables/useEmployeeSearch';

const props = defineProps({
  open: { type: Boolean, default: false },
  year: { type: Number, required: true },
  usedTermins: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const TERMINS = [1, 2, 3, 4];
const keyword = ref('');
const { results, message, search, pick, reset } = useEmployeeSearch('/one-academy/opd/pengajuan-grade/search-employee');
const form = useForm({ nrp: '', tahun: props.year, termin: null });

const freeTermins = computed(() => TERMINS.filter((t) => !(props.usedTermins[form.nrp] ?? []).includes(t)));

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;
  form.reset();
  form.tahun = props.year;
  keyword.value = '';
  reset();
});

const handleInput = () => {
  form.nrp = '';
  form.termin = null;
  search(keyword.value);
};

const handlePick = (employee) => {
  form.nrp = employee.nrp;
  form.termin = freeTermins.value[0] ?? null;
  keyword.value = `${employee.nrp} - ${employee.name}`;
  pick(employee);
};

const handleSubmit = () => form.post('/one-academy/opd/pengajuan-grade', {
  preserveScroll: true,
  onSuccess: () => emit('close'),
});
</script>

<template>
  <BaseModal :open="open" title="Tambah Pengajuan Grade" size="sm" @close="emit('close')">
    <form class="space-y-4 p-5" @submit.prevent="handleSubmit">
      <div class="relative">
        <label class="text-xs font-semibold text-slate-600">Karyawan (NRP / nama)</label>
        <input v-model="keyword" type="text" autocomplete="off" placeholder="Ketik minimal 2 huruf..." class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-blue-500" @input="handleInput">
        <ul v-if="results.length" class="absolute z-10 mt-1 max-h-56 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg">
          <li v-for="e in results" :key="e.nrp" class="flex cursor-pointer gap-2 px-3 py-2 text-xs hover:bg-blue-50" @click="handlePick(e)">
            <b class="min-w-16 text-blue-700">{{ e.nrp }}</b><span>{{ e.name }}</span>
          </li>
        </ul>
        <p v-else-if="message.tone === 'muted'" class="mt-1 text-[11px] text-slate-400">{{ message.text }}</p>
      </div>

      <div v-if="form.nrp">
        <p class="mb-2 text-xs font-semibold text-slate-600">Termin tahun {{ year }}</p>
        <p v-if="!freeTermins.length" class="text-xs text-red-600">Semua termin tahun ini sudah diajukan untuk karyawan ini.</p>
        <div v-else class="grid grid-cols-4 gap-2">
          <button
            v-for="t in freeTermins"
            :key="t"
            type="button"
            :class="['rounded-lg border py-2 text-sm font-bold', form.termin === t ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-200 text-slate-700 hover:bg-slate-50']"
            @click="form.termin = t"
          >
            T{{ t }}
          </button>
        </div>
      </div>

      <p v-if="form.errors.nrp || form.errors.termin" class="text-xs text-red-600">{{ form.errors.nrp ?? form.errors.termin }}</p>

      <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
        <button type="button" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700" @click="emit('close')">Batal</button>
        <button type="submit" :disabled="!form.nrp || !form.termin || form.processing" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white disabled:opacity-50">Tambah</button>
      </div>
    </form>
  </BaseModal>
</template>
