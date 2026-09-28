<script setup>
import { computed } from 'vue';
import { X, Download, QrCode } from 'lucide-vue-next';
import { certificateStatusClass, qrImageUrl } from '@/utils/procedureCertificate';

const props = defineProps({
  certificate: { type: Object, required: true },
});

const emit = defineEmits(['closed']);

const validUntilLabel = computed(() => (props.certificate.is_lifetime ? 'Selamanya' : props.certificate.valid_until));
const score = computed(() => Number(props.certificate.score ?? 0).toFixed(1));
const scoreClass = computed(() => (Number(props.certificate.score) >= 80 ? 'text-green-400' : 'text-yellow-400'));
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl">
      <div class="mb-3 flex items-center justify-between">
        <h5 class="text-base font-bold text-slate-800">Preview Sertifikat</h5>
        <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('closed')"><X class="h-5 w-5" /></button>
      </div>

      <div class="relative mb-4 overflow-hidden rounded-xl bg-gradient-to-br from-[#1a1a2e] to-[#0f3460] p-5 text-white">
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center opacity-[.04]">
          <img src="/images/title.png" class="w-44" alt="" />
        </div>
        <div class="mb-3 text-center">
          <div class="text-[10px] tracking-[2px] text-white/50">SERTIFIKAT KOMPETENSI</div>
          <div class="text-[9px] text-white/30">Certificate of Competency</div>
        </div>
        <div class="mb-2.5 text-center">
          <div class="mb-0.5 text-[10px] text-white/50">Diberikan kepada</div>
          <div class="text-base font-bold italic text-[#c9a84c]">{{ certificate.name }}</div>
          <div class="text-[10px] text-white/50">NRP: {{ certificate.nrp }} &nbsp;|&nbsp; {{ certificate.jabatan || '—' }}</div>
        </div>
        <div class="mb-3 text-center">
          <div class="text-[9px] text-white/40">Telah menyelesaikan</div>
          <div class="mt-0.5 text-[13px] font-semibold text-slate-200">{{ certificate.procedure_title }}</div>
        </div>
        <div class="flex items-end justify-between">
          <div>
            <div class="text-[9px] text-white/40">Nilai</div>
            <div :class="['text-lg font-extrabold', scoreClass]">{{ score }}</div>
          </div>
          <div class="text-center">
            <div class="font-mono text-[10px] tracking-wider text-[#c9a84c]">{{ certificate.certificate_code }}</div>
            <div class="mt-0.5 text-[9px] text-white/30">{{ certificate.issue_date }} — {{ validUntilLabel }}</div>
          </div>
          <img :src="qrImageUrl(certificate.verify_url)" class="h-12 w-12 rounded bg-white" alt="QR" />
        </div>
      </div>

      <div class="mb-3 flex items-center justify-between">
        <span :class="['rounded-full px-3 py-0.5 text-xs font-semibold', certificateStatusClass(certificate.status)]">{{ certificate.status_label }}</span>
        <span class="text-xs text-slate-400">Berlaku: {{ validUntilLabel }}</span>
      </div>

      <div class="flex gap-2">
        <a :href="certificate.pdf_url" target="_blank" class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-rose-600 py-2 text-sm font-semibold text-white hover:bg-rose-700">
          <Download class="h-4 w-4" />Download PDF
        </a>
        <a :href="certificate.verify_url" target="_blank" title="Verifikasi QR" class="inline-flex items-center rounded-full border border-slate-400 px-3 text-slate-600 hover:bg-slate-50">
          <QrCode class="h-4 w-4" />
        </a>
      </div>
    </div>
  </div>
</template>
