<script setup>
import { ref, computed, watch, nextTick, onUnmounted } from 'vue';
import { X, MessageCircle, Send, Search, User, ChevronLeft, Circle, RefreshCw } from 'lucide-vue-next';

const props = defineProps({
  show: { type: Boolean, default: false }
});

defineEmits(['close']);

const searchQuery = ref('');
const contacts = ref([]);
const activeContactId = ref(null);
const activeMessages = ref([]);
const newMessage = ref('');
const isLoadingContacts = ref(false);
const isLoadingMessages = ref(false);
const isSending = ref(false);
const chatContainer = ref(null);
const showMobileChat = ref(false);
const isAdminUser = ref(false);
let pollTimer = null;

const activeContact = computed(() => {
  return contacts.value.find(c => c.id === activeContactId.value) || null;
});

const isNearBottom = () => {
  if (!chatContainer.value) return true;
  const threshold = 120;
  const pos = chatContainer.value.scrollTop + chatContainer.value.clientHeight;
  const height = chatContainer.value.scrollHeight;
  return height - pos <= threshold;
};

const scrollToBottom = () => {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
  });
};

const fetchContacts = async (silent = false) => {
  if (!silent) isLoadingContacts.value = true;
  try {
    const res = await fetch(`/internal-messages/contacts?search=${encodeURIComponent(searchQuery.value)}`);
    const data = await res.json();
    if (data && data.contacts) {
      contacts.value = data.contacts;
      isAdminUser.value = !!data.is_admin;

      if (contacts.value.length > 0) {
        if (!activeContactId.value || !contacts.value.some(c => c.id === activeContactId.value)) {
          const defaultContact = contacts.value.find(c => c.unread > 0) || contacts.value[0];
          selectContact(defaultContact.id);
        }
      }

      // Non-admin opens directly into chat view without extra clicks
      if (!isAdminUser.value) {
        showMobileChat.value = true;
      }
    }
  } catch (err) {
    console.error('Failed to load contacts:', err);
  } finally {
    if (!silent) isLoadingContacts.value = false;
  }
};

const fetchMessages = async (contactId) => {
  if (!contactId) return;
  isLoadingMessages.value = true;
  try {
    const res = await fetch(`/internal-messages/${contactId}`);
    const data = await res.json();
    if (data && data.messages) {
      activeMessages.value = data.messages;
      scrollToBottom();
    }
  } catch (err) {
    console.error('Failed to load messages:', err);
  } finally {
    isLoadingMessages.value = false;
  }
};

const silentSyncMessages = async () => {
  if (!activeContactId.value || !props.show) return;
  try {
    const res = await fetch(`/internal-messages/${activeContactId.value}`);
    const data = await res.json();
    if (data && data.messages) {
      const newLen = data.messages.length;
      const curLen = activeMessages.value.length;
      
      // Update only when length changed or latest message id is different
      if (newLen !== curLen || (newLen > 0 && data.messages[newLen - 1].id !== activeMessages.value[curLen - 1]?.id)) {
        const wasAtBottom = isNearBottom();
        activeMessages.value = data.messages;
        if (wasAtBottom) scrollToBottom();
      }
    }
  } catch (err) {
    // Silent background sync error ignore
  }
};

const startPolling = () => {
  stopPolling();
  pollTimer = setInterval(() => {
    if (props.show) {
      fetchContacts(true);
      if (activeContactId.value) {
        silentSyncMessages();
      }
    }
  }, 4000); // 4 seconds smart polling
};

const stopPolling = () => {
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
};

const selectContact = (contactId) => {
  activeContactId.value = contactId;
  showMobileChat.value = true;
  const c = contacts.value.find(item => item.id === contactId);
  if (c) c.unread = 0;
  fetchMessages(contactId);
  startPolling();
};

let searchDebounce = null;
const handleSearchInput = () => {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    fetchContacts();
  }, 300);
};

const handleSendMessage = async () => {
  const text = newMessage.value.trim();
  if (!text || !activeContactId.value || isSending.value) return;

  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  isSending.value = true;

  try {
    const res = await fetch('/internal-messages', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        receiver_id: activeContactId.value,
        body: text
      })
    });
    const data = await res.json();
    if (data && data.message) {
      activeMessages.value.push(data.message);
      newMessage.value = '';
      
      if (activeContact.value) {
        activeContact.value.last_message = {
          body: text,
          created_at: 'Baru saja'
        };
      }

      scrollToBottom();
    }
  } catch (err) {
    console.error('Failed to send message:', err);
  } finally {
    isSending.value = false;
  }
};

watch(() => props.show, (val) => {
  if (val) {
    showMobileChat.value = !isAdminUser.value;
    fetchContacts();
    startPolling();
  } else {
    stopPolling();
  }
});

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
      <div class="bg-white w-full max-w-4xl h-[85vh] max-h-[680px] rounded-2xl shadow-2xl border border-slate-100 overflow-hidden text-xs flex flex-col animate-in fade-in zoom-in-95 duration-150">
        
        <!-- MODAL HEADER -->
        <div class="p-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 shrink-0">
          <div class="flex items-center gap-2">
            <button 
              v-if="showMobileChat && (isAdminUser || contacts.length > 1)" 
              @click="showMobileChat = false" 
              class="md:hidden text-slate-500 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-200/60"
              title="Kembali ke Daftar Kontak"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
              <MessageCircle class="w-4 h-4 text-blue-600" />
              Pesan Internal &amp; Obrolan
            </h3>
          </div>
          <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100">
            <X class="w-4.5 h-4.5" />
          </button>
        </div>

        <!-- MAIN LAYOUT (2 COLUMNS) -->
        <div class="flex-1 flex overflow-hidden">
          
          <!-- LEFT PANEL: CONTACTS / USER SEARCH (Hidden for non-admin if only 1 admin, shown if multi-admin or admin) -->
          <div 
            v-if="isAdminUser || contacts.length > 1"
            class="w-full md:w-80 border-r border-slate-200/80 flex flex-col bg-slate-50/40 shrink-0"
            :class="{ 'hidden md:flex': showMobileChat, 'flex': !showMobileChat }"
          >
            <!-- SEARCH BAR -->
            <div class="p-3 border-b border-slate-200/60 bg-white">
              <div class="relative">
                <Search class="w-3.5 h-3.5 absolute left-3 top-2.5 text-slate-400" />
                <input 
                  type="text" 
                  v-model="searchQuery" 
                  @input="handleSearchInput"
                  :placeholder="isAdminUser ? 'Cari nama, NRP, atau jabatan...' : 'Cari Admin...'" 
                  class="w-full pl-8 pr-8 py-1.5 border border-slate-200 rounded-xl bg-slate-50 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                />
                <button @click="fetchContacts(false)" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-blue-600">
                  <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoadingContacts }" />
                </button>
              </div>
            </div>

            <!-- CONTACTS LIST -->
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
              <template v-if="!isLoadingContacts">
                <div 
                  v-for="c in contacts" 
                  :key="c.id"
                  @click="selectContact(c.id)"
                  class="p-3 flex items-center gap-3 cursor-pointer transition relative"
                  :class="[
                    activeContactId === c.id ? 'bg-blue-50/90 border-l-4 border-blue-600' : 'bg-white hover:bg-slate-50',
                    c.unread > 0 ? 'bg-rose-50/50 hover:bg-rose-50/80' : ''
                  ]"
                >
                  <div class="relative shrink-0">
                    <div 
                      class="w-9 h-9 rounded-full font-bold flex items-center justify-center text-xs shadow-xs uppercase text-white"
                      :class="c.unread > 0 ? 'bg-gradient-to-br from-rose-500 to-red-600 ring-2 ring-rose-300' : 'bg-gradient-to-br from-blue-500 to-indigo-600'"
                    >
                      {{ c.name ? c.name.charAt(0) : 'U' }}
                    </div>
                    
                    <!-- Unread Badge Dot -->
                    <span 
                      v-if="c.unread > 0"
                      class="absolute -top-1 -right-1 flex h-3.5 w-3.5 items-center justify-center rounded-full bg-rose-600 border-2 border-white text-[8px] font-black text-white animate-bounce shadow-xs"
                    >
                      {{ c.unread }}
                    </span>
                    <Circle 
                      v-else
                      class="w-2.5 h-2.5 absolute bottom-0 right-0 fill-current text-emerald-500" 
                    />
                  </div>

                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                      <h4 
                        class="truncate text-[11px] flex items-center gap-1.5"
                        :class="c.unread > 0 ? 'font-black text-rose-950' : 'font-bold text-slate-800'"
                      >
                        <span>{{ c.name }}</span>
                      </h4>
                      <span 
                        class="text-[9px] shrink-0 font-medium"
                        :class="c.unread > 0 ? 'text-rose-600 font-extrabold' : 'text-slate-400'"
                      >
                        {{ c.last_message ? c.last_message.created_at : '' }}
                      </span>
                    </div>
                    <div class="flex items-center justify-between mt-0.5">
                      <p class="text-slate-500 text-[10px] truncate">
                        <span class="text-blue-600 font-medium">[{{ c.nrp }}]</span> {{ c.jabatan }}
                      </p>
                      
                      <!-- PROMINENT RED NEW MESSAGE BADGE -->
                      <span 
                        v-if="c.unread > 0" 
                        class="px-2 py-0.5 bg-rose-600 text-white font-black text-[9px] rounded-full shadow-2xs animate-pulse flex items-center gap-1 shrink-0"
                      >
                        <span class="w-1.5 h-1.5 rounded-full bg-white animate-ping"></span>
                        PESAN BARU
                      </span>
                    </div>
                  </div>
                </div>

                <div v-if="contacts.length === 0" class="p-6 text-center text-slate-400">
                  <p class="text-xs">Pengguna tidak ditemukan</p>
                </div>
              </template>

              <div v-else class="p-8 text-center text-slate-400 space-y-2">
                <RefreshCw class="w-5 h-5 mx-auto animate-spin text-blue-600" />
                <p class="text-xs font-semibold">Memuat daftar kontak...</p>
              </div>
            </div>
          </div>

          <!-- RIGHT PANEL: CHAT WINDOW -->
          <div 
            class="flex-1 flex flex-col bg-white"
            :class="{ 'flex': showMobileChat || !isAdminUser, 'hidden md:flex': !showMobileChat && isAdminUser }"
          >
            <!-- CHAT HEADER -->
            <div v-if="activeContact" class="p-3 border-b border-slate-200/80 bg-slate-50/50 flex items-center justify-between shrink-0 gap-2">
              <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-bold flex items-center justify-center text-xs shadow-xs uppercase shrink-0">
                  {{ activeContact.name.charAt(0) }}
                </div>
                <div class="min-w-0 flex-1">
                  <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="font-bold text-slate-800 text-xs truncate">
                      {{ activeContact.name }}
                    </h4>
                    <span class="text-[10px] font-semibold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-md border border-blue-100 shrink-0">
                      NRP: {{ activeContact.nrp }}
                    </span>
                  </div>
                  <p class="text-[10px] text-slate-500 truncate">{{ activeContact.jabatan }} • {{ activeContact.unit }}</p>
                </div>
              </div>

              <button @click="fetchMessages(activeContact.id)" class="text-slate-400 hover:text-blue-600 p-1 shrink-0" title="Refresh Chat">
                <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': isLoadingMessages }" />
              </button>
            </div>

            <!-- CHAT MESSAGES STREAM -->
            <div ref="chatContainer" class="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50/30">
              <template v-if="!isLoadingMessages">
                <template v-if="activeContact && activeMessages.length > 0">
                  <div 
                    v-for="m in activeMessages" 
                    :key="m.id" 
                    class="flex flex-col"
                    :class="m.sender_name === 'Anda' ? 'items-end' : 'items-start'"
                  >
                    <div 
                      class="p-3 rounded-2xl max-w-[85%] sm:max-w-[70%] space-y-1 shadow-2xs"
                      :class="m.sender_name === 'Anda' ? 'bg-blue-600 text-white rounded-br-none' : 'bg-white border border-slate-200 text-slate-800 rounded-bl-none'"
                    >
                      <div class="flex items-center justify-between gap-4 font-bold text-[10px]" :class="m.sender_name === 'Anda' ? 'text-blue-100' : 'text-slate-500'">
                        <span>{{ m.sender_name }}</span>
                        <span class="font-normal opacity-80 text-[9px]">{{ m.created_at }}</span>
                      </div>
                      <p class="text-xs leading-relaxed" :class="m.sender_name === 'Anda' ? 'text-white' : 'text-slate-700'">
                        {{ m.body }}
                      </p>
                    </div>
                  </div>
                </template>

                <div v-else class="h-full flex flex-col items-center justify-center text-center p-6 space-y-3">
                  <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mx-auto shadow-xs border border-blue-100">
                    <MessageCircle class="w-6 h-6" />
                  </div>
                  <div>
                    <h4 class="font-bold text-slate-800 text-sm">Selamat Datang di Layanan Pesan Internal</h4>
                    <p class="text-xs text-slate-500 mt-1.5 max-w-xs mx-auto leading-relaxed">
                      Silakan sampaikan pertanyaan, laporan, atau kendala Anda secara langsung kepada Admin {{ activeContact ? activeContact.name : '' }}.
                    </p>
                  </div>
                </div>
              </template>

              <div v-else class="h-full flex items-center justify-center text-slate-400">
                <RefreshCw class="w-5 h-5 animate-spin text-blue-600" />
              </div>
            </div>

            <!-- CHAT INPUT FORM -->
            <form @submit.prevent="handleSendMessage" class="p-3 bg-white border-t border-slate-200/80 flex gap-2 shrink-0">
              <input 
                type="text" 
                v-model="newMessage" 
                :disabled="isSending || !activeContactId"
                :placeholder="`Ketik pesan ke ${activeContact ? activeContact.name : 'Admin'}...`" 
                class="flex-1 px-3 py-2 border border-slate-300 rounded-xl bg-slate-50 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 disabled:opacity-50" 
              />
              <button 
                type="submit" 
                :disabled="isSending || !activeContactId || !newMessage.trim()"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 disabled:bg-slate-300 text-white font-bold rounded-xl flex items-center gap-1.5 transition shadow-xs"
              >
                <Send class="w-3.5 h-3.5" :class="{ 'animate-pulse': isSending }" /> Kirim
              </button>
            </form>

          </div>

        </div>

      </div>
    </div>
  </Teleport>
</template>
