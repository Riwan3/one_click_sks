<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ClipboardCheck, BookOpen, SquarePen, BarChart3, Award } from 'lucide-vue-next';
import ProcedureTab from './Partials/Procedure/ProcedureTab.vue';
import AssessmentTab from './Partials/Assessment/AssessmentTab.vue';
import MonitoringTab from './Partials/Monitoring/MonitoringTab.vue';
import CertificationTab from './Partials/Certification/CertificationTab.vue';

defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  isAdminOrGL: Boolean,
  activeTab: { type: String, default: 'procedure' },
  filters: { type: Object, required: true },
  procedures: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  assessment: { type: Object, default: null },
  monitoring: { type: Object, default: null },
  certification: { type: Object, default: null },
});

const TABS = [
  { key: 'procedure', label: 'Procedure', icon: BookOpen },
  { key: 'assessment', label: 'Assessment', icon: SquarePen },
  { key: 'monitoring', label: 'Monitoring', icon: BarChart3 },
  { key: 'certification', label: 'Certification', icon: Award },
];
</script>

<template>
  <AppLayout :user="user" :notifCounts="notifCounts" :menu="menu">
    <Head title="Evaluasi Prosedur - One-Procedure" />

    <div class="space-y-6">
      <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-3">
          <div class="rounded-xl bg-rose-50 p-2.5 text-rose-600">
            <ClipboardCheck class="h-6 w-6" />
          </div>
          <div>
            <h1 class="text-xl font-black tracking-tight text-slate-800">Evaluasi Prosedur</h1>
            <p class="mt-0.5 text-xs text-slate-500">Manajemen pengetahuan, penilaian, dan sertifikasi kompetensi karyawan</p>
          </div>
        </div>
      </div>

      <nav class="flex items-center gap-2 overflow-x-auto border-b border-slate-200/80 pb-3">
        <Link
          v-for="tab in TABS"
          :key="tab.key"
          :href="`/one-procedure/evaluasi?tab=${tab.key}`"
          :class="[
            'flex items-center gap-2 whitespace-nowrap rounded-full px-5 py-2.5 text-xs font-bold transition',
            activeTab === tab.key
              ? 'bg-rose-600 text-white shadow-md shadow-rose-200'
              : 'border border-slate-200 bg-white text-slate-600 hover:bg-rose-50 hover:text-rose-600',
          ]"
        >
          <component :is="tab.icon" class="h-4 w-4" />
          <span>{{ tab.label }}</span>
        </Link>
      </nav>

      <ProcedureTab v-if="activeTab === 'procedure'" :procedures="procedures" :categories="categories" :filters="filters" :can-manage="isAdminOrGL" />
      <AssessmentTab v-else-if="activeTab === 'assessment' && assessment" :assessment="assessment" :procedures="procedures" :can-manage="isAdminOrGL" />
      <MonitoringTab v-else-if="activeTab === 'monitoring' && monitoring" :monitoring="monitoring" :can-manage="isAdminOrGL" />
      <CertificationTab v-else-if="activeTab === 'certification' && certification" :certification="certification" :can-manage="isAdminOrGL" />
    </div>
  </AppLayout>
</template>
