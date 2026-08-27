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
        const response = await fetch(`/one-power/deviasi-operator/unfit/search-employee?keyword=${encodeURIComponent(keyword)}`);
        employeeResults.value = await response.json();
        showResults.value = true;
    }, 300);
});

const pickEmployee = (employee) => {
    props.form.nrp = employee.nrp;
    props.form.nama = employee.name;
    props.form.departemen = employee.unit ?? '';
    props.form.contact = employee.no_telepon ?? '';
    showResults.value = false;
};

const handleFileChange = (event) => {
    props.form.bukti = event.target.files[0] ?? null;
};
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <div class="grid gap-4 sm:grid-cols-2">
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
                        <span class="text-slate-500"> &ndash; {{ employee.name }}</span>
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
                <label class="mb-1 block text-sm font-semibold text-slate-700">Departemen</label>
                <input v-model="form.departemen" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Kontak</label>
                <input v-model="form.contact" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Diagnosa</label>
                <input v-model="form.diagnosa" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Dokter Rujukan</label>
                <input v-model="form.dokter_rujukan" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Mulai Unfit <span class="text-red-500">*</span></label>
                <input v-model="form.tgl_mulai_unfit" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tgl_mulai_unfit" class="mt-1 text-xs text-red-600">{{ form.errors.tgl_mulai_unfit }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal Akhir Unfit</label>
                <input v-model="form.tgl_akhir_unfit" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tgl_akhir_unfit" class="mt-1 text-xs text-red-600">{{ form.errors.tgl_akhir_unfit }}</p>
                <p class="mt-1 text-xs text-slate-400">Kosongkan jika status unfit masih berlangsung.</p>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Laporan</label>
                <textarea v-model="form.laporan" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Bukti (gambar/PDF/dokumen)</label>
                <input type="file" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx" class="w-full text-sm" @change="handleFileChange">
                <p v-if="form.errors.bukti" class="mt-1 text-xs text-red-600">{{ form.errors.bukti }}</p>
                <a v-if="existingBuktiUrl" :href="existingBuktiUrl" target="_blank" class="mt-1 inline-block text-xs font-semibold text-blue-600 hover:underline">Lihat bukti saat ini</a>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <a href="/one-power/deviasi-operator/unfit" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
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
