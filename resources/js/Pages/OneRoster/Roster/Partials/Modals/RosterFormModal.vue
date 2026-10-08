<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  roster: { type: Object, default: null }, // null = buat roster baru
  me: { type: Object, required: true },
  groups: { type: Array, default: () => [] },
  yearNow: { type: Number, default: () => new Date().getFullYear() },
});

const emit = defineEmits(['close']);

const CREATE_KEYS = ['roster_group', 'year', 'notes', 'pic_name', 'pic_jabatan'];
const EDIT_KEYS = ['roster_group', 'period_start', 'period_end', 'notes', 'pic_name', 'pic_jabatan'];

const form = useForm({ roster_group: '', year: props.yearNow, period_start: '', period_end: '', notes: '', pic_name: '', pic_jabatan: '' });

const isEdit = computed(() => props.roster !== null);
const years = computed(() => Array.from({ length: 5 }, (_, i) => props.yearNow - 1 + i));

const handleSubmit = () => {
  const keys = isEdit.value ? EDIT_KEYS : CREATE_KEYS;
  const url = isEdit.value ? `/one-roster/roster/${props.roster.id}/update` : '/one-roster/roster';
  form.transform((data) => Object.fromEntries(keys.map((key) => [key, data[key]])))
    .post(url, { preserveScroll: true, onSuccess: () => emit('close') });
};

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;
  form.clearErrors();
  form.defaults({
    roster_group: props.roster?.roster_group ?? '',
    year: props.yearNow,
    period_start: props.roster?.period_start ?? '',
    period_end: props.roster?.period_end ?? '',
    notes: props.roster?.notes ?? '',
    pic_name: props.roster?.pic_name || props.me.name,
    pic_jabatan: props.roster?.pic_jabatan || props.me.jabatan,
  }).reset();
});
</script>

<template>
  <BaseModal :open="open" :title="isEdit ? 'Edit Roster' : 'Buat Roster'" @close="emit('close')">
    <form autocomplete="off" @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormAlert :message="form.errors.form" />
        <datalist id="rosterGroupList"><option v-for="group in groups" :key="group" :value="group" /></datalist>
        <FormInput v-model="form.roster_group" label="Roster Group" maxlength="50" list="rosterGroupList" required />
        <template v-if="isEdit">
          <div class="grid grid-cols-2 gap-3">
            <FormInput v-model="form.period_start" label="Period Start" type="date" required />
            <FormInput v-model="form.period_end" label="Period End" type="date" required />
          </div>
        </template>
        <FormSelect v-else v-model="form.year" label="Tahun Roster" hint="Roster dibuat untuk 1 tahun penuh (1 Jan – 31 Des). Status awal: draft." required>
          <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
        </FormSelect>
        <FormInput v-model="form.notes" label="Catatan" maxlength="255" />
        <div class="grid grid-cols-[7fr_5fr] gap-3">
          <FormInput v-model="form.pic_name" label="PIC Roster (Nama)" maxlength="150" placeholder="Nama penanggung jawab" />
          <FormInput v-model="form.pic_jabatan" label="Jabatan PIC" maxlength="100" placeholder="mis. Group Leader Manpower" />
        </div>
        <p class="text-[11px] text-slate-500">Tampil sebagai tanda tangan di Share Roster.</p>
      </div>
      <ModalActions :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
