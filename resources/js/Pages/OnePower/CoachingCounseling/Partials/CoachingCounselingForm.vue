<script setup>
import SignaturePad from '@/Components/ui/SignaturePad.vue';
import EmployeeSearchInput from './EmployeeSearchInput.vue';

const props = defineProps({
    form: { type: Object, required: true },
    onSubmit: { type: Function, required: true },
    initialEmployeeLabel: { type: String, default: '' },
    initialCoachLabel: { type: String, default: '' },
});
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Anak Asuh <span class="text-red-500">*</span></label>
                <EmployeeSearchInput
                    :model-value="form.employee_id"
                    :initial-label="initialEmployeeLabel"
                    :error="form.errors.employee_id"
                    @update:model-value="(v) => (form.employee_id = v)"
                />
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Coach / Bapak Asuh <span class="text-red-500">*</span></label>
                <EmployeeSearchInput
                    :model-value="form.coach_employee_id"
                    :initial-label="initialCoachLabel"
                    :error="form.errors.coach_employee_id"
                    @update:model-value="(v) => (form.coach_employee_id = v)"
                />
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanggal <span class="text-red-500">*</span></label>
                <input v-model="form.tanggal" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.tanggal" class="mt-1 text-xs text-red-600">{{ form.errors.tanggal }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Waktu Pertemuan</label>
                <input v-model="form.waktu_pertemuan" type="time" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Lokasi</label>
                <input v-model="form.lokasi" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Fatigue Level (0-10)</label>
                <input v-model.number="form.fatigue_level" type="number" min="0" max="10" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Materi Pribadi</label>
                <textarea v-model="form.materi_pribadi" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Materi Keluarga</label>
                <textarea v-model="form.materi_keluarga" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Materi Lingkungan</label>
                <textarea v-model="form.materi_lingkungan" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Materi Pekerjaan</label>
                <textarea v-model="form.materi_pekerjaan" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Deskripsi</label>
                <textarea v-model="form.deskripsi" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Rekomendasi</label>
                <textarea v-model="form.rekomendasi" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>

            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanda Tangan Anak Asuh</label>
                <SignaturePad v-model="form.ttd_anak_asuh" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Tanda Tangan Bapak Asuh</label>
                <SignaturePad v-model="form.ttd_bapa_asuh" />
            </div>
        </div>

        <button type="submit" :disabled="form.processing" class="mt-6 w-full rounded-lg bg-blue-500 py-2.5 text-sm font-bold text-white hover:bg-blue-600 disabled:opacity-60 sm:w-auto sm:px-8">
            {{ form.processing ? 'Menyimpan...' : 'Simpan' }}
        </button>
    </form>
</template>
