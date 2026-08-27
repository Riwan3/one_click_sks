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
let searchTimer = null;

const pengawasResults = ref([]);
const showPengawasResults = ref(false);
const pengawasQuery = ref('');
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
    employeeQuery.value = `${employee.nrp} - ${employee.name}`;
    showResults.value = false;
};

const pickPengawas = (employee) => {
    props.form.pengawas_id = employee.id;
    pengawasQuery.value = `${employee.nrp} - ${employee.name}`;
    showPengawasResults.value = false;
};

const q1Options = ['Hari', 'Malam'];
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Operator <span class="text-red-500">*</span></label>
                <input v-model="employeeQuery" type="text" autocomplete="off" placeholder="Cari NRP atau nama operator..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500" @focus="showResults = true">
                <div v-if="showResults && employeeResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button v-for="employee in employeeResults" :key="employee.id" type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50" @click="pickEmployee(employee)">
                        <span class="font-semibold text-slate-700">{{ employee.nrp }}</span>
                        <span class="text-slate-500"> &ndash; {{ employee.name }}</span>
                    </button>
                </div>
                <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>
            </div>

            <div class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Pengawas</label>
                <input v-model="pengawasQuery" type="text" autocomplete="off" placeholder="Cari NRP atau nama pengawas..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500" @focus="showPengawasResults = true">
                <div v-if="showPengawasResults && pengawasResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button v-for="employee in pengawasResults" :key="employee.id" type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50" @click="pickPengawas(employee)">
                        <span class="font-semibold text-slate-700">{{ employee.nrp }}</span>
                        <span class="text-slate-500"> &ndash; {{ employee.name }}</span>
                    </button>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nomor Unit</label>
                <input v-model="form.nomor_unit" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Area</label>
                <input v-model="form.area" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal <span class="text-red-500">*</span></label>
                <input v-model="form.tanggal" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jam</label>
                <input v-model="form.jam" type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div class="sm:col-span-2 mt-2 border-t border-slate-100 pt-4">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-500">Kuesioner Fatigue</h3>
            </div>

            <!-- Q1 Shift Day -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q1. Pada hari berapakah anda saat ini bekerja pada shift ini? <span class="text-red-500">*</span></label>
                <select v-model="form.q1_shift_day" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option value="">Pilih shift...</option>
                    <option v-for="opt in q1Options" :key="opt" :value="opt">{{ opt }}</option>
                </select>
                <p v-if="form.errors.q1_shift_day" class="mt-1 text-xs text-red-600">{{ form.errors.q1_shift_day }}</p>
            </div>

            <!-- Q2 Shift Night Hours -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q2. Berapa lamakah anda bekerja pada shift malam dalam seminggu?</label>
                <input v-model="form.q2_body_condition" type="number" placeholder="Contoh: 3 hari" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <!-- Q3 Rest Time & Reason -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q3. Berapa lama waktu istirahat anda antara shift malam dan shift berikutnya? <span class="text-red-500">*</span></label>
                <textarea v-model="form.q3_reason" rows="2" placeholder="Contoh: Hari ke 10 - 13" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                <p v-if="form.errors.q3_reason" class="mt-1 text-xs text-red-600">{{ form.errors.q3_reason }}</p>
            </div>

            <!-- Q3 Background -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q3. Menurut anda kenapa bisa merasakan hal tersebut? <span class="text-red-500">*</span></label>
                <textarea v-model="form.q3_background" rows="2" placeholder="Contoh: Hari ke 10 shift malam" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                <p v-if="form.errors.q3_background" class="mt-1 text-xs text-red-600">{{ form.errors.q3_background }}</p>
            </div>

            <!-- Q4 Less Than 4 Hours Sleep -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q4. Apakah anda mengalami jam tidur kurang dari 4 jam? <span class="text-red-500">*</span></label>
                <select v-model="form.q4_rest_hours" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option value="">Pilih...</option>
                    <option value="Ya">Ya</option>
                    <option value="Tidak">Tidak</option>
                </select>
                <p v-if="form.errors.q4_rest_hours" class="mt-1 text-xs text-red-600">{{ form.errors.q4_rest_hours }}</p>
            </div>

            <!-- Q5 Sleep Hours -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q5. Berapa jam anda tidur dalam sehari? <span class="text-red-500">*</span></label>
                <input v-model="form.q5_rest_quality" type="number" placeholder="Contoh: 6 jam" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.q5_rest_quality" class="mt-1 text-xs text-red-600">{{ form.errors.q5_rest_quality }}</p>
            </div>

            <!-- Q5 Bad Quality Reason -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q5. Jelaskan alasan kualitas tidur tidak baik</label>
                <textarea v-model="form.q5_bad_quality_reason" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <!-- Q6 Home Comfort -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q6. Apakah anda merasa nyaman di rumah/tempat tinggal? <span class="text-red-500">*</span></label>
                <select v-model="form.q6_home_comfort" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option value="">Pilih...</option>
                    <option value="Ya">Ya</option>
                    <option value="Tidak">Tidak</option>
                </select>
                <p v-if="form.errors.q6_home_comfort" class="mt-1 text-xs text-red-600">{{ form.errors.q6_home_comfort }}</p>
            </div>

            <!-- Q6 Discomfort Reason -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q6. Jelaskan ketidaknyamanan tempat tinggal</label>
                <textarea v-model="form.q6_discomfort_reason" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <!-- Q7 Other Activities -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q7. Apakah anda melakukan aktivitas lain di luar pekerjaan? <span class="text-red-500">*</span></label>
                <select v-model="form.q7_other_activities" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option value="">Pilih...</option>
                    <option value="Ya">Ya</option>
                    <option value="Tidak">Tidak</option>
                </select>
                <p v-if="form.errors.q7_other_activities" class="mt-1 text-xs text-red-600">{{ form.errors.q7_other_activities }}</p>
            </div>

            <!-- Q7 Activity Details -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q7. Jelaskan aktivitas lain yang dilakukan</label>
                <textarea v-model="form.q7_activity_details" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <!-- Q8 Sleep Issues -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q8. Apakah anda memiliki gangguan tidur seperti sleep apnea, insomnia, restless legs? <span class="text-red-500">*</span></label>
                <select v-model="form.q8_education" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option value="">Pilih...</option>
                    <option value="Ya">Ya</option>
                    <option value="Tidak">Tidak</option>
                </select>
                <p v-if="form.errors.q8_education" class="mt-1 text-xs text-red-600">{{ form.errors.q8_education }}</p>
            </div>

            <!-- Q9 Suggestions -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Q9. Saran/Masukan anda untuk mengurangi fatigue? <span class="text-red-500">*</span></label>
                <textarea v-model="form.q11_suggestions" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                <p v-if="form.errors.q11_suggestions" class="mt-1 text-xs text-red-600">{{ form.errors.q11_suggestions }}</p>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanda Tangan Pengawas</label>
                <SignaturePad v-model="form.ttd_pengawas" />
            </div>
        </div>

        <button type="submit" :disabled="form.processing" class="mt-6 w-full rounded-lg bg-blue-500 py-2.5 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60 sm:w-auto sm:px-8">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
        </button>
    </form>
</template>
