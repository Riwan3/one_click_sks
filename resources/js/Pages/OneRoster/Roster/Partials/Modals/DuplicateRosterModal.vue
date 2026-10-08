<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  roster: { type: Object, default: null }, // roster asal; null = modal tertutup
  groups: { type: Array, default: () => [] },
  yearNow: { type: Number, required: true },
});

const emit = defineEmits(['close']);

const form = useForm({ roster_group: '', year: props.yearNow, create_moncu: true });

const sourceName = computed(() => (props.roster ? `${props.roster.name} (${props.roster.period_start.slice(0, 4)})` : ''));
const years = computed(() => Array.from({ length: 8 }, (_, i) => props.yearNow - 1 + i));

const handleSubmit = () => {
  form.post(`/one-roster/roster/${props.roster.id}/duplicate`, { preserveScroll: true, onSuccess: () => emit('close') });
};

watch(() => props.roster, (roster) => {
  if (!roster) return;
  form.clearErrors();
  form.defaults({ roster_group: roster.roster_group, year: Number(roster.period_start.slice(0, 4)) + 1, create_moncu: true }).reset();
});
</script>

<template>
  <BaseModal :open="roster !== null" title="Duplikat Roster" @close="emit('close')">
    <form autocomplete="off" @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormAlert :message="form.errors.form" />
        <FormInput :model-value="sourceName" label="Roster asal" readonly />
        <datalist id="duplicateGroupList"><option v-for="group in groups" :key="group" :value="group" /></datalist>
        <div class="grid grid-cols-[7fr_5fr] gap-3">
          <FormInput v-model="form.roster_group" label="Roster Group" maxlength="50" list="duplicateGroupList" required />
          <FormSelect v-model="form.year" label="Tahun tujuan" required>
            <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
          </FormSelect>
        </div>
        <p class="text-[11px] text-slate-500">Bawaan: tahun berikutnya dari roster asal. Roster baru dibuat 1 tahun penuh dengan status draft.</p>
        <label class="flex items-start gap-2 text-sm text-slate-700">
          <input v-model="form.create_moncu" type="checkbox" class="mt-1 rounded border-slate-300" />
          <span>Langsung daftarkan anggota ke MONCU <span class="text-xs text-slate-500">(yang belum terdaftar; data MONCU yang sudah ada tidak diubah)</span></span>
        </label>
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-slate-600">
          <div class="mb-1 font-semibold">Yang disalin</div>
          Section (urutan &amp; target), anggota (NRP, Unit, Kode Off, pola kerja), dan PIC.
          <div class="mb-1 mt-2 font-semibold">Jadwal</div>
          Dibuat ulang bersih mengikuti putaran Kode Off (siang-malam &amp; OFF). Cuti, MCU, dan isian manual <strong>tidak</strong> ikut disalin.
        </div>
      </div>
      <ModalActions submit-label="Duplikat" :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
