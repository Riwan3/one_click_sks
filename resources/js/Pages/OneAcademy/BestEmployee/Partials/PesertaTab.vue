<script setup>
import { computed, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { Lock, UserPlus, UserX, X } from 'lucide-vue-next';
import { beGetJson } from '@/composables/useBestEmployeeApi';
import SimplePager from './SimplePager.vue';

const props = defineProps({
  campaign: { type: Object, required: true },
  participants: { type: Array, required: true },
  canManage: { type: Boolean, default: false },
});

const locked = computed(() => props.campaign.participants_locked);

/* ============== INJECT MODAL ============== */
const showInject = ref(false);
const filterOptions = ref({ jabatan: [], unit: [], lokasi: [] });
const filters = ref({ q: '', jabatan: '', unit: '', lokasi: '' });
const candRows = ref([]);
const candPage = ref(1);
const candTotal = ref(0);
const candPerPage = ref(20);
const candLoading = ref(false);
const selectedIds = ref(new Set());
const selectAllMode = ref(false);
let searchDebounce = null;

async function loadCandidates(page = 1) {
  candLoading.value = true;
  candPage.value = page;
  try {
    const res = await beGetJson(`/one-academy/best-employee/${props.campaign.id}/participants/candidates`, { ...filters.value, page });
    candRows.value = res.rows;
    candTotal.value = res.total;
    candPerPage.value = res.per_page;
  } finally {
    candLoading.value = false;
  }
}

async function openInject() {
  selectedIds.value = new Set();
  selectAllMode.value = false;
  filters.value = { q: '', jabatan: '', unit: '', lokasi: '' };
  showInject.value = true;
  if (! filterOptions.value.jabatan.length) {
    filterOptions.value = await beGetJson('/one-academy/best-employee/employee-filter-options');
  }
  loadCandidates(1);
}

watch(() => ({ ...filters.value }), () => {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => loadCandidates(1), 300);
}, { deep: true });

function toggleSelect(userId, already) {
  if (already || selectAllMode.value) return;
  if (selectedIds.value.has(userId)) selectedIds.value.delete(userId);
  else selectedIds.value.add(userId);
  selectedIds.value = new Set(selectedIds.value);
}

function selectAllFiltered() {
  selectAllMode.value = true;
  selectedIds.value = new Set();
}

const selectionSummary = computed(() => (selectAllMode.value ? `Semua ${candTotal.value} hasil filter dipilih` : `${selectedIds.value.size} dipilih`));
const canSubmitInject = computed(() => (selectAllMode.value ? candTotal.value > 0 : selectedIds.value.size > 0));

const injectForm = useForm({});
const injectOverrideNeeded = ref(false);
const injectOverrideReason = ref('');

function submitInject() {
  const payload = selectAllMode.value
    ? { select_all: true, ...filters.value }
    : { user_ids: Array.from(selectedIds.value) };
  if (injectOverrideNeeded.value) {
    payload.force = true;
    payload.reason = injectOverrideReason.value;
  }

  injectForm.transform(() => payload).post(`/one-academy/best-employee/${props.campaign.id}/participants/assign`, {
    preserveScroll: true,
    onSuccess: () => {
      showInject.value = false;
      injectOverrideNeeded.value = false;
      injectOverrideReason.value = '';
    },
    onError: () => {
      if (injectForm.errors.reason) injectOverrideNeeded.value = true;
    },
  });
}

/* ============== REMOVE PARTICIPANT ============== */
const removeTarget = ref(null);
const removeOverrideNeeded = ref(false);
const removeReasonText = ref('');
const removeForm = useForm({});

function openRemove(participant) {
  removeTarget.value = participant;
  removeOverrideNeeded.value = false;
  removeReasonText.value = '';
  removeForm.reset();
  removeForm.clearErrors();
}

function submitRemove() {
  const payload = { user_id: removeTarget.value.user_id };
  if (removeOverrideNeeded.value) {
    payload.force = true;
    payload.reason = removeReasonText.value;
  }
  removeForm.transform(() => payload).post(`/one-academy/best-employee/${props.campaign.id}/participants/remove`, {
    preserveScroll: true,
    onSuccess: () => { removeTarget.value = null; },
    onError: () => {
      if (removeForm.errors.reason) removeOverrideNeeded.value = true;
    },
  });
}
</script>

<template>
  <div class="space-y-4">
    <div v-if="locked" class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800 dark:border-amber-900/40 dark:bg-amber-900/10 dark:text-amber-300">
      <Lock class="mt-0.5 h-4 w-4 shrink-0" />
      <p>Peserta terkunci karena kegiatan sudah <strong>{{ campaign.status }}</strong>. Tambah/keluarkan peserta butuh override + alasan.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center justify-between border-b border-slate-100 p-4 dark:border-slate-800">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Peserta Ditugaskan</h3>
        <button v-if="canManage" type="button" class="flex items-center gap-1 rounded-xl bg-amber-500 px-3 py-1.5 text-xs font-bold text-slate-950 shadow" @click="openInject">
          <UserPlus class="h-4 w-4" /> Inject Peserta
        </button>
      </div>

      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 font-bold uppercase text-slate-500 dark:bg-slate-800/50">
          <tr>
            <th class="p-3 pl-4">No</th><th class="p-3">NRP</th><th class="p-3">Nama</th><th class="p-3">Jabatan</th><th class="p-3">Unit</th><th class="p-3">Status</th>
            <th class="p-3 pr-4 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="(p, i) in participants" :key="p.id">
            <td class="p-3 pl-4 font-bold text-slate-400">{{ i + 1 }}</td>
            <td class="p-3"><span class="rounded border border-slate-200 px-2 py-0.5 font-mono dark:border-slate-700">{{ p.user_nrp }}</span></td>
            <td class="p-3 font-bold text-amber-700">{{ p.user_nama }}</td>
            <td class="p-3 text-slate-500">{{ p.user_jabatan || '-' }}</td>
            <td class="p-3 text-slate-500">{{ p.user_unit || '-' }}</td>
            <td class="p-3"><span class="rounded bg-emerald-50 px-2 py-0.5 font-bold text-emerald-700">Assigned</span></td>
            <td class="p-3 pr-4 text-right">
              <button v-if="canManage" type="button" class="rounded p-1.5 text-rose-600 hover:bg-rose-50" @click="openRemove(p)"><UserX class="h-4 w-4" /></button>
            </td>
          </tr>
          <tr v-if="!participants.length">
            <td colspan="7" class="p-8 text-center text-slate-400">Belum ada peserta yang diinject.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Inject Peserta -->
  <div v-if="showInject" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
    <div class="flex max-h-[90vh] w-full max-w-4xl flex-col rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
      <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800">
        <h3 class="text-base font-bold text-slate-900 dark:text-white">Inject Peserta dari Master User</h3>
        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" @click="showInject = false"><X class="h-5 w-5" /></button>
      </div>

      <div class="overflow-y-auto p-5">
        <div class="mb-3 grid grid-cols-1 gap-2 md:grid-cols-4">
          <input v-model="filters.q" type="text" placeholder="Cari NRP atau Nama..." class="rounded-xl border border-slate-200 p-2 text-sm dark:border-slate-700 dark:bg-slate-800">
          <select v-model="filters.jabatan" class="rounded-xl border border-slate-200 p-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            <option value="">Semua Jabatan</option>
            <option v-for="j in filterOptions.jabatan" :key="j" :value="j">{{ j }}</option>
          </select>
          <select v-model="filters.unit" class="rounded-xl border border-slate-200 p-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            <option value="">Semua Unit</option>
            <option v-for="u in filterOptions.unit" :key="u" :value="u">{{ u }}</option>
          </select>
          <select v-model="filters.lokasi" class="rounded-xl border border-slate-200 p-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            <option value="">Semua Lokasi</option>
            <option v-for="l in filterOptions.lokasi" :key="l" :value="l">{{ l }}</option>
          </select>
        </div>
        <p class="mb-2 text-xs text-slate-400">Hanya akun berstatus aktif (FIT) yang dapat ditambahkan sebagai peserta.</p>

        <div class="max-h-72 overflow-y-auto rounded-xl border border-slate-200 dark:border-slate-700">
          <table class="w-full text-left text-xs">
            <thead class="sticky top-0 bg-slate-50 font-bold uppercase text-slate-500 dark:bg-slate-800">
              <tr><th class="p-2 pl-3"></th><th class="p-2">NRP</th><th class="p-2">Nama</th><th class="p-2">Jabatan</th><th class="p-2">Unit</th><th class="p-2">Status</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr v-if="candLoading"><td colspan="6" class="p-4 text-center text-slate-400">Memuat...</td></tr>
              <tr v-else-if="!candRows.length"><td colspan="6" class="p-4 text-center text-slate-400">Tidak ada karyawan aktif yang cocok filter.</td></tr>
              <tr v-for="r in candRows" :key="r.user_id" :class="r.participant_status === 'assigned' ? 'opacity-50' : ''">
                <td class="p-2 pl-3">
                  <input
                    type="checkbox"
                    :disabled="r.participant_status === 'assigned' || selectAllMode"
                    :checked="r.participant_status !== 'assigned' && (selectAllMode || selectedIds.has(r.user_id))"
                    @change="toggleSelect(r.user_id, r.participant_status === 'assigned')"
                  >
                </td>
                <td class="p-2">{{ r.nrp }}</td>
                <td class="p-2 font-semibold">{{ r.name }}</td>
                <td class="p-2">{{ r.jabatan_ss6 || '-' }}</td>
                <td class="p-2">{{ r.unit || '-' }}</td>
                <td class="p-2">
                  <span v-if="r.participant_status === 'assigned'" class="rounded bg-emerald-50 px-2 py-0.5 font-bold text-emerald-700">Sudah Peserta</span>
                  <span v-else class="rounded border border-slate-200 px-2 py-0.5 dark:border-slate-700">Aktif</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-2">
          <SimplePager :page="candPage" :per-page="candPerPage" :total="candTotal" label="karyawan aktif" @change="loadCandidates" />
        </div>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/60">
          <div class="text-sm">
            <span class="font-bold">{{ selectionSummary }}</span>
            <button v-if="!selectAllMode" type="button" class="ml-2 text-xs font-semibold text-amber-700 hover:underline" @click="selectAllFiltered">Pilih semua hasil filter</button>
            <button v-else type="button" class="ml-2 text-xs font-semibold text-rose-600 hover:underline" @click="selectAllMode = false">Batalkan pilih semua</button>
          </div>

          <div v-if="injectOverrideNeeded" class="w-full">
            <textarea v-model="injectOverrideReason" rows="2" placeholder="Alasan override (wajib, kegiatan sudah berjalan)" class="w-full rounded-xl border border-rose-200 p-2 text-xs dark:border-rose-900/40 dark:bg-slate-800"></textarea>
          </div>

          <button type="button" :disabled="!canSubmitInject || injectForm.processing" class="rounded-xl bg-amber-500 px-4 py-2 text-xs font-bold text-slate-950 shadow disabled:opacity-50" @click="submitInject">
            Tambahkan Peserta Terpilih
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Keluarkan Peserta -->
  <div v-if="removeTarget" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
      <h3 class="text-base font-bold text-rose-600">Keluarkan Peserta</h3>
      <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
        <strong>{{ removeTarget.user_nama }}</strong> akan dikeluarkan dari kegiatan (status: removed). Riwayat attempt tetap tersimpan.
      </p>
      <div v-if="removeOverrideNeeded" class="mt-3">
        <p class="mb-1 rounded-lg bg-amber-50 p-2 text-xs text-amber-800 dark:bg-amber-900/10 dark:text-amber-300">{{ removeForm.errors.reason || 'Peserta ini butuh override untuk dikeluarkan.' }}</p>
        <label class="text-xs font-bold text-slate-500">Alasan (wajib)</label>
        <textarea v-model="removeReasonText" rows="2" class="mt-1 w-full rounded-xl border border-slate-200 p-2 text-xs dark:border-slate-700 dark:bg-slate-800"></textarea>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button type="button" class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold dark:bg-slate-800" @click="removeTarget = null">Batal</button>
        <button type="button" class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow" @click="submitRemove">
          Ya, Keluarkan
        </button>
      </div>
    </div>
  </div>
</template>
