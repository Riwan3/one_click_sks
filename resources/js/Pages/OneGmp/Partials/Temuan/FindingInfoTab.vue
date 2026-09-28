<script setup>
import { computed } from 'vue';
import { Camera } from 'lucide-vue-next';
import { formatFindingDate } from '@/utils/gmpFinding';

const props = defineProps({
  finding: { type: Object, required: true },
});

const sourceRows = computed(() => [
  ['Nomor Penilaian / Sumber', props.finding.assessment_id ? `Penilaian #${props.finding.assessment_id}` : 'Input Langsung'],
  ['Tahun & Week', `${props.finding.year ?? '-'} / ${props.finding.week ?? '-'}`],
  ['Area', props.finding.area_id],
  ['Lokasi Spesifik', props.finding.location_id || '-'],
]);

const sectionTitle = 'mb-3 text-xs font-bold uppercase tracking-wide text-blue-700';
const labelClass = 'text-xs font-bold text-slate-400';
</script>

<template>
  <div class="space-y-5">
    <section>
      <h6 :class="sectionTitle">Data Penilaian GMP</h6>
      <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div v-for="[label, value] in sourceRows" :key="label">
          <div :class="labelClass">{{ label }}</div>
          <div class="font-bold text-slate-800">{{ value }}</div>
        </div>
      </div>
    </section>

    <section class="border-t border-slate-100 pt-4">
      <h6 :class="sectionTitle">Detail Checklist</h6>
      <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <div class="md:col-span-2">
          <div :class="labelClass">Aspek GMP &amp; Item Checklist</div>
          <div class="font-bold text-slate-800">{{ finding.aspect_id || '-' }}</div>
          <div class="text-xs text-slate-500">{{ finding.checklist_id || '-' }}</div>
        </div>
        <div>
          <div :class="labelClass">Standar Seharusnya</div>
          <div class="mt-1 rounded-lg bg-slate-50 p-3 text-sm">{{ finding.standard_description || '-' }}</div>
        </div>
        <div>
          <div :class="labelClass">Deskripsi Temuan</div>
          <div class="mt-1 rounded-lg bg-slate-50 p-3 text-sm text-red-600">{{ finding.finding_description || '-' }}</div>
        </div>
        <div class="md:col-span-2">
          <div :class="[labelClass, 'mb-2']">Foto Sebelum (Evidence)</div>
          <div class="flex h-52 items-center justify-center rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50">
            <a v-if="finding.evidence?.before" :href="finding.evidence.before" target="_blank"><img :src="finding.evidence.before" alt="Foto sebelum" class="max-h-48 rounded-xl" /></a>
            <span v-else class="flex items-center gap-1 text-xs text-slate-400"><Camera class="h-4 w-4" />Tidak ada foto</span>
          </div>
        </div>
        <div>
          <div :class="labelClass">Ditemukan Oleh</div>
          <div class="font-semibold text-slate-800">{{ finding.created_by || 'Sistem' }}</div>
          <div class="text-xs text-slate-400">{{ formatFindingDate(finding.finding_date) }}</div>
        </div>
        <div>
          <div :class="labelClass">Sumber</div>
          <div class="font-semibold text-slate-800">{{ finding.source_type }}</div>
        </div>
      </div>
    </section>
  </div>
</template>
