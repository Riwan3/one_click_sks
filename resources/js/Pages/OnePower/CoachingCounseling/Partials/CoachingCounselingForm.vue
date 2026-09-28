<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import SignaturePad from '@/Components/ui/SignaturePad.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
    defaultCoach: { type: Object, default: null },
    targetItem: { type: Object, default: null },
    isEdit: { type: Boolean, default: false },
});

// === Employee (Anak Asuh) State ===
const employeeContainer = ref(null);
const initialTarget = props.targetItem?.data ?? props.targetItem ?? null;
const selectedEmployee = ref(
    initialTarget?.employeeId
        ? {
              id: initialTarget.employeeId,
              nrp: initialTarget.nrp,
              name: initialTarget.nama,
              jabatan_ss6: initialTarget.jabatan,
              unit: initialTarget.unit,
          }
        : null
);
const employeeQuery = ref(initialTarget?.nrp ? `${initialTarget.nrp} - ${initialTarget.nama}` : '');
const employeeResults = ref([]);
const showEmployeeResults = ref(false);
const isSearchingEmployee = ref(false);
let employeeSearchTimer = null;
let isSelectingEmployee = false;

// === Coach (Bapak Asuh) State ===
const coachContainer = ref(null);
const selectedCoach = ref(
    initialTarget?.coachEmployeeId
        ? {
              id: initialTarget.coachEmployeeId,
              nrp: initialTarget.coachNrp ?? '',
              name: initialTarget.coachNama,
              jabatan_ss6: initialTarget.coachJabatan ?? '',
          }
        : props.defaultCoach
        ? {
              id: props.defaultCoach.id,
              nrp: props.defaultCoach.nrp ?? '',
              name: props.defaultCoach.name,
              jabatan_ss6: props.defaultCoach.jabatan_ss6 ?? '',
          }
        : null
);
const coachQuery = ref(selectedCoach.value?.name ? `${selectedCoach.value.nrp ? selectedCoach.value.nrp + ' - ' : ''}${selectedCoach.value.name}` : '');
const coachResults = ref([]);
const showCoachResults = ref(false);
const isSearchingCoach = ref(false);
let coachSearchTimer = null;
let isSelectingCoach = false;
let coachManuallyCleared = false;

// File upload state
const fileName = ref('');
const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        props.form.file_bukti = file;
        fileName.value = file.name;
    } else {
        props.form.file_bukti = null;
        fileName.value = '';
    }
};

// Auto-fill initial values if targetItem changes (e.g. edit)
watch(
    () => props.targetItem,
    (val) => {
        const item = val?.data ?? val;
        if (item) {
            if (item.employeeId) {
                selectedEmployee.value = {
                    id: item.employeeId,
                    nrp: item.nrp,
                    name: item.nama,
                    jabatan_ss6: item.jabatan,
                    unit: item.unit,
                };
                employeeQuery.value = `${item.nrp} - ${item.nama}`;
            }
            if (item.coachEmployeeId) {
                selectedCoach.value = {
                    id: item.coachEmployeeId,
                    nrp: item.coachNrp ?? '',
                    name: item.coachNama,
                    jabatan_ss6: item.coachJabatan ?? '',
                };
                coachQuery.value = `${item.coachNrp ? item.coachNrp + ' - ' : ''}${item.coachNama}`;
            }
        }
    },
    { deep: true }
);

watch(
    () => props.defaultCoach,
    (newCoach) => {
        if (newCoach && !selectedCoach.value && !props.isEdit && !coachManuallyCleared) {
            isSelectingCoach = true;
            props.form.coach_employee_id = newCoach.id;
            selectedCoach.value = {
                id: newCoach.id,
                nrp: newCoach.nrp ?? '',
                name: newCoach.name,
                jabatan_ss6: newCoach.jabatan_ss6 ?? '',
            };
            coachQuery.value = newCoach.name
                ? `${newCoach.nrp ? newCoach.nrp + ' - ' : ''}${newCoach.name}`
                : '';
            setTimeout(() => {
                isSelectingCoach = false;
            }, 150);
        }
    },
    { immediate: true }
);

// === Employee Selection Methods ===
const pickEmployee = (emp) => {
    isSelectingEmployee = true;
    props.form.employee_id = emp.id;
    selectedEmployee.value = emp;
    employeeQuery.value = `${emp.nrp} - ${emp.name}`;
    showEmployeeResults.value = false;
    setTimeout(() => {
        isSelectingEmployee = false;
    }, 150);
};

const clearEmployee = () => {
    props.form.employee_id = null;
    selectedEmployee.value = null;
    employeeQuery.value = '';
    employeeResults.value = [];
    showEmployeeResults.value = false;
};

const selectFirstOrExactEmployee = () => {
    if (!employeeResults.value.length) return;
    const exact = employeeResults.value.find(
        (e) => (e.nrp || '').toLowerCase() === employeeQuery.value.trim().toLowerCase()
    );
    pickEmployee(exact || employeeResults.value[0]);
};

watch(employeeQuery, (val) => {
    if (isSelectingEmployee) return;
    const keyword = (val || '').trim();

    if (selectedEmployee.value && keyword !== `${selectedEmployee.value.nrp} - ${selectedEmployee.value.name}` && keyword !== selectedEmployee.value.nrp) {
        props.form.employee_id = null;
        selectedEmployee.value = null;
    }

    clearTimeout(employeeSearchTimer);
    if (keyword.length < 3) {
        employeeResults.value = [];
        showEmployeeResults.value = false;
        isSearchingEmployee.value = false;
        return;
    }

    isSearchingEmployee.value = true;
    employeeSearchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/pembinaan/coaching-counseling/search-employee?keyword=${encodeURIComponent(keyword)}`);
            employeeResults.value = await res.json();
            showEmployeeResults.value = true;
        } catch {
            employeeResults.value = [];
        } finally {
            isSearchingEmployee.value = false;
        }
    }, 250);
});

// === Coach Selection Methods ===
const pickCoach = (emp) => {
    coachManuallyCleared = false;
    isSelectingCoach = true;
    props.form.coach_employee_id = emp.id;
    selectedCoach.value = emp;
    coachQuery.value = `${emp.nrp ? emp.nrp + ' - ' : ''}${emp.name}`;
    showCoachResults.value = false;
    setTimeout(() => {
        isSelectingCoach = false;
    }, 150);
};

const clearCoach = () => {
    coachManuallyCleared = true;
    props.form.coach_employee_id = null;
    selectedCoach.value = null;
    coachQuery.value = '';
    coachResults.value = [];
    showCoachResults.value = false;
};

const selectFirstOrExactCoach = () => {
    if (!coachResults.value.length) return;
    const exact = coachResults.value.find(
        (e) => (e.nrp || '').toLowerCase() === coachQuery.value.trim().toLowerCase()
    );
    pickCoach(exact || coachResults.value[0]);
};

watch(coachQuery, (val) => {
    if (isSelectingCoach) return;
    const keyword = (val || '').trim();

    if (selectedCoach.value && keyword !== `${selectedCoach.value.nrp ? selectedCoach.value.nrp + ' - ' : ''}${selectedCoach.value.name}` && keyword !== selectedCoach.value.name) {
        props.form.coach_employee_id = null;
        selectedCoach.value = null;
    }

    clearTimeout(coachSearchTimer);
    if (keyword.length < 3) {
        coachResults.value = [];
        showCoachResults.value = false;
        isSearchingCoach.value = false;
        return;
    }

    isSearchingCoach.value = true;
    coachSearchTimer = setTimeout(async () => {
        try {
            const res = await fetch(`/one-power/pembinaan/coaching-counseling/search-employee?keyword=${encodeURIComponent(keyword)}`);
            coachResults.value = await res.json();
            showCoachResults.value = true;
        } catch {
            coachResults.value = [];
        } finally {
            isSearchingCoach.value = false;
        }
    }, 250);
});

// Click outside handling
const handleClickOutside = (e) => {
    if (employeeContainer.value && !employeeContainer.value.contains(e.target)) {
        showEmployeeResults.value = false;
    }
    if (coachContainer.value && !coachContainer.value.contains(e.target)) {
        showCoachResults.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    // If default coach provided and not set in form
    if (props.defaultCoach && !props.form.coach_employee_id) {
        props.form.coach_employee_id = props.defaultCoach.id;
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <form class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8" @submit.prevent="onSubmit">
        <div class="space-y-6">
            <!-- Section: Data Karyawan & Coach -->
            <div class="rounded-xl border border-slate-100 bg-slate-50/60 p-4 sm:p-5">
                <h3 class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-500">
                    Informasi Karyawan &amp; Coach
                </h3>
                
                <div class="grid gap-5 md:grid-cols-2">
                    <!-- Cari Karyawan (Anak Asuh) -->
                    <div ref="employeeContainer" class="relative">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Cari Karyawan (NRP / Nama) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="employeeQuery"
                                type="text"
                                autocomplete="off"
                                placeholder="Ketik min. 3 karakter NRP atau Nama..."
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 pr-9 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                @focus="!selectedEmployee && employeeQuery.trim().length >= 3 && (showEmployeeResults = true)"
                                @keydown.enter.prevent="selectFirstOrExactEmployee"
                            >
                            <button
                                v-if="employeeQuery"
                                type="button"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-lg text-slate-400 hover:text-slate-600"
                                title="Hapus"
                                @click="clearEmployee"
                            >
                                &times;
                            </button>
                        </div>
                        <p v-if="employeeQuery.trim().length > 0 && employeeQuery.trim().length < 3" class="mt-1 text-xs text-slate-400">
                            Ketik minimal 3 huruf atau angka...
                        </p>

                        <!-- Dropdown Search Results -->
                        <div
                            v-if="showEmployeeResults"
                            class="absolute z-30 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl"
                        >
                            <div v-if="isSearchingEmployee" class="px-4 py-3 text-xs text-slate-500">
                                Mencari karyawan...
                            </div>
                            <template v-else-if="employeeResults.length">
                                <button
                                    v-for="emp in employeeResults"
                                    :key="emp.id"
                                    type="button"
                                    class="block w-full border-b border-slate-100 px-4 py-2.5 text-left text-sm transition last:border-b-0 hover:bg-blue-50/70"
                                    @click="pickEmployee(emp)"
                                >
                                    <div class="font-bold text-blue-600">{{ emp.nrp }}</div>
                                    <div class="font-semibold uppercase text-slate-800">{{ emp.name }}</div>
                                    <div class="text-xs text-slate-500">{{ emp.jabatan_ss6 || '-' }} {{ emp.unit ? `• ${emp.unit}` : '' }}</div>
                                </button>
                            </template>
                            <div v-else class="px-4 py-3 text-xs text-slate-500">
                                Tidak ada karyawan yang cocok dengan "{{ employeeQuery }}".
                            </div>
                        </div>

                        <!-- Selected Employee Card -->
                        <div v-if="selectedEmployee" class="mt-2.5 rounded-xl border border-blue-100 bg-blue-50/60 p-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="text-xs font-bold text-blue-600">{{ selectedEmployee.nrp }}</div>
                                    <div class="text-sm font-bold uppercase text-slate-800">{{ selectedEmployee.name }}</div>
                                    <div class="text-xs text-slate-600">{{ selectedEmployee.jabatan_ss6 || '-' }} {{ selectedEmployee.unit ? `• ${selectedEmployee.unit}` : '' }}</div>
                                </div>
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                    @click="clearEmployee"
                                >
                                    Ganti
                                </button>
                            </div>
                        </div>
                        <p v-if="form.errors.employee_id" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.employee_id }}</p>
                    </div>

                    <!-- Cari Coach (Bapak Asuh) -->
                    <div ref="coachContainer" class="relative">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                            Coach / Bapak Asuh <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="coachQuery"
                                type="text"
                                autocomplete="off"
                                placeholder="Ketik min. 3 karakter NRP atau Nama..."
                                class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 pr-9 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                @focus="!selectedCoach && coachQuery.trim().length >= 3 && (showCoachResults = true)"
                                @keydown.enter.prevent="selectFirstOrExactCoach"
                            >
                            <button
                                v-if="coachQuery"
                                type="button"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-lg text-slate-400 hover:text-slate-600"
                                title="Hapus"
                                @click="clearCoach"
                            >
                                &times;
                            </button>
                        </div>
                        <p v-if="coachQuery.trim().length > 0 && coachQuery.trim().length < 3" class="mt-1 text-xs text-slate-400">
                            Ketik minimal 3 huruf atau angka...
                        </p>

                        <!-- Dropdown Coach Results -->
                        <div
                            v-if="showCoachResults"
                            class="absolute z-30 mt-1 max-h-60 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl"
                        >
                            <div v-if="isSearchingCoach" class="px-4 py-3 text-xs text-slate-500">
                                Mencari coach / bapak asuh...
                            </div>
                            <template v-else-if="coachResults.length">
                                <button
                                    v-for="emp in coachResults"
                                    :key="emp.id"
                                    type="button"
                                    class="block w-full border-b border-slate-100 px-4 py-2.5 text-left text-sm transition last:border-b-0 hover:bg-blue-50/70"
                                    @click="pickCoach(emp)"
                                >
                                    <div class="font-bold text-blue-600">{{ emp.nrp }}</div>
                                    <div class="font-semibold uppercase text-slate-800">{{ emp.name }}</div>
                                    <div class="text-xs text-slate-500">{{ emp.jabatan_ss6 || '-' }} {{ emp.unit ? `• ${emp.unit}` : '' }}</div>
                                </button>
                            </template>
                            <div v-else class="px-4 py-3 text-xs text-slate-500">
                                Tidak ada data yang cocok dengan "{{ coachQuery }}".
                            </div>
                        </div>

                        <!-- Selected Coach Card -->
                        <div v-if="selectedCoach" class="mt-2.5 rounded-xl border border-blue-100 bg-blue-50/60 p-3">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div v-if="selectedCoach.nrp" class="text-xs font-bold text-blue-600">{{ selectedCoach.nrp }}</div>
                                    <div class="text-sm font-bold uppercase text-slate-800">{{ selectedCoach.name }}</div>
                                    <div v-if="selectedCoach.jabatan_ss6" class="text-xs text-slate-600">{{ selectedCoach.jabatan_ss6 }}</div>
                                </div>
                                <button
                                    type="button"
                                    class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50"
                                    @click="clearCoach"
                                >
                                    Ganti
                                </button>
                            </div>
                        </div>
                        <p v-if="form.errors.coach_employee_id" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.coach_employee_id }}</p>
                    </div>
                </div>
            </div>

            <!-- Section: Detail Pertemuan -->
            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tanggal <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.tanggal"
                        type="date"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                    <p v-if="form.errors.tanggal" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.tanggal }}</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Lokasi <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.lokasi"
                        type="text"
                        placeholder="Contoh: Front Loading"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                    <p v-if="form.errors.lokasi" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.lokasi }}</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Waktu Pertemuan
                    </label>
                    <input
                        v-model="form.waktu_pertemuan"
                        type="time"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                    <p v-if="form.errors.waktu_pertemuan" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.waktu_pertemuan }}</p>
                </div>
            </div>

            <!-- Section: Pokok Materi & Level Fatigue (Matching V1) -->
            <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
                <div class="mb-3 flex items-center justify-between">
                    <label class="text-xs font-bold uppercase tracking-wider text-slate-700">
                        Pokok Materi &amp; Fatigue
                    </label>
                </div>

                <div class="grid gap-4 md:grid-cols-12 md:items-center">
                    <!-- Checkboxes Materi -->
                    <div class="md:col-span-8">
                        <div class="flex flex-wrap gap-4 sm:gap-6">
                            <label class="inline-flex cursor-pointer items-center gap-2 select-none">
                                <input
                                    v-model="form.materi_pribadi"
                                    type="checkbox"
                                    :true-value="1"
                                    :false-value="0"
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="rounded bg-sky-100 px-1.5 py-0.5 text-xs font-bold text-sky-700">P</span>
                                <span class="text-sm font-semibold text-slate-700">Pribadi</span>
                            </label>

                            <label class="inline-flex cursor-pointer items-center gap-2 select-none">
                                <input
                                    v-model="form.materi_keluarga"
                                    type="checkbox"
                                    :true-value="1"
                                    :false-value="0"
                                    class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500"
                                >
                                <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-xs font-bold text-emerald-700">K</span>
                                <span class="text-sm font-semibold text-slate-700">Keluarga</span>
                            </label>

                            <label class="inline-flex cursor-pointer items-center gap-2 select-none">
                                <input
                                    v-model="form.materi_lingkungan"
                                    type="checkbox"
                                    :true-value="1"
                                    :false-value="0"
                                    class="h-4 w-4 rounded border-slate-300 text-amber-600 focus:ring-amber-500"
                                >
                                <span class="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-bold text-amber-700">L</span>
                                <span class="text-sm font-semibold text-slate-700">Lingkungan</span>
                            </label>

                            <label class="inline-flex cursor-pointer items-center gap-2 select-none">
                                <input
                                    v-model="form.materi_pekerjaan"
                                    type="checkbox"
                                    :true-value="1"
                                    :false-value="0"
                                    class="h-4 w-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500"
                                >
                                <span class="rounded bg-rose-100 px-1.5 py-0.5 text-xs font-bold text-rose-700">W</span>
                                <span class="text-sm font-semibold text-slate-700">Pekerjaan</span>
                            </label>
                        </div>
                    </div>

                    <!-- Level Fatigue Dropdown -->
                    <div class="md:col-span-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500 whitespace-nowrap">
                                Level Fatigue:
                            </span>
                            <select
                                v-model.number="form.fatigue_level"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-800 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-100"
                            >
                                <option :value="0">Bukan Fatigue (0)</option>
                                <option :value="1">Level 1</option>
                                <option :value="2">Level 2</option>
                                <option :value="3">Level 3</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section: Deskripsi & Rekomendasi -->
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Deskripsi / Penjelasan <span class="text-red-500">*</span>
                </label>
                <textarea
                    v-model="form.deskripsi"
                    rows="4"
                    placeholder="Tuliskan deskripsi atau penjelasan masalah / pembahasan coaching secara lengkap..."
                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                ></textarea>
                <p v-if="form.errors.deskripsi" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.deskripsi }}</p>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Rekomendasi
                </label>
                <textarea
                    v-model="form.rekomendasi"
                    rows="3"
                    placeholder="Tuliskan rekomendasi atau tindakan perbaikan hasil pembinaan..."
                    class="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                ></textarea>
                <p v-if="form.errors.rekomendasi" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.rekomendasi }}</p>
            </div>

            <!-- Section: File Bukti CC -->
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                    File Bukti CC (Foto / Dokumen)
                </label>
                <div class="flex items-center gap-3">
                    <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100">
                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Pilih File</span>
                        <input
                            type="file"
                            accept=".jpg,.jpeg,.png,.pdf"
                            class="hidden"
                            @change="handleFileChange"
                        >
                    </label>
                    <span v-if="fileName" class="text-sm font-medium text-slate-700 truncate max-w-xs">{{ fileName }}</span>
                    <span v-else-if="targetItem?.fileBukti" class="text-xs text-blue-600">
                        <a :href="targetItem.fileBukti" target="_blank" class="underline hover:text-blue-800">Lihat file tersimpan</a>
                    </span>
                    <span v-else class="text-xs text-slate-400">Format: JPG, PNG, atau PDF (maks. 10MB)</span>
                </div>
                <p v-if="form.errors.file_bukti" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.file_bukti }}</p>
            </div>

            <!-- Section: Tanda Tangan Digital -->
            <div class="grid gap-6 pt-2 md:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tanda Tangan Anak Asuh (Karyawan)
                    </label>
                    <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-inner">
                        <SignaturePad v-model="form.ttd_anak_asuh" />
                    </div>
                    <p v-if="form.errors.ttd_anak_asuh" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.ttd_anak_asuh }}</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Tanda Tangan Bapak Asuh (Coach)
                    </label>
                    <div class="rounded-xl border border-slate-200 bg-white p-2 shadow-inner">
                        <SignaturePad v-model="form.ttd_bapa_asuh" />
                    </div>
                    <p v-if="form.errors.ttd_bapa_asuh" class="mt-1.5 text-xs font-medium text-red-600">{{ form.errors.ttd_bapa_asuh }}</p>
                </div>
            </div>
        </div>

        <!-- Submit & Actions -->
        <div class="mt-8 flex flex-col-reverse items-center justify-end gap-3 border-t border-slate-100 pt-6 sm:flex-row">
            <Link
                href="/one-power/pembinaan/coaching-counseling"
                class="w-full rounded-xl border border-slate-200 px-6 py-3 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto"
            >
                Kembali
            </Link>
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-xl bg-blue-600 px-8 py-3 text-center text-sm font-bold text-white shadow-md shadow-blue-500/20 transition hover:bg-blue-700 disabled:opacity-60 sm:w-auto"
            >
                <span v-if="form.processing">Menyimpan...</span>
                <span v-else>{{ isEdit ? 'Update Data Pembinaan' : 'Simpan Data Pembinaan' }}</span>
            </button>
        </div>
    </form>
</template>
