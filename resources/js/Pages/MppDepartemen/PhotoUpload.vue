<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Upload Foto Bukti</h1>
            <p class="mt-1 text-sm text-gray-600">Unggah foto dengan watermark otomatis</p>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow p-6">
          <form @submit.prevent="submitForm" class="space-y-6">
            <!-- Jenis Foto -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Foto</label>
              <select
                v-model="form.jenis"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
              >
                <option value="">-- Pilih Jenis --</option>
                <option value="OJK">OJK (On-Job Knowledge)</option>
                <option value="Checkpoint">Checkpoint</option>
              </select>
              <p v-if="form.errors.jenis" class="mt-1 text-sm text-red-600">{{ form.errors.jenis }}</p>
            </div>

            <!-- Unit Code -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Kode Unit</label>
              <select
                v-model="form.unit_code"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
              >
                <option value="">-- Pilih Unit --</option>
                <option v-for="unit in units" :key="unit" :value="unit">{{ unit }}</option>
              </select>
              <p v-if="form.errors.unit_code" class="mt-1 text-sm text-red-600">{{ form.errors.unit_code }}</p>
            </div>

            <!-- Date & Shift -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                <input
                  v-model="form.tanggal"
                  type="date"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                >
                <p v-if="form.errors.tanggal" class="mt-1 text-sm text-red-600">{{ form.errors.tanggal }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Shift</label>
                <select
                  v-model="form.shift"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                >
                  <option value="D">Siang (D)</option>
                  <option value="N">Malam (N)</option>
                </select>
                <p v-if="form.errors.shift" class="mt-1 text-sm text-red-600">{{ form.errors.shift }}</p>
              </div>
            </div>

            <!-- Operator Info -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">NRP Operator</label>
                <input
                  v-model="form.nrp"
                  type="text"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                  placeholder="Nomor Registrasi Personel"
                >
                <p v-if="form.errors.nrp" class="mt-1 text-sm text-red-600">{{ form.errors.nrp }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Operator</label>
                <input
                  v-model="form.nama"
                  type="text"
                  class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                  placeholder="Nama lengkap operator"
                >
                <p v-if="form.errors.nama" class="mt-1 text-sm text-red-600">{{ form.errors.nama }}</p>
              </div>
            </div>

            <!-- File Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">File Foto (Max 5MB)</label>
              <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                <input
                  type="file"
                  accept="image/*"
                  @change="handleFileChange"
                  class="hidden"
                  ref="fileInput"
                >
                <div @click="$refs.fileInput.click()" class="cursor-pointer">
                  <p class="text-center text-gray-600">
                    {{ form.file ? form.file.name : 'Klik untuk memilih file atau drag & drop' }}
                  </p>
                  <p class="text-center text-xs text-gray-500 mt-1">Gambar JPG, PNG, atau GIF</p>
                </div>
              </div>
              <p v-if="form.errors.file" class="mt-1 text-sm text-red-600">{{ form.errors.file }}</p>
            </div>

            <!-- Preview -->
            <div v-if="preview" class="border rounded-lg p-4 bg-gray-50">
              <p class="text-sm font-medium text-gray-700 mb-2">Preview Foto</p>
              <img :src="preview" alt="Preview" class="max-h-64 rounded-md">
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3 pt-4">
              <Link :href="route('one-power.mpp-departemen')" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                Kembali
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-md disabled:opacity-50"
              >
                {{ form.processing ? 'Mengunggah...' : 'Unggah Foto' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  units: Array,
});

const fileInput = ref(null);
const preview = ref(null);

const form = useForm({
  jenis: '',
  unit_code: '',
  tanggal: new Date().toISOString().split('T')[0],
  shift: 'D',
  nrp: '',
  nama: '',
  file: null,
});

const handleFileChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    form.file = file;
    const reader = new FileReader();
    reader.onload = (e) => {
      preview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const submitForm = () => {
  form.post(route('mpp-photo.store'), {
    onSuccess: () => {
      form.reset();
      preview.value = null;
      alert('Foto berhasil diunggah!');
    },
  });
};
</script>
