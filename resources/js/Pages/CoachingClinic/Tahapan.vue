<script setup>
import { ref, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { GripVertical, Edit2, Trash2, Plus } from 'lucide-vue-next';

defineProps({
    tahapans: { type: Array, required: true },
    canManage: { type: Boolean, default: true },
});

const tahapanList = ref([]);
const showEditModal = ref(false);
const editingTahapan = ref(null);
const formData = ref({});

onMounted(() => {
    tahapanList.value = JSON.parse(JSON.stringify(tahapans));
});

const startEdit = (tahapan) => {
    editingTahapan.value = tahapan;
    formData.value = { ...tahapan };
    showEditModal.value = true;
};

const saveEdit = async () => {
    await router.put(`/one-power/coaching-clinic/tahapan/${formData.value.id}`, formData.value);
    showEditModal.value = false;
};

const deleteTahapan = (id) => {
    if (confirm('Hapus tahapan ini?')) {
        router.delete(`/one-power/coaching-clinic/tahapan/${id}`);
    }
};

const reorderTahapans = async () => {
    const reordered = tahapanList.value.map((t, idx) => ({
        ...t,
        urutan: idx + 1
    }));
    
    await fetch('/one-power/coaching-clinic/tahapan/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ tahapans: reordered })
    });
};
</script>

<template>
    <Head title="Tahapan Coaching - OneClick" />
    
    <div class="mx-auto max-w-4xl p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-800">Tahapan Coaching</h1>
            <button v-if="canManage" class="flex items-center gap-2 rounded-lg bg-emerald-500 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-600">
                <Plus :size="18" />
                Tambah Tahapan
            </button>
        </div>

        <div class="space-y-3">
            <div v-for="(tahapan, idx) in tahapanList" :key="tahapan.id" class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <GripVertical :size="20" class="text-slate-400" />
                <div class="flex-1">
                    <p class="font-semibold text-slate-800">{{ tahapan.urutan }}. {{ tahapan.nama }}</p>
                    <p class="text-sm text-slate-500">Mulai: {{ tahapan.start_date }} | Target: {{ tahapan.due_date }}</p>
                </div>
                <div v-if="canManage" class="flex gap-2">
                    <button @click="startEdit(tahapan)" class="text-blue-500 hover:text-blue-700">
                        <Edit2 :size="18" />
                    </button>
                    <button @click="deleteTahapan(tahapan.id)" class="text-red-500 hover:text-red-700">
                        <Trash2 :size="18" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 flex items-center justify-center bg-black/50 p-4">
            <div class="max-w-md rounded-lg bg-white p-6 shadow-lg">
                <h2 class="mb-4 text-lg font-bold">Edit Tahapan</h2>
                <div class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Nama</label>
                        <input v-model="formData.nama" type="text" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Tanggal Mulai</label>
                        <input v-model="formData.start_date" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Tanggal Target</label>
                        <input v-model="formData.due_date" type="date" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" />
                    </div>
                    <div class="flex gap-2">
                        <button @click="saveEdit" class="flex-1 rounded-lg bg-blue-500 py-2 text-sm font-bold text-white hover:bg-blue-600">Simpan</button>
                        <button @click="showEditModal = false" class="flex-1 rounded-lg bg-slate-200 py-2 text-sm font-bold text-slate-700 hover:bg-slate-300">Batal</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
