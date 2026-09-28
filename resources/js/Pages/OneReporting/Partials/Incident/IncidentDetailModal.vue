<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { X, ClipboardList, Calendar, MapPin, Save, Trash2 } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { INCIDENT_BASE_URL, INCIDENT_STATUS_OPTIONS } from '@/utils/odrIncident';
import IncidentActionSection from './IncidentActionSection.vue';

const props = defineProps({
  incident: { type: Object, required: true },
});

const emit = defineEmits(['closed']);

const statusForm = useForm({ status: props.incident.status, target_selesai: props.incident.target_selesai ?? '' });

const handleSaveStatus = () => statusForm.put(`${INCIDENT_BASE_URL}/${props.incident.id}/status`, { preserveScroll: true, preserveState: true });

const handleDelete = async () => {
  if (!(await confirmDialog('Hapus seluruh data insiden ini beserta tindakan & evidence-nya?'))) return;
  router.delete(`${INCIDENT_BASE_URL}/${props.incident.id}`, { preserveScroll: true, onSuccess: () => emit('closed') });
};
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4">
    <div class="my-8 w-full max-w-6xl rounded-2xl bg-white p-5 shadow-2xl">
      <div class="mb-2 flex items-start justify-between gap-3">
        <h6 class="flex items-center gap-2 text-sm font-bold text-slate-800"><ClipboardList class="h-4 w-4 text-red-600" />{{ incident.judul_insiden }}</h6>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <div class="mb-3 flex flex-wrap gap-3 text-[13px] text-slate-500">
        <span class="inline-flex items-center gap-1"><Calendar class="h-3.5 w-3.5" />{{ incident.tanggal_insiden }}</span>
        <span class="inline-flex items-center gap-1"><MapPin class="h-3.5 w-3.5" />{{ incident.area || 'Area belum diisi' }}</span>
      </div>
      <p class="mb-3 whitespace-pre-line text-sm text-slate-500">{{ incident.deskripsi_insiden || 'Tidak ada deskripsi.' }}</p>

      <form class="mb-5 grid grid-cols-1 items-end gap-3 md:grid-cols-3" @submit.prevent="handleSaveStatus">
        <div>
          <label class="mb-1 block text-[13px] font-semibold text-slate-700">Status</label>
          <select v-model="statusForm.status" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
            <option v-for="s in INCIDENT_STATUS_OPTIONS" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
        </div>
        <div>
          <label class="mb-1 block text-[13px] font-semibold text-slate-700">Target Selesai</label>
          <input v-model="statusForm.target_selesai" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm" />
        </div>
        <button type="submit" :disabled="statusForm.processing" class="inline-flex items-center justify-center gap-1 rounded-lg bg-blue-600 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60">
          <Save class="h-4 w-4" />Simpan Status & Target
        </button>
      </form>

      <IncidentActionSection
        :incident-id="incident.id"
        jenis="pencegahan"
        title="Langkah Pencegahan (Preventif Action) — Mencegah Terjadinya Insiden"
        empty-text="Belum ada tindakan pencegahan."
        placeholder="Uraian tindakan pencegahan baru..."
        :actions="incident.pencegahan"
        :start-no="1"
      />
      <IncidentActionSection
        :incident-id="incident.id"
        jenis="mitigasi"
        title="Langkah Mitigasi (Mitigation Action) — Mengurangi Dampak Keparahan Insiden"
        empty-text="Belum ada tindakan mitigasi."
        placeholder="Uraian tindakan mitigasi baru..."
        :actions="incident.mitigasi"
        :start-no="incident.pencegahan.length + 1"
        submit-class="bg-amber-400 hover:bg-amber-500 text-slate-900"
      />

      <div class="flex justify-between pt-1">
        <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-red-500 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50" @click="handleDelete">
          <Trash2 class="h-4 w-4" />Hapus Data Insiden
        </button>
        <button type="button" class="rounded-lg border border-slate-400 px-3 py-1.5 text-sm text-slate-600" @click="emit('closed')">Tutup</button>
      </div>
    </div>
  </div>
</template>
