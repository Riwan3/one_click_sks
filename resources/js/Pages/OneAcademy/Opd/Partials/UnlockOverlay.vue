<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, ShieldCheck, TriangleAlert } from 'lucide-vue-next';

const isPasswordVisible = ref(false);
const form = useForm({ password: '' });

const handleSubmit = () => form.post('/one-academy/opd/pengajuan-grade/unlock', {
  preserveScroll: true,
  onFinish: () => form.reset('password'),
});
</script>

<template>
  <!-- Menutup seluruh layar (termasuk sidebar & topbar) dengan blur; z di atas sidebar (z-50) dan modal (z-60). -->
  <Teleport to="body">
    <div class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/30 p-5 backdrop-blur-md">
      <div class="w-full max-w-sm rounded-3xl border border-white/10 bg-slate-800/90 p-8 text-center shadow-2xl">
        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-600 text-white shadow-[0_0_0_8px_rgba(2,132,199,0.15)]">
          <ShieldCheck class="h-6 w-6" />
        </div>
        <h2 class="mb-1 text-lg font-bold text-white">Otorisasi Keamanan</h2>
        <p class="mb-5 text-sm text-slate-400">Pengajuan Grade berisi data sensitif. Masukkan password otorisasi (sama dengan One-Core) untuk membuka.</p>

        <p v-if="form.errors.password" class="mb-4 flex items-center justify-center gap-2 rounded-xl bg-red-500/10 px-3 py-2 text-sm font-semibold text-red-400">
          <TriangleAlert class="h-4 w-4 shrink-0" /> {{ form.errors.password }}
        </p>

        <form @submit.prevent="handleSubmit">
          <div class="relative mb-4">
            <input
              v-model="form.password"
              :type="isPasswordVisible ? 'text' : 'password'"
              placeholder="Password otorisasi"
              autofocus
              required
              class="w-full rounded-xl border border-white/10 bg-white/5 py-3 pl-4 pr-11 text-sm text-white outline-none placeholder:text-slate-500 focus:border-sky-500"
            >
            <button type="button" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-200" @click="isPasswordVisible = !isPasswordVisible">
              <EyeOff v-if="isPasswordVisible" class="h-4 w-4" />
              <Eye v-else class="h-4 w-4" />
            </button>
          </div>
          <button type="submit" :disabled="form.processing" class="w-full rounded-xl bg-sky-600 py-3 text-sm font-bold text-white shadow-lg hover:bg-sky-500 disabled:opacity-70">Buka Akses</button>
        </form>

        <a href="/portal" class="mt-4 inline-block text-sm text-slate-500 hover:text-slate-300">← Kembali ke Dashboard</a>
      </div>
    </div>
  </Teleport>
</template>
