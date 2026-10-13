<script setup>
defineProps({
    form: { type: Object, required: true },
    statuses: { type: Array, required: true },
    roles: { type: Array, default: () => [] },
    onSubmit: { type: Function, required: true },
});

defineEmits(['role-changed']);
</script>

<template>
    <form class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm" @submit.prevent="onSubmit">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">NRP <span class="text-red-500">*</span></label>
                <input v-model="form.nrp" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.nrp" class="mt-1 text-xs text-red-600">{{ form.errors.nrp }}</p>
                <p class="mt-1 text-xs text-slate-400">Akun login One-Core akan otomatis dibuat/diperbarui mengikuti NRP ini (password default = NRP).</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Nama Lengkap <span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jabatan (SS6)</label>
                <input v-model="form.jabatan_ss6" type="text" placeholder="Contoh: OPERATOR" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div v-if="roles.length">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Role Akun Login</label>
                <select
                    v-model="form.role"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                    @change="$emit('role-changed')"
                >
                    <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                </select>
                <p v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</p>
                <p class="mt-1 text-xs text-slate-400">Terisi otomatis dari Jabatan (SS6), tetap bisa diubah.</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Jabatan (Roster)</label>
                <input v-model="form.jabatan_roster" type="text" placeholder="Contoh: 1B" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Status Karyawan <span class="text-red-500">*</span></label>
                <select v-model="form.status_karyawan" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
                    <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                </select>
                <p v-if="form.errors.status_karyawan" class="mt-1 text-xs text-red-600">{{ form.errors.status_karyawan }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Unit</label>
                <input v-model="form.unit" type="text" placeholder="Contoh: EX2600" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Lokasi Kerja</label>
                <input v-model="form.lokasi_kerja" type="text" placeholder="Contoh: PIT 1" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Efektif Bekerja</label>
                <input v-model="form.efektif_bekerja" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                <input v-model="form.email" type="email" placeholder="nama@perusahaan.com" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">No. Telepon</label>
                <input v-model="form.no_telepon" type="text" placeholder="0812xxxx" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500">
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Catatan Tambahan</label>
                <textarea v-model="form.input_here" rows="3" placeholder="Tambahkan informasi tambahan jika ada..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"></textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
            <a href="/one-power/employees" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</a>
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
