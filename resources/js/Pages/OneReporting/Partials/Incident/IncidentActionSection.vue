<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { INCIDENT_BASE_URL } from '@/utils/odrIncident';
import EvidenceCell from './EvidenceCell.vue';

const props = defineProps({
  incidentId: { type: Number, required: true },
  jenis: { type: String, required: true },
  title: { type: String, required: true },
  emptyText: { type: String, required: true },
  placeholder: { type: String, required: true },
  actions: { type: Array, default: () => [] },
  startNo: { type: Number, default: 1 },
  submitClass: { type: String, default: 'bg-green-600 hover:bg-green-700 text-white' },
});

const fileInput = ref(null);
const form = useForm({ jenis: props.jenis, uraian_tindakan: '', pic: '', evidence: [] });

const handleSubmit = () => {
  form.post(`${INCIDENT_BASE_URL}/${props.incidentId}/tindakan`, {
    forceFormData: true,
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      form.reset('uraian_tindakan', 'pic', 'evidence');
      if (fileInput.value) fileInput.value.value = '';
    },
  });
};

const handleDelete = async (action) => {
  if (await confirmDialog('Hapus tindakan ini?')) router.delete(`${INCIDENT_BASE_URL}/tindakan/${action.id}`, { preserveScroll: true, preserveState: true });
};
</script>

<template>
  <div class="mb-4">
    <div class="rounded-t-lg bg-yellow-200 px-3 py-2 text-xs font-bold uppercase tracking-wide text-amber-900">{{ title }}</div>
    <div class="overflow-x-auto border-x border-slate-200">
      <table class="w-full min-w-[640px] text-left text-[13px] text-slate-700">
        <thead class="bg-slate-50 text-xs font-bold text-slate-600">
          <tr>
            <th class="w-12 px-2 py-2 text-center">No</th>
            <th class="px-2 py-2">Tindakan/Uraian Tindakan</th>
            <th class="w-36 px-2 py-2">PIC</th>
            <th class="px-2 py-2">Evidence</th>
            <th class="w-12 px-2 py-2" />
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="actions.length === 0">
            <td colspan="5" class="py-3 text-center text-slate-400">{{ emptyText }}</td>
          </tr>
          <tr v-for="(action, idx) in actions" :key="action.id" class="align-top">
            <td class="px-2 py-2 text-center">{{ startNo + idx }}</td>
            <td class="whitespace-pre-line px-2 py-2">{{ action.uraian_tindakan }}</td>
            <td class="px-2 py-2">{{ action.pic || '—' }}</td>
            <td class="px-2 py-2"><EvidenceCell :action-id="action.id" :items="action.evidence" /></td>
            <td class="px-2 py-2">
              <button type="button" title="Hapus" class="rounded-md border border-red-500 px-2 py-1 text-red-600 hover:bg-red-50" @click="handleDelete(action)"><Trash2 class="h-3 w-3" /></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <form class="grid grid-cols-1 gap-2 rounded-b-lg border border-t-0 border-slate-200 bg-slate-50 p-2 md:grid-cols-12" @submit.prevent="handleSubmit">
      <textarea v-model="form.uraian_tindakan" rows="2" required :placeholder="placeholder" class="rounded-lg border border-slate-200 px-2 py-1 text-sm md:col-span-6" />
      <input v-model="form.pic" type="text" placeholder="PIC" class="rounded-lg border border-slate-200 px-2 py-1 text-sm md:col-span-3" />
      <input ref="fileInput" type="file" multiple class="rounded-lg border border-slate-200 bg-white px-1 py-1 text-xs md:col-span-2" @change="form.evidence = [...$event.target.files]" />
      <button type="submit" :disabled="form.processing" :class="['flex items-center justify-center rounded-lg py-1.5 disabled:opacity-60 md:col-span-1', submitClass]">
        <Plus class="h-4 w-4" />
      </button>
      <p v-if="form.errors.uraian_tindakan" class="text-[11px] text-red-600 md:col-span-12">{{ form.errors.uraian_tindakan }}</p>
    </form>
  </div>
</template>
