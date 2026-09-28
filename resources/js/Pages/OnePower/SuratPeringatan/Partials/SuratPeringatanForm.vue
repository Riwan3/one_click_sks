<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import SignaturePad from '@/Components/ui/SignaturePad.vue';
import PhotoUploadField from '@/Components/ui/PhotoUploadField.vue';

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

const activeSp = ref(null);
const isCheckingActive = ref(false);

watch(employeeQuery, (keyword) => {
    clearTimeout(employeeSearchTimer);
    if (! keyword || keyword.length < 2) {
        employeeResults.value = [];
        return;
    }
    employeeSearchTimer = setTimeout(async () => {
        const response = await fetch(`/one-power/pembinaan/surat-peringatan/search-employee?keyword=${encodeURIComponent(keyword)}`);
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
        const response = await fetch(`/one-power/pembinaan/surat-peringatan/search-employee?keyword=${encodeURIComponent(keyword)}`);
        pengawasResults.value = await response.json();
        showPengawasResults.value = true;
    }, 300);
});

const availableJenisOptions = computed(() => {
    if (! activeSp.value) {
        return ['Peringatan Pertama', 'Peringatan Kedua', 'Peringatan Terakhir'];
    }
    const current = activeSp.value.jenisPembinaan || activeSp.value.jenis_pembinaan;
    if (current === 'Peringatan Pertama') {
        return ['Peringatan Kedua', 'Peringatan Terakhir'];
    }
    if (current === 'Peringatan Kedua') {
        return ['Peringatan Terakhir'];
    }
    if (current === 'Peringatan Terakhir') {
        return [];
    }
    return ['Peringatan Pertama', 'Peringatan Kedua', 'Peringatan Terakhir'];
});

const syncKodePelanggaran = (jenis) => {
    if (jenis === 'Peringatan Pertama') {
        props.form.kode_pelanggaran = 'SP 1';
    } else if (jenis === 'Peringatan Kedua') {
        props.form.kode_pelanggaran = 'SP 2';
    } else if (jenis === 'Peringatan Terakhir') {
        props.form.kode_pelanggaran = 'SP 3';
    }
};

const applyActiveSp = (sp) => {
    // Abaikan jika sp yang ditemukan adalah data yang sedang diedit ini sendiri
    if (sp && props.form.id && sp.id === props.form.id) {
        activeSp.value = null;
        return;
    }

    activeSp.value = sp;

    if (sp) {
        const current = sp.jenisPembinaan || sp.jenis_pembinaan;

        // Otomatis isi Peringatan Sebelumnya
        props.form.peringatan_sebelumnya_status = 'Ada';
        props.form.peringatan_sebelumnya_jenis = current;

        const list = [];
        if (sp.pelanggaranAbsenTerlambat || sp.pelanggaran_absen_terlambat) list.push('Absen/Terlambat');
        if (sp.pelanggaranPerilaku || sp.pelanggaran_perilaku) list.push('Perilaku');
        if (sp.pelanggaranCerobohLalai || sp.pelanggaran_ceroboh_lalai) list.push('Ceroboh/Lalai');
        if (sp.pelanggaranHasilKerja || sp.pelanggaran_hasil_kerja) list.push('Hasil Kerja');
        if (sp.pelanggaranMenolakPerintah || sp.pelanggaran_menolak_perintah) list.push('Menolak Perintah');
        if (sp.pelanggaranPeraturan || sp.pelanggaran_peraturan) list.push('Pelanggaran Peraturan');

        props.form.peringatan_sebelumnya_mengenai = list.join(', ') || sp.koreksiAlpa || sp.koreksi_alpa || sp.detailPelanggaran || sp.detail_pelanggaran || '';

        // Tentukan tingkat SP berikutnya secara otomatis
        if (current === 'Peringatan Pertama') {
            if (props.form.jenis_pembinaan === 'Peringatan Pertama' || ! props.form.jenis_pembinaan) {
                props.form.jenis_pembinaan = 'Peringatan Kedua';
                syncKodePelanggaran('Peringatan Kedua');
            }
        } else if (current === 'Peringatan Kedua') {
            props.form.jenis_pembinaan = 'Peringatan Terakhir';
            syncKodePelanggaran('Peringatan Terakhir');
        } else if (current === 'Peringatan Terakhir') {
            props.form.jenis_pembinaan = '';
            props.form.kode_pelanggaran = '';
        }
    } else {
        // Jika tidak ada SP aktif
        if (props.form.peringatan_sebelumnya_status === 'Ada' && ! props.form.id) {
            props.form.peringatan_sebelumnya_status = 'Tidak ada';
            props.form.peringatan_sebelumnya_jenis = '';
            props.form.peringatan_sebelumnya_mengenai = '';
        }
        if (! props.form.jenis_pembinaan || ! availableJenisOptions.value.includes(props.form.jenis_pembinaan)) {
            props.form.jenis_pembinaan = 'Peringatan Pertama';
            syncKodePelanggaran('Peringatan Pertama');
        } else if (! props.form.kode_pelanggaran) {
            syncKodePelanggaran(props.form.jenis_pembinaan);
        }
    }
};

const checkActiveSp = async () => {
    if (! props.form.employee_id) {
        applyActiveSp(null);
        return;
    }

    const tanggal = props.form.dikeluarkan_tanggal || props.form.tanggal_kejadian || new Date().toISOString().slice(0, 10);
    isCheckingActive.value = true;

    try {
        const params = new URLSearchParams({
            employee_id: props.form.employee_id,
            tanggal: tanggal,
        });
        const response = await fetch(`/one-power/pembinaan/surat-peringatan/check-active?${params}`);
        const data = await response.json();
        const active = data?.data ?? data;
        applyActiveSp(active);
    } catch (e) {
        console.error('Failed checking active SP:', e);
    } finally {
        isCheckingActive.value = false;
    }
};

const pickEmployee = (employee) => {
    props.form.employee_id = employee.id;
    props.form.nama = employee.name;
    employeeQuery.value = `${employee.nrp} - ${employee.name}`;
    showEmployeeResults.value = false;
    checkActiveSp();
};

const pickPengawas = (employee) => {
    props.form.pengawas_id = employee.id;
    props.form.pengawasName = employee.name;
    pengawasQuery.value = `${employee.nrp} - ${employee.name}`;
    showPengawasResults.value = false;
};

const onJenisPembinaanChange = () => {
    syncKodePelanggaran(props.form.jenis_pembinaan);
};

watch([() => props.form.dikeluarkan_tanggal, () => props.form.tanggal_kejadian], () => {
    if (props.form.employee_id) {
        checkActiveSp();
    }
});

onMounted(() => {
    if (props.form.employee_id) {
        checkActiveSp();
    } else {
        if (! props.form.kode_pelanggaran && props.form.jenis_pembinaan) {
            syncKodePelanggaran(props.form.jenis_pembinaan);
        }
    }
});

const pelanggaranOptions = [
    { key: 'pelanggaran_absen_terlambat', label: 'Absen / terlambat' },
    { key: 'pelanggaran_perilaku', label: 'Perilaku tidak sesuai' },
    { key: 'pelanggaran_ceroboh_lalai', label: 'Ceroboh / lalai' },
    { key: 'pelanggaran_hasil_kerja', label: 'Hasil kerja tidak sesuai standar' },
    { key: 'pelanggaran_menolak_perintah', label: 'Menolak perintah atasan' },
    { key: 'pelanggaran_peraturan', label: 'Pelanggaran peraturan perusahaan' },
];
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <!-- Banner Alert SP Aktif -->
        <div v-if="activeSp" class="mb-5 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
            <div class="flex items-start gap-2.5">
                <svg class="h-5 w-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="space-y-1">
                    <p class="font-bold text-amber-950">
                        Perhatian: Karyawan ini masih memiliki {{ activeSp.jenisPembinaan || activeSp.jenis_pembinaan }} yang aktif
                        <span v-if="activeSp.nomorSp || activeSp.nomor_sp">(No: {{ activeSp.nomorSp || activeSp.nomor_sp }})</span>.
                    </p>
                    <p v-if="availableJenisOptions.length > 0" class="text-xs text-amber-800">
                        Opsi SP tingkat sebelumnya otomatis dinonaktifkan. Pilihan yang tersedia disesuaikan ke:
                        <strong class="underline">{{ availableJenisOptions.join(' / ') }}</strong>
                        dan kode otomatis diset ke <strong>{{ form.kode_pelanggaran }}</strong>.
                    </p>
                    <p v-else class="text-xs font-bold text-red-600">
                        Karyawan ini telah mencapai tingkat Peringatan Terakhir aktif. Tidak dapat menerbitkan SP baru sebelum masa berlaku selesai atau melalui koordinasi HR/Pimpinan.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <!-- Pencarian Karyawan -->
            <div class="relative sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Karyawan <span class="text-red-500">*</span></label>
                <input
                    v-model="employeeQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Ketik NRP atau nama karyawan..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showEmployeeResults = true"
                >
                <div v-if="showEmployeeResults && employeeResults.length" class="absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl">
                    <button
                        v-for="employee in employeeResults"
                        :key="employee.id"
                        type="button"
                        class="block w-full px-3 py-2.5 text-left text-sm hover:bg-slate-50 border-b border-slate-100 last:border-0"
                        @click="pickEmployee(employee)"
                    >
                        <span class="font-bold text-slate-800">{{ employee.nrp }}</span>
                        <span class="text-slate-600"> &ndash; {{ employee.name }}</span>
                        <span class="ml-2 rounded bg-slate-100 px-1.5 py-0.5 text-xs text-slate-500">{{ employee.jabatan_ss6 || '-' }}</span>
                    </button>
                </div>
                <p v-if="form.errors.employee_id" class="mt-1 text-xs text-red-600">{{ form.errors.employee_id }}</p>
            </div>

            <!-- Jenis Pembinaan -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jenis Pembinaan <span class="text-red-500">*</span></label>
                <select
                    v-model="form.jenis_pembinaan"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold outline-none focus:border-blue-500"
                    :disabled="availableJenisOptions.length === 0"
                    @change="onJenisPembinaanChange"
                >
                    <option v-if="availableJenisOptions.length === 0" value="" disabled>Tidak ada pilihan SP (Sudah SP Terakhir)</option>
                    <option v-for="opt in availableJenisOptions" :key="opt" :value="opt">{{ opt }}</option>
                </select>
                <p v-if="form.errors.jenis_pembinaan" class="mt-1 text-xs text-red-600">{{ form.errors.jenis_pembinaan }}</p>
                <p v-if="availableJenisOptions.length === 0" class="mt-1 text-xs font-semibold text-red-600">
                    Karyawan masih dalam masa SP Terakhir aktif.
                </p>
            </div>

            <!-- Kode Pelanggaran (Otomatis SP 1, SP 2, SP 3) -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Kode Pelanggaran</label>
                <input
                    v-model="form.kode_pelanggaran"
                    type="text"
                    placeholder="Contoh: SP 1, SP 2, SP 3 atau A.1.1"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-800 outline-none focus:border-blue-500 bg-slate-50 focus:bg-white"
                >
                <p class="mt-1 text-[11px] text-slate-400">Terisi otomatis sesuai jenis pembinaan (bisa diedit jika memakai kode pasal).</p>
            </div>

            <!-- Tanggal Kejadian & Jam -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Kejadian <span class="text-red-500">*</span></label>
                <input v-model="form.tanggal_kejadian" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tanggal_kejadian" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal_kejadian }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jam Kejadian</label>
                <input v-model="form.jam_kejadian" type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <!-- Tempat Kejadian -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tempat Kejadian</label>
                <input v-model="form.tempat_kejadian" type="text" placeholder="Contoh: Pit 1 / Workshop / Jalan Hauling" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <!-- Checkbox Jenis Pelanggaran -->
            <div class="sm:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Jenis Pelanggaran</label>
                <div class="grid gap-2.5 sm:grid-cols-2 rounded-lg border border-slate-100 bg-slate-50/70 p-3">
                    <label v-for="opt in pelanggaranOptions" :key="opt.key" class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                        <input v-model="form[opt.key]" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span>{{ opt.label }}</span>
                    </label>
                </div>
            </div>

            <!-- Detail Pelanggaran -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Detail Pelanggaran</label>
                <textarea v-model="form.detail_pelanggaran" rows="3" placeholder="Uraian singkat mengenai kejadian pelanggaran..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <!-- Pengawas / Supervisor -->
            <div class="relative">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Pengawas / Atasan</label>
                <input
                    v-model="pengawasQuery"
                    type="text"
                    autocomplete="off"
                    placeholder="Ketik nama atau NRP pengawas..."
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @focus="showPengawasResults = true"
                >
                <div v-if="showPengawasResults && pengawasResults.length" class="absolute z-20 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl">
                    <button
                        v-for="employee in pengawasResults"
                        :key="employee.id"
                        type="button"
                        class="block w-full px-3 py-2.5 text-left text-sm hover:bg-slate-50 border-b border-slate-100 last:border-0"
                        @click="pickPengawas(employee)"
                    >
                        <span class="font-bold text-slate-800">{{ employee.nrp }}</span>
                        <span class="text-slate-600"> &ndash; {{ employee.name }}</span>
                    </button>
                </div>
            </div>

            <!-- Koreksi Alpa -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Koreksi Alpa</label>
                <input v-model="form.koreksi_alpa" type="text" placeholder="Jika ada koreksi alpa..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <!-- Peringatan Sebelumnya -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Peringatan Sebelumnya</label>
                <select v-model="form.peringatan_sebelumnya_status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option value="Tidak ada">Tidak ada</option>
                    <option value="Ada">Ada</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Masa Berlaku Sanksi (Bulan)</label>
                <input v-model.number="form.sanksi_masa_berlaku" type="number" min="1" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div v-if="form.peringatan_sebelumnya_status === 'Ada'" class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jenis Peringatan Sebelumnya</label>
                <input v-model="form.peringatan_sebelumnya_jenis" type="text" placeholder="Contoh: Peringatan Pertama" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div v-if="form.peringatan_sebelumnya_status === 'Ada'" class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Perihal Peringatan Sebelumnya</label>
                <textarea v-model="form.peringatan_sebelumnya_mengenai" rows="2" placeholder="Uraian perihal pelanggaran sebelumnya..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <!-- Lokasi & Tanggal Dikeluarkan -->
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Dikeluarkan Di</label>
                <input v-model="form.dikeluarkan_di" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Dikeluarkan <span class="text-red-500">*</span></label>
                <input v-model="form.dikeluarkan_tanggal" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500" @change="checkActiveSp">
                <p v-if="form.errors.dikeluarkan_tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.dikeluarkan_tanggal }}</p>
            </div>

            <!-- Catatan Karyawan -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Catatan Karyawan</label>
                <textarea v-model="form.catatan_karyawan" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <!-- Upload Foto Bukti SP -->
            <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                <PhotoUploadField
                    v-model="form.foto"
                    :existing-photo="form.existing_foto"
                    :hapus-foto="form.hapus_foto"
                    :error="form.errors?.foto"
                    label="Foto Bukti Pelanggaran SP"
                    @update:hapus-foto="form.hapus_foto = $event"
                />
            </div>

            <!-- Tanda Tangan -->
            <SignaturePad v-model="form.ttd_karyawan" label="TTD Karyawan" />
            <SignaturePad v-model="form.ttd_atasan" label="TTD Atasan" />
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <Link href="/one-power/pembinaan/surat-peringatan" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Kembali</Link>
            <button
                type="submit"
                :disabled="form.processing || availableJenisOptions.length === 0"
                class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                {{ form.processing ? 'Menyimpan...' : 'Simpan Data' }}
            </button>
        </div>
    </form>
</template>
