<script setup>
import { useForm } from '@inertiajs/vue3';
import { ShieldCheck, CheckCircle2, CheckCheck } from 'lucide-vue-next';
import { FINDING_BASE_URL, formatFindingDate } from '@/utils/gmpFinding';

const props = defineProps({
  finding: { type: Object, required: true },
});

const emit = defineEmits(['saved']);

const form = useForm({ hasil_verifikasi: '', komentar_verifikator: '', evidence_verifikasi: null });

const handleSubmit = () => form.post(`${FINDING_BASE_URL}/${props.finding.id}/verifikasi`, {
  forceFormData: true, preserveScroll: true, preserveState: true, onSuccess: () => emit('saved'),
});
</script>

<template>
  <div v-if="finding.status === 'closed'" class="py-10 text-center">
    <CheckCircle2 class="mx-auto mb-3 h-12 w-12 text-green-600" />
    <h5 class="font-bold text-green-700">Temuan Sudah Closed</h5>
    <p class="text-xs text-slate-500">Verifikasi berhasil pada {{ formatFindingDate(finding.verified_at) }} oleh {{ finding.verified_by }}</p>
  </div>

  <div v-else-if="finding.status !== 'waiting_verification'" class="py-10 text-center text-slate-400">
    <ShieldCheck class="mx-auto mb-3 h-12 w-12" />
    <h5 class="font-bold text-slate-600">Verifikasi Belum Dapat Dilakukan</h5>
    <p class="text-xs">Verifikasi tersedia setelah PIC mengirim laporan perbaikan (progress 100%).</p>
  </div>

  <form v-else class="space-y-3" @submit.prevent="handleSubmit">
    <h6 class="mb-2 text-xs font-bold uppercase tracking-wide text-blue-700">Keputusan Verifikator</h6>
    <div>
      <label class="mb-1 block text-xs font-bold text-slate-700">Hasil Pemeriksaan Bukti Perbaikan <span class="text-red-600">*</span></label>
      <div class="flex flex-wrap gap-4 text-sm">
        <label class="inline-flex items-center gap-2 font-bold text-green-700"><input v-model="form.hasil_verifikasi" type="radio" value="closed" required />Diterima (Closed)</label>
        <label class="inline-flex items-center gap-2 font-bold text-red-600"><input v-model="form.hasil_verifikasi" type="radio" value="rework" />Ditolak (Rework)</label>
      </div>
      <p v-if="form.errors.hasil_verifikasi" class="mt-1 text-[11px] text-red-600">{{ form.errors.hasil_verifikasi }}</p>
    </div>
    <div>
      <label class="mb-1 block text-xs font-bold text-slate-700">Komentar Verifikator <span class="text-red-600">* Wajib jika Rework</span></label>
      <textarea v-model="form.komentar_verifikator" rows="3" class="w-full rounded-lg border-0 bg-slate-100 px-3 py-2 text-sm" />
      <p v-if="form.errors.komentar_verifikator" class="mt-1 text-[11px] text-red-600">{{ form.errors.komentar_verifikator }}</p>
    </div>
    <div>
      <label class="mb-1 block text-xs font-bold text-slate-700">Evidence Verifikasi Tambahan (Opsional)</label>
      <input type="file" accept="image/*" class="w-full rounded-lg border-0 bg-slate-100 px-3 py-2 text-sm" @change="form.evidence_verifikasi = $event.target.files[0] ?? null" />
    </div>
    <div class="flex justify-end">
      <button type="submit" :disabled="form.processing" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-60">
        <CheckCheck class="h-4 w-4" />Simpan Keputusan
      </button>
    </div>
  </form>
</template>
