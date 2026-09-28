<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { FileText, X, Plus, Loader2 } from 'lucide-vue-next';
import { confirmDialog } from '@/utils/confirm';
import { INCIDENT_BASE_URL } from '@/utils/odrIncident';

const props = defineProps({
  actionId: { type: Number, required: true },
  items: { type: Array, default: () => [] },
});

const fileInput = ref(null);
const isUploading = ref(false);

const requestOptions = { preserveScroll: true, preserveState: true };

const handleUpload = (event) => {
  const files = [...event.target.files];
  if (files.length === 0) return;
  isUploading.value = true;
  router.post(`${INCIDENT_BASE_URL}/tindakan/${props.actionId}/evidence`, { evidence: files }, {
    ...requestOptions,
    forceFormData: true,
    onFinish: () => { isUploading.value = false; event.target.value = ''; },
  });
};

const handleDelete = async (evidence) => {
  if (await confirmDialog('Hapus evidence ini?')) router.delete(`${INCIDENT_BASE_URL}/evidence/${evidence.id}`, requestOptions);
};
</script>

<template>
  <div class="flex flex-wrap items-center gap-2">
    <div v-for="ev in items" :key="ev.id" class="relative">
      <a v-if="ev.is_image" :href="ev.url" target="_blank"><img :src="ev.url" :alt="ev.name" class="h-12 w-12 rounded-md border border-slate-200 object-cover" /></a>
      <a v-else :href="ev.url" target="_blank" class="flex max-w-[120px] items-center gap-1 rounded-md border border-slate-200 bg-slate-50 px-2 py-1 text-[11px] text-slate-600 hover:bg-slate-100">
        <FileText class="h-3.5 w-3.5 shrink-0" /><span class="truncate">{{ ev.name }}</span>
      </a>
      <button type="button" title="Hapus" class="absolute -right-1 -top-1.5 flex h-[18px] w-[18px] items-center justify-center rounded-full bg-red-600 text-white" @click="handleDelete(ev)">
        <X class="h-3 w-3" />
      </button>
    </div>
    <input ref="fileInput" type="file" multiple class="hidden" @change="handleUpload" />
    <button
      type="button"
      title="Tambah evidence"
      :disabled="isUploading"
      class="flex h-7 w-7 items-center justify-center rounded-md border border-dashed border-slate-300 bg-white text-slate-400 hover:text-slate-600"
      @click="fileInput.click()"
    >
      <component :is="isUploading ? Loader2 : Plus" :class="['h-4 w-4', isUploading && 'animate-spin']" />
    </button>
  </div>
</template>
