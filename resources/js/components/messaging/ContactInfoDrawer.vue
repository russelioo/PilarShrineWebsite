<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex justify-end bg-black/40 backdrop-blur-xs transition-opacity"
    role="dialog"
    aria-modal="true"
    aria-label="Conversation details"
    @click.self="$emit('close')"
    @keydown.esc="$emit('close')"
  >
    <!-- Drawer Panel -->
    <div
      class="bg-white w-full max-w-sm sm:max-w-md h-full shadow-2xl flex flex-col border-l border-slate-200 animate-in slide-in-from-right duration-200"
    >
      <!-- Drawer Header -->
      <div class="h-[68px] px-6 border-b border-slate-200 flex items-center justify-between flex-shrink-0 bg-white">
        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800">
          {{ conversation.is_commission ? 'Commission Channel' : (conversation.is_ministry ? 'Ministry Channel' : 'Contact Information') }}
        </h3>
        <button
          type="button"
          @click="$emit('close')"
          class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition cursor-pointer"
          aria-label="Close drawer"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Drawer Body -->
      <div class="flex-1 overflow-y-auto p-6 space-y-6">
        <!-- Hero Contact Card -->
        <div class="flex flex-col items-center text-center p-5 rounded-2xl bg-slate-50 border border-slate-200/80">
          <div class="w-20 h-20 rounded-full flex items-center justify-center font-bold text-2xl shadow-sm overflow-hidden mb-3 relative" :class="avatarBgClass">
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

          <h4 class="text-base font-bold text-slate-900 leading-tight">
            {{ displayName }}
          </h4>
          <p class="text-xs font-semibold text-[#062f78] mt-1">
            {{ roleLabel }}
          </p>

          <!-- Status badge -->
          <div
            class="mt-2.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-medium border"
            :class="presenceIsOnline
              ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
              : 'bg-slate-50 text-slate-500 border-slate-200'"
          >
            <span
              class="w-1.5 h-1.5 rounded-full"
              :class="presenceIsOnline ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400'"
            ></span>
            <span>{{ presenceLabel }}</span>
          </div>

          <!-- Quick action button: View Profile in Admin if applicable -->
          <div v-if="profileUrl" class="mt-4">
            <a
              :href="profileUrl"
              target="_blank"
              class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-[#062f78] hover:border-slate-300 shadow-2xs transition"
            >
              <span>View Profile</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Information Details List -->
        <div class="space-y-4">
          <h5 class="text-xs font-bold uppercase tracking-wider text-slate-400">
            Details
          </h5>

          <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white overflow-hidden text-xs">
            <!-- Account Type -->
            <div class="px-4 py-3 flex items-center justify-between">
              <span class="text-slate-500 font-medium">Account Type</span>
              <span class="font-semibold text-slate-800">{{ accountType }}</span>
            </div>

            <!-- Email (if direct conversation peer) -->
            <div v-if="peerEmail" class="px-4 py-3 flex items-center justify-between">
              <span class="text-slate-500 font-medium">Email</span>
              <span class="font-medium text-slate-800 truncate max-w-[200px]" :title="peerEmail">{{ peerEmail }}</span>
            </div>

            <!-- Conversation Started -->
            <div class="px-4 py-3 flex items-center justify-between">
              <span class="text-slate-500 font-medium">Conversation started</span>
              <span class="font-semibold text-slate-800">{{ startedDate }}</span>
            </div>

            <!-- Conversation Type -->
            <div class="px-4 py-3 flex items-center justify-between">
              <span class="text-slate-500 font-medium">Channel Type</span>
              <span class="font-semibold text-slate-800">
                {{ conversation.is_commission ? 'Commission Channel' : (conversation.is_ministry ? 'Ministry Channel' : 'Direct Message') }}
              </span>
            </div>

            <!-- Messages Count -->
            <div v-if="messagesCount !== undefined" class="px-4 py-3 flex items-center justify-between">
              <span class="text-slate-500 font-medium">Messages in thread</span>
              <span class="font-semibold text-slate-800">{{ messagesCount }} messages</span>
            </div>
          </div>
        </div>

        <!-- Commission Group Members List if applicable -->
        <div v-if="conversation.participants && conversation.participants.length > 0 && !conversation.is_direct" class="space-y-3">
          <div class="flex items-center justify-between">
            <h5 class="text-xs font-bold uppercase tracking-wider text-slate-400">
              Members ({{ conversation.participants.length }})
            </h5>
          </div>

          <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white overflow-hidden max-h-56 overflow-y-auto text-xs">
            <li
              v-for="p in conversation.participants"
              :key="p.id"
              class="px-3.5 py-2.5 flex items-center justify-between hover:bg-slate-50 transition"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-7 h-7 rounded-full bg-[#062f78] text-white flex items-center justify-center font-bold text-[10px] flex-shrink-0 overflow-hidden">
                  <img
                    v-if="p.avatar"
                    :src="p.avatar"
                    :alt="p.name"
                    class="w-full h-full object-cover rounded-full"
                    referrerpolicy="no-referrer"
                    @error="e => e.target.style.display = 'none'"
                  />
                  <span v-else>{{ p.initials || getInitials(p.name) }}</span>
                </div>
                <div class="min-w-0">
                  <p class="font-semibold text-slate-800 truncate">
                    {{ p.name }}
                    <span v-if="p.id === currentUserId" class="text-[10px] text-blue-700 font-bold ml-1">(You)</span>
                  </p>
                  <p class="text-[10px] text-slate-400 truncate">{{ p.role_label || p.role }}</p>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <!-- Actions -->
        <div class="space-y-2 pt-2 border-t border-slate-100">
          <button
            type="button"
            @click="$emit('toggle-archive')"
            class="w-full py-2.5 px-4 rounded-xl border border-slate-200 text-xs font-semibold flex items-center justify-center gap-2 text-slate-700 hover:bg-slate-50 transition cursor-pointer"
          >
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            <span>{{ conversation.is_archived ? 'Unarchive Conversation' : 'Archive Conversation' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
  conversation: {
    type: Object,
    required: true,
  },
  currentUserId: {
    type: Number,
    required: true,
  },
  messagesCount: {
    type: Number,
    default: 0,
  },
});

defineEmits(['close', 'toggle-archive']);

const imageError = ref(false);

const avatarUrl = computed(() => {
  return props.conversation.avatar || props.conversation.peer?.avatar || null;
});

watch(avatarUrl, () => {
  imageError.value = false;
});

const displayName = computed(() => {
  return props.conversation.name || props.conversation.title || props.conversation.peer?.name || 'Conversation';
});

const initials = computed(() => {
  if (props.conversation.initials) return props.conversation.initials;
  if (props.conversation.peer?.initials) return props.conversation.peer.initials;
  const name = displayName.value;
  return name.split(' ').map((w) => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || 'P';
});

const roleLabel = computed(() => {
  if (props.conversation.is_commission) return props.conversation.subtitle || 'Commission Channel';
  if (props.conversation.is_ministry) return props.conversation.subtitle || 'Ministry Channel';
  return props.conversation.peer?.role_label || props.conversation.subtitle || 'Parishioner';
});

const peerUser = computed(() => {
  if (props.conversation.peer) return props.conversation.peer;
  if (props.conversation.is_direct && props.conversation.participants) {
    return props.conversation.participants.find((p) => p.id !== props.currentUserId) || null;
  }
  return null;
});

const presenceIsOnline = computed(() => {
  return peerUser.value?.is_online || false;
});

const presenceLabel = computed(() => {
  return peerUser.value?.last_seen_label || 'Offline';
});

const accountType = computed(() => {
  if (props.conversation.is_commission) return 'Commission';
  if (props.conversation.is_ministry) return 'Ministry';
  const role = props.conversation.peer?.role;
  if (['super_admin', 'admin'].includes(role)) return 'Administrator';
  if (['parish_priest', 'parochial_vicar'].includes(role)) return 'Clergy';
  if (['staff', 'parish_secretary'].includes(role)) return 'Staff';
  return 'Parishioner';
});

const peerEmail = computed(() => {
  if (props.conversation.peer?.email) return props.conversation.peer.email;
  const p = props.conversation.participants?.find((x) => x.id !== props.currentUserId);
  return p?.email || null;
});

const startedDate = computed(() => {
  const d = props.conversation.created_at || props.conversation.last_message_at;
  if (!d) return 'Recent';
  try {
    const dt = new Date(d);
    return dt.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
  } catch {
    return 'Recent';
  }
});

const profileUrl = computed(() => {
  const peer = props.conversation.peer || props.conversation.participants?.find((x) => x.id !== props.currentUserId);
  if (!peer) return null;
  const role = peer.role;
  if (['staff', 'commission_admin', 'commission_member', 'parish_secretary'].includes(role)) {
    return `/admin/staff?search=${encodeURIComponent(peer.name || '')}`;
  }
  return `/admin/parishioners?search=${encodeURIComponent(peer.name || '')}`;
});

const avatarBgClass = computed(() => {
  if (props.conversation.is_commission) return 'bg-amber-100 text-amber-900 border border-amber-300';
  if (props.conversation.is_ministry) return 'bg-purple-100 text-purple-900 border border-purple-300';
  return 'bg-[#062f78] text-white';
});

const getInitials = (name) => {
  if (!name) return 'U';
  return name.split(' ').map((w) => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase();
};
</script>

