<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  Award, Plus, Settings, CheckCircle2, Users, 
  RotateCcw, Trophy, FileText, Search
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  menu: Array,
  id: Number,
  tab: String,
  campaigns: Array,
  activeCampaign: Object,
  questions: Array,
  participants: Array,
  attempts: Array,
});

const currentTab = ref(props.tab || 'pengaturan');
const showNewCampaignModal = ref(false);
const showAddQuestionModal = ref(false);
const showAddParticipantModal = ref(false);

const campaignForm = useForm({
  judul: '',
  deskripsi: '',
  aturan: '',
  publish_start: '',
  publish_end: '',
});

const questionForm = useForm({
  campaign_id: props.id,
  tipe_soal: 'multiple_choice',
  pertanyaan: '',
  bobot: 1.0,
  options: [
    { text: '', is_correct: true },
    { text: '', is_correct: false },
    { text: '', is_correct: false },
    { text: '', is_correct: false },
  ],
});

const participantForm = useForm({
  campaign_id: props.id,
  user_nrp: '',
  user_nama: '',
  user_jabatan: '',
  user_unit: '',
});

const submitCampaign = () => {
  campaignForm.post('/one-academy/best-employee/store-campaign', {
    onSuccess: () => {
      showNewCampaignModal.value = false;
      campaignForm.reset();
    }
  });
};

const submitQuestion = () => {
  questionForm.campaign_id = props.id;
  questionForm.post('/one-academy/best-employee/store-question', {
    onSuccess: () => {
      showAddQuestionModal.value = false;
      questionForm.reset();
    }
  });
};

const submitParticipant = () => {
  participantForm.campaign_id = props.id;
  participantForm.post('/one-academy/best-employee/store-participant', {
    onSuccess: () => {
      showAddParticipantModal.value = false;
      participantForm.reset();
    }
  });
};

const resetAttempt = (attemptId) => {
  if (confirm('Yakin ingin mereset attempt peserta ini?')) {
    router.post('/one-academy/best-employee/reset-attempt', { attempt_id: attemptId });
  }
};
</script>

<template>
  <AppLayout :user="user" :menu="menu">
    <Head title="Best Employee Assessment - One Academy" />

    <div class="p-6 space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-amber-950 via-amber-900 to-yellow-900 rounded-2xl p-6 text-white shadow-xl flex justify-between items-center flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-bold flex items-center gap-3">
            <Award class="w-8 h-8 text-amber-400" />
            Assessment Best Employee
          </h1>
          <p class="text-amber-200 text-sm mt-1">
            Kelola kegiatan pemilihan karyawan terbaik, bank soal seleksi, peserta, dan hasil penilaian.
          </p>
        </div>
        <div>
          <button @click="showNewCampaignModal = true" class="btn bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-4 py-2 rounded-xl flex items-center gap-2 shadow-lg transition">
            <Plus class="w-5 h-5" /> Buat Kegiatan Baru
          </button>
        </div>
      </div>

      <!-- Detail Campaign Mode -->
      <div v-if="activeCampaign" class="space-y-6">
        <div class="flex items-center justify-between bg-white dark:bg-slate-900 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
          <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">{{ activeCampaign.judul }}</h2>
            <p class="text-xs text-slate-500">{{ activeCampaign.deskripsi }}</p>
          </div>
          <a href="/one-academy/best-employee" class="text-xs font-semibold text-amber-600 hover:underline">
            &larr; Kembali ke Daftar Kegiatan
          </a>
        </div>

        <!-- Sub Tabs -->
        <div class="flex gap-2 border-b border-slate-200 dark:border-slate-800 pb-2">
          <a :href="`/one-academy/best-employee?id=${id}&tab=pengaturan`" class="px-4 py-2 rounded-lg font-semibold text-xs transition" :class="currentTab === 'pengaturan' ? 'bg-amber-500 text-slate-950' : 'bg-slate-100 text-slate-600'">
            Pengaturan & Aturan
          </a>
          <a :href="`/one-academy/best-employee?id=${id}&tab=soal`" class="px-4 py-2 rounded-lg font-semibold text-xs transition" :class="currentTab === 'soal' ? 'bg-amber-500 text-slate-950' : 'bg-slate-100 text-slate-600'">
            Soal Seleksi ({{ questions.length }})
          </a>
          <a :href="`/one-academy/best-employee?id=${id}&tab=peserta`" class="px-4 py-2 rounded-lg font-semibold text-xs transition" :class="currentTab === 'peserta' ? 'bg-amber-500 text-slate-950' : 'bg-slate-100 text-slate-600'">
            Peserta ({{ participants.length }})
          </a>
          <a :href="`/one-academy/best-employee?id=${id}&tab=monitoring`" class="px-4 py-2 rounded-lg font-semibold text-xs transition" :class="currentTab === 'monitoring' ? 'bg-amber-500 text-slate-950' : 'bg-slate-100 text-slate-600'">
            Monitoring & Hasil
          </a>
        </div>

        <!-- Tab: Soal Seleksi -->
        <div v-if="currentTab === 'soal'" class="space-y-4">
          <div class="flex justify-end">
            <button @click="showAddQuestionModal = true" class="btn bg-amber-600 text-white text-xs px-4 py-2 rounded-xl font-bold flex items-center gap-1 shadow">
              <Plus class="w-4 h-4" /> Tambah Soal
            </button>
          </div>
          <div v-for="(q, qIdx) in questions" :key="q.id" class="bg-white dark:bg-slate-900 rounded-xl p-4 border border-slate-200 dark:border-slate-800 space-y-2 text-xs">
            <div class="font-bold text-slate-900 dark:text-white">{{ qIdx + 1 }}. {{ q.pertanyaan }}</div>
            <div v-if="q.options?.length" class="grid grid-cols-2 gap-2 text-slate-600">
              <div v-for="opt in q.options" :key="opt.id" :class="opt.is_correct ? 'font-bold text-emerald-600' : ''">
                - {{ opt.option_text }}
              </div>
            </div>
          </div>
        </div>

        <!-- Tab: Peserta -->
        <div v-else-if="currentTab === 'peserta'" class="space-y-4">
          <div class="flex justify-end">
            <button @click="showAddParticipantModal = true" class="btn bg-amber-600 text-white text-xs px-4 py-2 rounded-xl font-bold flex items-center gap-1 shadow">
              <Plus class="w-4 h-4" /> Tambah Peserta
            </button>
          </div>
          <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <table class="w-full text-xs text-left">
              <thead class="bg-slate-100 dark:bg-slate-800 font-bold uppercase">
                <tr>
                  <th class="p-3">NRP</th>
                  <th class="p-3">Nama</th>
                  <th class="p-3">Jabatan</th>
                  <th class="p-3">Unit</th>
                  <th class="p-3">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="p in participants" :key="p.id">
                  <td class="p-3 font-mono font-bold">{{ p.user_nrp }}</td>
                  <td class="p-3 font-semibold">{{ p.user_nama }}</td>
                  <td class="p-3">{{ p.user_jabatan }}</td>
                  <td class="p-3">{{ p.user_unit }}</td>
                  <td class="p-3"><span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px]">{{ p.status }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tab: Monitoring & Hasil -->
        <div v-else-if="currentTab === 'monitoring'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
          <table class="w-full text-xs text-left">
            <thead class="bg-slate-100 dark:bg-slate-800 font-bold uppercase">
              <tr>
                <th class="p-3">Peserta</th>
                <th class="p-3">Skor Total</th>
                <th class="p-3">Skor Objektif</th>
                <th class="p-3">Status Attempt</th>
                <th class="p-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="att in attempts" :key="att.id">
                <td class="p-3 font-semibold">{{ att.user_nama }} ({{ att.user_nrp }})</td>
                <td class="p-3 font-bold text-amber-600 text-sm">{{ att.total_score }}</td>
                <td class="p-3">{{ att.objective_score }}</td>
                <td class="p-3"><span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[10px]">{{ att.status }}</span></td>
                <td class="p-3 text-center">
                  <button @click="resetAttempt(att.id)" class="px-3 py-1 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-[10px] font-bold shadow">
                    Reset Attempt
                  </button>
                </td>
              </tr>
              <tr v-if="!attempts.length">
                <td colspan="5" class="text-center p-8 text-slate-400">Belum ada riwayat pengerjaan.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Campaign List Mode -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="c in campaigns" :key="c.id" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 space-y-4 shadow-sm hover:shadow-md transition">
          <div class="flex justify-between items-start">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900">
              {{ c.status }}
            </span>
            <span class="text-xs text-slate-400">{{ c.total_questions }} Soal</span>
          </div>
          <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ c.judul }}</h3>
          <p class="text-xs text-slate-500 line-clamp-2">{{ c.deskripsi || 'Tidak ada deskripsi' }}</p>
          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <span class="text-xs text-slate-400">{{ c.total_participants }} Peserta</span>
            <a :href="`/one-academy/best-employee?id=${c.id}&tab=pengaturan`" class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shadow transition">
              Kelola Kegiatan
            </a>
          </div>
        </div>
        <div v-if="!campaigns.length" class="col-span-full text-center py-12 text-slate-400">
          Belum ada kegiatan Best Employee.
        </div>
      </div>
    </div>

    <!-- Modal Buat Kegiatan Baru -->
    <div v-if="showNewCampaignModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Kegiatan Best Employee Baru</h3>
        <form @submit.prevent="submitCampaign" class="space-y-3">
          <input v-model="campaignForm.judul" placeholder="Judul Kegiatan *" required class="w-full border rounded-xl p-2.5 text-xs" />
          <textarea v-model="campaignForm.deskripsi" placeholder="Deskripsi Singkat" class="w-full border rounded-xl p-2.5 text-xs" rows="3"></textarea>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" @click="showNewCampaignModal = false" class="px-4 py-2 text-xs rounded-xl bg-slate-200 dark:bg-slate-800 font-semibold">Batal</button>
            <button type="submit" class="px-4 py-2 text-xs rounded-xl bg-amber-500 text-slate-950 font-bold shadow">Buat Kegiatan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Tambah Question -->
    <div v-if="showAddQuestionModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Soal Seleksi</h3>
        <form @submit.prevent="submitQuestion" class="space-y-3">
          <textarea v-model="questionForm.pertanyaan" placeholder="Pertanyaan Soal *" required class="w-full border rounded-xl p-2.5 text-xs" rows="2"></textarea>
          <div v-for="(opt, idx) in questionForm.options" :key="idx" class="flex gap-2 items-center">
            <input v-model="opt.text" :placeholder="`Opsi ${idx + 1}`" required class="w-full border rounded-xl p-2 text-xs" />
            <input type="radio" :name="`correct_opt`" :checked="opt.is_correct" @change="questionForm.options.forEach((o, i) => o.is_correct = i === idx)" />
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" @click="showAddQuestionModal = false" class="px-4 py-2 text-xs rounded-xl bg-slate-200 dark:bg-slate-800 font-semibold">Batal</button>
            <button type="submit" class="px-4 py-2 text-xs rounded-xl bg-amber-500 text-slate-950 font-bold shadow">Simpan Soal</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Tambah Participant -->
    <div v-if="showAddParticipantModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Peserta</h3>
        <form @submit.prevent="submitParticipant" class="space-y-3">
          <input v-model="participantForm.user_nrp" placeholder="NRP *" required class="w-full border rounded-xl p-2.5 text-xs" />
          <input v-model="participantForm.user_nama" placeholder="Nama Lengkap *" required class="w-full border rounded-xl p-2.5 text-xs" />
          <input v-model="participantForm.user_jabatan" placeholder="Jabatan" class="w-full border rounded-xl p-2.5 text-xs" />
          <input v-model="participantForm.user_unit" placeholder="Unit / Departemen" class="w-full border rounded-xl p-2.5 text-xs" />
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" @click="showAddParticipantModal = false" class="px-4 py-2 text-xs rounded-xl bg-slate-200 dark:bg-slate-800 font-semibold">Batal</button>
            <button type="submit" class="px-4 py-2 text-xs rounded-xl bg-amber-500 text-slate-950 font-bold shadow">Simpan Peserta</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
