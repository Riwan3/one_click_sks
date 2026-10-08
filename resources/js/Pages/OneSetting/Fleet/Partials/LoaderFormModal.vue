<script setup>
import { computed, reactive, watch } from 'vue';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';

const props = defineProps({
  target: { type: Object, default: null }, // { mode: 'add', cluster?, code? } | { mode: 'edit', loader }
  availableLoaders: { type: Array, default: () => [] }, // master loader aktif yang belum dipasang
  areas: { type: Array, default: () => [] }, // view.areas: cluster tujuan dikelompokkan per area
  isProcessing: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'submit']);

const form = reactive({ loader_code: '', cluster_name: '', area: '', refueling_time: '', distance: 0 });

const isEdit = computed(() => props.target?.mode === 'edit');
const clusterGroups = computed(() => props.areas
  .map((area) => ({ label: area.name ?? 'Tanpa area', names: area.clusters.filter((c) => c.is_editable).map((c) => c.name) }))
  .filter((group) => group.names.length));

const canSubmit = computed(() => !!form.loader_code && (isEdit.value || !!form.cluster_name));

const handleSubmit = () => emit('submit', { ...form, distance: Number(form.distance) || 0 }, isEdit.value);

watch(() => props.target, (target) => {
  if (!target) return;
  const loader = isEdit.value ? target.loader : {};
  Object.assign(form, {
    loader_code: loader.code ?? target.code ?? '',
    cluster_name: target.cluster ?? '',
    area: loader.area ?? '',
    refueling_time: loader.refueling_time ?? '',
    distance: loader.distance ?? 0,
  });
});
</script>

<template>
  <BaseModal :open="!!target" :title="isEdit ? 'Ubah loader' : 'Tambah loader'" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormInput v-if="isEdit" v-model="form.loader_code" label="LOADER" readonly />
        <template v-else>
          <FormSelect v-model="form.cluster_name" label="CLUSTER" required>
            <option value="" disabled>Pilih cluster...</option>
            <optgroup v-for="group in clusterGroups" :key="group.label" :label="group.label">
              <option v-for="name in group.names" :key="name" :value="name">{{ name }}</option>
            </optgroup>
          </FormSelect>
          <p v-if="!clusterGroups.length" class="text-xs text-amber-700">Belum ada cluster. Tambah cluster dulu.</p>
          <FormSelect v-model="form.loader_code" label="LOADER (DARI MASTER UNIT)" required>
            <option value="" disabled>Pilih loader...</option>
            <option v-for="loader in availableLoaders" :key="loader.code" :value="loader.code">{{ loader.code }}{{ loader.category ? ` - ${loader.category}` : '' }}</option>
          </FormSelect>
          <p v-if="!availableLoaders.length" class="text-xs text-amber-700">Semua loader aktif di Master Unit sudah dipasang, atau Master Unit masih kosong.</p>
        </template>

        <div class="grid grid-cols-2 gap-3">
          <FormInput v-model="form.area" label="DUMPING POINT" />
          <FormInput v-model="form.refueling_time" label="JAM REFUELING" placeholder="mis. 10:00" />
          <FormInput v-model="form.distance" type="number" min="0" label="JARAK (M)" />
        </div>
      </div>
      <ModalActions :processing="isProcessing" :disabled="!canSubmit" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
