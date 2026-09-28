<script setup>
import { Award, Eye, Download, QrCode, Ban } from 'lucide-vue-next';
import { formatShortDate } from '@/utils/evalProcedure';
import { certificateStatusClass, certificateScoreClass } from '@/utils/procedureCertificate';

defineProps({
  rows: { type: Array, required: true },
  rowOffset: { type: Number, default: 0 },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['preview-requested', 'revoke-requested']);

const actionClass = 'flex h-8 w-8 items-center justify-center rounded-lg text-white shadow-sm';
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <h6 class="flex items-center gap-2 border-b border-slate-100 p-4 text-sm font-bold text-slate-800"><Award class="h-4 w-4 text-rose-600" />Daftar Sertifikat</h6>

    <div v-if="rows.length === 0" class="px-4 py-12 text-center">
      <Award class="mx-auto mb-2 h-8 w-8 text-slate-300" />
      <p class="mb-1 text-sm font-semibold text-slate-500">Belum Ada Sertifikat</p>
      <p class="text-xs text-slate-400">Sertifikat diterbitkan otomatis ketika operator lulus assessment.</p>
    </div>

    <div v-else class="overflow-x-auto">
      <table class="w-full min-w-[1000px] text-left text-xs">
        <thead class="border-b border-slate-200 bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-600">
          <tr>
            <th class="w-10 px-3 py-3">#</th>
            <th class="px-3 py-3">Kode Sertifikat</th>
            <th class="px-3 py-3">NRP</th>
            <th class="px-3 py-3">Nama</th>
            <th class="px-3 py-3">Jabatan</th>
            <th class="px-3 py-3">Prosedur</th>
            <th class="px-3 py-3 text-center">Score</th>
            <th class="px-3 py-3">Diterbitkan</th>
            <th class="px-3 py-3">Berlaku Hingga</th>
            <th class="px-3 py-3 text-center">Status</th>
            <th class="px-3 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-700">
          <tr v-for="(cert, idx) in rows" :key="cert.id" class="hover:bg-slate-50">
            <td class="px-3 py-3 text-slate-400">{{ rowOffset + idx + 1 }}</td>
            <td class="px-3 py-3">
              <span class="rounded-md border border-blue-200 bg-blue-50 px-2 py-0.5 font-mono text-[13px] font-bold text-[#0f3460]">{{ cert.certificate_code }}</span>
            </td>
            <td class="px-3 py-3 font-semibold">{{ cert.nrp }}</td>
            <td class="px-3 py-3 font-semibold text-slate-800">{{ cert.name }}</td>
            <td class="px-3 py-3 text-slate-500">{{ cert.jabatan || '—' }}</td>
            <td class="max-w-[150px] truncate px-3 py-3" :title="cert.procedure_title">{{ cert.procedure_title }}</td>
            <td :class="['px-3 py-3 text-center text-sm font-bold', certificateScoreClass(cert.score)]">{{ Number(cert.score ?? 0).toFixed(1) }}</td>
            <td class="whitespace-nowrap px-3 py-3">{{ formatShortDate(cert.issue_date) }}</td>
            <td class="whitespace-nowrap px-3 py-3">
              {{ cert.is_lifetime ? 'Selamanya' : formatShortDate(cert.valid_until) }}
              <div v-if="cert.status === 'expiring_soon'" class="text-[10px] text-yellow-800">{{ cert.days_left }} hari lagi</div>
            </td>
            <td class="px-3 py-3 text-center">
              <span :class="['rounded-full px-2 py-0.5 text-[10px] font-semibold', certificateStatusClass(cert.status)]">{{ cert.status_label }}</span>
            </td>
            <td class="px-3 py-3">
              <div class="flex justify-center gap-1">
                <button type="button" title="Preview" :class="[actionClass, 'bg-blue-600 hover:bg-blue-700']" @click="emit('preview-requested', cert)"><Eye class="h-4 w-4" /></button>
                <a :href="cert.pdf_url" target="_blank" title="Download PDF" :class="[actionClass, 'bg-green-600 hover:bg-green-700']"><Download class="h-4 w-4" /></a>
                <a :href="cert.verify_url" target="_blank" title="Verifikasi QR" :class="[actionClass, 'bg-cyan-500 hover:bg-cyan-600']"><QrCode class="h-4 w-4" /></a>
                <button
                  v-if="canManage && cert.status !== 'revoked'"
                  type="button"
                  title="Cabut Sertifikat"
                  :class="[actionClass, 'bg-rose-600 hover:bg-rose-700']"
                  @click="emit('revoke-requested', cert)"
                >
                  <Ban class="h-4 w-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <slot name="footer" />
  </div>
</template>
