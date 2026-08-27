<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { 
  Activity, 
  Plus, 
  Pencil, 
  Trash2, 
  HelpCircle, 
  Clock, 
  CheckCircle2, 
  BookOpen,
  X,
  FileQuestion
} from 'lucide-vue-next';

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  selectedProcedureId: String,
  procedures: Array,
  assessments: Array,
});

const filterProcedureId = ref(props.selectedProcedureId || '');

const applyFilter = () => {
  router.get('/one-procedure/activity', { procedure_id: filterProcedureId.value }, { preserveState: true });
};

// Assessment Modal
const showAssessmentModal = ref(false);
const editingAssessment = ref(null);
const assessmentForm = ref({
  id: null,
  procedure_id: props.procedures[0]?.id || '',
  title: '',
  passing_grade: 80,
  timer_minutes: 30,
  retry_limit: 3,
  validity_months: 6,
  status: 'draft',
});

const openAddAssessment = () => {
  editingAssessment.value = null;
  assessmentForm.value = {
    id: null,
    procedure_id: filterProcedureId.value || props.procedures[0]?.id || '',
    title: '',
    passing_grade: 80,
    timer_minutes: 30,
    retry_limit: 3,
    validity_months: 6,
    status: 'draft',
  };
  showAssessmentModal.value = true;
};

const openEditAssessment = (ass) => {
  editingAssessment.value = ass;
  assessmentForm.value = {
    id: ass.id,
    procedure_id: ass.procedure_id,
    title: ass.title,
    passing_grade: ass.passing_grade,
    timer_minutes: ass.timer_minutes,
    retry_limit: ass.retry_limit,
    validity_months: ass.validity_months,
    status: ass.status,
  };
  showAssessmentModal.value = true;
};

const submitAssessment = () => {
  if (assessmentForm.value.id) {
    router.post(`/one-procedure/assessment/update/${assessmentForm.value.id}`, assessmentForm.value, {
      preserveScroll: true,
      onSuccess: () => showAssessmentModal.value = false
    });
  } else {
    router.post('/one-procedure/assessment/store', assessmentForm.value, {
      preserveScroll: true,
      onSuccess: () => showAssessmentModal.value = false
    });
  }
};

const deleteAssessment = (id) => {
  if (confirm('Hapus Activity / Assessment ini?')) {
    router.delete(`/one-procedure/assessment/delete/${id}`, { preserveScroll: true });
  }
};

// Question Modal
const showQuestionModal = ref(false);
const selectedAssessment = ref(null);
const questionForm = ref({
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

const openAddQuestion = (ass) => {
  selectedAssessment.value = ass;
  questionForm.value = {
    assessment_id: ass.id,
    question_type: 'multiple_choice',
    question: '',
    option_a: '',
    option_b: '',
    option_c: '',
    option_d: '',
    correct: 'A',
    explanation: '',
  };
  showQuestionModal.value = true;
};

const submitQuestion = () => {
  router.post('/one-procedure/question/store', questionForm.value, {
    preserveScroll: true,
    onSuccess: () => showQuestionModal.value = false
  });
};

const deleteQuestion = (id) => {
  if (confirm('Hapus soal ini dari bank soal?')) {
    router.delete(`/one-procedure/question/delete/${id}`, { preserveScroll: true });
  }
};
</script>

<template>
  <Head title="Activity & Assessment - One-Procedure" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm">
        <div>
          <div class="flex items-center gap-2 text-xs font-semibold text-sky-600 mb-1">
            <span>SOP &amp; Knowledge Management</span>
            <span>/</span>
            <span class="text-slate-500">Activity</span>
          </div>
          <h1 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
            <Activity class="w-6 h-6 text-sky-500" />
            Activity &amp; Bank Soal Assessment
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">Kelola paket modul evaluasi pemahaman, bank soal, passing grade, dan batas timer</p>
        </div>

        <button @click="openAddAssessment" class="px-4 py-2 text-xs font-semibold rounded-xl bg-sky-600 hover:bg-sky-700 text-white shadow-sm transition flex items-center gap-2">
          <Plus class="w-4 h-4" />
          Buat Activity Assessment
        </button>
      </div>

      <!-- Filter Prosedur Bar -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form @submit.prevent="applyFilter" class="flex items-center gap-3 text-xs">
          <div>
            <label class="block text-[10px] font-bold text-slate-600 uppercase mb-1">Filter Berdasarkan Prosedur</label>
            <select v-model="filterProcedureId" @change="applyFilter" class="px-3 py-1.5 border border-slate-300 rounded-xl bg-slate-50/50 min-w-64">
              <option value="">Semua Prosedur SOP</option>
              <option v-for="p in procedures" :key="p.id" :value="p.id">{{ p.title }}</option>
            </select>
          </div>
        </form>
      </div>

      <!-- Assessment Cards List -->
      <div v-if="assessments && assessments.length > 0" class="space-y-6">
        <div 
          v-for="ass in assessments" 
          :key="ass.id"
          class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden"
        >
          <!-- Assessment Header -->
          <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full uppercase" :class="ass.status === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">
                  {{ ass.status }}
                </span>
                <span class="text-xs font-semibold text-sky-600">Prosedur: {{ ass.procedure?.title || '-' }}</span>
              </div>
              <h3 class="text-base font-bold text-slate-800">{{ ass.title }}</h3>
            </div>

            <!-- Stats Badge Bar -->
            <div class="flex flex-wrap items-center gap-3 text-xs">
              <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 text-center">
                <span class="text-[10px] text-slate-400 font-semibold block">PASSING GRADE</span>
                <span class="font-extrabold text-emerald-600 text-sm">{{ ass.passing_grade }}%</span>
              </div>

              <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 text-center">
                <span class="text-[10px] text-slate-400 font-semibold block">TIMER</span>
                <span class="font-bold text-slate-700 text-xs">{{ ass.timer_minutes }} Mins</span>
              </div>

              <div class="bg-white px-3 py-1.5 rounded-xl border border-slate-200 text-center">
                <span class="text-[10px] text-slate-400 font-semibold block">MAX RETRY</span>
                <span class="font-bold text-slate-700 text-xs">{{ ass.retry_limit }}x</span>
              </div>

              <div class="flex items-center gap-1">
                <button @click="openAddQuestion(ass)" class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-900 hover:bg-slate-800 text-white transition flex items-center gap-1">
                  <Plus class="w-3.5 h-3.5" /> Tambah Soal
                </button>
                <button @click="openEditAssessment(ass)" class="p-2 text-slate-400 hover:text-sky-600 rounded-xl hover:bg-white border border-slate-200 transition">
                  <Pencil class="w-4 h-4" />
                </button>
                <button @click="deleteAssessment(ass.id)" class="p-2 text-slate-400 hover:text-rose-600 rounded-xl hover:bg-white border border-slate-200 transition">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>

          <!-- Question Bank List -->
          <div class="p-5 space-y-3">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
              <span class="flex items-center gap-2">
                <FileQuestion class="w-4 h-4 text-sky-500" />
                Bank Soal Assessment
              </span>
              <span class="text-slate-400 font-normal">Total: {{ ass.questions?.length || 0 }} Soal</span>
            </h4>

            <div v-if="ass.questions && ass.questions.length > 0" class="space-y-2">
              <div 
                v-for="(q, qIdx) in ass.questions" 
                :key="q.id"
                class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60 flex items-start justify-between gap-3 text-xs"
              >
                <div class="space-y-1.5 flex-1">
                  <div class="flex items-center gap-2">
                    <span class="font-black text-slate-400">{{ qIdx + 1 }}.</span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-sky-100 text-sky-800">
                      {{ q.question_type }}
                    </span>
                    <span class="font-bold text-slate-800">{{ q.question }}</span>
                  </div>

                  <!-- Options Grid -->
                  <div v-if="q.option_a" class="grid grid-cols-2 gap-2 pl-6 text-[11px] text-slate-600">
                    <div :class="q.correct === 'A' ? 'font-bold text-emerald-700' : ''">A. {{ q.option_a }}</div>
                    <div :class="q.correct === 'B' ? 'font-bold text-emerald-700' : ''">B. {{ q.option_b }}</div>
                    <div v-if="q.option_c" :class="q.correct === 'C' ? 'font-bold text-emerald-700' : ''">C. {{ q.option_c }}</div>
                    <div v-if="q.option_d" :class="q.correct === 'D' ? 'font-bold text-emerald-700' : ''">D. {{ q.option_d }}</div>
                  </div>

                  <p v-if="q.explanation" class="pl-6 text-[10px] text-amber-700 italic">
                    Kunci Jawaban: {{ q.correct }} &bull; {{ q.explanation }}
                  </p>
                </div>

                <button @click="deleteQuestion(q.id)" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>

            <p v-else class="text-xs text-slate-400 italic py-3 text-center border border-dashed border-slate-200 rounded-xl">
              Belum ada soal ditambahkan untuk activity assessment ini. Klik "Tambah Soal" untuk membuat bank soal.
            </p>
          </div>
        </div>
      </div>

      <div v-else class="bg-white p-12 rounded-2xl border border-slate-200/80 shadow-sm text-center text-slate-400 space-y-2">
        <Activity class="w-12 h-12 mx-auto text-slate-300" />
        <p class="text-sm font-semibold text-slate-600">Belum ada Activity Assessment untuk prosedur ini.</p>
      </div>
    </div>

    <!-- ADD / EDIT ASSESSMENT MODAL -->
    <div v-if="showAssessmentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-bold text-slate-800 text-sm">{{ editingAssessment ? 'Edit Activity Assessment' : 'Buat Activity Assessment Baru' }}</h3>
          <button @click="showAssessmentModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitAssessment" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Prosedur SOP *</label>
            <select v-model="assessmentForm.procedure_id" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50">
              <option v-for="p in procedures" :key="p.id" :value="p.id">{{ p.title }}</option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Judul Activity Assessment *</label>
            <input type="text" v-model="assessmentForm.title" placeholder="Contoh: Uji Pemahaman SOP Pengisian BBM..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Passing Grade (%) *</label>
              <input type="number" v-model="assessmentForm.passing_grade" min="0" max="100" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Timer (Menit) *</label>
              <input type="number" v-model="assessmentForm.timer_minutes" min="5" max="180" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Batas Ulang (Retry)</label>
              <input type="number" v-model="assessmentForm.retry_limit" min="1" max="10" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Status</label>
              <select v-model="assessmentForm.status" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="archived">Archived</option>
              </select>
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" @click="showAssessmentModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="px-4 py-2 font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl">Simpan Activity</button>
          </div>
        </form>
      </div>
    </div>

    <!-- ADD QUESTION MODAL -->
    <div v-if="showQuestionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl border border-slate-100 p-5 space-y-4 text-xs">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-bold text-slate-800 text-sm">Tambah Soal ke {{ selectedAssessment?.title }}</h3>
          <button @click="showQuestionModal = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitQuestion" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Pertanyaan / Soal *</label>
            <textarea v-model="questionForm.question" rows="3" placeholder="Tuliskan pertanyaan evaluasi di sini..." required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Pilihan A</label>
              <input type="text" v-model="questionForm.option_a" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Pilihan B</label>
              <input type="text" v-model="questionForm.option_b" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Pilihan C</label>
              <input type="text" v-model="questionForm.option_c" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Pilihan D</label>
              <input type="text" v-model="questionForm.option_d" class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-700 mb-1">Kunci Jawaban Benar *</label>
              <select v-model="questionForm.correct" required class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50">
                <option value="A">Jawaban A</option>
                <option value="B">Jawaban B</option>
                <option value="C">Jawaban C</option>
                <option value="D">Jawaban D</option>
              </select>
            </div>

            <div>
              <label class="block font-bold text-slate-700 mb-1">Penjelasan / Pembahasan</label>
              <input type="text" v-model="questionForm.explanation" placeholder="Penjelasan singkat..." class="w-full px-3 py-2 border border-slate-300 rounded-xl bg-slate-50/50" />
            </div>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
            <button type="button" @click="showQuestionModal = false" class="px-4 py-2 font-semibold bg-slate-100 rounded-xl">Batal</button>
            <button type="submit" class="px-4 py-2 font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl">Simpan Soal</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
