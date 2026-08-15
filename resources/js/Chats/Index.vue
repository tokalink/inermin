<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import InerminAppLayout from '../InerminAppLayout.vue'

const props = defineProps({
  page_title: String,
  threads: Array,
  active_phone: String,
  active_thread: Object,
  messages: Array,
})

const searchQuery = ref('')
const messageContainer = ref(null)

// Search filter for threads
const filteredThreads = computed(() => {
  if (!searchQuery.value.trim()) return props.threads || []
  const q = searchQuery.value.toLowerCase()
  return (props.threads || []).filter(t => 
    (t.name && t.name.toLowerCase().includes(q)) ||
    (t.phone && t.phone.toLowerCase().includes(q)) ||
    (t.last_message && t.last_message.toLowerCase().includes(q))
  )
})

// Current selected active contact
const currentPhone = ref(props.active_phone || (props.threads && props.threads.length ? props.threads[0].phone : ''))

// Send Message Form
const form = useForm({
  to_phone: currentPhone.value,
  message: '',
  file: null,
})

const selectThread = (phone) => {
  currentPhone.value = phone
  form.to_phone = phone
  router.get(window.location.pathname, { phone }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const scrollToBottom = () => {
  nextTick(() => {
    if (messageContainer.value) {
      messageContainer.value.scrollTop = messageContainer.value.scrollHeight
    }
  })
}

watch(() => props.messages, () => {
  scrollToBottom()
}, { deep: true, immediate: true })

onMounted(() => {
  scrollToBottom()
})

const handleSendMessage = () => {
  if (!form.message.trim() && !form.file) return
  form.to_phone = currentPhone.value
  
  form.post(window.location.pathname + '/send', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('message', 'file')
      scrollToBottom()
    }
  })
}

// Helpers
const formatPhone = (phone) => {
  if (!phone) return ''
  if (phone.startsWith('62')) {
    return '+62 ' + phone.substring(2, 5) + '-' + phone.substring(5, 9) + '-' + phone.substring(9)
  }
  return phone
}

const getInitials = (name) => {
  if (!name) return '?'
  const parts = name.split(' ')
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase()
  return name.substring(0, 2).toUpperCase()
}

const formatTime = (timeStr) => {
  if (!timeStr) return ''
  try {
    const d = new Date(timeStr)
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
  } catch (e) {
    return timeStr
  }
}
</script>

<template>
  <InerminAppLayout>
    <Head :title="page_title || 'WhatsApp Chats Center'" />

    <!-- Main WhatsApp Web Container -->
    <div class="h-[calc(100vh-140px)] bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800/80 shadow-2xl overflow-hidden flex flex-col md:flex-row antialiased">
      
      <!-- LEFT PANEL: Conversations Sidebar -->
      <div class="w-full md:w-80 lg:w-96 border-r border-slate-200 dark:border-slate-800 flex flex-col bg-slate-50/50 dark:bg-slate-900/60 shrink-0">
        
        <!-- Sidebar Header & Actions -->
        <div class="p-4 border-b border-slate-200 dark:border-slate-800 space-y-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black">
                <i class="bi bi-whatsapp text-lg"></i>
              </div>
              <div>
                <h2 class="font-extrabold text-sm text-slate-900 dark:text-white tracking-tight">WhatsApp Chats</h2>
                <p class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  Connected
                </p>
              </div>
            </div>

            <!-- Datagrid Table View Toggle Button -->
            <a
              :href="adminPath + '/chats?view_type=table'"
              class="p-2 rounded-xl text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-200/60 dark:hover:bg-slate-800 transition"
              title="Switch to Data Table View"
            >
              <i class="bi bi-table text-base"></i>
            </a>
          </div>

          <!-- Search Contacts Filter Input -->
          <div class="relative">
            <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search or start new chat..."
              class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/80 rounded-xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition"
            />
          </div>
        </div>

        <!-- Conversations Threads List -->
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/50 custom-scrollbar">
          <template v-if="filteredThreads.length">
            <div
              v-for="t in filteredThreads"
              :key="t.phone"
              @click="selectThread(t.phone)"
              :class="[
                'p-3.5 flex items-center gap-3.5 cursor-pointer transition-all duration-150 relative group',
                currentPhone === t.phone
                  ? 'bg-emerald-500/10 dark:bg-emerald-500/15 border-l-4 border-emerald-500'
                  : 'hover:bg-slate-100/80 dark:hover:bg-slate-800/50'
              ]"
            >
              <!-- Avatar -->
              <div class="relative shrink-0">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-emerald-600 via-teal-500 to-emerald-400 flex items-center justify-center text-white font-extrabold text-xs shadow-md shadow-emerald-500/20">
                  {{ getInitials(t.name || t.phone) }}
                </div>
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 rounded-full ring-2 ring-white dark:ring-slate-900"></span>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <h4 class="font-bold text-xs text-slate-900 dark:text-white truncate">
                    {{ t.name || formatPhone(t.phone) }}
                  </h4>
                  <span class="text-[10px] font-semibold text-slate-400 shrink-0">
                    {{ formatTime(t.send_at) }}
                  </span>
                </div>
                
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5 font-normal">
                  <span v-if="t.last_type === 'out'" class="text-emerald-500 font-semibold me-1">You:</span>
                  {{ t.last_message || 'Attachment' }}
                </p>
              </div>

              <!-- Unread Count Badge if any -->
              <span v-if="t.unread" class="px-1.5 py-0.5 rounded-full bg-emerald-500 text-white font-bold text-[10px]">
                {{ t.unread }}
              </span>
            </div>
          </template>

          <div v-else class="p-8 text-center text-slate-400 space-y-2">
            <i class="bi bi-chat-left-dots text-3xl"></i>
            <p class="text-xs font-semibold">No chats found</p>
          </div>
        </div>

      </div>

      <!-- RIGHT PANEL: Active Chat Thread Window -->
      <div v-if="active_thread" class="flex-1 flex flex-col bg-[#efeae2]/30 dark:bg-slate-950/80 relative">
        
        <!-- WhatsApp Chat Pattern Overlay Effect -->
        <div class="absolute inset-0 opacity-5 dark:opacity-10 pointer-events-none bg-[radial-gradient(#000_1px,transparent_1px)] dark:bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:16px_16px]"></div>

        <!-- Chat Window Top Bar Header -->
        <div class="h-16 px-6 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 flex items-center justify-between z-10 shrink-0">
          
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-500 flex items-center justify-center text-white font-extrabold text-xs shadow-md">
              {{ getInitials(active_thread.name || active_thread.phone) }}
            </div>
            
            <div>
              <h3 class="font-bold text-sm text-slate-900 dark:text-white leading-tight">
                {{ active_thread.name || formatPhone(active_thread.phone) }}
              </h3>
              <p class="text-[11px] font-medium text-slate-400">
                {{ formatPhone(active_thread.phone) }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-xl transition">
              <i class="bi bi-search text-base"></i>
            </button>
            <button class="p-2 text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-xl transition">
              <i class="bi bi-three-dots-vertical text-base"></i>
            </button>
          </div>

        </div>

        <!-- Scrollable Messages Container -->
        <div
          ref="messageContainer"
          class="flex-1 p-4 md:p-6 overflow-y-auto space-y-3 z-10 custom-scrollbar"
        >
          <template v-if="messages && messages.length">
            <div
              v-for="msg in messages"
              :key="msg.id"
              :class="[
                'flex flex-col max-w-[80%] md:max-w-[65%]',
                msg.chat_type === 'out' ? 'ml-auto items-end' : 'mr-auto items-start'
              ]"
            >
              <!-- Chat Bubble -->
              <div
                :class="[
                  'px-4 py-2.5 rounded-2xl text-xs leading-relaxed shadow-sm relative group transition-all',
                  msg.chat_type === 'out'
                    ? 'bg-emerald-600 text-white rounded-tr-xs'
                    : 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-tl-xs border border-slate-200/80 dark:border-slate-700/60'
                ]"
              >
                <!-- Image/File Attachment if present -->
                <div v-if="msg.file" class="mb-2 rounded-xl overflow-hidden">
                  <img :src="msg.file" class="max-w-full max-h-60 object-cover" alt="Attachment" />
                </div>

                <!-- Text Message -->
                <p class="whitespace-pre-wrap break-words font-normal">{{ msg.message }}</p>

                <!-- Bubble Metadata Footer (Time & Checkmarks) -->
                <div
                  :class="[
                    'flex items-center justify-end gap-1 mt-1 text-[10px] font-medium',
                    msg.chat_type === 'out' ? 'text-emerald-100' : 'text-slate-400'
                  ]"
                >
                  <span>{{ formatTime(msg.send_at || msg.created_at) }}</span>
                  <i v-if="msg.chat_type === 'out'" class="bi bi-check2-all text-xs text-emerald-200"></i>
                </div>
              </div>
            </div>
          </template>

          <div v-else class="h-full flex flex-col items-center justify-center text-slate-400 space-y-2">
            <i class="bi bi-chat-quote text-4xl text-slate-300 dark:text-slate-600"></i>
            <p class="text-xs font-semibold">No messages in this chat yet</p>
          </div>
        </div>

        <!-- Bottom Action Bar Input -->
        <div class="p-3 md:p-4 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 z-10 shrink-0">
          <form @submit.prevent="handleSendMessage" class="flex items-center gap-2">
            
            <!-- Attachment Paperclip -->
            <label class="p-2.5 rounded-xl text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer transition">
              <i class="bi bi-paperclip text-lg"></i>
              <input type="file" class="hidden" @change="(e) => form.file = e.target.files[0]" />
            </label>

            <!-- Text Message Input Field -->
            <input
              v-model="form.message"
              type="text"
              placeholder="Type a message..."
              class="flex-1 px-4 py-2.5 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition"
              @keydown.enter.prevent="handleSendMessage"
            />

            <!-- Send Button -->
            <button
              type="submit"
              :disabled="form.processing || (!form.message.trim() && !form.file)"
              class="p-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold transition-all shadow-md shadow-emerald-600/30 flex items-center justify-center shrink-0"
            >
              <i class="bi bi-send-fill text-base"></i>
            </button>
          </form>
        </div>

      </div>

      <!-- Blank Empty State when no conversation selected -->
      <div v-else class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400 space-y-4 bg-slate-50/50 dark:bg-slate-950/50">
        <div class="w-20 h-20 rounded-3xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-4xl shadow-xl shadow-emerald-500/10">
          <i class="bi bi-whatsapp"></i>
        </div>
        <div>
          <h3 class="font-extrabold text-base text-slate-900 dark:text-white">Inermin WhatsApp Web</h3>
          <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mt-1">
            Select a conversation from the sidebar to start messaging directly with your customers.
          </p>
        </div>
      </div>

    </div>
  </InerminAppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(100, 116, 139, 0.4);
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.6);
}
</style>
