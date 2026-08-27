<script setup>
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { KeyRound, CheckCircle2, Clock, MessageSquare } from 'lucide-vue-next'

const props = defineProps({
  user: Object,
  notifCounts: Object,
  menu: Array,
  requests: Array,
})

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const completeRequest = (id) => {
  if (confirm('Tandai request reset password ini sebagai Selesai?')) {
    router.put(`/one-core/reset-password-requests/${id}/complete`, {}, {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <Head title="Request Reset Password — One-Core" />

  <AppLayout :user="user" :notif-counts="notifCounts" :menu="menu" active-module="one-core">
    <div class="p-6 space-y-6">

      <!-- HEADER -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
            <KeyRound class="w-7 h-7 text-amber-500" />
            Request Reset Password
          </h1>
          <p class="text-sm text-slate-500 mt-1">
            Daftar permintaan reset password dari pengguna / karyawan.
          </p>
        </div>
      </div>

      <!-- TABLE CONTAINER -->
      <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left border-collapse">
            <thead class="bg-slate-50 text-slate-700 font-semibold text-xs border-b border-slate-200 uppercase tracking-wider">
              <tr>
                <th class="py-3.5 px-6 w-16 text-center">NO</th>
                <th class="py-3.5 px-6">TANGGAL & WAKTU</th>
                <th class="py-3.5 px-6">NAMA LENGKAP</th>
                <th class="py-3.5 px-6">NRP</th>
                <th class="py-3.5 px-6">NOMOR WA</th>
                <th class="py-3.5 px-6 w-32 text-center">STATUS</th>
                <th class="py-3.5 px-6 w-36 text-center">AKSI</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-if="!requests || requests.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-400 italic">
                  Belum ada data request reset password.
                </td>
              </tr>
              <tr v-for="(req, idx) in requests" :key="req.id" class="hover:bg-slate-50">
                <td class="py-3.5 px-6 text-center text-slate-500 font-medium">{{ idx + 1 }}</td>
                <td class="py-3.5 px-6 text-slate-600 text-xs font-medium">{{ formatDate(req.created_at) }}</td>
                <td class="py-3.5 px-6 font-bold text-slate-900">{{ req.nama }}</td>
                <td class="py-3.5 px-6 font-mono text-blue-700 font-semibold">{{ req.nrp }}</td>
                <td class="py-3.5 px-6">
                  <a 
                    :href="'https://wa.me/' + req.no_wa.replace(/[^0-9]/g, '')" 
                    target="_blank" 
                    class="inline-flex items-center gap-1.5 font-medium text-emerald-600 hover:text-emerald-700 transition"
                  >
                    <MessageSquare class="w-4 h-4" />
                    <span>{{ req.no_wa }}</span>
                  </a>
                </td>
                <td class="py-3.5 px-6 text-center">
                  <span 
                    v-if="req.status === 'pending'" 
                    class="inline-flex items-center gap-1 px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-semibold"
                  >
                    <Clock class="w-3.5 h-3.5" /> Pending
                  </span>
                  <span 
                    v-else 
                    class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-semibold"
                  >
                    <CheckCircle2 class="w-3.5 h-3.5" /> Selesai
                  </span>
                </td>
                <td class="py-3.5 px-6 text-center">
                  <button 
                    v-if="req.status === 'pending'" 
                    @click="completeRequest(req.id)" 
                    class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition flex items-center justify-center gap-1.5 mx-auto"
                  >
                    <CheckCircle2 class="w-3.5 h-3.5" /> Selesaikan
                  </button>
                  <span v-else class="text-xs text-slate-400 font-medium">Closed</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
