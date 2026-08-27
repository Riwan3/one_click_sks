<script setup>
import { ref, watch } from 'vue';
import SignaturePad from '@/Components/ui/SignaturePad.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
});

const employeeResults = ref([]);
const showResults = ref(false);
const employeeQuery = ref(props.form.nama);
let searchTimer = null;

watch(employeeQuery, (keyword) => {
    clearTimeout(searchTimer);
    if (! keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/validasi-istirahat/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showResults.value = true;
    }, 300);
});

const pickEmployee = (employee) => {
    props.form.employee_id = employee.id;
    props.form.nrp = employee.nrp;
    props.form.nama = employee.name;
    props.form.jabatan = employee.jabatan_ss6 ?? '';
    props.form.departemen = employee.unit ?? '';
    employeeQuery.value = `${employee.nrp} - ${employee.name}`;
    showResults.value = false;
};
</script>

<template>
    <form class="max-w-4xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <!-- SECTION 1: IDENTITAS KARYAWAN -->
        <div class="mb-6">
            <h3 class="mb-4 text-lg font-bold text-slate-800">IDENTITAS KARYAWAN</h3>
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
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Jabatan</label>
                    <input v-model="form.jabatan" type="text" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Departemen</label>
                    <input v-model="form.departemen" type="text" readonly class="w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Validasi <span class="text-red-500">*</span></label>
                    <input v-model="form.tanggal_validasi" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <p v-if="form.errors.tanggal_validasi" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal_validasi }}</p>
                </div>
            </div>
        </div>

        <!-- SECTION 2: PERTANYAAN & JAWABAN -->
        <div class="mb-6">
            <h3 class="mb-4 text-lg font-bold text-slate-800">PERTANYAAN & JAWABAN</h3>
            <div class="grid gap-4 sm:grid-cols-2 space-y-4">
                <!-- Q1: Alasan Kurang Tidur -->
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q1. Mengapa jam tidur anda kurang dari 6 jam? <span class="text-red-500">*</span></label>
                    <textarea
                        v-model="form.alasan_kurang_tidur"
                        rows="2"
                        placeholder="Contoh: Sibuk bekerja lembur..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    ></textarea>
                    <p v-if="form.errors.alasan_kurang_tidur" class="mt-1 text-xs text-red-600">{{ form.errors.alasan_kurang_tidur }}</p>
                </div>

                <!-- Q2: Alasan Kurang Tidur Berulang -->
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q2. (Khusus untuk karyawan dengan jam tidur kurang dari 6 jam, berulang) Berapa lama sudah seperti itu? <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input
                            v-model="form.alasan_kurang_tidur_berulang"
                            type="number"
                            placeholder="Contoh: 3"
                            class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                        >
                        <span class="text-sm text-slate-600">bulan</span>
                    </div>
                    <p v-if="form.errors.alasan_kurang_tidur_berulang" class="mt-1 text-xs text-red-600">{{ form.errors.alasan_kurang_tidur_berulang }}</p>
                </div>

                <!-- Q3: Kendala Tidur -->
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q3. Apakah anda mengalami kendala tidur seperti gangguan tidur, insomnia, sleep apnea, restless legs? <span class="text-red-500">*</span></label>
                    <select v-model="form.kendala_tidur" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                        <option value="">-- Pilih --</option>
                        <option value="Ya">Ya</option>
                        <option value="Tidak">Tidak</option>
                    </select>
                    <p v-if="form.errors.kendala_tidur" class="mt-1 text-xs text-red-600">{{ form.errors.kendala_tidur }}</p>
                </div>

                <!-- Q4: Tinggal Di -->
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q4. Dimana anda tinggal saat ini? <span class="text-red-500">*</span></label>
                    <select v-model="form.tinggal_di" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                        <option value="">-- Pilih --</option>
                        <option value="Mess">Mess</option>
                        <option value="Non Mess">Non Mess</option>
                    </select>
                    <p v-if="form.errors.tinggal_di" class="mt-1 text-xs text-red-600">{{ form.errors.tinggal_di }}</p>
                </div>

                <!-- Q4a: Nama Mess -->
                <div v-if="form.tinggal_di === 'Mess'" class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q4a. Nama Mess</label>
                    <textarea
                        v-model="form.nama_mess"
                        rows="2"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    ></textarea>
                </div>

                <!-- Q4b: Alamat Non Mess -->
                <div v-if="form.tinggal_di === 'Non Mess'" class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q4b. Alamat Non Mess</label>
                    <textarea
                        v-model="form.alamat_non_mess"
                        rows="2"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    ></textarea>
                </div>

                <!-- Q5: Status Perkawinan -->
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q5. Status perkawinan anda saat ini? <span class="text-red-500">*</span></label>
                    <select v-model="form.status_perkawinan" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                        <option value="">-- Pilih Status --</option>
                        <option value="Single">Single</option>
                        <option value="Menikah">Menikah</option>
                    </select>
                    <p v-if="form.errors.status_perkawinan" class="mt-1 text-xs text-red-600">{{ form.errors.status_perkawinan }}</p>
                </div>

                <!-- Q6: Jumlah Anak Serumah -->
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q6. Jumlah anak yang tinggal serumah?</label>
                    <input
                        v-model="form.jumlah_anak_serumah"
                        type="number"
                        placeholder="Contoh: 2 orang"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                </div>

                <!-- Q7: Anak Usia < 2 Tahun -->
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q7. Apakah ada anak berusia kurang dari 2 tahun?</label>
                    <input
                        v-model="form.anak_usia_kurang_2th"
                        type="number"
                        placeholder="Contoh: 1 tahun"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    >
                </div>

                <!-- Q8: Pekerjaan Sampingan -->
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q8. Apakah anda memiliki pekerjaan sampingan di luar perusahaan? <span class="text-red-500">*</span></label>
                    <textarea
                        v-model="form.pekerjaan_sampingan"
                        rows="2"
                        placeholder="Contoh: Usaha sampingan online..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    ></textarea>
                    <p v-if="form.errors.pekerjaan_sampingan" class="mt-1 text-xs text-red-600">{{ form.errors.pekerjaan_sampingan }}</p>
                </div>

                <!-- Q9: Keterangan Tambahan -->
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Q9. Keterangan Tambahan</label>
                    <textarea
                        v-model="form.keterangan"
                        rows="2"
                        placeholder="Contoh: Sudah konsultasi dengan dokter..."
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    ></textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 3: INFORMASI PENGAWAS -->
        <div class="mb-6">
            <h3 class="mb-4 text-lg font-bold text-slate-800">INFORMASI PENGAWAS</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Nama Pengawas <span class="text-red-500">*</span></label>
                    <input v-model="form.nama_pengawas" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <p v-if="form.errors.nama_pengawas" class="mt-1 text-xs text-red-600">{{ form.errors.nama_pengawas }}</p>
                </div>
            </div>
        </div>

        <!-- SECTION 4: TANDA TANGAN -->
        <div class="mb-6">
            <h3 class="mb-4 text-lg font-bold text-slate-800">TANDA TANGAN</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">TTD Karyawan</label>
                    <SignaturePad v-model="form.ttd_karyawan" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">TTD Pengawas</label>
                    <SignaturePad v-model="form.ttd_pengawas" />
                </div>
            </div>
        </div>

        <button type="submit" :disabled="form.processing" class="mt-6 w-full rounded-lg bg-blue-500 py-2.5 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60 sm:w-auto sm:px-8">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
        </button>
    </form>
</template>
