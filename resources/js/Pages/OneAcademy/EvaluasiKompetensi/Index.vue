<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  ClipboardCheck, BookOpen, Edit3, BarChart2, 
  Plus, Search, CheckCircle, Clock, AlertTriangle, Users
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  menu: Array,
  activeTab: String,
  search: String,
  materiList: Array,
  assessments: Array,
  results: Array,
  jabatanList: Array,
});

const currentTab = ref(props.activeTab || 'materi');
const searchQuery = ref(props.search || '');

const showAddMateriModal = ref(false);
const showAddAssessmentModal = ref(false);
const showAddQuestionModal = ref(false);
const selectedAssessmentForQuestion = ref(null);

const materiForm = useForm({
  title: '',
  category: '',
  revision: 'Rev. 0',
  description: '',
  risk_level: 'medium',
  status: 'active',
  effective_date: '',
  expired_date: '',
});

const assessmentForm = useForm({
  title: '',
  procedure_id: '',
  passing_grade: 70,
  timer_minutes: 30,
  retry_limit: 3,
  validity_months: 6,
});

const questionForm = useForm({
  assessment_id: null,
  question_type: 'multiple_choice',
  question: '',
  option_a: '',
  option_b: '',
  option_c: '',
  option_d: '',
  correct: 'A',
  explanation: '',
});

const handleSearch = () => {
  router.get('/one-academy/evaluasi-kompetensi', {
    tab: currentTab.value,
    search: searchQuery.value,
  }, { preserveState: true });
};

const submitMateri = () => {
  materiForm.post('/one-academy/evaluasi-kompetensi/store-materi', {
    onSuccess: () => {
      showAddMateriModal.value = false;
      materiForm.reset();
    }
  });
};

const submitAssessment = () => {
  assessmentForm.post('/one-academy/evaluasi-kompetensi/store-assessment', {
    onSuccess: () => {
      showAddAssessmentModal.value = false;
      assessmentForm.reset();
    }
  });
};

const openAddQuestion = (astId) => {
  questionForm.assessment_id = astId;
  selectedAssessmentForQuestion.value = astId;
  showAddQuestionModal.value = true;
};

const submitQuestion = () => {
  questionForm.post('/one-academy/evaluasi-kompetensi/store-question', {
    onSuccess: () => {
      showAddQuestionModal.value = false;
      questionForm.reset();
    }
  });
};
</script>

<template>
  <AppLayout :user="user" :menu="menu">
    <Head title="Evaluasi Kompetensi - One Academy" />

    <div class="p-6 space-y-6">
      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-rose-950 via-rose-900 to-red-900 rounded-2xl p-6 text-white shadow-xl flex justify-between items-center flex-wrap gap-4">
        <div>
          <h1 class="text-2xl font-bold flex items-center gap-3">
            <ClipboardCheck class="w-8 h-8 text-rose-400" />
            Evaluasi Kompetensi
          </h1>
          <p class="text-rose-200 text-sm mt-1">
            Manajemen materi training, bank soal assessment, dan monitoring kelulusan kompetensi karyawan.
          </p>
        </div>
        <div class="flex gap-3">
          <button v-if="currentTab === 'materi'" @click="showAddMateriModal = true" class="btn bg-rose-600 hover:bg-rose-500 text-white font-semibold px-4 py-2 rounded-xl flex items-center gap-2 shadow-lg transition">
            <Plus class="w-5 h-5" /> Tambah Materi
          </button>
          <button v-if="currentTab === 'assessment'" @click="showAddAssessmentModal = true" class="btn bg-red-600 hover:bg-red-500 text-white font-semibold px-4 py-2 rounded-xl flex items-center gap-2 shadow-lg transition">
            <Plus class="w-5 h-5" /> Buat Assessment
          </button>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="flex gap-3 border-b border-slate-200 dark:border-slate-800 pb-3">
        <button @click="currentTab = 'materi'" class="px-5 py-2.5 rounded-full font-semibold text-sm transition flex items-center gap-2" :class="currentTab === 'materi' ? 'bg-rose-600 text-white shadow-md' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50'">
          <BookOpen class="w-4 h-4" /> Materi Training
        </button>
        <button @click="currentTab = 'assessment'" class="px-5 py-2.5 rounded-full font-semibold text-sm transition flex items-center gap-2" :class="currentTab === 'assessment' ? 'bg-rose-600 text-white shadow-md' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50'">
          <Edit3 class="w-4 h-4" /> Bank Assessment
        </button>
        <button @click="currentTab = 'monitoring'" class="px-5 py-2.5 rounded-full font-semibold text-sm transition flex items-center gap-2" :class="currentTab === 'monitoring' ? 'bg-rose-600 text-white shadow-md' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 hover:bg-slate-50'">
          <BarChart2 class="w-4 h-4" /> Hasil & Monitoring
        </button>
      </div>

      <!-- Search Bar -->
      <div class="bg-white dark:bg-slate-900 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-800 flex justify-between items-center gap-4">
        <form @submit.prevent="handleSearch" class="flex gap-3 w-full max-w-md">
          <div class="relative w-full">
            <Search class="w-4 h-4 absolute left-3 top-3 text-slate-400" />
            <input v-model="searchQuery" type="text" placeholder="Cari materi atau assessment..." class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-rose-500 outline-none" />
          </div>
          <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-semibold transition">
            Cari
          </button>
        </form>
      </div>

      <!-- Tab Content 1: Materi Training -->
      <div v-if="currentTab === 'materi'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="materi in materiList" :key="materi.id" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-3 shadow-sm hover:shadow-md transition">
          <div class="flex justify-between items-start">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider" :class="materi.risk_level === 'critical' ? 'bg-purple-100 text-purple-800' : (materi.risk_level === 'high' ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800')">
              Risiko {{ materi.risk_level }}
            </span>
            <span class="text-xs text-slate-400 font-mono">{{ materi.revision }}</span>
          </div>
          <h3 class="font-bold text-slate-900 dark:text-white line-clamp-2">{{ materi.title }}</h3>
          <p class="text-xs text-slate-500 line-clamp-3">{{ materi.description || 'Tidak ada deskripsi' }}</p>
          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs text-slate-400">
            <span>{{ materi.category || 'Umum' }}</span>
            <span class="font-semibold text-emerald-600">{{ materi.status }}</span>
          </div>
        </div>
        <div v-if="!materiList.length" class="col-span-full text-center py-12 text-slate-400">
          Belum ada materi training yang diunggah.
        </div>
      </div>

      <!-- Tab Content 2: Bank Assessment -->
      <div v-else-if="currentTab === 'assessment'" class="space-y-4">
        <div v-for="ast in assessments" :key="ast.id" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
          <div class="flex justify-between items-center">
            <div>
              <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ ast.title }}</h3>
              <p class="text-xs text-slate-500">Materi: {{ ast.procedure_title || 'General' }}</p>
            </div>
            <div class="flex items-center gap-3">
              <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                Passing Grade: {{ ast.passing_grade }}%
              </span>
              <button @click="openAddQuestion(ast.id)" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-semibold flex items-center gap-1 shadow">
                <Plus class="w-4 h-4" /> Tambah Soal ({{ ast.total_questions }})
              </button>
            </div>
          </div>

          <!-- Question List -->
          <div v-if="ast.questions.length" class="space-y-2 pt-3 border-t border-slate-100 dark:border-slate-800">
            <div v-for="(q, qIdx) in ast.questions" :key="q.id" class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-xl text-xs space-y-1">
              <div class="font-semibold text-slate-800 dark:text-slate-200">{{ qIdx + 1 }}. {{ q.question }}</div>
              <div class="grid grid-cols-2 gap-2 text-slate-500 pt-1">
                <div :class="q.correct === 'A' ? 'font-bold text-emerald-600' : ''">A. {{ q.option_a }}</div>
                <div :class="q.correct === 'B' ? 'font-bold text-emerald-600' : ''">B. {{ q.option_b }}</div>
                <div :class="q.correct === 'C' ? 'font-bold text-emerald-600' : ''">C. {{ q.option_c }}</div>
                <div :class="q.correct === 'D' ? 'font-bold text-emerald-600' : ''">D. {{ q.option_d }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Content 3: Hasil & Monitoring -->
      <div v-else-if="currentTab === 'monitoring'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <table class="w-full text-xs text-left text-slate-700 dark:text-slate-300">
          <thead class="bg-slate-100 dark:bg-slate-800 font-semibold uppercase">
            <tr>
              <th class="p-3 border-b">Peserta (NRP/Nama)</th>
              <th class="p-3 border-b">Jabatan</th>
              <th class="p-3 border-b">Assessment</th>
              <th class="p-3 border-b text-center">Skor Akhir</th>
              <th class="p-3 border-b text-center">Hasil</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            <tr v-for="res in results" :key="res.id">
              <td class="p-3 font-semibold">{{ res.nama }} ({{ res.nrp }})</td>
              <td class="p-3">{{ res.jabatan }}</td>
              <td class="p-3">{{ res.assessment_title }}</td>
              <td class="p-3 text-center font-bold text-lg">{{ res.score }}</td>
              <td class="p-3 text-center">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold" :class="res.is_passed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
                  {{ res.is_passed ? 'LULUS' : 'GAGAL' }}
                </span>
              </td>
            </tr>
            <tr v-if="!results.length">
              <td colspan="5" class="text-center p-8 text-slate-400">Belum ada data hasil tes assessment.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Tambah Materi -->
    <div v-if="showAddMateriModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Materi Training</h3>
        <form @submit.prevent="submitMateri" class="space-y-3">
          <input v-model="materiForm.title" placeholder="Judul Materi *" required class="w-full border rounded-xl p-2.5 text-xs" />
          <input v-model="materiForm.category" placeholder="Kategori (K3, Operasional, dll)" class="w-full border rounded-xl p-2.5 text-xs" />
          <textarea v-model="materiForm.description" placeholder="Deskripsi Singkat" class="w-full border rounded-xl p-2.5 text-xs" rows="3"></textarea>
          <div class="grid grid-cols-2 gap-3">
            <select v-model="materiForm.risk_level" class="border rounded-xl p-2 text-xs">
              <option value="low">Risiko Low</option>
              <option value="medium">Risiko Medium</option>
              <option value="high">Risiko High</option>
              <option value="critical">Risiko Critical</option>
            </select>
            <select v-model="materiForm.status" class="border rounded-xl p-2 text-xs">
              <option value="active">Status Aktif</option>
              <option value="revision_needed">Perlu Revisi</option>
            </select>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" @click="showAddMateriModal = false" class="px-4 py-2 text-xs rounded-xl bg-slate-200 dark:bg-slate-800 font-semibold">Batal</button>
            <button type="submit" class="px-4 py-2 text-xs rounded-xl bg-rose-600 text-white font-semibold shadow">Simpan</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Tambah Question -->
    <div v-if="showAddQuestionModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 space-y-4 shadow-2xl border border-slate-200 dark:border-slate-800">
        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Soal Multiple Choice</h3>
        <form @submit.prevent="submitQuestion" class="space-y-3">
          <textarea v-model="questionForm.question" placeholder="Pertanyaan *" required class="w-full border rounded-xl p-2.5 text-xs" rows="2"></textarea>
          <div class="grid grid-cols-2 gap-2">
            <input v-model="questionForm.option_a" placeholder="Pilihan A" required class="border rounded-xl p-2 text-xs" />
            <input v-model="questionForm.option_b" placeholder="Pilihan B" required class="border rounded-xl p-2 text-xs" />
            <input v-model="questionForm.option_c" placeholder="Pilihan C" required class="border rounded-xl p-2 text-xs" />
            <input v-model="questionForm.option_d" placeholder="Pilihan D" required class="border rounded-xl p-2 text-xs" />
          </div>
          <div class="flex items-center gap-3">
            <span class="text-xs font-semibold">Kunci Jawaban:</span>
            <select v-model="questionForm.correct" class="border rounded-xl p-2 text-xs w-24">
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
            </select>
          </div>
          <div class="flex justify-end gap-2 pt-3">
            <button type="button" @click="showAddQuestionModal = false" class="px-4 py-2 text-xs rounded-xl bg-slate-200 dark:bg-slate-800 font-semibold">Batal</button>
            <button type="submit" class="px-4 py-2 text-xs rounded-xl bg-rose-600 text-white font-semibold shadow">Simpan Soal</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
