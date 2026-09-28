<script setup>
import { RefreshCw, Unlock, GraduationCap } from 'lucide-vue-next';

defineProps({
  isPassed: { type: Boolean, required: true },
  followups: { type: Array, default: () => [] },
  extraAttempts: { type: Number, default: 0 },
  canManage: { type: Boolean, default: false },
});

const emit = defineEmits(['remedial-requested', 'coaching-requested', 'grant-requested']);

const isCoaching = (f) => f.type === 'coaching';
</script>

<template>
  <div class="space-y-3">
    <div v-if="!isPassed && canManage" class="rounded-xl border border-orange-200 bg-orange-50 p-3">
      <div class="mb-2 flex items-center gap-1 text-[11px] font-bold text-orange-800">
        <RefreshCw class="h-3.5 w-3.5" /> TINDAK LANJUT
      </div>
      <div class="flex flex-col gap-2">
        <button
          type="button"
          class="inline-flex items-center justify-center gap-1 rounded-full border border-emerald-500 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50"
          @click="emit('remedial-requested')"
        >
          <Unlock class="h-3.5 w-3.5" /> Berikan Akses Remedial (Ulang Assessment)
        </button>
        <button
          type="button"
          class="inline-flex items-center justify-center gap-1 rounded-full border border-blue-500 bg-white px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-50"
          @click="emit('coaching-requested')"
        >
          <GraduationCap class="h-3.5 w-3.5" /> Jadwalkan Coaching &amp; Mentoring
        </button>
      </div>
    </div>

    <div>
      <div class="mb-2 flex items-center gap-1 text-xs font-semibold text-slate-700">
        Catatan Tindak Lanjut
        <span v-if="extraAttempts > 0" class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] text-white">+{{ extraAttempts }} percobaan</span>
      </div>
      <p v-if="followups.length === 0" class="text-xs text-slate-400">Belum ada tindak lanjut.</p>
      <div v-for="f in followups" :key="f.id" class="mb-2 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs">
        <div class="flex items-start justify-between gap-2">
          <span class="inline-flex items-center gap-1 font-bold text-slate-800">
            <component :is="isCoaching(f) ? GraduationCap : Unlock" :class="['h-3.5 w-3.5', isCoaching(f) ? 'text-blue-600' : 'text-emerald-600']" />
            {{ f.type_label }}
          </span>
          <span :class="['rounded-full px-2 py-0.5 text-[10px] font-semibold', f.remedial_granted_at ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-800']">
            {{ f.remedial_granted_at ? 'Remedial Diberikan' : 'Menunggu Coaching' }}
          </span>
        </div>
        <template v-if="isCoaching(f)">
          <div class="mt-1">Mentor: <strong>{{ f.mentor_name || '—' }}</strong></div>
          <div v-if="f.weak_material_notes" class="mt-1 whitespace-pre-line text-slate-500">{{ f.weak_material_notes }}</div>
          <div class="mt-1 text-slate-500">Target Coaching: {{ f.coaching_target_date || '—' }}</div>
        </template>
        <div v-if="f.remedial_granted_at" class="mt-1 text-slate-500">
          Diberikan oleh <strong>{{ f.remedial_granted_by || '—' }}</strong> pada {{ f.remedial_granted_at }}
        </div>
        <button
          v-else-if="isCoaching(f) && canManage"
          type="button"
          class="mt-2 inline-flex items-center gap-1 rounded-full border border-emerald-500 bg-white px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 hover:bg-emerald-50"
          @click="emit('grant-requested', f.id)"
        >
          <Unlock class="h-3 w-3" /> Berikan Akses Remedial
        </button>
      </div>
    </div>
  </div>
</template>
