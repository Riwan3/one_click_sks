<script setup>
import { computed, reactive, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';
import { UNIT_TYPE_LABEL } from '@/utils/fleet';

const props = defineProps({
  target: { type: Object, default: null }, // { type } untuk tambah, { type, unit } untuk ubah
  groupOptions: { type: Array, default: () => [] },
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const form = reactive({ code: '', category: '', default_group: '', default_area: '', default_front: '', default_refueling_time: '', default_distance: 0 });

const isEdit = computed(() => !!props.target?.unit);
const isLoader = computed(() => props.target?.type === 'loader');
const title = computed(() => (props.target ? `${isEdit.value ? 'Ubah' : 'Tambah'} ${UNIT_TYPE_LABEL[props.target.type]}` : ''));

const handleSubmit = () => emit('submit', { ...form, unit_type: props.target.type, default_distance: Number(form.default_distance) || 0 }, props.target.unit?.id ?? null);

watch(() => props.target, (target) => {
  if (!target) return;
  const unit = target.unit ?? {};
  Object.assign(form, {
    code: unit.code ?? '', category: unit.category ?? '', default_group: unit.default_group ?? '', default_area: unit.default_area ?? '',
    default_front: unit.default_front ?? '', default_refueling_time: unit.default_refueling_time ?? '', default_distance: unit.default_distance ?? 0,
  });
});
</script>

<template>
  <BaseModal :open="!!target" :title="title" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <div class="grid grid-cols-2 gap-3">
          <FormInput v-model="form.code" label="KODE UNIT" required maxlength="50" class="uppercase" placeholder="mis. E6107" />
          <FormInput v-model="form.category" label="KATEGORI" maxlength="120" placeholder="mis. EXCAVATOR - 2000" />
        </div>

        <template v-if="isLoader">
          <p class="text-[11px] text-slate-500">Bawaan loader: terisi otomatis saat loader ini ditambahkan ke setting harian (bisa diubah di sana).</p>
          <FormInput v-model="form.default_group" label="GROUP BAWAAN" list="fleet-master-groups" class="uppercase" />
          <datalist id="fleet-master-groups"><option v-for="group in groupOptions" :key="group" :value="group" /></datalist>
          <div class="grid grid-cols-2 gap-3">
            <FormInput v-model="form.default_area" label="AREA" />
            <FormInput v-model="form.default_front" label="FRONT" />
            <FormInput v-model="form.default_refueling_time" label="JAM REFUELING" />
            <FormInput v-model="form.default_distance" type="number" min="0" label="JARAK (M)" />
          </div>
        </template>
      </div>
      <ModalActions :processing="isProcessing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
