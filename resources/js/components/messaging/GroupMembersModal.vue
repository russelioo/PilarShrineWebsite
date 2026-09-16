<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
    role="dialog"
    aria-modal="true"
    aria-label="Conversation details"
    @click.self="$emit('close')"
    @keydown.esc="$emit('close')"
  >
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[85vh]">
      <!-- Header -->
      <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full bg-blue-50 text-[#062f78] flex items-center justify-center font-bold text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="text-base font-semibold text-slate-800">Conversation Details</h3>
        </div>
        <button
          type="button"
          @click="$emit('close')"
          class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200/50 transition"
          aria-label="Close"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-5 overflow-y-auto space-y-5">
        <!-- Overview Card -->
        <div class="flex items-center gap-3.5 p-3.5 bg-slate-50 rounded-xl border border-slate-100">
          <div class="w-12 h-12 rounded-full bg-[#062f78] text-white flex items-center justify-center font-bold text-lg shadow-xs overflow-hidden">
            <span v-if="conversation.is_commission">🏛️</span>
            <span v-else-if="conversation.is_ministry">👥</span>
            <img
              v-else-if="conversation.avatar"
              :src="conversation.avatar"
              :alt="conversation.name || conversation.title"
              class="w-full h-full object-cover rounded-full"
              referrerpolicy="no-referrer"
              @error="e => e.target.style.display = 'none'"
            />
            <span v-else>{{ conversation.initials || getInitials(conversation.name || conversation.title) }}</span>
          </div>
          <div class="min-w-0 flex-1">
            <h4 class="font-semibold text-slate-900 truncate">{{ conversation.name || conversation.title || 'Conversation' }}</h4>
            <p class="text-xs text-slate-500 flex items-center gap-1.5 mt-0.5">
              <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
              <span>{{ conversation.is_direct ? 'Direct Conversation' : `${participants.length} members` }}</span>
            </p>
          </div>
        </div>

        <!-- Commission Info if applicable -->
        <div v-if="conversation.commission_name" class="text-xs bg-amber-50/70 border border-amber-200/60 rounded-xl p-3 text-amber-900">
          <div class="font-semibold flex items-center gap-1 mb-1 text-amber-800">
            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span>Commission Group</span>
          </div>
          <p class="text-amber-800/90 leading-relaxed">
            Messages in this group are visible only to members and leaders of <strong>{{ conversation.commission_name }}</strong> and authorized parish administration.
          </p>
        </div>

        <!-- Participants List -->
        <div>
          <div class="flex items-center justify-between mb-2.5">
            <h5 class="text-xs font-semibold uppercase tracking-wider text-slate-500">
              Participants ({{ participants.length }})
            </h5>
          </div>
          <ul class="divide-y divide-slate-100 rounded-xl border border-slate-100 bg-white overflow-hidden max-h-60 overflow-y-auto">
            <li
              v-for="p in participants"
              :key="p.id"
              class="px-3.5 py-2.5 flex items-center justify-between hover:bg-slate-50/70 transition"
            >
              <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-xs font-semibold flex-shrink-0 overflow-hidden">
                  <img
                    v-if="p.avatar"
                    :src="p.avatar"
                    :alt="p.name"
                    class="w-full h-full object-cover rounded-full"
                    referrerpolicy="no-referrer"
                    @error="e => e.target.style.display = 'none'"
                  />
                  <span v-else>{{ getInitials(p.name) }}</span>
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-slate-800 truncate flex items-center gap-1.5">
                    <span>{{ p.name }}</span>
                    <span v-if="p.id === currentUserId" class="text-[10px] bg-blue-100 text-[#062f78] px-1.5 py-0.2 rounded font-semibold">You</span>
                  </p>
                  <p class="text-xs text-slate-400 truncate">{{ p.email }}</p>
                </div>
              </div>
              <span
                class="ml-2 px-2 py-0.5 rounded-md text-[11px] font-medium whitespace-nowrap"
                :class="getRoleClass(p.role)"
              >
                {{ p.role_label || p.role }}
              </span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/70 flex justify-end">
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-200 text-slate-700 hover:bg-slate-300 transition"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

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
});

defineEmits(['close']);

const participants = computed(() => {
  return props.conversation?.participants || [];
});

const getInitials = (name) => {
  if (!name) return '?';
  return name
    .split(' ')
    .map((w) => w[0])
    .filter(Boolean)
    .slice(0, 2)
    .join('')
    .toUpperCase();
};

const getRoleClass = (role) => {
  switch (role) {
    case 'super_admin':
    case 'admin':
      return 'bg-amber-100 text-amber-800';
    case 'parish_priest':
    case 'parochial_vicar':
      return 'bg-purple-100 text-purple-800';
    case 'commission_admin':
      return 'bg-blue-100 text-blue-800';
    case 'staff':
      return 'bg-emerald-100 text-emerald-800';
    default:
      return 'bg-slate-100 text-slate-700';
  }
};
</script>

