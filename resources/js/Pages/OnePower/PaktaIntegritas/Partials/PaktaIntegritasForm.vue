<script setup>
import { ref, watch } from 'vue';
import SignaturePad from '@/Components/ui/SignaturePad.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
});

const employeeResults = ref([]);
const showResults = ref(false);
const employeeQuery = ref('');
const atasanResults = ref([]);
const atasanShowResults = ref(false);
const atasanQuery = ref('');
const mengetahuiResults = ref([]);
const mengetahuiShowResults = ref(false);
const mengetahuiQuery = ref('');
const nomorSuratPreview = ref('');

let searchTimer = null;
let atasanSearchTimer = null;
let mengetahuiSearchTimer = null;

const defaultPernyataan = 'Saya berkomitmen dengan bersungguh-sungguh untuk menjaga ATR dan Kinerja sesuai dengan standar perusahaan.';

// Initialize default values on mount
if (!props.form.pernyataan) {
    props.form.pernyataan = defaultPernyataan;
}
if (!props.form.cc) {
    props.form.cc = 'Bapak Fachrul Latuconsina';
}
if (!props.form.arsip) {
    props.form.arsip = 'Dokumen Control MPE';
}

watch(employeeQuery, (keyword) => {
    clearTimeout(searchTimer);
    if (! keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/pakta-integritas/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showResults.value = true;
    }, 300);
});

watch(() => props.form.tanggal, async (newTanggal) => {
    if (!newTanggal) {
        nomorSuratPreview.value = '';
        return;
    }
    try {
        const response = await fetch(`/one-power/pembinaan/pakta-integritas/next-number?tanggal=${encodeURIComponent(newTanggal)}`);
        const data = await response.json();
        nomorSuratPreview.value = data.nomor_surat || '';
        props.form.nomor_surat = data.nomor_surat || '';
    } catch (error) {
        console.error('Error fetching next number:', error);
    }
});

watch(atasanQuery, (keyword) => {
    clearTimeout(atasanSearchTimer);
    if (! keyword || keyword.length < 2) {
        atasanResults.value = [];
        return;
    }
    atasanSearchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/pakta-integritas/search-employee?keyword=${encodeURIComponent(keyword)}`);
        atasanResults.value = await response.json();
        atasanShowResults.value = true;
    }, 300);
});

watch(mengetahuiQuery, (keyword) => {
    clearTimeout(mengetahuiSearchTimer);
    if (! keyword || keyword.length < 2) {
        mengetahuiResults.value = [];
        return;
    }
    mengetahuiSearchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/pakta-integritas/search-employee?keyword=${encodeURIComponent(keyword)}`);
        mengetahuiResults.value = await response.json();
        mengetahuiShowResults.value = true;
    }, 300);
});

const pickEmployee = (employee) => {
    props.form.employee_id = employee.id;
    employeeQuery.value = `${employee.nrp} - ${employee.name}`;
    showResults.value = false;
};

const pickAtasan = (employee) => {
    props.form.atasan_nama = employee.name;
    atasanQuery.value = employee.name;
    atasanShowResults.value = false;
};

const pickMengetahui = (employee) => {
    props.form.mengetahui_nama = employee.name;
    mengetahuiQuery.value = employee.name;
    mengetahuiShowResults.value = false;
};
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Karyawan <span class="text-red-500">*</span></label>
                <input
                    v-model="employeeQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari NRP atau nama karyawan..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showResults = true"
                >
                <div v-if="showResults && employeeResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button
                        v-for="employee in employeeResults"
                        :key="employee.id"
                        type="button"
                        class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="pickEmployee(employee)"
                    >
                        <span class="font-semibold text-slate-700">{{ employee.nrp }}</span>
                        <span class="text-slate-500"> &ndash; {{ employee.name }}</span>
                    </button>
                </div>
                <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal <span class="text-red-500">*</span></label>
                <input v-model="form.tanggal" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nomor Surat</label>
                <input v-model="nomorSuratPreview" type="text" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none">
                <input v-model="form.nomor_surat" type="hidden">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tempat</label>
                <input v-model="form.tempat" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Pernyataan <span class="text-red-500">*</span></label>
                <textarea v-model="form.pernyataan" rows="5" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                <p v-if="form.errors.pernyataan" class="mt-1 text-xs text-red-600">{{ form.errors.pernyataan }}</p>
            </div>

            <div class="relative sm:col-span-1">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nama Atasan</label>
                <input
                    v-model="atasanQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari nama atau NRP..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="atasanShowResults = true"
                >
                <div v-if="atasanShowResults && atasanResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button
                        v-for="employee in atasanResults"
                        :key="employee.id"
                        type="button"
                        class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="pickAtasan(employee)"
                    >
                        <span class="font-semibold text-slate-700">{{ employee.name }}</span>
                        <span class="text-slate-500"> ({{ employee.nrp }})</span>
                    </button>
                </div>
            </div>
            <div class="relative sm:col-span-1">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Mengetahui</label>
                <input
                    v-model="mengetahuiQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari nama atau NRP..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="mengetahuiShowResults = true"
                >
                <div v-if="mengetahuiShowResults && mengetahuiResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button
                        v-for="employee in mengetahuiResults"
                        :key="employee.id"
                        type="button"
                        class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="pickMengetahui(employee)"
                    >
                        <span class="font-semibold text-slate-700">{{ employee.name }}</span>
                        <span class="text-slate-500"> ({{ employee.nrp }})</span>
                    </button>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">CC</label>
                <input v-model="form.cc" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Arsip</label>
                <input v-model="form.arsip" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanda Tangan Karyawan</label>
                <SignaturePad v-model="form.ttd_karyawan" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanda Tangan Atasan</label>
                <SignaturePad v-model="form.ttd_atasan" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanda Tangan Mengetahui</label>
                <SignaturePad v-model="form.ttd_mengetahui" />
            </div>
        </div>

        <button type="submit" :disabled="form.processing" class="mt-6 w-full rounded-lg bg-blue-500 py-2.5 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60 sm:w-auto sm:px-8">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
        </button>
    </form>
</template>
