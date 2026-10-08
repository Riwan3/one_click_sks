<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  rosterId: { type: Number, required: true },
  move: { type: Object, default: null }, // { member, sectionId, position } posisi sekarang; null = modal tertutup
  sections: { type: Array, required: true }, // [{ id, name, total }]
});

const emit = defineEmits(['close']);

const form = useForm({ section_id: '', position: '' });

const target = computed(() => props.sections.find((s) => s.id === Number(form.section_id)));
const isSameSection = computed(() => props.move !== null && Number(form.section_id) === props.move.sectionId);
// Section lain: boleh sesudah anggota terakhir.
const maxPosition = computed(() => (target.value ? target.value.total + (isSameSection.value ? 0 : 1) : 1));
const positions = computed(() => Array.from({ length: maxPosition.value }, (_, i) => i + 1));
const title = computed(() => (isSameSection.value ? 'Ubah Nomor Urut' : 'Pindah ke Section Lain'));

const positionLabel = (n) => `Nomor ${n}${isSameSection.value && n === props.move.position ? ' (sekarang)' : ''}${!isSameSection.value && n === maxPosition.value ? ' (paling akhir)' : ''}`;

const handleSubmit = () => form.post(`/one-roster/roster/${props.rosterId}/members/${props.move.member.id}/move`, {
  preserveScroll: true,
  onSuccess: () => emit('close'),
});

watch(() => props.move, (move) => {
  if (!move) return;
  form.clearErrors();
  form.section_id = move.sectionId;
  form.position = move.position;
});

watch(() => form.section_id, () => {
  if (props.move) form.position = isSameSection.value ? props.move.position : maxPosition.value;
});
</script>

<template>
  <BaseModal :open="move !== null" :title="title" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormAlert :message="form.errors.form" />
        <FormInput :model-value="move?.member.employee_name ?? ''" label="Karyawan" readonly />
        <FormSelect v-model="form.section_id" label="Section tujuan">
          <option v-for="section in sections" :key="section.id" :value="section.id">{{ section.name }}</option>
        </FormSelect>
        <FormSelect
          v-model="form.position" label="Pindah ke nomor"
          hint="Anggota lain bergeser otomatis. Contoh: nomor 1 dipindah ke nomor 3 → nomor 2 jadi 1, nomor 3 jadi 2, dan yang tadi nomor 1 jadi 3."
        >
          <option v-for="n in positions" :key="n" :value="n">{{ positionLabel(n) }}</option>
        </FormSelect>
      </div>
      <ModalActions submit-label="Pindahkan" :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
