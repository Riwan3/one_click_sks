<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Gauge, BookOpen, Activity, FileCheck, Award, Search, Plus, FileText } from 'lucide-vue-next';
import { formatShortDate } from '@/utils/evalProcedure';
import MenuSummaryCard from './Partials/Dashboard/MenuSummaryCard.vue';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  search: { type: String, default: '' },
  documents: { type: Object, required: true },
  activities: { type: Array, default: () => [] },
  evaluation: { type: Object, required: true },
  recentDocuments: { type: Array, default: () => [] },
});

const EVALUASI_URL = '/one-procedure/evaluasi';

const searchQuery = ref(props.search);

const statCards = computed(() => [
  { label: 'Dokumen Prosedur Aktif', value: `${props.documents.active} / ${props.documents.total}`, icon: BookOpen, tone: 'bg-rose-50 text-rose-600', href: '/one-procedure/prosedur' },
  { label: 'Kategori Activity', value: props.activities.length, icon: Activity, tone: 'bg-sky-50 text-sky-600', href: '/one-procedure/activity' },
  { label: 'Assessment Published', value: `${props.evaluation.published} / ${props.evaluation.assessments}`, icon: FileCheck, tone: 'bg-purple-50 text-purple-600', href: `${EVALUASI_URL}?tab=assessment` },
  { label: 'Sertifikat Aktif', value: props.evaluation.certificates, icon: Award, tone: 'bg-emerald-50 text-emerald-600', href: `${EVALUASI_URL}?tab=certification` },
]);

const documentRows = computed(() => props.documents.types.map((t) => ({ label: t.name, value: t.total, href: t.url })));

const activityRows = computed(() => props.activities.map((a) => ({ label: a.name, value: `${a.total} dokumen`, href: '/one-procedure/activity' })));

const evaluationRows = computed(() => [
  { label: 'Materi Prosedur', value: props.evaluation.procedures, href: `${EVALUASI_URL}?tab=procedure` },
  { label: 'Assessment', value: props.evaluation.assessments, href: `${EVALUASI_URL}?tab=assessment` },
  { label: 'Karyawan Ditugaskan', value: props.evaluation.assigned, href: `${EVALUASI_URL}?tab=monitoring` },
  { label: 'Karyawan Lulus', value: props.evaluation.passed, href: `${EVALUASI_URL}?tab=monitoring&status=PASS` },
  { label: 'Sertifikat Aktif', value: props.evaluation.certificates, href: `${EVALUASI_URL}?tab=certification` },
]);

const handleSearch = () => {
  router.get('/one-procedure/dashboard', searchQuery.value ? { search: searchQuery.value } : {}, { preserveState: true, preserveScroll: true });
};
</script>

<template>
  <Head title="Dashboard Procedure - One-Procedure" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <div class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="flex items-center gap-2 text-xl font-bold tracking-tight text-slate-800">
            <Gauge class="h-6 w-6 text-rose-500" /> Dashboard Procedure
          </h1>
          <p class="mt-0.5 text-xs text-slate-500">Ringkasan dokumen Prosedur, Activity, dan Evaluasi Prosedur</p>
        </div>
        <Link href="/one-procedure/prosedur" class="flex items-center gap-2 self-start rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 md:self-auto">
          <Plus class="h-4 w-4" /> Tambah Prosedur
        </Link>
      </div>

      <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <Link v-for="card in statCards" :key="card.label" :href="card.href" class="flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm hover:border-rose-300">
          <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl', card.tone]">
            <component :is="card.icon" class="h-5 w-5" />
          </div>
          <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ card.label }}</p>
            <p class="text-xl font-black text-slate-800">{{ card.value }}</p>
          </div>
        </Link>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <MenuSummaryCard
          title="Prosedur"
          description="Dokumen aktif per jenis prosedur."
          href="/one-procedure/prosedur"
          :icon="BookOpen"
          :rows="documentRows"
          empty-text="Belum ada jenis prosedur"
        />
        <MenuSummaryCard
          title="Activity"
          description="Dokumen yang dipetakan ke tiap aktivitas."
          href="/one-procedure/activity"
          :icon="Activity"
          :rows="activityRows"
          empty-text="Belum ada kategori aktivitas"
        />
        <MenuSummaryCard
          title="Evaluasi Prosedur"
          description="Assessment, monitoring kelulusan, dan sertifikat."
          :href="EVALUASI_URL"
          :icon="FileCheck"
          :rows="evaluationRows"
        />
      </div>

      <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-4 sm:flex-row sm:items-center">
          <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-800">
            <FileText class="h-4 w-4 text-rose-500" /> Dokumen Prosedur Terkini
          </h3>
          <form class="relative" @submit.prevent="handleSearch">
            <Search class="absolute left-3 top-2.5 h-3.5 w-3.5 text-slate-400" />
            <input v-model="searchQuery" type="text" placeholder="Cari judul / kode dokumen..." class="w-full rounded-xl border border-slate-300 bg-white py-1.5 pl-8 pr-3 text-xs sm:w-56" />
          </form>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full min-w-[640px] text-left text-xs">
            <thead class="border-b border-slate-100 bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500">
              <tr>
                <th class="w-12 px-4 py-3 text-center">#</th>
                <th class="px-4 py-3">Kode Dokumen</th>
                <th class="px-4 py-3">Judul</th>
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3">Tanggal</th>
                <th class="px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="(doc, idx) in recentDocuments" :key="doc.id" class="hover:bg-slate-50/60">
                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-3 font-mono text-slate-500">{{ doc.kode_dokumen || '—' }}</td>
                <td class="px-4 py-3 font-bold text-slate-800">{{ doc.judul }}</td>
                <td class="px-4 py-3 text-slate-600">{{ doc.jenis_label }}</td>
                <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ formatShortDate(doc.tanggal) }}</td>
                <td class="px-4 py-3">
                  <span :class="['rounded-full px-2 py-0.5 text-[10px] font-bold', doc.status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700']">
                    {{ doc.status === 'aktif' ? 'Aktif' : doc.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="recentDocuments.length === 0">
                <td colspan="6" class="px-4 py-8 text-center text-slate-400">Belum ada dokumen prosedur</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
