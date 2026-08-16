<script setup>
import { useForm } from '@inertiajs/vue3';

defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const form = useForm({
    nama: '',
    nrp: '',
    no_wa: '',
});

// TODO: hubungkan ke endpoint reset password begitu backend auth siap.
const handleSubmit = () => {
    emit('close');
    form.reset();
};
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="emit('close')"
    >
        <div class="w-full max-w-md overflow-hidden rounded-[20px] border-none bg-white shadow-[0_20px_40px_rgba(0,0,0,0.2)]">
            <div class="flex items-center justify-between bg-[#7B112C] px-6 py-5 text-white">
                <h5 class="flex items-center gap-2 text-[1.15rem] font-bold">
                    <svg class="h-5 w-5" viewBox="0 0 16 16" fill="currentColor"><path d="M0 8a4 4 0 1 1 7.465 2H14a.5.5 0 0 1 .354.146l1.5 1.5a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0L12.5 12.707l-1.146 1.147a.5.5 0 0 1-.708 0L9.5 12.707l-1.146 1.147a.5.5 0 0 1-.708-.708L8.5 12.293 7.965 11.76A4 4 0 0 1 0 8m4-3a3 3 0 1 0 0 6 3 3 0 0 0 0-6"/></svg>
                    Lupa Password
                </h5>
                <button type="button" class="text-2xl leading-none text-white/80 hover:text-white" @click="emit('close')">&times;</button>
            </div>

            <form @submit.prevent="handleSubmit">
                <div class="space-y-4 p-6">
                    <p class="text-sm text-slate-500">
                        Silakan masukkan data diri Anda. Admin akan menghubungi nomor WhatsApp Anda untuk konfirmasi dan reset password.
                    </p>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-800">Nama Lengkap</label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 16 16" fill="currentColor"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/></svg>
                            <input v-model="form.nama" type="text" placeholder="Masukkan nama lengkap" required
                                class="w-full rounded-[10px] border-[1.5px] border-slate-200 py-2.5 pl-10 pr-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-800">NRP</label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 16 16" fill="currentColor"><path d="M8.39 12.648a1.32 1.32 0 0 0-.015.18c0 .305.21.508.5.508.266 0 .432-.14.46-.377l.115-1.086h.611c.386 0 .658-.208.658-.535 0-.245-.202-.42-.494-.42h-.665l.256-2.42h.746c.398 0 .671-.203.671-.531 0-.245-.203-.421-.507-.421h-.85l.213-1.978a1 1 0 0 0 .008-.146c0-.302-.21-.502-.492-.502-.266 0-.431.14-.46.377l-.114 1.088h-1.34l-.113 1.088h-.615c-.386 0-.658.208-.658.535 0 .245.202.42.494.42h.665l-.256 2.42h-.746c-.398 0-.671.203-.671.531 0 .245.203.421.507.421h.85l-.213 1.978zm-3.043-3.665.256-2.42h1.339l-.256 2.42z"/></svg>
                            <input v-model="form.nrp" type="text" placeholder="Masukkan NRP Anda" required
                                class="w-full rounded-[10px] border-[1.5px] border-slate-200 py-2.5 pl-10 pr-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-slate-800">Nomor WhatsApp</label>
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 16 16" fill="currentColor"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.337-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/></svg>
                            <input v-model="form.no_wa" type="text" placeholder="Contoh: 081234567890" required
                                class="w-full rounded-[10px] border-[1.5px] border-slate-200 py-2.5 pl-10 pr-3 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10" />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4">
                    <button type="button" class="text-sm font-bold text-slate-500" @click="emit('close')">Batal</button>
                    <button type="submit" class="rounded-[10px] bg-[#7B112C] px-4 py-2 text-sm font-bold text-white">
                        Kirim Permintaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
