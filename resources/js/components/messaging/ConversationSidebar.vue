<template>
  <aside class="w-full md:w-[380px] border-r border-slate-200 bg-white flex flex-col h-full flex-shrink-0">
    <!-- Top Header -->
    <div class="px-5 py-4 border-b border-slate-200 bg-white flex-shrink-0">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <h1 class="text-xl font-bold text-slate-900 tracking-tight">Messages</h1>
          <span
            v-if="totalUnreadCount > 0"
            class="px-2 py-0.5 rounded-full bg-[#d8aa3c] text-slate-950 font-bold text-xs shadow-2xs"
          >
            {{ totalUnreadCount }}
          </span>
        </div>

        <!-- + New Message Button -->
        <button
          type="button"
          @click="$emit('open-new-modal')"
          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#062f78] hover:bg-[#0d4399] text-white text-xs font-semibold shadow-xs transition active:scale-95 cursor-pointer"
          title="Start a new message"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          <span>New Message</span>
        </button>
      </div>

      <!-- Search Input -->
      <div class="relative mb-3">
        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </span>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search conversations..."
          class="w-full pl-9 pr-8 py-2 text-xs rounded-xl bg-slate-100/90 border border-transparent focus:border-slate-300 focus:bg-white focus:outline-hidden transition placeholder:text-slate-400"
        />
        <button
          v-if="searchQuery"
          type="button"
          @click="searchQuery = ''"
          class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 text-xs cursor-pointer"
        >
          ✕
        </button>
      </div>

      <!-- Filter Tabs (All, Unread, Archived) with active bottom indicator -->
      <div class="flex items-center gap-4 text-xs font-medium -mb-4 pt-1">
        <button
          type="button"
          @click="activeFilter = 'all'"
          class="pb-3 transition border-b-2 font-semibold cursor-pointer"
          :class="activeFilter === 'all'
            ? 'text-[#062f78] border-[#062f78]'
            : 'text-slate-500 border-transparent hover:text-slate-900'"
        >
          All
        </button>
        <button
          type="button"
          @click="activeFilter = 'unread'"
          class="pb-3 transition border-b-2 font-semibold cursor-pointer flex items-center gap-1.5"
          :class="activeFilter === 'unread'
            ? 'text-[#062f78] border-[#062f78]'
            : 'text-slate-500 border-transparent hover:text-slate-900'"
        >
          <span>Unread</span>
          <span
            v-if="totalUnreadCount > 0"
            class="px-1.5 py-0.2 rounded-full text-[10px] bg-[#d8aa3c] text-slate-950 font-bold"
          >
            {{ totalUnreadCount }}
          </span>
        </button>
        <button
          type="button"
          @click="activeFilter = 'archived'"
          class="pb-3 transition border-b-2 font-semibold cursor-pointer"
          :class="activeFilter === 'archived'
            ? 'text-[#062f78] border-[#062f78]'
            : 'text-slate-500 border-transparent hover:text-slate-900'"
        >
          Archived
        </button>
      </div>
    </div>

    <!-- Conversations List -->
    <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
      <!-- Loading indicator -->
      <div v-if="isLoading" class="py-12 text-center text-slate-400">
        <div class="inline-block w-6 h-6 border-2 border-[#062f78] border-t-transparent rounded-full animate-spin"></div>
        <p class="mt-2 text-xs font-medium">Loading conversations...</p>
      </div>

      <!-- Empty state -->
      <div
        v-else-if="filteredConversations.length === 0"
        class="py-12 px-4 text-center text-slate-400 space-y-2"
      >
        <div class="w-12 h-12 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-xl text-slate-400">
          <span v-if="activeFilter === 'unread'">✨</span>
          <span v-else-if="activeFilter === 'archived'">🗂️</span>
          <span v-else>💬</span>
        </div>
        <p class="text-xs font-semibold text-slate-700">
          <span v-if="activeFilter === 'unread'">All caught up!</span>
          <span v-else-if="activeFilter === 'archived'">No archived chats</span>
          <span v-else>No conversations found</span>
        </p>
        <p class="text-[11px] text-slate-400 max-w-xs mx-auto">
          <span v-if="activeFilter === 'unread'">You have responded to all your inquiries and messages.</span>
          <span v-else-if="activeFilter === 'archived'">Conversations you archive will appear here.</span>
          <span v-else>Click "New Message" to connect with clergy, commissions, staff, or parishioners.</span>
        </p>
        <button
          v-if="activeFilter === 'all'"
          type="button"
          @click="$emit('open-new-modal')"
          class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#062f78] text-white hover:bg-[#0d4399] transition shadow-xs cursor-pointer"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
          </svg>
          <span>Start a chat</span>
        </button>
      </div>

      <!-- Items -->
      <ConversationItem
        v-for="conv in filteredConversations"
        :key="conv.id"
        :conversation="conv"
        :is-active="conv.id === activeId"
        :current-user-id="currentUserId"
        @select="$emit('select-conversation', conv)"
      />
    </div>
  </aside>
</template>

<script setup>
import { ref, computed } from 'vue';
import ConversationItem from './ConversationItem.vue';

const props = defineProps({
  conversations: {
    type: Array,
    default: () => [],
  },
  activeId: {
    type: Number,
    default: null,
  },
  currentUserId: {
    type: Number,
    required: true,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['select-conversation', 'open-new-modal']);

const searchQuery = ref('');
const activeFilter = ref('all');

const totalUnreadCount = computed(() => {
  return props.conversations.reduce((sum, c) => sum + (c.unread_count || 0), 0);
});

const filteredConversations = computed(() => {
  let list = props.conversations;

  // Filter tab
  if (activeFilter.value === 'unread') {
    list = list.filter((c) => (c.unread_count || 0) > 0 && !c.is_archived);
  } else if (activeFilter.value === 'archived') {
    list = list.filter((c) => !!c.is_archived);
  } else {
    // 'all' filter excludes archived by default
    list = list.filter((c) => !c.is_archived);
  }

  // Search query
  const q = searchQuery.value.trim().toLowerCase();
  if (q) {
    list = list.filter((c) => {
      const nameMatch = (c.name || '')?.toLowerCase().includes(q)
        || (c.title || '')?.toLowerCase().includes(q)
        || (c.peer?.name || '')?.toLowerCase().includes(q)
        || (c.peer?.display_name || '')?.toLowerCase().includes(q);
      const msgMatch = c.latest_message?.body?.toLowerCase().includes(q);
      const commMatch = c.commission_name?.toLowerCase().includes(q);
      const subtitleMatch = c.subtitle?.toLowerCase().includes(q);
      return nameMatch || msgMatch || commMatch || subtitleMatch;
    });
  }

  return list;
});
</script>

