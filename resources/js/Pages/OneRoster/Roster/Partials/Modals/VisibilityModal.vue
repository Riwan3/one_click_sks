<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';
import { monthLabel } from '@/utils/rosterMatrix';

const props = defineProps({
  open: { type: Boolean, default: false },
  roster: { type: Object, required: true },
  months: { type: Array, required: true }, // 'YYYY-MM' yang dicakup periode roster
});

const emit = defineEmits(['close']);

const form = useForm({ months: [] });

const isPublished = computed(() => props.roster.status === 'published');

const handleSelectAll = () => { form.months = [...props.months]; };
const handleSelectNone = () => { form.months = []; };
const handleSubmit = () => form.post(`/one-roster/roster/${props.roster.id}/visibility`, { preserveScroll: true, onSuccess: () => emit('close') });

watch(() => props.open, (isOpen) => {
  if (!isOpen) return;
  form.clearErrors();
  form.months = (props.roster.visible_months ?? '').split(',').filter(Boolean);
});
</script>

<template>
  <BaseModal :open="open" title="Visibilitas Roster" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormAlert :message="form.errors.form" />
        <p class="text-xs text-slate-500">
          Centang bulan yang boleh dilihat <strong>operator, helper, GL operation</strong>, dan role lain di <strong>Share Roster</strong>. Bulan yang tidak dicentang tampil kosong bagi mereka.
          Admin, Pimpinan, GL Manpower, dan Admin Produksi selalu melihat semua.
        </p>
        <div class="flex gap-2">
          <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50" @click="handleSelectAll">Pilih semua</button>
          <button type="button" class="rounded-lg border border-slate-300 px-2 py-1 text-xs text-slate-600 hover:bg-slate-50" @click="handleSelectNone">Kosongkan</button>
        </div>
        <div class="grid grid-cols-2 gap-2">
          <label v-for="month in months" :key="month" class="flex items-center gap-2 text-sm text-slate-700">
            <input v-model="form.months" type="checkbox" :value="month" class="rounded border-slate-300" />{{ monthLabel(month) }}
          </label>
        </div>
        <p v-if="!isPublished" class="text-[11px] text-slate-500">
          Roster ini masih <strong>{{ roster.status_label }}</strong>: pengaturan tetap tersimpan, tetapi baru tampil di Share Roster setelah roster dipublish.
        </p>
      </div>
      <ModalActions :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
