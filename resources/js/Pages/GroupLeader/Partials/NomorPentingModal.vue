<script setup>
import { MessageCircle, Phone, X } from '@lucide/vue';

defineProps({
    open: { type: Boolean, required: true },
});

defineEmits(['close']);

// TODO: ganti dengan props dari Inertia begitu endpoint kontak penting siap
const sections = [
    {
        label: 'Darurat',
        contacts: [
            { name: 'Emergency Call Adaro', role: 'Emergency Call Adaro', phone: '08001237911' },
            { name: 'Emergency Call Dept. Produksi', role: 'Emergency Call Dept. Produksi', phone: '081349065346' },
        ],
    },
    {
        label: 'Kesehatan',
        contacts: [
            { name: 'Hotline Medic', role: 'Medic', phone: '081350804431' },
        ],
    },
    {
        label: 'Informasi',
        contacts: [
            { name: 'CS Produksi Site ADW', role: null, phone: '08115125496' },
        ],
    },
    {
        label: 'Operasional',
        contacts: [
            { name: 'Ahmad Aqmal Shadik', role: 'Customer Care One Click', phone: '085849079767' },
        ],
    },
];

const waLink = (phone) => `https://wa.me/62${phone.replace(/^0/, '')}`;
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="$emit('close')">
        <div class="flex max-h-[85vh] w-full max-w-md flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h2 class="m-0 flex items-center gap-2 text-base font-extrabold text-slate-800">
                    <Phone class="h-4 w-4 text-sky-500" /> Nomor Penting
                </h2>
                <button type="button" class="text-slate-400 hover:text-slate-600" @click="$emit('close')">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="flex flex-col gap-5 overflow-y-auto px-5 py-4">
                <div v-for="section in sections" :key="section.label">
                    <p class="mb-2 text-xs font-extrabold uppercase tracking-wide text-blue-500">{{ section.label }}</p>
                    <div class="flex flex-col gap-2">
                        <div
                            v-for="contact in section.contacts"
                            :key="contact.phone"
                            class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3 shadow-sm"
                        >
                            <div class="min-w-0">
                                <p class="m-0 truncate text-sm font-bold uppercase text-slate-800">{{ contact.name }}</p>
                                <p v-if="contact.role" class="m-0 truncate text-xs text-slate-400">{{ contact.role }}</p>
                                <p class="m-0 text-xs text-slate-400">{{ contact.phone }}</p>
                            </div>
                            <a
                                :href="waLink(contact.phone)"
                                target="_blank"
                                rel="noopener"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-white"
                            >
                                <MessageCircle class="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 p-4">
                <button type="button" class="w-full rounded-xl bg-slate-100 py-3 text-sm font-bold text-slate-600 hover:bg-slate-200" @click="$emit('close')">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</template>
