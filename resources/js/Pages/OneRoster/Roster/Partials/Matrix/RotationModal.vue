<script setup>
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import BaseModal from '@/Components/ui/BaseModal.vue';
import FormInput from '@/Components/ui/FormInput.vue';
import FormSelect from '@/Components/ui/FormSelect.vue';
import FormAlert from '@/Components/ui/FormAlert.vue';
import ModalActions from '@/Components/ui/ModalActions.vue';
import { KODE_OFF_OPTIONS, WORK_PATTERNS } from '@/utils/rosterMatrix';

const props = defineProps({
  roster: { type: Object, required: true },
  member: { type: Object, default: null }, // anggota yang diatur; null = modal tertutup
});

const emit = defineEmits(['close']);

const form = useForm({ kode_off: '', work_pattern: 'ROTASI', date_from: '', date_to: '', apply: true });

const handleSubmit = () => form.post(`/one-roster/roster/${props.roster.id}/members/${props.member.id}/rotation`, {
  preserveScroll: true,
  onSuccess: () => emit('close'),
});

watch(() => props.member, (member) => {
  if (!member) return;
  form.clearErrors();
  form.defaults({
    kode_off: member.kode_off ?? '',
    work_pattern: member.work_pattern ?? 'ROTASI',
    date_from: props.roster.period_start,
    date_to: props.roster.period_end,
    apply: true,
  }).reset();
});
</script>

<template>
  <BaseModal :open="member !== null" title="Kode Off & Putaran" @close="emit('close')">
    <form @submit.prevent="handleSubmit">
      <div class="space-y-3 px-5 py-4">
        <FormAlert :message="form.errors.form" />
        <FormInput :model-value="member?.employee_name ?? ''" label="Karyawan" readonly />
        <FormSelect v-model="form.kode_off" label="Kode Off">
          <option value="">- manual (tanpa putaran) -</option>
          <option v-for="code in KODE_OFF_OPTIONS" :key="code" :value="code">{{ code }}</option>
        </FormSelect>
        <FormSelect v-model="form.work_pattern" label="Pola Kerja" hint="Full Siang: tanggal OFF tetap sama dengan putaran, tetapi hari malam diganti siang (tidak ada N).">
          <option v-for="[value, label] in WORK_PATTERNS" :key="value" :value="value">{{ label }}</option>
        </FormSelect>
        <div class="grid grid-cols-2 gap-3">
          <FormInput v-model="form.date_from" label="Dari" type="date" :min="roster.period_start" :max="roster.period_end" />
          <FormInput v-model="form.date_to" label="Sampai" type="date" :min="roster.period_start" :max="roster.period_end" />
        </div>
        <label class="flex items-center gap-2 text-sm text-slate-700"><input v-model="form.apply" type="checkbox" class="rounded border-slate-300" />Terapkan putaran D/N/OFF ke jadwal pada rentang ini</label>
        <p class="text-[11px] text-slate-500">
          Putaran mengikuti sheet HELP (siklus 14 hari) dan berlanjut terus. Sel yang sudah berisi <strong>CT, ID, MCU, H, TR</strong> tidak ditimpa.
          Cuti ditarik dari MONCU lewat tombol "Terapkan Cuti (MONCU)"; MCU dan lainnya diisi manual.
        </p>
      </div>
      <ModalActions :processing="form.processing" @cancel="emit('close')" />
    </form>
  </BaseModal>
</template>
