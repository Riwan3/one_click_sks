<script setup>
import { computed, reactive, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';
import { PARKING_OPTIONS, UNIT_TYPE_LABEL } from '@/utils/fleet';

const props = defineProps({
  target: { type: Object, default: null }, // { type } untuk tambah, { type, unit } untuk ubah
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const form = reactive({ code: '', category: '', parking_code: '' });

const isEdit = computed(() => !!props.target?.unit);
const isHauler = computed(() => props.target?.type === 'hauler');
const title = computed(() => (props.target ? `${isEdit.value ? 'Ubah' : 'Tambah'} ${UNIT_TYPE_LABEL[props.target.type]}` : ''));

const handleSubmit = () => emit('submit', { ...form, unit_type: props.target.type }, props.target.unit?.id ?? null);

watch(() => props.target, (target) => {
  if (!target) return;
  const unit = target.unit ?? {};
  Object.assign(form, { code: unit.code ?? '', category: unit.category ?? '', parking_code: unit.parking_code ?? '' });
});
</script>

<template>
  <BaseModal :open="!!target" :title="title" size="sm" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormInput v-model="form.code" label="KODE UNIT" required maxlength="50" class="uppercase" :placeholder="isHauler ? 'mis. HD78418' : 'mis. E6107'" />
        <FormInput v-model="form.category" label="KATEGORI" maxlength="120" :placeholder="isHauler ? 'mis. HEAVY DUMP TRUCK - KOMATSU 785' : 'mis. EXCAVATOR - 2000'" />
        <FormSelect v-if="isHauler" v-model="form.parking_code" label="PARKIRAN" hint="Dari CSA mana hauler ini parkir. Disalin ke setting harian saat hauler dipasang.">
          <option value="">- Belum diisi -</option>
          <option v-for="option in PARKING_OPTIONS" :key="option.code" :value="option.code">{{ option.label }}</option>
        </FormSelect>
      </div>
      <ModalActions :processing="isProcessing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
