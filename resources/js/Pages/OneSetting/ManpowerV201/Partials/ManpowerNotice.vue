<script setup>
import { useManpowerNotice } from '@/composables/useManpowerV201';

const { notice } = useManpowerNotice();

const TONE_CLASS = { success: 'bg-emerald-600', error: 'bg-red-600' };
</script>

<template>
  <Teleport to="body">
    <div
      v-if="notice.message"
      role="alert"
      :class="['fixed bottom-4 right-4 z-[70] max-w-md rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-lg', TONE_CLASS[notice.tone]]"
    >
      {{ notice.message }}
    </div>
  </Teleport>
</template>
