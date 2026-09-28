<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
  ArrowLeft, Award, CheckCircle2, CircleX, Gauge,
  ListChecks, Plus, Rocket, Settings, ShieldAlert, Trash2, Trophy, Users, X, XCircle,
} from 'lucide-vue-next';

import PengaturanTab from './Partials/PengaturanTab.vue';
import SoalTab from './Partials/SoalTab.vue';
import PesertaTab from './Partials/PesertaTab.vue';
import MonitoringTab from './Partials/MonitoringTab.vue';
import HasilTab from './Partials/HasilTab.vue';

const props = defineProps({
  user: Object,
  menu: Array,
  id: Number,
  tab: String,
  can: Object,
  campaigns: { type: Array, default: null },
  campaign: { type: Object, default: null },
  questions: { type: Array, default: null },
  participants: { type: Array, default: null },
  filterOptions: { type: Object, default: null },
  monitoringSummary: { type: Object, default: null },
  monitoringList: { type: Object, default: null },
  rankingRows: { type: Array, default: null },
  publishReadiness: { type: Object, default: null },
});

const STATUS_META = {
  draft: ['Draft', 'bg-slate-100 text-slate-700'],
  scheduled: ['Terjadwal', 'bg-blue-100 text-blue-700'],
  active: ['Aktif', 'bg-amber-100 text-amber-900'],
  finished: ['Selesai', 'bg-emerald-100 text-emerald-800'],
  cancelled: ['Dibatalkan', 'bg-rose-100 text-rose-800'],
};

const TABS = [
  { key: 'pengaturan', label: 'Pengaturan & Aturan', icon: Settings, always: true },
  { key: 'soal', label: 'Soal Seleksi', icon: ListChecks, always: true },
  { key: 'peserta', label: 'Peserta', icon: Users, always: true },
  { key: 'monitoring', label: 'Monitoring', icon: Gauge, always: false },
  { key: 'hasil', label: 'Hasil', icon: Trophy, always: false },
];

const visibleTabs = computed(() => TABS.filter((t) => t.always || props.can.viewResults));

function goTab(key) {
  router.get('/one-academy/best-employee', { id: props.id, tab: key }, { preserveState: false });
}

/* ============== CREATE CAMPAIGN ============== */
const showCreateModal = ref(false);
const createForm = useForm({ judul: '', deskripsi: '' });

function submitCreate() {
  createForm.post('/one-academy/best-employee', {
    onSuccess: () => { showCreateModal.value = false; createForm.reset(); },
  });
}

/* ============== DELETE CAMPAIGN (list mode, reauth) ============== */
const deleteTarget = ref(null);
const deleteForm = useForm({ confirm_username: '', confirm_password: '' });

function openDelete(c) {
  deleteTarget.value = c;
  deleteForm.reset();
  deleteForm.clearErrors();
}

function submitDelete() {
  deleteForm.delete(`/one-academy/best-employee/${deleteTarget.value.id}`, {
    onSuccess: () => { deleteTarget.value = null; },
  });
}

/* ============== PUBLISH ============== */
const showPublishModal = ref(false);
const publishForm = useForm({});

function submitPublish() {
  publishForm.post(`/one-academy/best-employee/${props.campaign.id}/publish`, {
    preserveScroll: true,
    onSuccess: () => { showPublishModal.value = false; },
  });
}

/* ============== CANCEL ============== */
const showCancelModal = ref(false);
const cancelForm = useForm({ reason: '' });

function submitCancel() {
  cancelForm.post(`/one-academy/best-employee/${props.campaign.id}/cancel`, {
    preserveScroll: true,
    onSuccess: () => { showCancelModal.value = false; cancelForm.reset(); },
  });
}
</script>

<template>
  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <Head title="Assessment Best Employee - One Academy" />

    <div class="space-y-6 p-6">
      <!-- Header Banner -->
      <div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-gradient-to-r from-amber-950 via-amber-900 to-yellow-900 p-6 text-white shadow-xl">
        <div>
          <h1 class="flex items-center gap-3 text-2xl font-bold">
            <Award class="h-8 w-8 text-amber-400" />
            Assessment Best Employee
          </h1>
          <p class="mt-1 text-sm text-amber-200">
            Kelola kegiatan pemilihan karyawan terbaik, bank soal seleksi, peserta, dan hasil penilaian.
          </p>
        </div>
        <button v-if="!campaign && can.manage" type="button" class="flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2 font-bold text-slate-950 shadow-lg transition hover:bg-amber-400" @click="showCreateModal = true">
          <Plus class="h-5 w-5" /> Buat Kegiatan Baru
        </button>
      </div>

      <!-- ================= DETAIL MODE ================= -->
      <div v-if="campaign" class="space-y-6">
        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
          <a href="/one-academy/best-employee" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 shadow-sm hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700">
            <ArrowLeft class="h-5 w-5 text-slate-600 dark:text-slate-300" />
          </a>
          <h2 class="flex-1 text-lg font-bold text-slate-900 dark:text-white">{{ campaign.judul }}</h2>
          <span class="rounded-full px-3 py-1 text-xs font-bold" :class="(STATUS_META[campaign.effective_status] || [campaign.effective_status, 'bg-slate-100'])[1]">
            {{ (STATUS_META[campaign.effective_status] || [campaign.effective_status])[0] }}
          </span>

          <button v-if="can.manage && campaign.status === 'draft'" type="button" class="flex items-center gap-1.5 rounded-full bg-emerald-600 px-3 py-2 text-xs font-bold text-white shadow hover:bg-emerald-500" @click="showPublishModal = true">
            <Rocket class="h-4 w-4" /> Publish Kegiatan
          </button>
          <button v-if="can.manage && ['draft', 'scheduled', 'active'].includes(campaign.status)" type="button" class="flex items-center gap-1.5 rounded-full border border-rose-300 px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50" @click="showCancelModal = true">
            <XCircle class="h-4 w-4" /> Batalkan Kegiatan
          </button>
        </div>

        <div v-if="campaign.settings_critical_locked" class="flex items-start gap-2 rounded-xl border border-blue-200 bg-blue-50 p-3 text-xs text-blue-800 dark:border-blue-900/40 dark:bg-blue-900/10 dark:text-blue-300">
          <ShieldAlert class="mt-0.5 h-4 w-4 shrink-0" />
          <p><strong>Kegiatan sudah dipublish.</strong> Judul/deskripsi/instruksi tetap bisa diubah, tapi jadwal &amp; perilaku assessment terkunci (bisa di-override dengan alasan di tab Pengaturan).</p>
        </div>

        <!-- Sub Tabs -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2 dark:border-slate-800">
          <button
            v-for="t in visibleTabs"
            :key="t.key"
            type="button"
            class="flex items-center gap-1.5 rounded-lg px-4 py-2 text-xs font-semibold transition"
            :class="tab === t.key ? 'bg-amber-500 text-slate-950' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300'"
            @click="goTab(t.key)"
          >
            <component :is="t.icon" class="h-4 w-4" />
            {{ t.label }}
            <span v-if="t.key === 'soal'">({{ campaign.stats.total_questions }})</span>
            <span v-if="t.key === 'peserta'">({{ campaign.stats.total_participants }})</span>
          </button>
        </div>

        <PengaturanTab v-if="tab === 'pengaturan'" :campaign="campaign" :can-manage="can.manage" />
        <SoalTab v-else-if="tab === 'soal'" :campaign="campaign" :questions="questions" :can-manage="can.manage" />
        <PesertaTab v-else-if="tab === 'peserta'" :campaign="campaign" :participants="participants" :can-manage="can.manage" />
        <MonitoringTab
          v-else-if="tab === 'monitoring'"
          :campaign="campaign"
          :filter-options="filterOptions"
          :monitoring-summary="monitoringSummary"
          :monitoring-list="monitoringList"
          :can-reset="can.manage"
          :can-review="can.manage"
        />
        <HasilTab
          v-else-if="tab === 'hasil'"
          :campaign="campaign"
          :filter-options="filterOptions"
          :ranking-rows="rankingRows"
          :can-review="can.manage"
          :can-reset="can.manage"
        />
      </div>

      <!-- ================= LIST MODE ================= -->
      <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="c in campaigns" :key="c.id" class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
          <div class="flex items-start justify-between">
            <span class="rounded-full px-2.5 py-1 text-xs font-bold" :class="(STATUS_META[c.effective_status] || [c.effective_status, 'bg-slate-100'])[1]">
              {{ (STATUS_META[c.effective_status] || [c.effective_status])[0] }}
            </span>
            <div class="flex items-center gap-2">
              <span class="text-xs text-slate-400">{{ c.total_questions }} Soal</span>
              <button v-if="can.manage" type="button" class="rounded-lg p-1 text-rose-500 hover:bg-rose-50" title="Hapus Kegiatan" @click="openDelete(c)">
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
          </div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ c.judul }}</h3>
          <p class="line-clamp-2 text-xs text-slate-500">{{ c.deskripsi || 'Tidak ada deskripsi' }}</p>
          <div class="flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
            <span class="text-xs text-slate-400">{{ c.total_participants }} Peserta</span>
            <a :href="`/one-academy/best-employee?id=${c.id}&tab=pengaturan`" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 shadow transition hover:bg-amber-400">
              Kelola Kegiatan
            </a>
          </div>
        </div>
        <div v-if="!campaigns.length" class="col-span-full py-12 text-center text-slate-400">
          Belum ada kegiatan Best Employee.
        </div>
      </div>
    </div>

    <!-- Modal Buat Kegiatan Baru -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
      <div class="w-full max-w-md space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Kegiatan Best Employee Baru</h3>
        <form class="space-y-3" @submit.prevent="submitCreate">
          <input v-model="createForm.judul" placeholder="Judul Kegiatan *" required class="w-full rounded-xl border p-2.5 text-xs dark:border-slate-700 dark:bg-slate-800">
          <p v-if="createForm.errors.judul" class="text-xs text-rose-600">{{ createForm.errors.judul }}</p>
          <textarea v-model="createForm.deskripsi" placeholder="Deskripsi Singkat" rows="3" class="w-full rounded-xl border p-2.5 text-xs dark:border-slate-700 dark:bg-slate-800"></textarea>
          <p class="text-xs text-slate-400">Jadwal, soal, dan peserta diatur setelah kegiatan dibuat.</p>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" class="rounded-xl bg-slate-200 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="showCreateModal = false">Batal</button>
            <button type="submit" :disabled="createForm.processing" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 shadow disabled:opacity-60">Buat Kegiatan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Hapus Kegiatan (reauth) -->
    <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
      <div class="w-full max-w-md space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
        <h3 class="flex items-center gap-2 text-lg font-bold text-rose-600"><Trash2 class="h-5 w-5" /> Hapus Kegiatan</h3>
        <p class="text-sm text-slate-600 dark:text-slate-300">
          Kegiatan <strong>{{ deleteTarget.judul }}</strong> beserta seluruh soal, peserta, dan riwayat pengerjaannya akan disembunyikan permanen. Masukkan kembali username dan password akun Anda untuk melanjutkan.
        </p>
        <form class="space-y-3" @submit.prevent="submitDelete">
          <div>
            <label class="text-xs font-bold text-slate-500">Username</label>
            <input v-model="deleteForm.confirm_username" type="text" autocomplete="off" required class="mt-1 w-full rounded-xl border p-2.5 text-xs dark:border-slate-700 dark:bg-slate-800">
          </div>
          <div>
            <label class="text-xs font-bold text-slate-500">Password</label>
            <input v-model="deleteForm.confirm_password" type="password" autocomplete="current-password" required class="mt-1 w-full rounded-xl border p-2.5 text-xs dark:border-slate-700 dark:bg-slate-800">
          </div>
          <p v-if="deleteForm.errors.confirm_password" class="text-xs text-rose-600">{{ deleteForm.errors.confirm_password }}</p>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" class="rounded-xl bg-slate-200 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="deleteTarget = null">Batal</button>
            <button type="submit" :disabled="deleteForm.processing" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow disabled:opacity-60">Ya, Hapus Kegiatan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Publish -->
    <div v-if="showPublishModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
      <div class="w-full max-w-md space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center justify-between">
          <h3 class="flex items-center gap-2 text-lg font-bold text-emerald-700"><Rocket class="h-5 w-5" /> Checklist Kesiapan Publish</h3>
          <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="showPublishModal = false"><X class="h-5 w-5" /></button>
        </div>
        <div v-if="publishReadiness" class="divide-y divide-slate-100 dark:divide-slate-800">
          <div v-for="item in publishReadiness.items" :key="item.key" class="flex items-start gap-2 py-2">
            <CheckCircle2 v-if="item.ok" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />
            <CircleX v-else class="mt-0.5 h-5 w-5 shrink-0 text-rose-500" />
            <div>
              <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ item.label }}</p>
              <p class="text-xs text-slate-500">{{ item.detail }}</p>
            </div>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="rounded-xl bg-slate-200 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="showPublishModal = false">Tutup</button>
          <button type="button" :disabled="!publishReadiness?.ready || publishForm.processing" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow disabled:opacity-50" @click="submitPublish">Ya, Publish Sekarang</button>
        </div>
      </div>
    </div>

    <!-- Modal Batalkan Kegiatan -->
    <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
      <div class="w-full max-w-md space-y-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
        <h3 class="flex items-center gap-2 text-lg font-bold text-rose-600"><XCircle class="h-5 w-5" /> Batalkan Kegiatan</h3>
        <p class="text-sm text-slate-600 dark:text-slate-300">Kegiatan yang dibatalkan tidak bisa dipublish ulang. Lanjutkan?</p>
        <div>
          <label class="text-xs font-bold text-slate-500">Alasan (opsional)</label>
          <textarea v-model="cancelForm.reason" rows="2" class="mt-1 w-full rounded-xl border p-2.5 text-xs dark:border-slate-700 dark:bg-slate-800"></textarea>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <button type="button" class="rounded-xl bg-slate-200 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="showCancelModal = false">Batal</button>
          <button type="button" :disabled="cancelForm.processing" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow disabled:opacity-60" @click="submitCancel">Ya, Batalkan</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
