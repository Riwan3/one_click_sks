<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Master Kategori MPP</h1>
              <p class="mt-1 text-sm text-gray-600">Kelola kategori pekerjaan dan plan MP</p>
            </div>
            <button
              @click="openCreateModal"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700"
            >
              Tambah Kategori
            </button>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan MP</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(category, index) in categories.data" :key="category.id" :draggable="true">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ index + 1 }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ category.nama }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ category.kode }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">{{ category.kategori_tipe }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ category.plan_mp }}%</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                      category.active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ category.active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  <button
                    @click="editCategory(category)"
                    class="text-indigo-600 hover:text-indigo-900 mr-4"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteCategory(category.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Modal Create/Edit -->
      <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
              {{ editingId ? 'Edit Kategori' : 'Tambah Kategori' }}
            </h3>
          </div>
          <form @submit.prevent="submitForm" class="px-6 py-4 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Nama Kategori</label>
              <input
                v-model="form.nama"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                placeholder="Contoh: Operator Produksi"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Kode</label>
              <input
                v-model="form.kode"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                placeholder="Contoh: OP_PROD"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Tipe Kategori</label>
              <select
                v-model="form.kategori_tipe"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
              >
                <option value="staff">Staff</option>
                <option value="pengawas">Pengawas</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Plan MP (%)</label>
              <input
                v-model.number="form.plan_mp"
                type="number"
                min="0"
                max="100"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 border px-3 py-2"
                placeholder="100"
              >
            </div>
            <div class="flex items-center">
              <input
                v-model="form.active"
                type="checkbox"
                class="rounded border-gray-300"
              >
              <label class="ml-2 text-sm text-gray-700">Aktif</label>
            </div>
            <div class="flex justify-end gap-3 pt-4">
              <button
                @click="closeModal"
                type="button"
                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
              >
                Batal
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-indigo-600 text-white hover:bg-indigo-700 rounded-md"
              >
                {{ editingId ? 'Perbarui' : 'Simpan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  categories: Object,
  pagination: Object,
});

const showModal = ref(false);
const editingId = ref(null);

const form = useForm({
  nama: '',
  kode: '',
  kategori_tipe: 'staff',
  plan_mp: 100,
  active: true,
});

const openCreateModal = () => {
  editingId.value = null;
  form.reset();
  showModal.value = true;
};

const editCategory = (category) => {
  editingId.value = category.id;
  form.nama = category.nama;
  form.kode = category.kode;
  form.kategori_tipe = category.kategori_tipe;
  form.plan_mp = category.plan_mp;
  form.active = category.active;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitForm = () => {
  if (editingId.value) {
    form.put(route('mpp-master-categories.update', editingId.value), {
      onSuccess: () => closeModal(),
    });
  } else {
    form.post(route('mpp-master-categories.store'), {
      onSuccess: () => closeModal(),
    });
  }
};

const deleteCategory = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
    useForm().delete(route('mpp-master-categories.destroy', id));
  }
};
</script>
