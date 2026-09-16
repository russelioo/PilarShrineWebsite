<template>
  <header class="h-[68px] px-6 border-b border-slate-200 bg-white flex items-center justify-between flex-shrink-0 z-10">
    <div class="flex items-center gap-3.5 min-w-0">
      <!-- Mobile Back Button -->
      <button
        type="button"
        @click="$emit('back')"
        class="md:hidden p-1.5 -ml-1 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition"
        aria-label="Back to conversations list"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
      </button>

      <!-- Avatar -->
      <div class="relative flex-shrink-0">
        <div
          class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shadow-xs overflow-hidden"
          :class="avatarBgClass"
        >
          <span v-if="conversation.is_commission">🏛️</span>
          <span v-else-if="conversation.is_ministry">👥</span>
          <img
            v-else-if="avatarUrl && !imageError"
            :src="avatarUrl"
            :alt="displayName"
            class="w-full h-full object-cover rounded-full"
            referrerpolicy="no-referrer"
            @error="imageError = true"
          />
          <span v-else>{{ initials }}</span>
        </div>
        <!-- Active indicator dot (only for online direct message peers) -->
        <span
          v-if="conversation.is_direct && peerIsOnline"
          class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-white bg-emerald-500"
          title="Online"
        ></span>
      </div>

      <!-- Title & Subtitle -->
      <div class="min-w-0 flex-1">
        <h2 class="text-base font-bold text-slate-900 truncate leading-snug">
          {{ displayName }}
        </h2>
        <p class="text-xs text-slate-500 truncate flex items-center gap-1.5 leading-snug">
          <span v-if="conversation.is_commission">
            Commission Channel · {{ memberCount }} {{ memberCount === 1 ? 'member' : 'members' }}
          </span>
          <span v-else-if="conversation.is_ministry">
            Ministry Channel · {{ memberCount }} {{ memberCount === 1 ? 'member' : 'members' }}
          </span>
          <span v-else class="flex items-center gap-1">
            <span>{{ peerRoleLabel || 'User' }} · {{ peerPresenceLabel }}</span>
            <span :class="peerIsOnline ? 'text-emerald-500' : 'text-slate-400'" class="text-[9px]">●</span>
          </span>
        </p>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center gap-1 text-slate-500">
      <!-- Search in conversation -->
      <button
        type="button"
        @click="$emit('toggle-search')"
        class="p-2 rounded-lg hover:bg-slate-100 hover:text-[#062f78] transition cursor-pointer"
        title="Search messages"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </button>

      <!-- Details / Information -->
      <button
        type="button"
        @click="$emit('open-details')"
        class="p-2 rounded-lg hover:bg-slate-100 hover:text-[#062f78] transition cursor-pointer"
        title="More / Contact Information"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </button>

      <!-- Toggle Archive -->
      <button
        type="button"
        @click="$emit('toggle-archive')"
        class="p-2 rounded-lg hover:bg-slate-100 hover:text-[#062f78] transition cursor-pointer"
        :title="conversation.is_archived ? 'Unarchive conversation' : 'Archive conversation'"
      >
        <svg v-if="conversation.is_archived" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
      </button>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  conversation: {
    type: Object,
    required: true,
  },
  currentUserId: {
    type: Number,
    required: true,
  },
});

defineEmits(['back', 'open-details', 'toggle-archive', 'toggle-search']);

const imageError = ref(false);

const avatarUrl = computed(() => {
  return props.conversation.avatar || props.conversation.peer?.avatar || null;
});

watch(avatarUrl, () => {
  imageError.value = false;
});

const memberCount = computed(() => {
  return props.conversation.participants?.length || 0;
});

const displayName = computed(() => {
  return props.conversation.name || props.conversation.title || props.conversation.peer?.name || 'Conversation';
});

const formatRole = (role) => {
  switch (role) {
    case 'super_admin': return 'Super Admin';
    case 'admin': return 'Parish Administrator';
    case 'parish_priest': return 'Parish Priest';
    case 'parochial_vicar': return 'Parochial Vicar';
    case 'parish_secretary': return 'Parish Secretary';
    case 'commission_admin': return 'Commission Admin';
    case 'commission_member': return 'Commission Member';
    case 'staff': return 'Parish Staff';
    case 'parishioner':
    case 'user': return 'Parishioner';
    default: return role ? role.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase()) : 'Parishioner';
  }
};

const peerRoleLabel = computed(() => {
  if (props.conversation.peer?.role_label) {
    return props.conversation.peer.role_label;
  }
  if (props.conversation.peer?.role) {
    return formatRole(props.conversation.peer.role);
  }
  if (props.conversation.is_direct && props.conversation.participants) {
    const peer = props.conversation.participants.find((p) => p.id !== props.currentUserId);
    if (peer?.role_label) return peer.role_label;
    if (peer?.role) return formatRole(peer.role);
  }
  return null;
});

const peerUser = computed(() => {
  if (props.conversation.peer) return props.conversation.peer;
  if (props.conversation.is_direct && props.conversation.participants) {
    return props.conversation.participants.find((p) => p.id !== props.currentUserId) || null;
  }
  return null;
});

const peerIsOnline = computed(() => {
  return peerUser.value?.is_online || false;
});

const peerPresenceLabel = computed(() => {
  return peerUser.value?.last_seen_label || 'Offline';
});

const initials = computed(() => {
  if (props.conversation.initials) return props.conversation.initials;
  if (peerUser.value?.initials) return peerUser.value.initials;
  const name = displayName.value;
  if (!name || name === 'Conversation') return '?';
  return name
    .split(' ')
    .map((w) => w[0])
    .filter(Boolean)
    .slice(0, 2)
    .join('')
    .toUpperCase();
});

const avatarBgClass = computed(() => {
  if (props.conversation.is_commission) return 'bg-amber-100 text-amber-900 border border-amber-300';
  if (props.conversation.is_ministry) return 'bg-purple-100 text-purple-900 border border-purple-300';
  return 'bg-[#062f78] text-white';
});
</script>
