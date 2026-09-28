<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Save, Send } from 'lucide-vue-next';
import { FINDING_BASE_URL } from '@/utils/gmpFinding';

const props = defineProps({
  finding: { type: Object, required: true },
});

const emit = defineEmits(['saved']);

const form = useForm({
  action_type: 'save_progress',
  analisis_penyebab: props.finding.root_cause ?? '',
  rencana_tindakan: props.finding.action_plan ?? '',
  tindakan_aktual: props.finding.actual_action ?? '',
  progress: props.finding.progress_percentage ?? 0,
  tanggal_mulai: props.finding.started_at ?? '',
  tanggal_selesai_aktual: props.finding.completed_at ?? '',
  kendala: props.finding.obstacle ?? '',
  foto_progress: null,
  foto_sesudah: null,
});

/** Temuan closed / menunggu verifikasi tidak bisa diubah lagi oleh PIC. */
const isLocked = computed(() => ['closed', 'waiting_verification'].includes(props.finding.status));
const hasPic = computed(() => !!props.finding.pic_user_id);

const fieldClass = 'w-full rounded-lg border-0 bg-slate-100 px-3 py-2 text-sm';
const labelClass = 'mb-1 block text-xs font-bold text-slate-700';

const submit = (actionType) => {
  form.action_type = actionType;
  form.post(`${FINDING_BASE_URL}/${props.finding.id}/progress`, {
    forceFormData: true,
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      form.reset('foto_progress', 'foto_sesudah');
      emit('saved');
    },
  });
};

const evidenceGroups = computed(() => [
  { key: 'foto_progress', label: 'Foto Progress', items: props.finding.evidence?.progress ?? [] },
  { key: 'foto_sesudah', label: 'Foto Sesudah (Selesai)', items: props.finding.evidence?.after ?? [] },
]);
</script>

<template>
  <div>
    <p v-if="!hasPic" class="mb-3 rounded-lg bg-amber-50 p-3 text-xs text-amber-800">Tetapkan PIC terlebih dahulu di tab "PIC" sebelum mengisi tindakan perbaikan.</p>
    <p v-else-if="isLocked" class="mb-3 rounded-lg bg-blue-50 p-3 text-xs text-blue-700">Laporan perbaikan sudah dikirim / temuan sudah closed.</p>

    <form class="space-y-3" @submit.prevent>
      <fieldset :disabled="!hasPic || isLocked || form.processing" class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <div><label :class="labelClass">Analisis Penyebab (Root Cause)</label><textarea v-model="form.analisis_penyebab" rows="2" placeholder="Mengapa hal ini bisa terjadi?" :class="fieldClass" /></div>
        <div><label :class="labelClass">Rencana Tindakan (Action Plan)</label><textarea v-model="form.rencana_tindakan" rows="2" placeholder="Langkah-langkah perbaikan..." :class="fieldClass" /></div>
        <div class="md:col-span-2">
          <label :class="labelClass">Tindakan Aktual (Sudah Dikerjakan) <span class="text-red-600">*</span></label>
          <textarea v-model="form.tindakan_aktual" rows="3" placeholder="Jelaskan tindakan yang sudah dilakukan..." class="w-full rounded-lg border border-blue-400 bg-blue-50 px-3 py-2 text-sm" />
          <p v-if="form.errors.tindakan_aktual" class="mt-1 text-[11px] text-red-600">{{ form.errors.tindakan_aktual }}</p>
        </div>
        <div class="md:col-span-2">
          <label :class="labelClass">Persentase Progress</label>
          <div class="flex items-center gap-3">
            <input v-model.number="form.progress" type="range" min="0" max="100" step="10" class="flex-1" />
            <span class="rounded-md bg-blue-600 px-2.5 py-1 text-sm font-bold text-white">{{ form.progress }}%</span>
          </div>
          <p v-if="form.errors.progress" class="mt-1 text-[11px] text-red-600">{{ form.errors.progress }}</p>
        </div>
        <div><label :class="labelClass">Tanggal Mulai</label><input v-model="form.tanggal_mulai" type="date" :class="fieldClass" /></div>
        <div><label :class="labelClass">Tanggal Selesai Aktual</label><input v-model="form.tanggal_selesai_aktual" type="date" :class="fieldClass" /></div>
        <div class="md:col-span-2"><label :class="labelClass">Kendala di Lapangan</label><input v-model="form.kendala" type="text" placeholder="Opsional..." :class="fieldClass" /></div>
        <div v-for="group in evidenceGroups" :key="group.key">
          <label :class="labelClass">{{ group.label }}</label>
          <input type="file" accept="image/*" :class="fieldClass" @change="form[group.key] = $event.target.files[0] ?? null" />
          <p v-if="form.errors[group.key]" class="mt-1 text-[11px] text-red-600">{{ form.errors[group.key] }}</p>
          <div class="mt-2 flex flex-wrap gap-2">
            <span v-if="group.items.length === 0" class="text-xs italic text-slate-400">Belum ada foto.</span>
            <a v-for="path in group.items" :key="path" :href="path" target="_blank" title="Lihat foto"><img :src="path" alt="" class="h-16 w-16 rounded-lg border border-slate-200 object-cover" /></a>
          </div>
        </div>
      </fieldset>

      <div v-if="hasPic && !isLocked" class="flex flex-wrap justify-end gap-2">
        <button type="button" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-lg border border-blue-600 px-4 py-2 text-sm font-bold text-blue-600 hover:bg-blue-50 disabled:opacity-60" @click="submit('save_progress')">
          <Save class="h-4 w-4" />Simpan Progress
        </button>
        <button type="button" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-bold text-white hover:bg-green-700 disabled:opacity-60" @click="submit('submit_verifikasi')">
          <Send class="h-4 w-4" />Submit untuk Verifikasi
        </button>
      </div>
    </form>
  </div>
</template>
