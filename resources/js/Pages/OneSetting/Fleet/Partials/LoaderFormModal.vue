<script setup>
import { computed, reactive, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  target: { type: Object, default: null }, // { mode: 'add' } | { mode: 'edit', loader }
  availableLoaders: { type: Array, default: () => [] }, // master loader aktif yang belum dipasang
  groupOptions: { type: Array, default: () => [] },
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const form = reactive({ loader_code: '', group_name: '', area: '', front: '', refueling_time: '', distance: 0 });

const isEdit = computed(() => props.target?.mode === 'edit');

const fillFrom = (source) => Object.assign(form, {
  group_name: source.group_name ?? '',
  area: source.area ?? '',
  front: source.front ?? '',
  refueling_time: source.refueling_time ?? '',
  distance: source.distance ?? 0,
});

const handleSubmit = () => emit('submit', { ...form, distance: Number(form.distance) || 0 }, isEdit.value);

watch(() => props.target, (target) => {
  if (!target) return;
  form.loader_code = isEdit.value ? target.loader.code : '';
  fillFrom(isEdit.value ? target.loader : {});
});
</script>

<template>
  <BaseModal :open="!!target" :title="isEdit ? 'Ubah loader' : 'Tambah loader'" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormInput v-if="isEdit" v-model="form.loader_code" label="LOADER" readonly />
        <FormSelect v-else v-model="form.loader_code" label="LOADER (DARI MASTER UNIT)" required>
          <option value='' disabled>Pilih loader...</option>
          <option v-for="loader in availableLoaders" :key="loader.code" :value="loader.code">{{ loader.code }}{{ loader.category ? ` - ${loader.category}` : '' }}</option>
        </FormSelect>
        <p v-if="!isEdit && !availableLoaders.length" class="text-xs text-amber-700">Semua loader aktif di Master Unit sudah dipasang, atau Master Unit masih kosong.</p>

        <FormInput v-model="form.group_name" label="GROUP" list="fleet-group-options" placeholder="mis. COAL GETTING" class="uppercase" />
        <datalist id="fleet-group-options"><option v-for="group in groupOptions" :key="group" :value="group" /></datalist>
        <div class="grid grid-cols-2 gap-3">
          <FormInput v-model="form.area" label="AREA" />
          <FormInput v-model="form.front" label="FRONT" />
          <FormInput v-model="form.refueling_time" label="JAM REFUELING" placeholder="mis. 10:00" />
          <FormInput v-model="form.distance" type="number" min="0" label="JARAK (M)" />
        </div>
      </div>
      <ModalActions :processing="isProcessing" :disabled="!form.loader_code" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
