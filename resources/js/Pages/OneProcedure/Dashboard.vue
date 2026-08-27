<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  BookOpen, 
  Activity, 
  FileCheck, 
  CheckCircle2, 
  Search, 
  Plus, 
  ArrowRight,
  ShieldCheck,
  FileText
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  search: String,
  stats: Object,
  recentProcedures: Array,
});

const searchQuery = ref(props.search || '');

const handleSearch = () => {
  router.get('/one-procedure', { search: searchQuery.value }, { preserveState: true });
};
</script>

<template>
  <Head title="Dashboard Procedure - One-Procedure" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-rose-600 mb-1">
            <span>SOP &amp; Knowledge Management</span>
            <span>/</span>
            <span class="text-slate-500">Dashboard</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <BookOpen class="w-6 h-6 text-rose-500" />
            Dashboard Prosedur Operasional
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Kelola dokumen SOP, bank soal activity, dan evaluasi kompetensi prosedur karyawan</p>
        </div>

        <div class="flex items-center gap-2">
          <a href="/one-procedure/prosedur" class="px-4 py-2 text-xs font-semibold rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-sm transition flex items-center gap-2">
            <Plus class="w-4 h-4" />
            Tambah Prosedur SOP
          </a>
        </div>
      </div>

      <!-- Quick Metrics -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
            <BookOpen class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Prosedur</p>
            <p class="text-xl font-black text-slate-800">{{ stats?.totalProcedures || 0 }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <CheckCircle2 class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Prosedur Aktif</p>
            <p class="text-xl font-black text-emerald-600">{{ stats?.activeProcedures || 0 }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
            <Activity class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Bank Activity</p>
            <p class="text-xl font-black text-sky-600">{{ stats?.totalAssessments || 0 }}</p>
          </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
            <FileCheck class="w-5 h-5" />
          </div>
          <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Hasil Evaluasi</p>
            <p class="text-xl font-black text-purple-600">{{ stats?.totalEvaluations || 0 }}</p>
          </div>
        </div>
      </div>

      <!-- Module Navigation Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="/one-procedure/prosedur" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-rose-300 hover:shadow-md transition">
          <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <BookOpen class="w-5 h-5" />
          </div>
          <h3 class="font-bold text-slate-800 text-sm mb-1 group-hover:text-rose-600 flex items-center justify-between">
            Prosedur (SOP)
            <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          </h3>
          <p class="text-xs text-slate-500">Repositori dokumen SOP operasional, tingkat risiko K3, dan versi revisi.</p>
        </a>

        <a href="/one-procedure/activity" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-sky-300 hover:shadow-md transition">
          <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <Activity class="w-5 h-5" />
          </div>
          <h3 class="font-bold text-slate-800 text-sm mb-1 group-hover:text-sky-600 flex items-center justify-between">
            Activity &amp; Assessment
            <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          </h3>
          <p class="text-xs text-slate-500">Kelola bank soal uji pemahaman, passing grade, dan batas timer.</p>
        </a>

        <a href="/one-procedure/evaluasi" class="group bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm hover:border-purple-300 hover:shadow-md transition">
          <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
            <FileCheck class="w-5 h-5" />
          </div>
          <h3 class="font-bold text-slate-800 text-sm mb-1 group-hover:text-purple-600 flex items-center justify-between">
            Evaluasi Prosedur
            <ArrowRight class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" />
          </h3>
          <p class="text-xs text-slate-500">Hasil evaluasi pemahaman karyawan, sertifikat kompetensi, dan remedial.</p>
        </a>
      </div>

      <!-- Recent Procedures Table -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
            <FileText class="w-4 h-4 text-rose-500" />
            Dokumen Prosedur Terkini
          </h3>

          <form @submit.prevent="handleSearch" class="relative">
            <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
            <input type="text" v-model="searchQuery" placeholder="Cari prosedur..." class="pl-8 pr-3 py-1 text-xs border border-slate-300 rounded-xl bg-white w-48" />
          </form>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider text-[10px] font-bold border-b border-slate-100">
              <tr>
                <th class="px-4 py-3 text-center w-12">#</th>
                <th class="px-4 py-3">Judul Prosedur</th>
                <th class="px-4 py-3">Kategori</th>
                <th class="px-4 py-3">Risiko</th>
                <th class="px-4 py-3">Revisi</th>
                <th class="px-4 py-3 text-center">Jumlah Activity</th>
                <th class="px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-for="(p, idx) in recentProcedures" :key="p.id" class="hover:bg-slate-50/60 transition">
                <td class="px-4 py-3 text-center font-bold text-slate-400">{{ idx + 1 }}</td>
                <td class="px-4 py-3 font-bold text-slate-800">{{ p.title }}</td>
                <td class="px-4 py-3 font-medium text-slate-600">{{ p.category }}</td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 text-[10px] font-bold rounded-full uppercase" :class="{
                    'bg-emerald-100 text-emerald-800': p.risk_level === 'low',
                    'bg-amber-100 text-amber-800': p.risk_level === 'medium',
                    'bg-rose-100 text-rose-800': p.risk_level === 'high',
                    'bg-purple-100 text-purple-800': p.risk_level === 'critical',
                  }">
                    {{ p.risk_level }}
                  </span>
                </td>
                <td class="px-4 py-3 font-mono text-slate-500">{{ p.revision }}</td>
                <td class="px-4 py-3 text-center font-bold text-rose-600">{{ p.assessments_count || 0 }}</td>
                <td class="px-4 py-3">
                  <span class="px-2 py-0.5 text-[10px] font-bold rounded-full" :class="p.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700'">
                    {{ p.status === 'active' ? 'Aktif' : p.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="!recentProcedures || recentProcedures.length === 0">
                <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs">
                  Belum ada dokumen prosedur
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
