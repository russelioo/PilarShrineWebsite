<template>
  <div class="h-full w-full flex-1 flex flex-col min-h-0 bg-white font-sans overflow-hidden">
    <!-- Main Two-Column Messenger Container -->
    <div class="flex-1 flex min-h-0 relative h-full">
      <!-- LEFT: Conversations Sidebar (Strict 380px on desktop) -->
      <div
        class="h-full flex-shrink-0"
        :class="mobileView === 'chat' ? 'hidden md:block' : 'w-full md:w-[380px]'"
      >
        <ConversationSidebar
          :conversations="conversations"
          :active-id="activeConversationId"
          :current-user-id="currentUser.id"
          :is-loading="isLoadingConversations"
          @select-conversation="selectConversation"
          @open-new-modal="showNewMessageModal = true"
        />
      </div>

      <!-- RIGHT: Active Chat View / Empty State -->
      <main
        class="flex-1 flex flex-col h-full bg-slate-50/40 min-w-0"
        :class="mobileView === 'list' ? 'hidden md:flex' : 'flex'"
      >
        <!-- Case A: A conversation is active -->
        <template v-if="activeConversation">
          <!-- Chat Header -->
          <ChatHeader
            :conversation="activeConversation"
            :current-user-id="currentUser.id"
            @back="mobileView = 'list'"
            @open-details="showContactInfoDrawer = true"
            @toggle-archive="toggleArchiveConversation"
            @toggle-search="toggleChatSearch"
          />

          <!-- In-Conversation Search Bar -->
          <div
            v-if="showChatSearch"
            class="px-6 py-2.5 bg-white border-b border-slate-200 flex items-center gap-3 transition-all flex-shrink-0"
          >
            <div class="relative flex-1">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </span>
              <input
                ref="chatSearchInputRef"
                v-model="chatSearchQuery"
                type="text"
                placeholder="Search in this conversation..."
                class="w-full pl-9 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:border-[#062f78] focus:bg-white text-slate-800"
              />
              <button
                v-if="chatSearchQuery"
                type="button"
                @click="chatSearchQuery = ''"
                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 text-xs"
              >
                ✕
              </button>
            </div>
            <span v-if="chatSearchQuery" class="text-[11px] text-slate-500 font-medium">
              {{ displayedMessages.length }} match{{ displayedMessages.length === 1 ? '' : 'es' }}
            </span>
            <button
              type="button"
              @click="closeChatSearch"
              class="text-xs text-slate-500 hover:text-slate-800 font-medium px-2 py-1 rounded hover:bg-slate-100 transition"
            >
              Done
            </button>
          </div>

          <!-- Messages Scrollable Body -->
          <MessageList
            ref="messageListRef"
            :messages="displayedMessages"
            :current-user-id="currentUser.id"
            :is-group="!activeConversation.is_direct"
            :is-loading-more="isLoadingMoreMessages"
            @preview-image="openLightbox"
            @retry-message="retryMessage"
            @load-more="loadOlderMessages"
          />

          <!-- Message Composer -->
          <MessageComposer
            :daily-usage="dailyUsage"
            :is-sending="isSending"
            @send="sendMessage"
          />
        </template>

        <!-- Case B: No conversation selected (Compact, Subtle Empty State) -->
        <div
          v-else
          class="flex-1 flex flex-col items-center justify-center p-6 text-center bg-slate-50/40 select-none"
        >
          <div class="w-12 h-12 rounded-2xl bg-[#062f78]/8 text-[#062f78] flex items-center justify-center text-2xl mb-3 shadow-2xs border border-[#062f78]/10">
            ⛪
          </div>
          <h3 class="text-base font-semibold text-slate-800 tracking-tight">
            Pilar Shrine Parish Messaging
          </h3>
          <p class="text-xs text-slate-500 max-w-sm mt-1 leading-relaxed">
            Select a conversation to view messages, or start a new conversation.
          </p>
          <div class="mt-4">
            <button
              type="button"
              @click="showNewMessageModal = true"
              class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-[#062f78] hover:bg-[#0d4399] text-white text-xs font-semibold shadow-xs transition active:scale-95 cursor-pointer"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
              </svg>
              <span>New Message</span>
            </button>
          </div>
        </div>
      </main>
    </div>

    <!-- Lightbox Modal for Photo Zoom -->
    <LightboxModal
      v-if="lightboxImage"
      :image-url="lightboxImage.preview_url || lightboxImage.url"
      :image-name="lightboxImage.name"
      :download-url="lightboxImage.url"
      @close="lightboxImage = null"
    />

    <!-- Contact Info Slide-Out Drawer -->
    <ContactInfoDrawer
      v-if="activeConversation"
      :is-open="showContactInfoDrawer"
      :conversation="activeConversation"
      :current-user-id="currentUser.id"
      :messages-count="messages.length"
      @close="showContactInfoDrawer = false"
      @toggle-archive="toggleArchiveConversation"
    />

    <!-- Start New Conversation / Directory Modal -->
    <NewConversationModal
      :is-open="showNewMessageModal"
      @close="showNewMessageModal = false"
      @select-user="handleStartUserConversation"
      @select-commission="handleStartCommissionConversation"
      @select-ministry="handleStartMinistryConversation"
      @send-new-message="handleSendNewMessage"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import axios from 'axios';
import ConversationSidebar from './ConversationSidebar.vue';
import ChatHeader from './ChatHeader.vue';
import MessageList from './MessageList.vue';
import MessageComposer from './MessageComposer.vue';
import LightboxModal from './LightboxModal.vue';
import ContactInfoDrawer from './ContactInfoDrawer.vue';
import NewConversationModal from './NewConversationModal.vue';

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({}),
  },
});

// Current user state
const currentUser = ref(props.initialData?.currentUser || {
  id: 0,
  name: 'Parishioner',
  email: '',
  role: 'parishioner',
});

// Daily attachment usage
const dailyUsage = ref(props.initialData?.dailyUsage || {
  images: 0,
  files: 0,
  max_images: 3,
  max_files: 3,
});

// App states
const conversations = ref([]);
const activeConversationId = ref(props.initialData?.activeConversationId || null);
const activeConversation = ref(null);
const messages = ref([]);

const isLoadingConversations = ref(false);
const isLoadingMessages = ref(false);
const isLoadingMoreMessages = ref(false);
const isSending = ref(false);

const mobileView = ref('list'); // 'list' | 'chat'
const messageListRef = ref(null);

// Modals and Drawers
const lightboxImage = ref(null);
const showContactInfoDrawer = ref(false);
const showNewMessageModal = ref(false);
const showChatSearch = ref(false);
const chatSearchQuery = ref('');
const chatSearchInputRef = ref(null);

const displayedMessages = computed(() => {
  if (!chatSearchQuery.value.trim()) return messages.value;
  const q = chatSearchQuery.value.trim().toLowerCase();
  return messages.value.filter((m) => {
    if (m.body && m.body.toLowerCase().includes(q)) return true;
    if (m.attachments && m.attachments.some((a) => a.name?.toLowerCase().includes(q))) return true;
    return false;
  });
});

const toggleChatSearch = () => {
  showChatSearch.value = !showChatSearch.value;
  if (showChatSearch.value) {
    nextTick(() => {
      chatSearchInputRef.value?.focus();
    });
  } else {
    chatSearchQuery.value = '';
  }
};

const closeChatSearch = () => {
  showChatSearch.value = false;
  chatSearchQuery.value = '';
};

// Polling interval tracker
let pollTimer = null;
let pollIntervalMs = 2500;

// =========================================================================
// API Calls
// =========================================================================

const fetchConversations = async () => {
  try {
    const res = await axios.get('/api/messages/conversations');
    conversations.value = res.data.conversations || [];

    // If initial active conversation ID is provided, open it
    if (activeConversationId.value && !activeConversation.value) {
      const match = conversations.value.find((c) => c.id === activeConversationId.value);
      if (match) {
        selectConversation(match, false);
      } else {
        fetchConversationDetails(activeConversationId.value);
      }
    }
  } catch (err) {
    console.error('Failed to load conversations:', err);
  }
};

const fetchConversationDetails = async (convId) => {
  try {
    const res = await axios.get(`/api/messages/conversations/${convId}`);
    activeConversation.value = res.data.conversation;
    activeConversationId.value = convId;
    mobileView.value = 'chat';
    await fetchMessages(convId);
  } catch (err) {
    console.error('Failed to load conversation details:', err);
  }
};

const fetchMessages = async (convId) => {
  isLoadingMessages.value = true;
  try {
    const res = await axios.get(`/api/messages/conversations/${convId}/messages`);
    messages.value = (res.data.messages || []).map((m) => ({
      ...m,
      is_mine: m.sender_id === currentUser.value.id,
      status: 'sent',
    }));

    // Mark as read immediately
    markConversationAsRead(convId);

    // Scroll to bottom
    nextTick(() => {
      messageListRef.value?.scrollToBottom('auto');
    });
  } catch (err) {
    console.error('Failed to load messages:', err);
  } finally {
    isLoadingMessages.value = false;
  }
};

const markConversationAsRead = async (convId) => {
  try {
    await axios.post(`/api/messages/conversations/${convId}/read`);
    // Decrement unread count locally
    const conv = conversations.value.find((c) => c.id === convId);
    if (conv) {
      conv.unread_count = 0;
    }
  } catch (err) {
    console.error('Failed to mark read:', err);
  }
};

// =========================================================================
// Sending & Optimistic UI
// =========================================================================

const sendMessage = async (payload) => {
  if (!activeConversationId.value) return;

  const tempId = 'temp-' + Date.now();
  const tempMessage = {
    id: tempId,
    temp_id: tempId,
    conversation_id: activeConversationId.value,
    sender_id: currentUser.value.id,
    sender: {
      id: currentUser.value.id,
      name: currentUser.value.name,
      role: currentUser.value.role,
      role_label: currentUser.value.role_label,
    },
    body: payload.body,
    attachments: payload.files.map((file, i) => ({
      id: `temp-att-${Date.now()}-${i}`,
      name: file.name,
      size: file.size,
      kind: file.type.startsWith('image/') ? 'image' : 'file',
      preview_url: file.type.startsWith('image/') ? URL.createObjectURL(file) : null,
      url: '#',
    })),
    status: 'sending',
    is_mine: true,
    created_at: new Date().toISOString(),
  };

  // 1. Optimistic append
  messages.value.push(tempMessage);
  nextTick(() => {
    messageListRef.value?.scrollToBottom('smooth');
  });

  // Update conversation last message snippet locally
  const activeConv = conversations.value.find((c) => c.id === activeConversationId.value);
  if (activeConv) {
    activeConv.latest_message = {
      body: payload.body,
      sender_id: currentUser.value.id,
      created_at: new Date().toISOString(),
      attachments: tempMessage.attachments,
    };
    activeConv.last_message_at = new Date().toISOString();
  }

  // 2. Prepare FormData
  const formData = new FormData();
  if (payload.body) {
    formData.append('body', payload.body);
  }
  for (const file of payload.files) {
    formData.append('attachments[]', file);
  }

  isSending.value = true;

  try {
    const res = await axios.post(
      `/api/messages/conversations/${activeConversationId.value}/messages`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    );

    const savedMsg = res.data.message;

    // 3. Replace temp message with server response
    const idx = messages.value.findIndex((m) => m.temp_id === tempId || m.id === tempId);
    if (idx !== -1) {
      messages.value[idx] = {
        ...savedMsg,
        is_mine: true,
        status: 'sent',
      };
    }

    // Update daily quota usage if returned
    if (res.data.usage) {
      dailyUsage.value = res.data.usage;
    }
  } catch (err) {
    console.error('Failed to send message:', err);
    // Mark as failed for retry
    const idx = messages.value.findIndex((m) => m.temp_id === tempId);
    if (idx !== -1) {
      messages.value[idx].status = 'failed';
      messages.value[idx]._originalPayload = payload;
    }
  } finally {
    isSending.value = false;
  }
};

const retryMessage = (failedMsg) => {
  if (failedMsg._originalPayload) {
    // Remove failed message from list
    messages.value = messages.value.filter((m) => m !== failedMsg);
    // Resend
    sendMessage(failedMsg._originalPayload);
  }
};

// =========================================================================
// Real-time Sync & Polling Engine
// =========================================================================

const syncRealTime = async () => {
  try {
    const lastMsg = messages.value.filter((m) => m.status === 'sent').slice(-1)[0];
    const lastMsgId = lastMsg?.id && !String(lastMsg.id).startsWith('temp-') ? lastMsg.id : null;

    const params = {
      active_conversation_id: activeConversationId.value || undefined,
      last_message_id: lastMsgId || undefined,
    };

    const res = await axios.get('/api/messages/sync', { params });
    const data = res.data;

    // A. Update new messages in active conversation
    if (data.new_messages && data.new_messages.length > 0) {
      let appendedAny = false;
      for (const newMsg of data.new_messages) {
        // Skip if already in list
        if (!messages.value.some((m) => m.id === newMsg.id)) {
          messages.value.push({
            ...newMsg,
            is_mine: newMsg.sender_id === currentUser.value.id,
            status: 'sent',
          });
          appendedAny = true;
        }
      }

      if (appendedAny) {
        // Automatically mark as read
        markConversationAsRead(activeConversationId.value);
        nextTick(() => {
          messageListRef.value?.scrollToBottom('smooth');
        });
      }
    }

    // B. Update read statuses (ticks turn to double ticks)
    if (data.read_status_updates && data.read_status_updates.length > 0) {
      const readIds = new Set(data.read_status_updates);
      messages.value.forEach((m) => {
        if (readIds.has(m.id)) {
          m.is_read = true;
          m.read_at = m.read_at || new Date().toISOString();
        }
      });
    }

    // C. Refresh conversations list if updated
    if (data.conversations_updated && data.conversations_updated.length > 0) {
      data.conversations_updated.forEach((updatedConv) => {
        const existingIdx = conversations.value.findIndex((c) => c.id === updatedConv.id);
        if (existingIdx !== -1) {
          conversations.value[existingIdx] = {
            ...conversations.value[existingIdx],
            ...updatedConv,
          };
        } else {
          conversations.value.unshift(updatedConv);
        }
      });
    }
  } catch (err) {
    // Suppress network jitter errors in polling loop
  }
};

const startPolling = () => {
  stopPolling();
  pollTimer = setInterval(syncRealTime, pollIntervalMs);
};

const stopPolling = () => {
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
};

const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    pollIntervalMs = 2500;
    startPolling();
    syncRealTime(); // sync immediately on tab refocus
  } else {
    pollIntervalMs = 10000; // slow down when tab is in background
    startPolling();
  }
};

// =========================================================================
// Conversation Selection & Actions
// =========================================================================

const selectConversation = async (conv, updateHistory = true) => {
  activeConversationId.value = conv.id;
  activeConversation.value = conv;
  mobileView.value = 'chat';
  closeChatSearch();
  showContactInfoDrawer.value = false;

  // Update browser URL query without reload
  if (updateHistory) {
    const url = new URL(window.location.href);
    url.searchParams.set('conversation', conv.id);
    url.searchParams.delete('with');
    window.history.replaceState({}, '', url.toString());
  }

  await fetchMessages(conv.id);
};

const toggleArchiveConversation = async () => {
  if (!activeConversationId.value) return;
  try {
    const res = await axios.post(`/api/messages/conversations/${activeConversationId.value}/archive`);
    if (activeConversation.value) {
      activeConversation.value.is_archived = res.data.is_archived;
    }
    const conv = conversations.value.find((c) => c.id === activeConversationId.value);
    if (conv) {
      conv.is_archived = res.data.is_archived;
    }
  } catch (err) {
    console.error('Failed to toggle archive:', err);
  }
};

const handleStartUserConversation = async (user) => {
  showNewMessageModal.value = false;
  try {
    const res = await axios.post('/api/messages/conversations', { recipient_id: user.id });
    const conv = res.data.conversation;

    // Check if already in list
    const existing = conversations.value.find((c) => c.id === conv.id);
    if (!existing) {
      conversations.value.unshift(conv);
    }
    await selectConversation(conv);
  } catch (err) {
    console.error('Failed to start conversation:', err);
  }
};

const handleStartCommissionConversation = async (commission) => {
  showNewMessageModal.value = false;
  try {
    const res = await axios.post('/api/messages/conversations', { commission_id: commission.id });
    const conv = res.data.conversation;

    const existing = conversations.value.find((c) => c.id === conv.id);
    if (!existing) {
      conversations.value.unshift(conv);
    }
    await selectConversation(conv);
  } catch (err) {
    console.error('Failed to start commission conversation:', err);
  }
};

const handleStartMinistryConversation = async (ministry) => {
  showNewMessageModal.value = false;
  try {
    const res = await axios.post('/api/messages/conversations', { ministry_id: ministry.id });
    const conv = res.data.conversation;

    const existing = conversations.value.find((c) => c.id === conv.id);
    if (!existing) {
      conversations.value.unshift(conv);
    }
    await selectConversation(conv);
  } catch (err) {
    console.error('Failed to start ministry conversation:', err);
  }
};

const handleSendNewMessage = async ({ type, data, body }) => {
  showNewMessageModal.value = false;
  try {
    let payload = {};
    if (type === 'commission') {
      payload.commission_id = data.id;
    } else if (type === 'ministry') {
      payload.ministry_id = data.id;
    } else {
      payload.recipient_id = data.id;
    }

    const res = await axios.post('/api/messages/conversations', payload);
    const conv = res.data.conversation;

    const existing = conversations.value.find((c) => c.id === conv.id);
    if (!existing) {
      conversations.value.unshift(conv);
    }
    await selectConversation(conv);

    if (body) {
      await sendMessage({ body, files: [] });
    }
  } catch (err) {
    console.error('Failed to send new message:', err);
  }
};

const openLightbox = (img) => {
  lightboxImage.value = img;
};

const loadOlderMessages = async () => {
  // Can be hooked up to pagination if needed
};

// =========================================================================
// Lifecycle
// =========================================================================

onMounted(async () => {
  isLoadingConversations.value = true;
  await fetchConversations();
  isLoadingConversations.value = false;

  // Check if activePeerId was passed from server
  if (!activeConversationId.value && props.initialData?.activePeerId) {
    const peerConv = conversations.value.find(
      (c) => c.is_direct && c.participants?.some((p) => p.id === props.initialData.activePeerId)
    );
    if (peerConv) {
      selectConversation(peerConv, false);
    }
  }

  // Start real-time polling
  startPolling();
  document.addEventListener('visibilitychange', handleVisibilityChange);
});

onUnmounted(() => {
  stopPolling();
  document.removeEventListener('visibilitychange', handleVisibilityChange);
});
</script>

