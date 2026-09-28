<script setup>
import { confirmDialog } from '@/utils/confirm';
import { ref, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { X, Phone, PhoneCall, Pencil, Trash2, Plus, Settings, Check, Loader2, ExternalLink } from 'lucide-vue-next';

const props = defineProps({
  show: { type: Boolean, default: false },
  user: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const page = usePage();
const currentUser = computed(() => props.user || page.props.auth?.user || page.props.user || null);
const isAdmin = computed(() => {
  const u = currentUser.value;
  if (!u) return false;
  const role = (u.role || '').toLowerCase();
  const jabatan = (u.jabatan || '').toLowerCase();
  return role === 'admin' || role === 'admin_produksi' || jabatan.includes('admin');
});

// Default fallback items
const defaultCategories = [
  {
    category: 'SHE & Emergency Response Team',
    items: [
      { id: 'def-1', nama: 'Pos Command Center SHE / Darurat Tambang', jabatan: 'Emergency Call 24 Jam', no_telepon: '0811-555-911', aktif: true },
      { id: 'def-2', nama: 'Klinik / Medical Center Central', jabatan: 'Tim Medis Darurat', no_telepon: '0812-555-118', aktif: true },
    ]
  },
  {
    category: 'Human Resources & Operation',
    items: [
      { id: 'def-3', nama: 'Posko Manpower & Absensi', jabatan: 'HR Admin Staff', no_telepon: '0813-555-7788', aktif: true },
      { id: 'def-4', nama: 'Dispatcher Main Control Room', jabatan: 'Mining Operator Control', no_telepon: '0812-555-3344', aktif: true },
    ]
  }
];

const categories = ref([...defaultCategories]);
const isLoading = ref(false);
const isManageMode = ref(false);

// Edit/Add Form states
const isFormOpen = ref(false);
const isSaving = ref(false);
const editingItem = ref(null);
const formData = ref({
  nama: '',
  jabatan: '',
  no_telepon: '',
  kategori: 'SHE & Emergency Response Team',
  urutan: 0,
  aktif: true,
});

const getCookie = (name) => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
  return '';
};

const getCsrfToken = () => {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || getCookie('XSRF-TOKEN');
};

const fetchNomorPenting = async () => {
  isLoading.value = true;
  try {
    const res = await fetch('/one-core/nomor-penting/api/list', {
      headers: { Accept: 'application/json' },
    });
    const data = await res.json();
    if (data.status === 'success' && data.grouped && Object.keys(data.grouped).length > 0) {
      categories.value = Object.keys(data.grouped).map(catName => ({
        category: catName,
        items: data.grouped[catName],
      }));
    } else if (data.list && data.list.length > 0) {
      categories.value = [{
        category: 'Daftar Kontak',
        items: data.list,
      }];
    }
  } catch (err) {
    console.error('Gagal mengambil data nomor penting:', err);
  } finally {
    isLoading.value = false;
  }
};

watch(() => props.show, (newVal) => {
  if (newVal) {
    isManageMode.value = false;
    isFormOpen.value = false;
    fetchNomorPenting();
  }
});

const openAddModal = () => {
  editingItem.value = null;
  formData.value = {
    nama: '',
    jabatan: '',
    no_telepon: '',
    kategori: categories.value[0]?.category || 'SHE & Emergency Response Team',
    urutan: 0,
    aktif: true,
  };
  isFormOpen.value = true;
};

const openEditModal = (item) => {
  editingItem.value = item;
  formData.value = {
    nama: item.nama || '',
    jabatan: item.jabatan || '',
    no_telepon: item.no_telepon || item.hp || '',
    kategori: item.kategori || 'SHE & Emergency Response Team',
    urutan: item.urutan || 0,
    aktif: Boolean(item.aktif ?? true),
  };
  isFormOpen.value = true;
};

const saveItem = async () => {
  if (!formData.value.nama || !formData.value.no_telepon) {
    alert('Nama dan Nomor Telepon wajib diisi.');
    return;
  }

  isSaving.value = true;
  try {
    const isEdit = Boolean(editingItem.value && editingItem.value.id && !String(editingItem.value.id).startsWith('def-'));
    const url = isEdit
      ? `/one-core/nomor-penting/${editingItem.value.id}`
      : '/one-core/nomor-penting';
    const method = isEdit ? 'PUT' : 'POST';

    const res = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-XSRF-TOKEN': getCsrfToken(),
      },
      body: JSON.stringify(formData.value),
    });

    if (!res.ok) {
      const err = await res.json();
      throw new Error(err.message || 'Gagal menyimpan kontak.');
    }

    isFormOpen.value = false;
    await fetchNomorPenting();
  } catch (err) {
    alert(err.message || 'Terjadi kesalahan saat menyimpan.');
  } finally {
    isSaving.value = false;
  }
};

const deleteItem = async (item) => {
  if (!await confirmDialog(`Yakin ingin menghapus nomor "${item.nama}"?`)) return;

  if (String(item.id).startsWith('def-')) {
    categories.value.forEach(c => {
      c.items = c.items.filter(i => i.id !== item.id);
    });
    return;
  }

  try {
    const res = await fetch(`/one-core/nomor-penting/${item.id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-XSRF-TOKEN': getCsrfToken(),
      },
    });
    if (!res.ok) throw new Error('Gagal menghapus');
    await fetchNomorPenting();
  } catch (err) {
    alert('Gagal menghapus kontak.');
  }
};
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl border border-slate-100 overflow-hidden text-xs animate-in fade-in zoom-in-95 duration-150 flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
          <div class="flex items-center gap-2">
            <div class="p-2 rounded-xl bg-sky-50 text-sky-600 border border-sky-100">
              <Phone class="w-4 h-4" />
            </div>
            <div>
              <h3 class="font-bold text-slate-800 text-sm">
                Nomor Penting Darurat &amp; Operational
              </h3>
              <p class="text-[11px] text-slate-400">Kontak darurat dan bantuan operasional</p>
            </div>
          </div>

          <div class="flex items-center gap-1.5">
            <!-- Mode Kelola (Khusus Admin) -->
            <button
              v-if="isAdmin"
              type="button"
              @click="isManageMode = !isManageMode"
              class="flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold transition border"
              :class="isManageMode ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200'"
              title="Kelola Kontak (Role Admin)"
            >
              <Settings class="w-3.5 h-3.5" />
              <span>{{ isManageMode ? 'Selesai' : 'Kelola' }}</span>
            </button>

            <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
              <X class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Admin Toolbar (jika mode kelola aktif) -->
        <div v-if="isAdmin && isManageMode" class="px-4 py-2 bg-blue-50/80 border-b border-blue-100 flex items-center justify-between shrink-0">
          <div class="text-[11px] font-semibold text-blue-800 flex items-center gap-1.5">
            <span class="inline-block w-2 h-2 rounded-full bg-blue-600"></span>
            Mode Kelola (Admin Aktif)
          </div>
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="openAddModal"
              class="flex items-center gap-1 px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-[11px] transition shadow-xs"
            >
              <Plus class="w-3.5 h-3.5" /> Tambah Kontak
            </button>
            <a
              href="/one-core/nomor-penting"
              target="_blank"
              class="flex items-center gap-1 px-2 py-1 text-slate-600 hover:text-blue-600 hover:bg-white rounded-lg text-[11px] font-semibold transition"
              title="Buka menu One-Core Nomor Penting"
            >
              <ExternalLink class="w-3 h-3" /> Menu Penuh
            </a>
          </div>
        </div>

        <!-- Content List -->
        <div class="p-4 overflow-y-auto space-y-4 grow">
          <div v-if="isLoading" class="py-8 text-center text-slate-400 flex flex-col items-center gap-2">
            <Loader2 class="w-6 h-6 animate-spin text-blue-500" />
            <span>Memuat nomor penting...</span>
          </div>

          <template v-else>
            <div v-for="(cat, idx) in categories" :key="idx" class="space-y-2">
              <div class="flex items-center justify-between text-[10px] font-extrabold uppercase text-slate-400 tracking-wider">
                <span>{{ cat.category }}</span>
                <span class="text-slate-300">({{ cat.items?.length || 0 }})</span>
              </div>

              <div class="divide-y divide-slate-100 border border-slate-200 rounded-xl overflow-hidden bg-white shadow-2xs">
                <div
                  v-for="item in cat.items"
                  :key="item.id || item.nama"
                  class="p-3 flex items-center justify-between gap-3 hover:bg-slate-50/50 transition"
                  :class="!item.aktif && isManageMode ? 'opacity-50 bg-slate-50' : ''"
                >
                  <div class="min-w-0">
                    <div class="font-bold text-slate-800 truncate flex items-center gap-2">
                      <span>{{ item.nama }}</span>
                      <span v-if="!item.aktif && isManageMode" class="text-[9px] px-1.5 py-0.2 rounded bg-slate-200 text-slate-600 font-bold uppercase">Non-aktif</span>
                    </div>
                    <div class="text-[10px] text-slate-500 truncate">
                      {{ item.jabatan || '-' }} &bull; <strong class="text-slate-700">{{ item.no_telepon || item.hp }}</strong>
                    </div>
                  </div>

                  <!-- Actions -->
                  <div class="flex items-center gap-1.5 shrink-0">
                    <!-- Admin Edit & Delete buttons -->
                    <template v-if="isAdmin && isManageMode">
                      <button
                        type="button"
                        @click="openEditModal(item)"
                        class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                        title="Edit Nomor"
                      >
                        <Pencil class="w-3.5 h-3.5" />
                      </button>
                      <button
                        type="button"
                        @click="deleteItem(item)"
                        class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                        title="Hapus Nomor"
                      >
                        <Trash2 class="w-3.5 h-3.5" />
                      </button>
                    </template>

                    <!-- Hubungi button (All users) -->
                    <a
                      :href="`https://api.whatsapp.com/send/?phone=${(item.no_telepon || item.hp || '').replace(/[^0-9]/g, '')}`"
                      target="_blank"
                      class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-[10px] flex items-center gap-1 transition shadow-xs"
                    >
                      <PhoneCall class="w-3.5 h-3.5" /> Hubungi
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>

        <!-- Footer -->
        <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center gap-2 shrink-0">
          <button @click="$emit('close')" class="w-full py-2 font-bold bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl transition">
            TUTUP
          </button>
        </div>
      </div>
    </div>

    <!-- Inner Modal Tambah / Edit Kontak (Admin Only) -->
    <div v-if="isFormOpen" class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
      <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 p-5 text-xs animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
          <h4 class="text-sm font-bold text-slate-800">
            {{ editingItem ? 'Edit Nomor Penting' : 'Tambah Nomor Penting' }}
          </h4>
          <button type="button" @click="isFormOpen = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="saveItem" class="space-y-3">
          <div>
            <label class="block font-bold text-slate-700 mb-1">Nama Kontak / Instansi *</label>
            <input
              v-model="formData.nama"
              type="text"
              required
              placeholder="Contoh: Posko Manpower & Absensi"
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs outline-none focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Jabatan / Keterangan</label>
            <input
              v-model="formData.jabatan"
              type="text"
              placeholder="Contoh: Tim Medis Darurat"
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs outline-none focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Nomor Telepon / WhatsApp *</label>
            <input
              v-model="formData.no_telepon"
              type="text"
              required
              placeholder="Contoh: 0812-355-118 atau 0811555911"
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs outline-none focus:border-blue-500"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-700 mb-1">Kategori *</label>
            <input
              v-model="formData.kategori"
              type="text"
              required
              placeholder="Contoh: SHE & Emergency Response Team"
              class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs outline-none focus:border-blue-500"
            />
            <div class="mt-1 flex flex-wrap gap-1">
              <button
                v-for="cat in ['SHE & Emergency Response Team', 'Human Resources & Operation', 'Operasional', 'Darurat']"
                :key="cat"
                type="button"
                @click="formData.kategori = cat"
                class="px-2 py-0.5 rounded text-[10px] bg-slate-100 hover:bg-slate-200 text-slate-600 transition"
              >
                {{ cat }}
              </button>
            </div>
          </div>

          <div class="flex items-center gap-2 pt-1">
            <input
              type="checkbox"
              id="cbAktif"
              v-model="formData.aktif"
              class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
            />
            <label for="cbAktif" class="font-bold text-slate-700 cursor-pointer">
              Tampilkan (Status Aktif)
            </label>
          </div>

          <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 mt-4">
            <button
              type="button"
              @click="isFormOpen = false"
              class="px-4 py-2 font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="isSaving"
              class="flex items-center gap-1.5 px-4 py-2 font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition disabled:opacity-50"
            >
              <Loader2 v-if="isSaving" class="w-3.5 h-3.5 animate-spin" />
              <Check v-else class="w-3.5 h-3.5" />
              {{ isSaving ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

