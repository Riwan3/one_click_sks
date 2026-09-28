<script setup>
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import SignaturePad from '@/Components/ui/SignaturePad.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
});

const employeeResults = ref([]);
const showResults = ref(false);
const employeeQuery = ref(props.form.employee_name || '');
let searchTimer = null;

const pengawasResults = ref([]);
const showPengawasResults = ref(false);
const pengawasQuery = ref(props.form.pengawas_name || '');
let pengawasTimer = null;

watch(employeeQuery, (keyword) => {
    clearTimeout(searchTimer);
    if (! keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/interview-fatigue/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showResults.value = true;
    }, 300);
});

watch(pengawasQuery, (keyword) => {
    clearTimeout(pengawasTimer);
    if (! keyword || keyword.length < 2) {
        pengawasResults.value = [];
        return;
    }
    pengawasTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/interview-fatigue/search-employee?keyword=${encodeURIComponent(keyword)}`);
        pengawasResults.value = await response.json();
        showPengawasResults.value = true;
    }, 300);
});

const pickEmployee = (employee) => {
    props.form.employee_id = employee.id;
    props.form.employee_name = `${employee.nrp} - ${employee.name}`;
    employeeQuery.value = `${employee.nrp} - ${employee.name}`;
    showResults.value = false;
};

const pickPengawas = (employee) => {
    props.form.pengawas_id = employee.id;
    props.form.pengawas_name = `${employee.nrp} - ${employee.name}`;
    pengawasQuery.value = `${employee.nrp} - ${employee.name}`;
    showPengawasResults.value = false;
};
</script>

<template>
    <form class="max-w-4xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <!-- Informasi Karyawan & Dokumen -->
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Operator <span class="text-red-500">*</span></label>
                <input
                    v-model="employeeQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari NRP atau nama operator..."
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showResults = true"
                >
                <div v-if="showResults && employeeResults.length" class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
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

            <div class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Pengawas</label>
                <input
                    v-model="pengawasQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Cari NRP atau nama pengawas..."
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showPengawasResults = true"
                >
                <div v-if="showPengawasResults && pengawasResults.length" class="absolute z-20 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
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
                <p v-if="form.errors.pengawas_id" class="mt-1 text-xs text-red-600">{{ form.errors.pengawas_id }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nomor Unit</label>
                <input
                    v-model="form.nomor_unit"
                    type="text"
                    placeholder="Contoh: DT 123"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                >
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Area</label>
                <input
                    v-model="form.area"
                    type="text"
                    placeholder="Contoh: Pit / Hauling"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                >
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal <span class="text-red-500">*</span></label>
                <input
                    v-model="form.tanggal"
                    type="date"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                >
                <p v-if="form.errors.tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jam</label>
                <input
                    v-model="form.jam"
                    type="time"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:border-blue-500"
                >
            </div>
        </div>

        <!-- Bagian Pertanyaan Kuesioner Fatigue (Sesuai Form Asli) -->
        <div class="mt-8 space-y-6 border-t border-slate-200 pt-6">
            <!-- 1 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    1. Pada Hari berapakah anda saat ini bekerja pada shift ini?
                </label>
                <input
                    v-model="form.q1_shift_day"
                    type="text"
                    placeholder="Contoh: Hari ke 10 shift malam"
                    class="w-full rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                >
                <p v-if="form.errors.q1_shift_day" class="mt-1 text-xs text-red-600">{{ form.errors.q1_shift_day }}</p>
            </div>

            <!-- 2 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    2. Bagaimana kondisi tubuh anda saat ini?
                </label>
                <input
                    v-model="form.q2_body_condition"
                    type="text"
                    placeholder="Contoh: Lelah"
                    class="w-full rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                >
                <p v-if="form.errors.q2_body_condition" class="mt-1 text-xs text-red-600">{{ form.errors.q2_body_condition }}</p>
            </div>

            <!-- 3 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    3. Apakah yang anda rasakan (Yang melatarbelakangi anda) Sehingga meminta Izin Fatigue?
                </label>
                <textarea
                    v-model="form.q3_reason"
                    rows="3"
                    class="w-full resize-y rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                ></textarea>
                <p v-if="form.errors.q3_reason" class="mt-1 text-xs text-red-600">{{ form.errors.q3_reason }}</p>

                <label class="mb-1.5 mt-3 block text-xs italic font-medium text-slate-600">
                    Menurut anda kenapa bisa merasakan hal tersebut?
                </label>
                <textarea
                    v-model="form.q3_background"
                    rows="3"
                    class="w-full resize-y rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                ></textarea>
                <p v-if="form.errors.q3_background" class="mt-1 text-xs text-red-600">{{ form.errors.q3_background }}</p>
            </div>

            <!-- 4 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    4. Berapa Jam Anda Istirahat sebelum masuk kerja?
                </label>
                <input
                    v-model="form.q4_rest_hours"
                    type="text"
                    placeholder="Contoh: 6 jam"
                    class="w-full rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                >
                <p v-if="form.errors.q4_rest_hours" class="mt-1 text-xs text-red-600">{{ form.errors.q4_rest_hours }}</p>
            </div>

            <!-- 5 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    5. Bagaimana kualitas istirahat anda?
                </label>
                <input
                    v-model="form.q5_rest_quality"
                    type="text"
                    placeholder="Contoh: Baik"
                    class="w-full rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                >
                <p v-if="form.errors.q5_rest_quality" class="mt-1 text-xs text-red-600">{{ form.errors.q5_rest_quality }}</p>

                <label class="mb-1.5 mt-3 block text-xs italic font-medium text-slate-600">
                    Jika kurang berkualitas, hal apa yang menyebabkan hal tersebut?
                </label>
                <textarea
                    v-model="form.q5_bad_quality_reason"
                    rows="3"
                    class="w-full resize-y rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                ></textarea>
                <p v-if="form.errors.q5_bad_quality_reason" class="mt-1 text-xs text-red-600">{{ form.errors.q5_bad_quality_reason }}</p>
            </div>

            <!-- 6 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    6. Apakah lingkungan tempat tinggal anda nyaman untuk istirahat?
                </label>
                <input
                    v-model="form.q6_home_comfort"
                    type="text"
                    placeholder="Contoh: Baik"
                    class="w-full rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                >
                <p v-if="form.errors.q6_home_comfort" class="mt-1 text-xs text-red-600">{{ form.errors.q6_home_comfort }}</p>

                <label class="mb-1.5 mt-3 block text-xs italic font-medium text-slate-600">
                    Apabila lingkungan tempat tinggal anda tidak nyaman apa yang menyebabkan hal tersebut?
                </label>
                <textarea
                    v-model="form.q6_discomfort_reason"
                    rows="3"
                    class="w-full resize-y rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                ></textarea>
                <p v-if="form.errors.q6_discomfort_reason" class="mt-1 text-xs text-red-600">{{ form.errors.q6_discomfort_reason }}</p>
            </div>

            <!-- 7 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    7. Setelah anda pulang kerja, sampai rumah / mess apakah ada aktivitas lain selain istirahat / tidur?
                </label>
                <input
                    v-model="form.q7_other_activities"
                    type="text"
                    placeholder="Contoh: Tidak ada"
                    class="w-full rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                >
                <p v-if="form.errors.q7_other_activities" class="mt-1 text-xs text-red-600">{{ form.errors.q7_other_activities }}</p>

                <label class="mb-1.5 mt-3 block text-xs italic font-medium text-slate-600">
                    Apabila Melakukan Aktivitas lain, apa aktivitas yang anda lakukan?
                </label>
                <textarea
                    v-model="form.q7_activity_details"
                    rows="3"
                    class="w-full resize-y rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                ></textarea>
                <p v-if="form.errors.q7_activity_details" class="mt-1 text-xs text-red-600">{{ form.errors.q7_activity_details }}</p>
            </div>

            <!-- 8 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    8. Apakah pernah mendapat edukasi karyawan terkait fatigue? Kapan?
                </label>
                <input
                    v-model="form.q8_education"
                    type="text"
                    placeholder="Contoh: Pernah"
                    class="w-full rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                >
                <p v-if="form.errors.q8_education" class="mt-1 text-xs text-red-600">{{ form.errors.q8_education }}</p>
            </div>

            <!-- 9 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    9. Hari Keberapa biasanya anda merasakan fatigue berat?
                </label>
                <input
                    v-model="form.q9_heavy_fatigue_day"
                    type="text"
                    placeholder="Contoh: Hari ke 10 - 13"
                    class="w-full rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                >
                <p v-if="form.errors.q9_heavy_fatigue_day" class="mt-1 text-xs text-red-600">{{ form.errors.q9_heavy_fatigue_day }}</p>
            </div>

            <!-- 10 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    10. Hal Apakah yang akan anda lakukan agar bisa senantiasa fit/bugar saat mengoperasikan unit?
                </label>
                <textarea
                    v-model="form.q10_fit_plan"
                    rows="3"
                    class="w-full resize-y rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                ></textarea>
                <p v-if="form.errors.q10_fit_plan" class="mt-1 text-xs text-red-600">{{ form.errors.q10_fit_plan }}</p>
            </div>

            <!-- 11 -->
            <div>
                <label class="mb-1.5 block text-sm font-bold text-slate-800">
                    11. Apa saran anda untuk mengatasi fatigue?
                </label>
                <textarea
                    v-model="form.q11_suggestions"
                    rows="3"
                    class="w-full resize-y rounded-lg border border-slate-200 bg-[#F8FAFC] px-3.5 py-2.5 text-sm text-slate-800 placeholder-slate-400 outline-none transition-colors focus:border-blue-500 focus:bg-white"
                ></textarea>
                <p v-if="form.errors.q11_suggestions" class="mt-1 text-xs text-red-600">{{ form.errors.q11_suggestions }}</p>
            </div>

            <!-- Tanda Tangan Pengawas -->
            <div class="border-t border-slate-200 pt-5">
                <label class="mb-2 block text-sm font-bold text-slate-800">Tanda Tangan Pengawas</label>
                <SignaturePad v-model="form.ttd_pengawas" />
            </div>
        </div>

        <div class="mt-8 flex items-center gap-3">
            <button
                type="submit"
                :disabled="form.processing"
                class="rounded-lg bg-blue-500 px-8 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-600 disabled:opacity-60"
            >
                {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
            <Link
                href="/one-power/pembinaan/interview-fatigue"
                class="rounded-lg border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition"
            >
                Kembali
            </Link>
        </div>
    </form>
</template>
