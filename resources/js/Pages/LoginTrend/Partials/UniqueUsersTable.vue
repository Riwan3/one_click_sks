<script setup>
import { computed, ref, watch } from 'vue';
import { ChevronLeft, ChevronRight, UserX, Users } from 'lucide-vue-next';

const props = defineProps({
  users: { type: Array, required: true }, // [{ nrp, nama, jabatan, login_count, ip_address, last_login }]
  periodLabel: { type: String, required: true },
});

const PER_PAGE = 20;

const page = ref(1);

const lastPage = computed(() => Math.max(1, Math.ceil(props.users.length / PER_PAGE)));
const offset = computed(() => (page.value - 1) * PER_PAGE);
const rows = computed(() => props.users.slice(offset.value, offset.value + PER_PAGE));
const info = computed(() => `Menampilkan ${props.users.length ? offset.value + 1 : 0}–${Math.min(offset.value + PER_PAGE, props.users.length)} dari ${props.users.length} user`);

const formatLastLogin = (value) => new Date(value.replace(' ', 'T')).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

const handlePage = (target) => {
  page.value = Math.min(lastPage.value, Math.max(1, target));
};

watch(() => props.users, () => { page.value = 1; });
</script>

<template>
  <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
    <div class="flex items-center justify-between px-5 pb-2 pt-4">
      <h2 class="flex items-center gap-2 text-sm font-bold text-slate-800"><Users class="h-4 w-4 text-blue-600" /> Data User Unik Login — {{ periodLabel }}</h2>
      <span class="rounded-full bg-blue-600 px-2.5 py-0.5 text-xs font-bold text-white">{{ users.length }} user</span>
    </div>

    <div v-if="!users.length" class="py-12 text-center text-sm text-slate-400">
      <UserX class="mx-auto mb-2 h-6 w-6" /> Tidak ada data login untuk periode ini.
    </div>

    <template v-else>
      <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-left text-xs">
          <thead class="bg-slate-50 text-[11px] uppercase text-slate-500">
            <tr>
              <th class="w-10 px-5 py-2.5">No</th>
              <th class="px-3 py-2.5">NRP</th>
              <th class="px-3 py-2.5">Nama</th>
              <th class="px-3 py-2.5">Jabatan</th>
              <th class="px-3 py-2.5 text-center">Jml Login</th>
              <th class="px-3 py-2.5">IP Terakhir</th>
              <th class="px-3 py-2.5">Login Terakhir</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="(user, index) in rows" :key="user.nrp" class="hover:bg-slate-50/60">
              <td class="px-5 py-2 text-slate-400">{{ offset + index + 1 }}</td>
              <td class="px-3 py-2 font-bold text-blue-600">{{ user.nrp }}</td>
              <td class="px-3 py-2 font-semibold text-slate-800">{{ user.nama || '-' }}</td>
              <td class="px-3 py-2 text-slate-500">{{ user.jabatan || '-' }}</td>
              <td class="px-3 py-2 text-center"><span class="rounded-full bg-sky-100 px-2 py-0.5 font-bold text-sky-800">{{ user.login_count }}×</span></td>
              <td class="px-3 py-2"><code v-if="user.ip_address" class="rounded bg-slate-100 px-1.5 py-0.5 text-[11px]">{{ user.ip_address }}</code><span v-else class="text-slate-400">-</span></td>
              <td class="px-3 py-2 text-[11px] text-slate-500">{{ formatLastLogin(user.last_login) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 px-5 py-3">
        <span class="text-xs text-slate-500">{{ info }}</span>
        <div v-if="lastPage > 1" class="flex items-center gap-1 text-xs">
          <button type="button" class="rounded-lg border border-slate-200 p-1.5 hover:bg-slate-50 disabled:opacity-40" :disabled="page === 1" @click="handlePage(page - 1)"><ChevronLeft class="h-3.5 w-3.5" /></button>
          <span class="px-2 font-semibold text-slate-600">{{ page }} / {{ lastPage }}</span>
          <button type="button" class="rounded-lg border border-slate-200 p-1.5 hover:bg-slate-50 disabled:opacity-40" :disabled="page === lastPage" @click="handlePage(page + 1)"><ChevronRight class="h-3.5 w-3.5" /></button>
        </div>
      </div>
    </template>
  </div>
</template>
