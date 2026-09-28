<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import SignaturePad from '@/Components/ui/SignaturePad.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
    targetItem: { type: Object, default: null },
});

const employeeContainer = ref(null);
const initialTarget = props.targetItem?.data ?? props.targetItem ?? null;
const selectedNrp = ref(initialTarget?.nrp ?? '');
const employeeQuery = ref(initialTarget?.nrp ?? '');
const selectedNama = ref(initialTarget?.nama ?? '');
const selectedJabatan = ref(initialTarget?.jabatan ?? '');
const employeeResults = ref([]);
const showResults = ref(false);
const isSearchingEmployee = ref(false);

const diketahuiContainer = ref(null);
const selectedDiketahuiLabel = ref(initialTarget?.diketahuiOlehNama ?? '');
const diketahuiQuery = ref(initialTarget?.diketahuiOlehNama ?? '');
const diketahuiResults = ref([]);
const showDiketahuiResults = ref(false);
const isSearchingDiketahui = ref(false);

let searchTimer = null;
let diketahuiTimer = null;
let isSelectingEmployee = false;
let isSelectingDiketahui = false;

watch(() => props.targetItem, (val) => {
    const item = val?.data ?? val;
    if (item) {
        selectedNrp.value = item.nrp ?? '';
        employeeQuery.value = item.nrp ?? '';
        selectedNama.value = item.nama ?? '';
        selectedJabatan.value = item.jabatan ?? '';
        selectedDiketahuiLabel.value = item.diketahuiOlehNama ?? '';
        diketahuiQuery.value = item.diketahuiOlehNama ?? '';
    }
}, { deep: true });

const pickEmployee = (employee) => {
    isSelectingEmployee = true;
    props.form.employee_id = employee.id;
    selectedNrp.value = employee.nrp;
    employeeQuery.value = employee.nrp;
    selectedNama.value = employee.name;
    selectedJabatan.value = employee.jabatan_ss6 ?? '-';
    showResults.value = false;
    setTimeout(() => {
        isSelectingEmployee = false;
    }, 150);
};

const clearEmployee = () => {
    props.form.employee_id = null;
    selectedNrp.value = '';
    employeeQuery.value = '';
    selectedNama.value = '';
    selectedJabatan.value = '';
    employeeResults.value = [];
    showResults.value = false;
};

const selectFirstOrExactEmployee = () => {
    if (! employeeResults.value.length) return;
    const exact = employeeResults.value.find(
        (e) => (e.nrp || '').toLowerCase() === employeeQuery.value.trim().toLowerCase()
    );
    pickEmployee(exact || employeeResults.value[0]);
};

const handleEmployeeBlur = () => {
    if (! props.form.employee_id && employeeResults.value.length) {
        const exact = employeeResults.value.find(
            (e) => (e.nrp || '').toLowerCase() === employeeQuery.value.trim().toLowerCase()
        );
        if (exact) {
            pickEmployee(exact);
        }
    }
};

watch(employeeQuery, (val) => {
    if (isSelectingEmployee) return;

    const keyword = (val || '').trim();

    if (props.form.employee_id && keyword !== selectedNrp.value) {
        props.form.employee_id = null;
        selectedNrp.value = '';
        selectedNama.value = '';
        selectedJabatan.value = '';
    }

    clearTimeout(searchTimer);
    if (keyword.length < 3) {
        employeeResults.value = [];
        showResults.value = false;
        isSearchingEmployee.value = false;
        return;
    }

    isSearchingEmployee.value = true;
    searchTimer = setTimeout(async () => {
        try {
            const response = await fetch(`/one-power/pembinaan/berita-acara/search-employee?keyword=${encodeURIComponent(keyword)}`);
            employeeResults.value = await response.json();
            showResults.value = true;
        } catch {
            employeeResults.value = [];
        } finally {
            isSearchingEmployee.value = false;
        }
    }, 250);
});

const pickDiketahui = (employee) => {
    isSelectingDiketahui = true;
    props.form.diketahui_oleh_id = employee.id;
    selectedDiketahuiLabel.value = `${employee.nrp} - ${employee.name}`;
    diketahuiQuery.value = selectedDiketahuiLabel.value;
    showDiketahuiResults.value = false;
    setTimeout(() => {
        isSelectingDiketahui = false;
    }, 150);
};

const clearDiketahui = () => {
    props.form.diketahui_oleh_id = null;
    selectedDiketahuiLabel.value = '';
    diketahuiQuery.value = '';
    diketahuiResults.value = [];
    showDiketahuiResults.value = false;
};

watch(diketahuiQuery, (val) => {
    if (isSelectingDiketahui) return;

    const keyword = (val || '').trim();

    if (props.form.diketahui_oleh_id && keyword !== selectedDiketahuiLabel.value) {
        props.form.diketahui_oleh_id = null;
        selectedDiketahuiLabel.value = '';
    }

    clearTimeout(diketahuiTimer);
    if (keyword.length < 3) {
        diketahuiResults.value = [];
        showDiketahuiResults.value = false;
        isSearchingDiketahui.value = false;
        return;
    }

    isSearchingDiketahui.value = true;
    diketahuiTimer = setTimeout(async () => {
        try {
            const response = await fetch(`/one-power/pembinaan/berita-acara/search-employee?keyword=${encodeURIComponent(keyword)}`);
            diketahuiResults.value = await response.json();
            showDiketahuiResults.value = true;
        } catch {
            diketahuiResults.value = [];
        } finally {
            isSearchingDiketahui.value = false;
        }
    }, 250);
});

const handleClickOutside = (e) => {
    if (employeeContainer.value && ! employeeContainer.value.contains(e.target)) {
        showResults.value = false;
    }
    if (diketahuiContainer.value && ! diketahuiContainer.value.contains(e.target)) {
        showDiketahuiResults.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <div class="grid gap-4 sm:grid-cols-2">
            <!-- Nomor Surat -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nomor Surat</label>
                <input
                    v-model="form.nomor_surat"
                    type="text"
                    placeholder="Contoh: 001/BA/PPA/ADW/PRO/IX/2026"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                >
                <p v-if="form.errors.nomor_surat" class="mt-1 text-xs text-red-600">{{ form.errors.nomor_surat }}</p>
            </div>

            <!-- NRP Field with Search -->
            <div ref="employeeContainer" class="relative">
                <label class="mb-1 block text-sm font-semibold text-slate-700">
                    Cari NRP Karyawan <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        v-model="employeeQuery"
                        type="text"
                        autocomplete="off"
                        placeholder="Ketik min. 3 huruf/angka NRP..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 pr-8 text-sm outline-none focus:border-blue-500"
                        @focus="employeeQuery.trim().length >= 3 && (showResults = true)"
                        @blur="handleEmployeeBlur"
                        @keydown.enter.prevent="selectFirstOrExactEmployee"
                    >
                    <button
                        v-if="employeeQuery"
                        type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                        title="Hapus"
                        @click="clearEmployee"
                    >
                        &times;
                    </button>
                </div>
                <p v-if="employeeQuery.trim().length > 0 && employeeQuery.trim().length < 3" class="mt-1 text-xs text-slate-400">
                    Ketik minimal 3 huruf atau angka untuk mencari...
                </p>

                <!-- Dropdown results -->
                <div
                    v-if="showResults"
                    class="absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg"
                >
                    <div v-if="isSearchingEmployee" class="px-3 py-2 text-xs text-slate-500">
                        Mencari karyawan...
                    </div>
                    <template v-else-if="employeeResults.length">
                        <button
                            v-for="employee in employeeResults"
                            :key="employee.id"
                            type="button"
                            class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm last:border-b-0 hover:bg-blue-50"
                            @click="pickEmployee(employee)"
                        >
                            <span class="font-bold text-blue-600">{{ employee.nrp }}</span>
                            <span class="text-slate-700"> &ndash; {{ employee.name }}</span>
                            <span v-if="employee.jabatan_ss6" class="ml-1 text-xs text-slate-400">({{ employee.jabatan_ss6 }})</span>
                        </button>
                    </template>
                    <div v-else class="px-3 py-2 text-xs text-slate-500">
                        Tidak ada karyawan yang cocok dengan "{{ employeeQuery }}".
                    </div>
                </div>
                <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>
            </div>

            <!-- Nama Karyawan (Readonly / Auto-filled) -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nama Karyawan</label>
                <input
                    :value="selectedNama"
                    type="text"
                    readonly
                    placeholder="Otomatis muncul setelah memilih NRP..."
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 outline-none"
                >
            </div>

            <!-- Jabatan -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jabatan</label>
                <input
                    :value="selectedJabatan"
                    type="text"
                    readonly
                    placeholder="Otomatis terisi..."
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800 outline-none"
                >
            </div>

            <!-- Tanggal -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal <span class="text-red-500">*</span></label>
                <input v-model="form.tanggal" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal }}</p>
            </div>

            <!-- Diketahui Oleh (Atasan) -->
            <div ref="diketahuiContainer" class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Diketahui Oleh (Atasan)</label>
                <div class="relative">
                    <input
                        v-model="diketahuiQuery"
                        type="text"
                        autocomplete="off"
                        placeholder="Cari NRP atau nama atasan (min. 3 karakter)..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 pr-8 text-sm outline-none focus:border-blue-500"
                        @focus="diketahuiQuery.trim().length >= 3 && (showDiketahuiResults = true)"
                    >
                    <button
                        v-if="diketahuiQuery"
                        type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                        title="Hapus"
                        @click="clearDiketahui"
                    >
                        &times;
                    </button>
                </div>
                <div
                    v-if="showDiketahuiResults"
                    class="absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg"
                >
                    <div v-if="isSearchingDiketahui" class="px-3 py-2 text-xs text-slate-500">
                        Mencari atasan...
                    </div>
                    <template v-else-if="diketahuiResults.length">
                        <button
                            v-for="employee in diketahuiResults"
                            :key="employee.id"
                            type="button"
                            class="block w-full border-b border-slate-100 px-3 py-2 text-left text-sm last:border-b-0 hover:bg-blue-50"
                            @click="pickDiketahui(employee)"
                        >
                            <span class="font-bold text-blue-600">{{ employee.nrp }}</span>
                            <span class="text-slate-700"> &ndash; {{ employee.name }}</span>
                            <span v-if="employee.jabatan_ss6" class="ml-1 text-xs text-slate-400">({{ employee.jabatan_ss6 }})</span>
                        </button>
                    </template>
                    <div v-else class="px-3 py-2 text-xs text-slate-500">
                        Tidak ada atasan yang cocok dengan "{{ diketahuiQuery }}".
                    </div>
                </div>
            </div>

            <!-- Kronologi -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Kronologi <span class="text-red-500">*</span></label>
                <textarea v-model="form.kronologi" rows="5" placeholder="Tuliskan kronologi kejadian secara lengkap..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                <p v-if="form.errors.kronologi" class="mt-1 text-xs text-red-600">{{ form.errors.kronologi }}</p>
            </div>

            <!-- Signatures -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanda Tangan Karyawan</label>
                <SignaturePad v-model="form.ttd_karyawan" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanda Tangan Atasan</label>
                <SignaturePad v-model="form.ttd_atasan" />
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit" :disabled="form.processing" class="w-full rounded-lg bg-blue-500 py-2.5 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60 sm:w-auto sm:px-8">
                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <Link
                href="/one-power/pembinaan/berita-acara"
                class="rounded-lg border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition"
            >
                Kembali
            </Link>
        </div>
    </form>
</template>
