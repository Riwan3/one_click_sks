<script setup>
import { ref, watch } from 'vue';
import SignaturePad from '@/Components/ui/SignaturePad.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
});

const employeeResults = ref([]);
const showEmployeeResults = ref(false);
const employeeQuery = ref(props.form.nama ?? '');
let employeeSearchTimer = null;

const pengawasResults = ref([]);
const showPengawasResults = ref(false);
const pengawasQuery = ref(props.form.pengawasName ?? '');
let pengawasSearchTimer = null;

const spdkResults = ref([]);
const showSpdkResults = ref(false);
const spdkQuery = ref('');
let spdkSearchTimer = null;

const previewNomorSurat = ref(null);
let nextNumberTimer = null;

watch(
    () => [props.form.perusahaan, props.form.dikeluarkan_tanggal],
    ([perusahaan, dikeluarkanTanggal]) => {
        clearTimeout(nextNumberTimer);
        if (! dikeluarkanTanggal) {
            previewNomorSurat.value = null;
            return;
        }
        nextNumberTimer = setTimeout(async () => {
            const params = new URLSearchParams({ dikeluarkan_tanggal: dikeluarkanTanggal, perusahaan: perusahaan ?? '' });
            const response = await fetch(`/one-power/pembinaan/sk-pembinaan/next-number?${params}`);
            const data = await response.json();
            previewNomorSurat.value = data.nomorSurat;
        }, 300);
    },
);

watch(employeeQuery, (keyword) => {
    clearTimeout(employeeSearchTimer);
    if (! keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    employeeSearchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/sk-pembinaan/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showEmployeeResults.value = true;
    }, 300);
});

watch(pengawasQuery, (keyword) => {
    clearTimeout(pengawasSearchTimer);
    if (! keyword || keyword.length < 2) {
        pengawasResults.value = [];
        return;
    }
    pengawasSearchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/sk-pembinaan/search-employee?keyword=${encodeURIComponent(keyword)}`);
        pengawasResults.value = await response.json();
        showPengawasResults.value = true;
    }, 300);
});

watch(spdkQuery, (keyword) => {
    clearTimeout(spdkSearchTimer);
    if (! keyword || keyword.length < 2) {
        spdkResults.value = [];
        return;
    }
    spdkSearchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/sk-pembinaan/search-spdk?keyword=${encodeURIComponent(keyword)}`);
        spdkResults.value = await response.json();
        showSpdkResults.value = true;
    }, 300);
});

const pickEmployee = (employee) => {
    props.form.employee_id = employee.id;
    employeeQuery.value = employee.name;
    showEmployeeResults.value = false;
};

const pickPengawas = (employee) => {
    props.form.pengawas_id = employee.id;
    pengawasQuery.value = employee.name;
    showPengawasResults.value = false;
};

const pickSpdk = (spdk) => {
    props.form.spdk_ref = spdk.no_pelanggaran;
    props.form.jenis_pelanggaran = spdk.jenis_pelanggaran;
    const lubangMatch = spdk.sanksi.match(/\d+/);
    if (lubangMatch) props.form.lubang = parseInt(lubangMatch[0], 10);
    spdkQuery.value = `${spdk.no_pelanggaran} - ${spdk.jenis_pelanggaran}`;
    showSpdkResults.value = false;
};
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <div v-if="previewNomorSurat" class="mb-4 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2 text-sm text-blue-700">
            Nomor surat: <span class="font-bold">{{ previewNomorSurat }}</span>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Karyawan <span class="text-red-500">*</span></label>
                <input
                    v-model="employeeQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari NRP atau nama karyawan..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showEmployeeResults = true"
                >
                <div v-if="showEmployeeResults && employeeResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
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
                <label class="mb-1 block text-sm font-semibold text-slate-700">Perusahaan</label>
                <input v-model="form.perusahaan" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Lokasi</label>
                <input v-model="form.lokasi" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Pelanggaran <span class="text-red-500">*</span></label>
                <input v-model="form.tanggal_pelanggaran" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tanggal_pelanggaran" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal_pelanggaran }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jam Pelanggaran</label>
                <input v-model="form.jam_pelanggaran" type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Cari Referensi SPDK</label>
                <input
                    v-model="spdkQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari jenis pelanggaran atau sanksi..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showSpdkResults = true"
                >
                <div v-if="showSpdkResults && spdkResults.length" class="absolute z-10 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button
                        v-for="spdk in spdkResults"
                        :key="spdk.id"
                        type="button"
                        class="block w-full border-b border-slate-50 px-3 py-2 text-left text-sm last:border-0 hover:bg-slate-50"
                        @click="pickSpdk(spdk)"
                    >
                        <div class="font-semibold text-slate-700">{{ spdk.no_pelanggaran }} &mdash; {{ spdk.sanksi }}</div>
                        <div class="text-xs text-slate-500">{{ spdk.jenis_pelanggaran }}</div>
                    </button>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jenis Pelanggaran <span class="text-red-500">*</span></label>
                <textarea v-model="form.jenis_pelanggaran" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                <p v-if="form.errors.jenis_pelanggaran" class="mt-1 text-xs text-red-600">{{ form.errors.jenis_pelanggaran }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">SPDK Ref</label>
                <input v-model="form.spdk_ref" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jumlah Lubang</label>
                <input v-model.number="form.lubang" type="number" min="0" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Keterangan</label>
                <textarea v-model="form.keterangan" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <div class="relative">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Pengawas</label>
                <input
                    v-model="pengawasQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari nama pengawas..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showPengawasResults = true"
                >
                <div v-if="showPengawasResults && pengawasResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button
                        v-for="employee in pengawasResults"
                        :key="employee.id"
                        type="button"
                        class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="pickPengawas(employee)"
                    >
                        <span class="font-semibold text-slate-700">{{ employee.nrp }}</span>
                        <span class="text-slate-500"> &ndash; {{ employee.name }}</span>
                    </button>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Berlaku Sampai <span class="text-red-500">*</span></label>
                <input v-model="form.berlaku_sampai" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.berlaku_sampai" class="mt-1 text-xs text-red-600">{{ form.errors.berlaku_sampai }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Dikeluarkan Di</label>
                <input v-model="form.dikeluarkan_di" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Dikeluarkan <span class="text-red-500">*</span></label>
                <input v-model="form.dikeluarkan_tanggal" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.dikeluarkan_tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.dikeluarkan_tanggal }}</p>
            </div>

            <SignaturePad v-model="form.ttd_karyawan" label="TTD Karyawan" />
            <SignaturePad v-model="form.ttd_atasan" label="TTD Atasan" />
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <a href="/one-power/pembinaan/sk-pembinaan" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60"
            >
                {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
            </button>
        </div>
    </form>
</template>
