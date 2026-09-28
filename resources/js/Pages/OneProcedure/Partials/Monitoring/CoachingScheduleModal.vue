<script setup>
import { useForm } from '@inertiajs/vue3';
import { GraduationCap, X, Save } from 'lucide-vue-next';

const props = defineProps({
  evaluationId: { type: Number, required: true },
  mentors: { type: Array, default: () => [] },
});

const emit = defineEmits(['closed', 'scheduled']);

const form = useForm({
  mentor_id: '',
  weak_material_notes: '',
  coaching_target_date: '',
});

const inputClass = 'w-full rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-rose-400 focus:outline-none focus:ring-2 focus:ring-rose-100';

const handleSubmit = () => {
  form.post(`/one-procedure/evaluasi/monitoring/${props.evaluationId}/coaching`, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => emit('scheduled'),
  });
};
</script>

<template>
  <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 p-4">
    <form class="w-full max-w-md space-y-4 rounded-2xl bg-white p-5 shadow-2xl" @submit.prevent="handleSubmit">
      <div class="flex items-center justify-between">
        <h6 class="flex items-center gap-2 text-sm font-bold text-slate-800">
          <GraduationCap class="h-4 w-4 text-rose-600" /> Jadwalkan Coaching &amp; Mentoring
        </h6>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <div>
        <label class="mb-1 block text-xs font-semibold text-slate-700">Mentor (Pengawas Shift / Trainer) <span class="text-rose-600">*</span></label>
        <select v-model="form.mentor_id" :class="inputClass" required>
          <option value="">-- Pilih Mentor --</option>
          <option v-for="m in mentors" :key="m.id" :value="m.id">{{ m.name }}{{ m.jabatan_ss6 ? ` — ${m.jabatan_ss6}` : '' }}</option>
        </select>
        <p v-if="form.errors.mentor_id" class="mt-1 text-[11px] text-rose-600">{{ form.errors.mentor_id }}</p>
      </div>

      <div>
        <label class="mb-1 block text-xs font-semibold text-slate-700">Catatan Materi Prosedur yang Lemah</label>
        <textarea
          v-model="form.weak_material_notes"
          rows="4"
          :class="inputClass"
          placeholder="Contoh: prosedur lockout-tagout masih belum dipahami..."
        />
        <p v-if="form.errors.weak_material_notes" class="mt-1 text-[11px] text-rose-600">{{ form.errors.weak_material_notes }}</p>
      </div>

      <div>
        <label class="mb-1 block text-xs font-semibold text-slate-700">Tanggal Target Coaching <span class="text-rose-600">*</span></label>
        <input v-model="form.coaching_target_date" type="date" :class="inputClass" required />
        <p v-if="form.errors.coaching_target_date" class="mt-1 text-[11px] text-rose-600">{{ form.errors.coaching_target_date }}</p>
      </div>

      <div class="flex justify-end gap-2">
        <button type="button" class="rounded-full border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-600" @click="emit('closed')">Batal</button>
        <button
          type="submit"
          :disabled="form.processing"
          class="inline-flex items-center gap-1 rounded-full bg-rose-600 px-4 py-1.5 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-60"
        >
          <Save class="h-3.5 w-3.5" /> Simpan Jadwal
        </button>
      </div>
    </form>
  </div>
</template>
