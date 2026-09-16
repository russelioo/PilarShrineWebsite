<template>
  <button
    type="button"
    @click="$emit('select')"
    :class="[
      'w-full text-left px-4 py-3 transition duration-150 relative flex items-center gap-3 select-none group cursor-pointer border-l-3',
      isActive ? 'bg-[#062f78]/8 border-l-[#062f78]' : 'hover:bg-slate-50 border-l-transparent'
    ]"
  >
    <!-- Avatar -->
    <div class="relative flex-shrink-0">
      <div
        class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-sm shadow-xs overflow-hidden"
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
      <!-- Unread indicator dot on avatar -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 w-3 h-3 rounded-full bg-[#d8aa3c] border-2 border-white ring-1 ring-amber-400"
      ></span>
      <!-- Online indicator dot (direct conversations only) -->
      <span
        v-else-if="conversation.is_direct && conversation.peer?.is_online"
        class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white"
        title="Online"
      ></span>
    </div>

    <!-- Content -->
    <div class="min-w-0 flex-1">
      <!-- Row 1: Name & Timestamp -->
      <div class="flex items-center justify-between gap-1">
        <h4
          class="text-sm truncate"
          :class="unreadCount > 0 ? 'font-bold text-slate-900' : 'font-semibold text-slate-800 group-hover:text-[#062f78]'"
        >
          {{ displayName }}
        </h4>
        <span
          class="text-[11px] flex-shrink-0 font-medium"
          :class="unreadCount > 0 ? 'text-[#062f78] font-bold' : 'text-slate-400'"
        >
          {{ formattedTime }}
        </span>
      </div>

      <!-- Row 2: Role / Account type underneath name -->
      <div class="text-[11px] font-medium truncate leading-tight mt-0.5" :class="roleColorClass">
        {{ roleText }}
      </div>

      <!-- Row 3: Latest message snippet & Unread Badge -->
      <div class="flex items-center justify-between gap-2 mt-0.5">
        <p
          class="text-xs truncate leading-tight"
          :class="unreadCount > 0 ? 'font-medium text-slate-900' : 'text-slate-500'"
        >
          <span v-if="isLastFromMe" class="text-slate-400 font-normal">You: </span>
          <span>{{ lastMessageSnippet }}</span>
        </p>

        <!-- Unread badge pill -->
        <span
          v-if="unreadCount > 0"
          class="flex-shrink-0 min-w-4.5 h-4.5 px-1.5 rounded-full bg-[#d8aa3c] text-slate-950 font-bold text-[10px] flex items-center justify-center shadow-xs"
        >
          {{ unreadCount }}
        </span>
      </div>
    </div>
  </button>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  conversation: {
    type: Object,
    required: true,
  },
  isActive: {
    type: Boolean,
    default: false,
  },
  currentUserId: {
    type: Number,
    required: true,
  },
});

defineEmits(['select']);

const imageError = ref(false);

const avatarUrl = computed(() => {
  return props.conversation.avatar || props.conversation.peer?.avatar || null;
});

watch(avatarUrl, () => {
  imageError.value = false;
});

const unreadCount = computed(() => {
  return props.conversation.unread_count || 0;
});

const isLastFromMe = computed(() => {
  return props.conversation.latest_message?.sender_id === props.currentUserId;
});

const lastMessageSnippet = computed(() => {
  const latest = props.conversation.latest_message;
  if (!latest) return 'No messages yet';
  if (latest.body) return latest.body;
  if (latest.attachments?.length) {
    const hasImg = latest.attachments.some((a) => a.kind === 'image');
    return hasImg ? '📷 Sent a photo' : '📎 Sent an attachment';
  }
  return 'Sent a message';
});

const formattedTime = computed(() => {
  const t = props.conversation.last_message_at || props.conversation.latest_message?.created_at;
  if (!t) return '';
  try {
    const d = new Date(t);
    const now = new Date();
    const isToday = d.toDateString() === now.toDateString();
    if (isToday) {
      return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
    }
    const yesterday = new Date();
    yesterday.setDate(now.getDate() - 1);
    if (d.toDateString() === yesterday.toDateString()) {
      return 'Yesterday';
    }
    return d.toLocaleDateString([], { month: 'short', day: 'numeric' });
  } catch {
    return '';
  }
});

const displayName = computed(() => {
  return props.conversation.name || props.conversation.title || props.conversation.peer?.name || 'Conversation';
});

const initials = computed(() => {
  if (props.conversation.initials) return props.conversation.initials;
  if (props.conversation.peer?.initials) return props.conversation.peer.initials;
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

const roleText = computed(() => {
  if (props.conversation.is_commission) return 'Commission';
  if (props.conversation.is_ministry) return 'Ministry';
  if (props.conversation.commission_name) return props.conversation.commission_name;
  if (props.conversation.peer?.role_label) return props.conversation.peer.role_label;
  if (props.conversation.peer?.role) {
    return formatRole(props.conversation.peer.role);
  }
  if (props.conversation.subtitle && !props.conversation.subtitle.includes('Direct Conversation')) {
    return props.conversation.subtitle;
  }
  return 'Parishioner';
});

const roleColorClass = computed(() => {
  if (props.conversation.is_commission) return 'text-amber-700';
  if (props.conversation.is_ministry) return 'text-purple-700';
  const role = props.conversation.peer?.role;
  if (['super_admin', 'admin'].includes(role)) return 'text-blue-700';
  if (['parish_priest', 'parochial_vicar'].includes(role)) return 'text-purple-700';
  return 'text-slate-500';
});

const avatarBgClass = computed(() => {
  if (props.conversation.is_commission) return 'bg-amber-100 text-amber-900 border border-amber-300';
  if (props.conversation.is_ministry) return 'bg-purple-100 text-purple-900 border border-purple-300';
  return 'bg-[#062f78] text-white';
});
</script>
