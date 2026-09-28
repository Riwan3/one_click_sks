<script setup>
import { reactive, computed, ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { ClipboardCheck, Save, Edit2, X, Camera, Trash2, ImagePlus } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: { type: Object, required: true },
    notifCounts: { type: Object, required: true },
    menu: { type: Array, required: true },
    type: { type: String, required: true },
    questions: { type: Object, required: true },
    rubrics: { type: Array, default: () => [] },
    masterLocations: { type: Array, default: () => [] },
});

const form = reactive({
    tipe_gmp: props.type,
    lokasi: props.masterLocations[0] || '',
    penilai: props.user?.displayName || '',
    nrp: props.user?.username || '',
    tanggal_gmp: new Date().toISOString().split('T')[0],
    keterangan: '',
    foto_area: null,
    question_photos: {},
    answers: {},
    editedQuestions: {}, // Simpan perubahan soal
});

// State & handler upload foto area
const fileInputRef = ref(null);
const photoPreview = ref(null);
const photoFileName = ref('');

const triggerPhotoInput = () => {
    fileInputRef.value?.click();
};

const handleFile = (file) => {
    if (!file) return;
    if (!file.type.startsWith('image/')) {
        alert('File harus berupa gambar (JPG, PNG, WEBP).');
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        alert('Ukuran file maksimal 10MB.');
        return;
    }
    form.foto_area = file;
    photoFileName.value = file.name;
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const handleFileSelect = (e) => {
    const file = e.target.files?.[0];
    if (file) handleFile(file);
};

const handleFileDrop = (e) => {
    const file = e.dataTransfer?.files?.[0];
    if (file) handleFile(file);
};

const removePhoto = () => {
    form.foto_area = null;
    photoPreview.value = null;
    photoFileName.value = '';
    if (fileInputRef.value) fileInputRef.value.value = '';
};

// State & handler upload foto per soal
const questionPreviews = reactive({});

const triggerQuestionPhoto = (qNo) => {
    const el = document.getElementById(`q-photo-input-${qNo}`);
    if (el) el.click();
};

const handleQuestionFile = (qNo, file) => {
    if (!file) return;
    if (!file.type.startsWith('image/')) {
        alert('File harus berupa gambar (JPG, PNG, WEBP).');
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        alert('Ukuran file maksimal 10MB.');
        return;
    }
    form.question_photos[qNo] = file;
    const reader = new FileReader();
    reader.onload = (e) => {
        questionPreviews[qNo] = {
            url: e.target.result,
            name: file.name,
        };
    };
    reader.readAsDataURL(file);
};

const handleQuestionFileSelect = (qNo, e) => {
    const file = e.target.files?.[0];
    if (file) handleQuestionFile(qNo, file);
};

const removeQuestionPhoto = (qNo) => {
    delete form.question_photos[qNo];
    delete questionPreviews[qNo];
    const el = document.getElementById(`q-photo-input-${qNo}`);
    if (el) el.value = '';
};

const handlePaste = (e) => {
    const items = e.clipboardData?.items;
    if (!items) return;
    for (const item of items) {
        if (item.type.indexOf('image') !== -1) {
            const file = item.getAsFile();
            if (file) handleFile(file);
            break;
        }
    }
};

onMounted(() => {
    window.addEventListener('paste', handlePaste);
});

onUnmounted(() => {
    window.removeEventListener('paste', handlePaste);
});

// Default skor tertinggi yang tersedia untuk tiap kriteria (bukan selalu 5, tergantung allowed_scores).
Object.keys(props.questions).forEach((qNo) => {
    const options = props.questions[qNo].allowed_scores;
    form.answers[qNo] = options[options.length - 1];
});

// Track soal mana yang sedang diedit
const editingQuestion = ref(null);

// Track cell tabel mana yang sedang diedit (format: "rubricNo-rowIdx-colIdx")
const editingCell = ref(null);
const editingCellValue = ref('');

// Simpan perubahan rubrik tabel
form.editedRubrics = {};

// Gabungkan rubrik (tabel/HTML rujukan skor) dengan section_key dari questions, diurutkan per nomor.
const criteria = computed(() => props.rubrics
    .map((rubric) => ({ ...rubric, section_key: props.questions[rubric.number]?.section_key || 'teknis' }))
    .sort((a, b) => a.number - b.number));

// Get soal original atau yang sudah diedit
const getQuestionData = (qNo) => {
    return form.editedQuestions[qNo] || { ...props.questions[qNo] };
};

// Start editing soal
const startEditQuestion = (qNo) => {
    editingQuestion.value = qNo;
    if (!form.editedQuestions[qNo]) {
        form.editedQuestions[qNo] = { ...props.questions[qNo] };
    }
};

// Cancel edit
const cancelEditQuestion = () => {
    editingQuestion.value = null;
};

// Save edit
const saveEditQuestion = (qNo) => {
    editingQuestion.value = null;
};

// Start edit cell
const startEditCell = (rubricNo, rowIdx, colIdx, currentValue) => {
    editingCell.value = `${rubricNo}-${rowIdx}-${colIdx}`;
    editingCellValue.value = currentValue || '';
};

// Save cell edit
const saveCellEdit = (rubricNo, rowIdx, colIdx) => {
    if (!form.editedRubrics[rubricNo]) {
        form.editedRubrics[rubricNo] = JSON.parse(JSON.stringify(props.rubrics.find(r => r.number === rubricNo) || {}));
    }

    if (form.editedRubrics[rubricNo].rows && form.editedRubrics[rubricNo].rows[rowIdx]) {
        // Update cells array dengan reactivity
        const updatedCells = [...form.editedRubrics[rubricNo].rows[rowIdx].cells];
        updatedCells[colIdx] = editingCellValue.value;
        form.editedRubrics[rubricNo].rows[rowIdx].cells = updatedCells;
    }

    editingCell.value = null;
    editingCellValue.value = '';
};

// Cancel cell edit
const cancelCellEdit = () => {
    editingCell.value = null;
    editingCellValue.value = '';
};

// Get cell value (original or edited)
const getCellValue = (rubricNo, rowIdx, colIdx, row) => {
    if (form.editedRubrics[rubricNo]?.rows?.[rowIdx]?.cells?.[colIdx]) {
        return form.editedRubrics[rubricNo].rows[rowIdx].cells[colIdx];
    }
    return row.cells[colIdx] ?? '';
};

// Get available scoring options - dinamis berdasarkan semua rows yang punya ANY content (include edited)
const getAvailableScores = (rubricNo, rubric) => {
    // Force reactivity dengan mengakses form.editedRubrics secara explicit
    const editedRubricData = form.editedRubrics[rubricNo];

    if (!rubric.rows) {
        // Fallback untuk non-table format
        const questionData = props.questions[rubricNo];
        return (questionData && questionData.allowed_scores) ? questionData.allowed_scores : (rubric.options || []);
    }

    // Untuk table format: scan SEMUA rows untuk check apakah ada ANY cell content (termasuk edited)
    const rubricData = editedRubricData || rubric;
    const scoresWithContent = [];

    if (rubricData.rows) {
        rubricData.rows.forEach(row => {
            // Check if row punya ANY cell yang tidak kosong (regardless of black/original)
            const hasContent = row.cells && row.cells.some(cell => {
                return cell && typeof cell === 'string' && cell.trim() !== '';
            });

            if (hasContent) {
                scoresWithContent.push(row.score);
            }
        });
    }

    return scoresWithContent.length > 0 ? scoresWithContent : [];
};



const sectionLabel = {
    teknis: 'Teknis',
    keselamatan: 'Keselamatan',
    optimalisasi: 'Optimalisasi',
    lingkungan: 'Lingkungan',
};

const handleSubmit = () => {
    // Include edited questions & rubrics di form submission
    const submitData = {
        ...form,
        editedQuestions: Object.keys(form.editedQuestions).length > 0 ? form.editedQuestions : undefined,
        editedRubrics: Object.keys(form.editedRubrics).length > 0 ? form.editedRubrics : undefined,
    };
    router.post('/one-gmp/penilaian/store', submitData, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head :title="`Form Penilaian GMP - ${type}`" />

    <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" :back-url="`/one-gmp/area/${type}`">
        <!-- Banner Header -->
        <div class="mb-6 rounded-3xl p-6 text-white shadow-md flex justify-between items-center flex-wrap gap-4" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
            <div>
                <h1 class="mb-1 text-2xl font-extrabold flex items-center gap-3">
                    <ClipboardCheck class="w-8 h-8 text-emerald-200" />
                    Form Penilaian GMP: {{ type }}
                </h1>
                <p class="m-0 text-sm text-white/90">
                    Isi lembar kriteria evaluasi inspeksi Good Mining Practice untuk kategori {{ type }}. Gunakan prinsip skor terendah yang paling menggambarkan kondisi aktual di lapangan.
                </p>
            </div>
        </div>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Metadata Header Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-sm font-extrabold text-slate-800 mb-4 uppercase tracking-wider">Identitas Penilaian</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Lokasi / Equipment</label>
                        <select v-if="masterLocations.length > 0" v-model="form.lokasi" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500">
                            <option v-for="loc in masterLocations" :key="loc" :value="loc">{{ loc }}</option>
                        </select>
                        <input v-else v-model="form.lokasi" type="text" placeholder="Ketikkan nama lokasi..." required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Penilai</label>
                        <input v-model="form.penilai" type="text" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">NRP Penilai</label>
                        <input v-model="form.nrp" type="text" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tanggal GMP</label>
                        <input v-model="form.tanggal_gmp" type="date" required class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Catatan / Keterangan</label>
                        <input v-model="form.keterangan" type="text" placeholder="Opsional..." class="w-full border border-slate-300 rounded-xl px-3 py-2 text-xs bg-slate-50 text-slate-800 outline-none focus:border-emerald-500" />
                    </div>

                    <div class="md:col-span-3 pt-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1.5 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <Camera class="w-3.5 h-3.5 text-emerald-600" />
                                Foto Dokumentasi Area / Temuan (Opsional)
                            </span>
                            <span class="text-[10px] font-normal text-slate-400">Bisa Ctrl + V / Drag & Drop foto</span>
                        </label>

                        <!-- Hidden File Input -->
                        <input
                            ref="fileInputRef"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="handleFileSelect"
                        />

                        <!-- Upload Zone when no photo selected -->
                        <div
                            v-if="!photoPreview"
                            @click="triggerPhotoInput"
                            @dragover.prevent
                            @drop.prevent="handleFileDrop"
                            class="border-2 border-dashed border-slate-300 hover:border-emerald-500 hover:bg-emerald-50/40 rounded-2xl p-6 text-center cursor-pointer transition flex flex-col items-center justify-center gap-2 bg-slate-50/50 group"
                        >
                            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition">
                                <ImagePlus class="w-6 h-6" />
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-700">
                                    Klik untuk ambil / pilih foto dari galeri
                                </p>
                                <p class="text-[11px] text-slate-400 mt-0.5">
                                    atau seret foto ke sini / tekan <kbd class="px-1.5 py-0.5 bg-slate-200 rounded text-slate-600 font-mono text-[10px]">Ctrl + V</kbd>
                                </p>
                            </div>
                            <span class="text-[10px] text-slate-400">Format JPG, PNG, WEBP (Maksimal 10MB)</span>
                        </div>

                        <!-- Preview Zone when photo is selected -->
                        <div
                            v-else
                            class="flex flex-col sm:flex-row items-center gap-4 p-3 bg-emerald-50/50 border border-emerald-200 rounded-2xl"
                        >
                            <div class="relative group shrink-0">
                                <img
                                    :src="photoPreview"
                                    alt="Preview Foto Area"
                                    class="w-32 h-24 sm:w-40 sm:h-28 object-cover rounded-xl border border-emerald-200 shadow-sm"
                                />
                            </div>
                            <div class="flex-1 min-w-0 text-center sm:text-left">
                                <div class="flex items-center justify-center sm:justify-start gap-1.5 mb-1">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Foto Siap Dikirim
                                    </span>
                                </div>
                                <p class="text-xs font-semibold text-slate-800 truncate max-w-xs">
                                    {{ photoFileName || 'Foto Dokumen Area' }}
                                </p>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    Foto ini akan tersimpan otomatis bersama hasil penilaian.
                                </p>
                                <div class="flex items-center justify-center sm:justify-start gap-2 mt-3">
                                    <button
                                        type="button"
                                        @click="triggerPhotoInput"
                                        class="px-3 py-1.5 text-xs font-bold text-emerald-700 bg-white border border-emerald-300 hover:bg-emerald-100/50 rounded-xl transition flex items-center gap-1"
                                    >
                                        <Camera class="w-3.5 h-3.5" /> Ganti Foto
                                    </button>
                                    <button
                                        type="button"
                                        @click="removePhoto"
                                        class="px-3 py-1.5 text-xs font-bold text-rose-600 bg-white border border-rose-200 hover:bg-rose-50 rounded-xl transition flex items-center gap-1"
                                    >
                                        <Trash2 class="w-3.5 h-3.5" /> Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kriteria Penilaian -->
            <div v-for="rubric in criteria" :key="rubric.number" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <!-- Mode Normal (Display) -->
                <div v-if="editingQuestion !== rubric.number" class="flex items-start gap-3 mb-3">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-xs font-black text-emerald-800">
                        {{ rubric.number }}
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-slate-900 mt-1">{{ getQuestionData(rubric.number).title }}</h4>
                    </div>
                    <button
                        type="button"
                        @click="startEditQuestion(rubric.number)"
                        class="shrink-0 p-1.5 rounded-lg hover:bg-slate-100 transition"
                        title="Edit soal"
                    >
                        <Edit2 class="w-4 h-4 text-slate-500 hover:text-slate-700" />
                    </button>
                </div>

                <!-- Mode Edit -->
                <div v-else class="mb-3 p-4 bg-blue-50 rounded-xl border border-blue-200">
                    <div class="flex items-center justify-between mb-4">
                        <h5 class="font-bold text-slate-800">Edit Soal #{{ rubric.number }}</h5>
                        <button
                            type="button"
                            @click="cancelEditQuestion"
                            class="p-1 hover:bg-slate-200 rounded transition"
                        >
                            <X class="w-4 h-4 text-slate-600" />
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Judul Pertanyaan</label>
                            <input
                                v-model="form.editedQuestions[rubric.number].title"
                                type="text"
                                class="w-full border border-blue-300 rounded-lg px-3 py-2 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div v-if="form.editedQuestions[rubric.number].notes && form.editedQuestions[rubric.number].notes.length">
                            <label class="block text-xs font-bold text-slate-600 mb-1">Catatan (setiap baris)</label>
                            <textarea
                                :value="form.editedQuestions[rubric.number].notes.join('\n')"
                                @input="(e) => form.editedQuestions[rubric.number].notes = e.target.value.split('\n').filter(n => n.trim())"
                                class="w-full border border-blue-300 rounded-lg px-3 py-2 text-sm bg-white text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                rows="3"
                            />
                        </div>

                        <div class="flex gap-2 justify-end">
                            <button
                                type="button"
                                @click="cancelEditQuestion"
                                class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                @click="saveEditQuestion(rubric.number)"
                                class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition"
                            >
                                Simpan
                            </button>
                        </div>
                    </div>
                </div>

                <ul v-if="getQuestionData(rubric.number).notes && getQuestionData(rubric.number).notes.length" class="mb-3 space-y-0.5 text-[11px] text-slate-500">
                    <li v-for="(note, idx) in getQuestionData(rubric.number).notes" :key="idx">{{ note }}</li>
                </ul>

                <!-- Tabel rubrik terstruktur (kolom x baris skor) -->
                <div v-if="rubric.format === 'table'" class="mb-4 overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left text-[11px] whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-100">
                                <th :colspan="rubric.columns.length + 1" class="p-2 text-center font-extrabold text-slate-700">{{ rubric.rubricTitle }}</th>
                            </tr>
                            <tr class="bg-slate-50 font-bold text-slate-600">
                                <th class="p-2 w-14 text-center">Skor</th>
                                <th v-for="(col, idx) in rubric.columns" :key="idx" class="p-2">{{ col }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(row, rowIdx) in rubric.rows" :key="row.score" :class="row.score === '5' ? 'bg-yellow-50' : ''">
                                <td class="p-2 text-center font-black text-slate-800">{{ row.score }}</td>
                                <template v-if="row.colspan">
                                    <td
                                        :colspan="row.colspan"
                                        class="p-2 whitespace-normal cursor-pointer bg-white transition relative"
                                        @dblclick="startEditCell(rubric.number, rowIdx, 0, row.cells[0])"
                                    >
                                        <!-- Edit Mode -->
                                        <div v-if="editingCell === `${rubric.number}-${rowIdx}-0`" class="flex gap-1">
                                            <input
                                                v-model="editingCellValue"
                                                type="text"
                                                class="flex-1 border border-blue-400 rounded px-2 py-1 text-xs bg-white"
                                                @keydown.enter="saveCellEdit(rubric.number, rowIdx, 0)"
                                                @keydown.escape="cancelCellEdit"
                                                autofocus
                                            />
                                            <button
                                                type="button"
                                                @click="saveCellEdit(rubric.number, rowIdx, 0)"
                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition shadow-sm flex items-center gap-1"
                                                title="Simpan perubahan"
                                            >
                                                ✓
                                            </button>
                                        </div>
                                        <!-- Display Mode -->
                                        <span v-else class="text-slate-700" v-html="getCellValue(rubric.number, rowIdx, 0, row)"></span>
                                    </td>
                                    <td v-if="row.colspan < rubric.columns.length" :colspan="rubric.columns.length - row.colspan" class="p-2"></td>
                                </template>
                                <template v-else>
                                    <td
                                        v-for="(col, colIdx) in rubric.columns"
                                        :key="colIdx"
                                        class="p-2 whitespace-normal cursor-pointer bg-white transition relative"
                                        @dblclick="startEditCell(rubric.number, rowIdx, colIdx, row.cells[colIdx])"
                                    >
                                        <!-- Edit Mode -->
                                        <div v-if="editingCell === `${rubric.number}-${rowIdx}-${colIdx}`" class="flex gap-1">
                                            <input
                                                v-model="editingCellValue"
                                                type="text"
                                                class="flex-1 border border-blue-400 rounded px-2 py-1 text-xs bg-white text-slate-800"
                                                @keydown.enter="saveCellEdit(rubric.number, rowIdx, colIdx)"
                                                @keydown.escape="cancelCellEdit"
                                                autofocus
                                            />
                                            <button
                                                type="button"
                                                @click="saveCellEdit(rubric.number, rowIdx, colIdx)"
                                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-lg transition shadow-sm flex items-center gap-1"
                                                title="Simpan perubahan"
                                            >
                                                ✓
                                            </button>
                                        </div>
                                        <!-- Display Mode -->
                                        <span v-else class="text-slate-700" v-html="getCellValue(rubric.number, rowIdx, colIdx, row)"></span>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>


                <!-- Pilihan Skor (Dynamic) & Upload Foto Per-Soal -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[10px] font-bold uppercase text-slate-400 mr-1">Pilih Skor:</span>
                        <button
                            v-for="score in getAvailableScores(rubric.number, rubric)"
                            :key="score"
                            type="button"
                            @click="form.answers[rubric.number] = score"
                            :class="[
                                'w-10 h-9 rounded-lg text-xs font-black transition border',
                                form.answers[rubric.number] === score
                                    ? 'bg-emerald-600 text-white border-emerald-600 shadow'
                                    : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-100'
                            ]"
                        >
                            {{ score }}
                        </button>
                    </div>

                    <!-- Upload Foto Per-Soal -->
                    <div>
                        <input
                            :id="`q-photo-input-${rubric.number}`"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="(e) => handleQuestionFileSelect(rubric.number, e)"
                        />

                        <!-- Tombol belum ada foto -->
                        <div v-if="!questionPreviews[rubric.number]">
                            <button
                                type="button"
                                @click="triggerQuestionPhoto(rubric.number)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50 hover:bg-emerald-50/50 text-slate-600 hover:text-emerald-700 text-xs font-semibold transition"
                            >
                                <Camera class="w-3.5 h-3.5 text-slate-400" />
                                <span>Foto Soal #{{ rubric.number }} (Opsional)</span>
                            </button>
                        </div>

                        <!-- Preview jika sudah ada foto -->
                        <div v-else class="inline-flex items-center gap-2.5 p-1.5 bg-emerald-50/60 border border-emerald-200 rounded-xl">
                            <img
                                :src="questionPreviews[rubric.number].url"
                                alt="Bukti Soal"
                                class="w-10 h-8 object-cover rounded-lg border border-emerald-200 shadow-xs"
                            />
                            <div class="text-left">
                                <span class="text-[10px] font-bold text-emerald-800 bg-emerald-100 px-1.5 py-0.5 rounded">
                                    Foto Q{{ rubric.number }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    @click="triggerQuestionPhoto(rubric.number)"
                                    class="p-1 text-emerald-700 hover:bg-emerald-100 rounded-lg transition"
                                    title="Ganti foto"
                                >
                                    <Camera class="w-3.5 h-3.5" />
                                </button>
                                <button
                                    type="button"
                                    @click="removeQuestionPhoto(rubric.number)"
                                    class="p-1 text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                    title="Hapus foto"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Action Footer -->
            <div class="flex justify-end gap-3 pt-2">
                <button type="submit" class="w-full sm:w-auto justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-2xl text-xs shadow-lg flex items-center gap-2 transition-transform active:scale-95">
                    <Save class="w-4 h-4" /> Kirim Penilaian GMP
                </button>
            </div>
        </form>

    </AppLayout>
</template>

<style scoped>
.gmp-raw-rubric :deep(table) {
    width: 100%;
    border-collapse: collapse;
    font-size: 11px;
}
.gmp-raw-rubric :deep(th),
.gmp-raw-rubric :deep(td) {
    border: 1px solid #e2e8f0;
    padding: 6px 8px;
    text-align: left;
    background: #ffffff;
    color: #1f2937;
}
.gmp-raw-rubric :deep(th.top-title),
.gmp-raw-rubric :deep(.top-title) {
    background: #f1f5f9;
    text-align: center;
    font-weight: 800;
}
.gmp-raw-rubric :deep(.sub-copy) {
    color: #64748b;
    font-style: italic;
}
.gmp-raw-rubric :deep(.column-head),
.gmp-raw-rubric :deep(.score-head) {
    background: #f8fafc;
    font-weight: 700;
    text-align: center;
}
.gmp-raw-rubric :deep(.dark-cell) {
    background: #ffffff;
    color: #1f2937;
}
</style>
