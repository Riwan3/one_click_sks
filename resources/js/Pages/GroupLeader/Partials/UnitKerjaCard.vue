<script setup>
import { computed } from 'vue';
import { MapPin, UserSquare2, Truck, Fuel, ShieldCheck } from 'lucide-vue-next';
import SpeakUpBanner from './SpeakUpBanner.vue';

const props = defineProps({
    unit: { type: String, default: null },
    csa: { type: String, default: null },
    speakUp: { type: Object, default: null },
    lokasiRow: { type: Object, default: null },
    glRow: { type: Object, default: null },
    fleet: { type: Object, default: null },
    diggerFleet: { type: Array, default: () => [] },
});

const cleanUnit = computed(() => {
    if (!props.unit) return null;
    return props.unit.replace(/^MASTER\s+/i, '').trim();
});
</script>

<template>
    <div class="flex flex-col gap-3">
        <!-- Main Unit Display -->
        <div class="flex min-h-[84px] flex-col items-center justify-center rounded-2xl bg-[#f8fafc] p-4 shadow-xs border border-slate-100 text-center">
            <p v-if="cleanUnit" class="m-0 text-3xl font-extrabold tracking-tight text-[#0d2340] uppercase">{{ cleanUnit }}</p>
            <div v-else class="h-2 w-24 animate-pulse rounded-full bg-slate-200"></div>
            <p v-if="csa" class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-500">{{ csa }}</p>
        </div>
        
        <!-- Lokasi / Posisi Box (from Unit Support / Dozer PC) -->
        <div v-if="lokasiRow" class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 p-3 shadow-xs">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600">
                <MapPin class="h-5 w-5 text-white" />
            </div>
            <div class="leading-tight">
                <div class="text-[0.62rem] font-bold uppercase tracking-widest text-red-400">Posisi / Lokasi Anda</div>
                <div class="text-[0.95rem] font-bold text-slate-800">{{ lokasiRow.lokasi }}</div>
                <div v-if="lokasiRow.keterangan" class="text-xs text-slate-500 mt-0.5">{{ lokasiRow.keterangan }}</div>
            </div>
        </div>

        <!-- Password Speak Up Check Point Banner -->
        <SpeakUpBanner v-if="speakUp" :speak-up="speakUp" />

        <!-- Fleet Card (Operator HD/Dump Truck) -->
        <div v-if="fleet" class="flex flex-col overflow-hidden rounded-2xl bg-[#fdf2f8] shadow-xs border border-pink-100/60">
            <div class="flex flex-col p-3 gap-2">
                <!-- Loading -->
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white shadow-xs border border-fuchsia-100">
                        <Truck class="h-4 w-4 text-rose-600" />
                    </div>
                    <div class="flex flex-col justify-center leading-tight">
                        <span class="text-[0.62rem] font-extrabold uppercase tracking-widest text-slate-500">Loading (Excavator) & Area</span>
                        <span class="text-sm font-bold text-slate-800">{{ fleet.loader_id || '-' }}</span>
                        <span v-if="fleet.front" class="text-[0.7rem] text-slate-500">di {{ fleet.front }}</span>
                    </div>
                </div>
                
                <!-- Dumping -->
                <div class="flex items-center gap-3 mt-1 pt-2 border-t border-fuchsia-200/50">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white shadow-xs border border-fuchsia-100">
                        <MapPin class="h-4 w-4 text-rose-600" />
                    </div>
                    <div class="flex flex-col justify-center leading-tight">
                        <span class="text-[0.62rem] font-extrabold uppercase tracking-widest text-slate-500">Dumping (Disposal)</span>
                        <span class="text-sm font-bold text-slate-800">{{ fleet.area || (fleet.group_name || '-') }}</span>
                    </div>
                </div>

                <!-- Refueling -->
                <div class="flex items-center gap-3 mt-1 pt-2 border-t border-fuchsia-200/50">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white shadow-xs border border-fuchsia-100">
                        <Fuel class="h-4 w-4 text-orange-600" />
                    </div>
                    <div class="flex flex-col justify-center leading-tight">
                        <span class="text-[0.62rem] font-extrabold uppercase tracking-widest text-slate-500">Refueling Time</span>
                        <span class="text-sm font-bold text-slate-800">{{ fleet.refueling_time || '-' }}</span>
                    </div>
                </div>
                
                <!-- PIC from GL row combined with fleet -->
                <div v-if="glRow && glRow.front_pic" class="flex items-center gap-3 mt-1 pt-2 border-t border-fuchsia-200/50">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white shadow-xs border border-fuchsia-100">
                        <UserSquare2 class="h-4 w-4 text-blue-600" />
                    </div>
                    <div class="flex flex-col justify-center leading-tight">
                        <span class="text-[0.62rem] font-extrabold uppercase tracking-widest text-slate-500">PIC Loading / Front</span>
                        <span class="text-sm font-bold text-slate-800">{{ glRow.front_pic }}</span>
                    </div>
                </div>

                <div v-if="glRow && glRow.disposal_pic" class="flex items-center gap-3 mt-1 pt-2 border-t border-fuchsia-200/50">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white shadow-xs border border-fuchsia-100">
                        <UserSquare2 class="h-4 w-4 text-emerald-600" />
                    </div>
                    <div class="flex flex-col justify-center leading-tight">
                        <span class="text-[0.62rem] font-extrabold uppercase tracking-widest text-slate-500">PIC Disposal</span>
                        <span class="text-sm font-bold text-slate-800">{{ glRow.disposal_pic }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Digger Fleet Card (Operator Excavator) -->
        <div v-if="!fleet && diggerFleet && diggerFleet.length > 0" class="flex flex-col overflow-hidden rounded-xl border border-slate-200 shadow-sm mt-2">
            <div class="bg-amber-50 text-center py-1 px-2 border-b border-slate-100">
                <span class="text-[0.65rem] font-bold uppercase tracking-wider text-slate-700">{{ diggerFleet[0].area || '-' }}</span>
            </div>
            <div class="bg-orange-50 text-center py-1 px-2 border-b border-slate-100">
                <span class="text-[0.65rem] font-bold text-amber-700">{{ diggerFleet[0].refueling_time || '-' }}</span>
            </div>
            
            <div v-if="glRow && (glRow.front_pic || glRow.disposal_pic)" class="bg-sky-50 text-center py-1.5 px-2 border-b border-slate-100 flex items-center justify-center gap-2 text-[0.65rem]">
                <span v-if="glRow.front_pic" class="font-bold text-blue-700 flex items-center gap-1">
                    <UserSquare2 class="h-3 w-3" /> PIC Front: {{ glRow.front_pic }}
                </span>
                <span v-if="glRow.front_pic && glRow.disposal_pic" class="text-slate-400">&bull;</span>
                <span v-if="glRow.disposal_pic" class="font-bold text-green-700 flex items-center gap-1">
                    <UserSquare2 class="h-3 w-3" /> PIC Disposal: {{ glRow.disposal_pic }}
                </span>
            </div>
            
            <div class="bg-slate-900 text-center py-2 text-sm font-extrabold text-white uppercase tracking-wide">
                {{ unit }}
            </div>
            
            <div class="flex flex-col p-2 bg-white gap-1">
                <div v-for="df in diggerFleet" :key="df.unit_nrp" class="flex justify-between items-center py-1.5 px-2 border-b border-slate-100 last:border-0">
                    <span class="text-xs font-bold text-slate-600 uppercase">{{ df.unit_nrp }}</span>
                    <span class="rounded-full px-2 py-0.5 text-[0.65rem] font-bold text-white min-w-[24px] text-center shadow-xs"
                        :class="[
                            df.unit_status === 'M' ? 'bg-blue-600' :
                            df.unit_status === 'N' ? 'bg-green-600' :
                            ['G', 'J', 'R'].includes(df.unit_status) ? 'bg-red-600' : 'bg-slate-500'
                        ]">
                        {{ df.unit_status }}
                    </span>
                </div>
            </div>
            
            <div class="bg-slate-50 p-2 border-t border-slate-200">
                <div class="text-center font-bold text-[0.7rem] text-slate-800 mb-1 uppercase">
                    {{ unit }}{{ diggerFleet[0].front ? ' DI ' + diggerFleet[0].front : '' }}
                </div>
                <div class="flex justify-between px-2 font-bold text-[0.7rem]">
                    <span class="text-blue-600">{{ diggerFleet[0].distance ? parseInt(diggerFleet[0].distance) + ' m' : '-' }}</span>
                    <span class="text-green-600">{{ diggerFleet.length }} Unit</span>
                </div>
            </div>
        </div>

        <!-- PIC Pengawas Box (Standalone, if no fleet and no digger fleet) -->
        <div v-if="!fleet && (!diggerFleet || diggerFleet.length === 0) && glRow && (glRow.front_pic || glRow.disposal_pic)" class="flex flex-col gap-2 rounded-xl border border-sky-100 bg-sky-50 p-3 shadow-sm">
            <div v-if="glRow.front_pic" class="flex items-center gap-2">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white shadow-xs">
                    <UserSquare2 class="h-4 w-4 text-sky-600" />
                </div>
                <div class="leading-tight">
                    <div class="text-[0.62rem] font-bold uppercase tracking-widest text-sky-500">PIC Loading / Front</div>
                    <div class="text-[0.9rem] font-bold text-slate-800">{{ glRow.front_pic }}</div>
                </div>
            </div>
            <div v-if="glRow.disposal_pic" class="flex items-center gap-2" :class="{ 'mt-1 pt-2 border-t border-sky-100/50': glRow.front_pic }">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white shadow-xs">
                    <UserSquare2 class="h-4 w-4 text-emerald-600" />
                </div>
                <div class="leading-tight">
                    <div class="text-[0.62rem] font-bold uppercase tracking-widest text-emerald-500">PIC Disposal</div>
                    <div class="text-[0.9rem] font-bold text-slate-800">{{ glRow.disposal_pic }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
