<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ArrowLeft, Loader2 } from '@lucide/vue';

const form = useForm({
  judul: '',
  kategori: 'pribadi',
  ringkasan: '',
  deskripsi: '',
  file_pdf: null,
  video: null,
  link_sop: '',
  thumbnail: null,
  is_revisi: false,
  revisi_note: '',
});

const handleFileUpload = (fieldName, event) => {
  form[fieldName] = event.target.files[0];
};

const submit = () => {
  form.post(route('coaching-materi.store'));
};
</script>

<template>
  <Head title="Tambah Materi Coaching" />
  <AppLayout>
    <div class="space-y-6">
      <div class="flex items-center gap-4">
        <Link :href="route('coaching-materi.index')" class="rounded-lg border border-slate-300 p-2 text-slate-600 hover:bg-slate-100">
          <ArrowLeft class="h-5 w-5" />
        </Link>
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Tambah Materi Coaching</h1>
          <p class="text-slate-600">Buat konten materi baru untuk program coaching</p>
        </div>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Judul & Kategori -->
          <div class="grid gap-6 md:grid-cols-2">
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Judul Materi *</label>
              <input
                v-model="form.judul"
                type="text"
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              />
              <span v-if="form.errors.judul" class="text-xs text-red-600">{{ form.errors.judul }}</span>
            </div>

            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori *</label>
              <select
                v-model="form.kategori"
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              >
                <option value="pribadi">Pribadi</option>
                <option value="keluarga">Keluarga</option>
                <option value="lingkungan">Lingkungan</option>
                <option value="pekerjaan">Pekerjaan</option>
              </select>
              <span v-if="form.errors.kategori" class="text-xs text-red-600">{{ form.errors.kategori }}</span>
            </div>
          </div>

          <!-- Ringkasan -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Ringkasan *</label>
            <textarea
              v-model="form.ringkasan"
              class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              rows="3"
              placeholder="Tuliskan ringkasan singkat materi..."
            />
            <span v-if="form.errors.ringkasan" class="text-xs text-red-600">{{ form.errors.ringkasan }}</span>
          </div>

          <!-- Deskripsi -->
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi Lengkap</label>
            <textarea
              v-model="form.deskripsi"
              class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              rows="5"
              placeholder="Tuliskan deskripsi lengkap materi..."
            />
            <span v-if="form.errors.deskripsi" class="text-xs text-red-600">{{ form.errors.deskripsi }}</span>
          </div>

          <!-- File Uploads -->
          <div class="space-y-4">
            <h3 class="font-semibold text-slate-800">File & Media</h3>
            <div class="grid gap-4 md:grid-cols-2">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">File PDF (max 10MB)</label>
                <input
                  type="file"
                  @change="(e) => handleFileUpload('file_pdf', e)"
                  accept=".pdf"
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
                <span v-if="form.errors.file_pdf" class="text-xs text-red-600">{{ form.errors.file_pdf }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Video (max 50MB)</label>
                <input
                  type="file"
                  @change="(e) => handleFileUpload('video', e)"
                  accept=".mp4,.webm,.mov"
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
                <span v-if="form.errors.video" class="text-xs text-red-600">{{ form.errors.video }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Thumbnail (max 2MB)</label>
                <input
                  type="file"
                  @change="(e) => handleFileUpload('thumbnail', e)"
                  accept=".jpg,.jpeg,.png"
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                />
                <span v-if="form.errors.thumbnail" class="text-xs text-red-600">{{ form.errors.thumbnail }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Link SOP</label>
                <input
                  v-model="form.link_sop"
                  type="url"
                  class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                  placeholder="https://..."
                />
                <span v-if="form.errors.link_sop" class="text-xs text-red-600">{{ form.errors.link_sop }}</span>
              </div>
            </div>
          </div>

          <!-- Revisi Options -->
          <div class="rounded-lg bg-blue-50 p-4 space-y-3">
            <label class="flex items-center gap-2">
              <input
                v-model="form.is_revisi"
                type="checkbox"
                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm font-medium text-slate-700">Ini adalah revisi dari materi yang sudah ada</span>
            </label>
            <div v-if="form.is_revisi">
              <label class="block text-sm font-medium text-slate-700 mb-2">Catatan Revisi</label>
              <textarea
                v-model="form.revisi_note"
                class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
                rows="3"
                placeholder="Jelaskan perubahan dari versi sebelumnya..."
              />
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex gap-3 border-t border-slate-200 pt-6">
            <Link :href="route('coaching-materi.index')" class="rounded-lg border border-slate-300 px-6 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
              Batal
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="flex items-center gap-2 rounded-lg bg-green-600 px-6 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
            >
              <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
              Simpan Materi
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
