<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    form: { type: Object, required: true },
    existingBuktiUrl: { type: String, default: null },
    onSubmit: { type: Function, required: true },
});

const employeeResults = ref([]);
const showResults = ref(false);
let searchTimer = null;

watch(() => props.form.nrp, (keyword) => {
    clearTimeout(searchTimer);
    if (! keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    searchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/deviasi-operator/izin-karyawan/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showResults.value = true;
    }, 300);
});

const pickEmployee = (employee) => {
    props.form.nrp = employee.nrp;
    props.form.nama = employee.nama;
    props.form.jabatan = employee.jabatan ?? '';
    props.form.departemen = employee.departemen ?? '';
    showResults.value = false;
};

const handleFileChange = (event) => {
    props.form.file_bukti = event.target.files[0] ?? null;
};
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jenis Izin <span class="text-red-500">*</span></label>
                <select v-model="form.jenis_izin" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="emergency">Emergency</option>
                </select>
                <p v-if="form.errors.jenis_izin" class="mt-1 text-xs text-red-600">{{ form.errors.jenis_izin }}</p>
            </div>

            <div class="relative">
                <label class="mb-1 block text-sm font-semibold text-slate-700">NRP <span class="text-red-500">*</span></label>
                <input
                    v-model="form.nrp"
                    type="text"
                    autocomplete="off"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showResults = true"
                >
                <div v-if="showResults && employeeResults.length" class="absolute z-10 mt-1 w-full rounded-lg border border-slate-200 bg-white shadow-lg">
                    <button
                        v-for="employee in employeeResults"
                        :key="employee.nrp"
                        type="button"
                        class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50"
                        @click="pickEmployee(employee)"
                    >
                        <span class="font-semibold text-slate-700">{{ employee.nrp }}</span>
                        <span class="text-slate-500"> &ndash; {{ employee.nama }}</span>
                    </button>
                </div>
                <p v-if="form.errors.nrp" class="mt-1 text-xs text-red-600">{{ form.errors.nrp }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                <input v-model="form.nama" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.nama" class="mt-1 text-xs text-red-600">{{ form.errors.nama }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jabatan</label>
                <input v-model="form.jabatan" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Departemen</label>
                <input v-model="form.departemen" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <template v-if="form.jenis_izin === 'izin' || form.jenis_izin === 'emergency'">
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Alasan <span class="text-red-500">*</span></label>
                    <textarea v-model="form.alasan" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                    <p v-if="form.errors.alasan" class="mt-1 text-xs text-red-600">{{ form.errors.alasan }}</p>
                </div>
                <div v-if="form.jenis_izin === 'izin'">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Tujuan</label>
                    <input v-model="form.tujuan" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div v-if="form.jenis_izin === 'izin'">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Alamat</label>
                    <input v-model="form.alamat" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Berangkat <span class="text-red-500">*</span></label>
                    <input v-model="form.tgl_berangkat" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <p v-if="form.errors.tgl_berangkat" class="mt-1 text-xs text-red-600">{{ form.errors.tgl_berangkat }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Kembali</label>
                    <input v-model="form.tgl_kembali" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <p v-if="form.errors.tgl_kembali" class="mt-1 text-xs text-red-600">{{ form.errors.tgl_kembali }}</p>
                </div>
            </template>

            <template v-else>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">No. Telepon</label>
                    <input v-model="form.no_telepon" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Keluhan</label>
                    <textarea v-model="form.keluhan" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">TD</label>
                    <input v-model="form.hasil_td" type="text" placeholder="120/80" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">RR</label>
                    <input v-model="form.hasil_rr" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Suhu (T)</label>
                    <input v-model="form.hasil_t" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Nadi (P)</label>
                    <input v-model="form.hasil_p" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">SpO2</label>
                    <input v-model="form.hasil_spo" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Diagnosa</label>
                    <textarea v-model="form.diagnosa" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Terapi</label>
                    <textarea v-model="form.terapi" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Rujuk Ke</label>
                    <input v-model="form.rujuk_ke" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Rumah Sakit Rujukan</label>
                    <input v-model="form.rumah_sakit_rujukan" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700">Dokter Rujukan</label>
                    <input v-model="form.dokter_rujukan" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input id="dipulangkan_tidur" v-model="form.dipulangkan_tidur" type="checkbox" class="h-4 w-4 rounded border-slate-300">
                    <label for="dipulangkan_tidur" class="text-sm font-semibold text-slate-700">Dipulangkan / Istirahat</label>
                </div>
            </template>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Bukti (gambar/PDF/dokumen)</label>
                <input type="file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx" class="w-full text-sm" @change="handleFileChange">
                <p v-if="form.errors.file_bukti" class="mt-1 text-xs text-red-600">{{ form.errors.file_bukti }}</p>
                <a v-if="existingBuktiUrl" :href="existingBuktiUrl" target="_blank" class="mt-1 inline-block text-xs font-semibold text-blue-600 hover:underline">Lihat bukti saat ini</a>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <a href="/one-power/deviasi-operator/izin-karyawan" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
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
