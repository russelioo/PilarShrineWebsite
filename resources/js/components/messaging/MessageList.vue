<template>
  <div
    ref="scrollContainer"
    class="flex-1 overflow-y-auto px-6 sm:px-8 py-6 bg-slate-50/50"
    @scroll="handleScroll"
  >
    <!-- Loading older indicator -->
    <div v-if="isLoadingMore" class="py-2 text-center text-xs text-slate-400">
      <span class="inline-block w-4 h-4 border-2 border-[#062f78] border-t-transparent rounded-full animate-spin"></span>
      <span class="ml-2">Loading previous messages...</span>
    </div>

    <!-- Empty messages state -->
    <div
      v-if="messages.length === 0"
      class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-400 space-y-3"
    >
      <div class="w-14 h-14 rounded-2xl bg-blue-50 text-[#062f78] flex items-center justify-center text-2xl shadow-2xs border border-[#062f78]/10">
        💬
      </div>
      <div>
        <h4 class="text-sm font-semibold text-slate-700">No messages yet</h4>
        <p class="text-xs text-slate-500 max-w-xs mt-1">
          Type a message below to start communicating with this contact.
        </p>
      </div>
    </div>

    <!-- Grouped messages by date and sender clusters -->
    <div v-else class="flex flex-col justify-start">
      <div v-for="dateGroup in groupedDateClusters" :key="dateGroup.dateKey">
        <!-- Date Divider -->
        <div class="flex items-center justify-center my-5 select-none">
          <span class="bg-white border border-slate-200 text-slate-400 text-[11px] font-bold uppercase tracking-wider px-3.5 py-1 rounded-full shadow-2xs">
            {{ formatDividerDate(dateGroup.dateKey) }}
          </span>
        </div>

        <!-- Clusters in this date -->
        <div class="space-y-4">
          <MessageBubble
            v-for="cluster in dateGroup.clusters"
            :key="cluster.id"
            :cluster="cluster"
            :current-user-id="currentUserId"
            @preview-image="$emit('preview-image', $event)"
            @retry-message="$emit('retry-message', $event)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue';
import MessageBubble from './MessageBubble.vue';

const props = defineProps({
  messages: {
    type: Array,
    default: () => [],
  },
  currentUserId: {
    type: Number,
    required: true,
  },
  isGroup: {
    type: Boolean,
    default: false,
  },
  isLoadingMore: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['preview-image', 'retry-message', 'load-more']);

const scrollContainer = ref(null);
const isScrolledToBottom = ref(true);

// Group messages by Date, then into consecutive Sender Clusters
const groupedDateClusters = computed(() => {
  const dates = {};

  for (const msg of props.messages) {
    let dateKey = 'Today';
    if (msg.created_at) {
      try {
        const d = new Date(msg.created_at);
        dateKey = d.toISOString().split('T')[0];
      } catch {
        dateKey = 'Today';
      }
    }
    if (!dates[dateKey]) {
      dates[dateKey] = [];
    }
    dates[dateKey].push(msg);
  }

  const result = [];
  for (const [dateKey, msgs] of Object.entries(dates)) {
    const clusters = [];
    let currentCluster = null;

    for (const msg of msgs) {
      const isMine = (msg.sender_id === props.currentUserId || msg.is_mine);
      const senderId = msg.sender_id;

      if (!currentCluster || currentCluster.senderId !== senderId) {
        currentCluster = {
          id: msg.id || msg.temp_id || `cluster-${senderId}-${Math.random()}`,
          senderId,
          isMine,
          senderName: msg.sender?.name || msg.sender_name || 'Parishioner',
          senderRole: msg.sender?.role_label || msg.sender_role || null,
          senderAvatar: msg.sender?.avatar || msg.sender_avatar || null,
          senderInitials: msg.sender?.initials || null,
          messages: [msg],
        };
        clusters.push(currentCluster);
      } else {
        currentCluster.messages.push(msg);
      }
    }

    result.push({
      dateKey,
      clusters,
    });
  }

  return result;
});

// Format date divider: "TODAY", "YESTERDAY", or "SEPTEMBER 15, 2026"
const formatDividerDate = (dateStr) => {
  if (dateStr === 'Today') return 'TODAY';
  try {
    const d = new Date(dateStr + 'T00:00:00');
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    if (d.toDateString() === today.toDateString()) {
      return 'TODAY';
    }
    if (d.toDateString() === yesterday.toDateString()) {
      return 'YESTERDAY';
    }
    return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }).toUpperCase();
  } catch {
    return dateStr.toUpperCase();
  }
};

const scrollToBottom = (behavior = 'smooth') => {
  nextTick(() => {
    if (scrollContainer.value) {
      scrollContainer.value.scrollTo({
        top: scrollContainer.value.scrollHeight,
        behavior,
      });
      isScrolledToBottom.value = true;
    }
  });
};

const handleScroll = () => {
  if (!scrollContainer.value) return;
  const { scrollTop, scrollHeight, clientHeight } = scrollContainer.value;
  isScrolledToBottom.value = scrollHeight - scrollTop - clientHeight < 50;

  if (scrollTop === 0 && !props.isLoadingMore) {
    emit('load-more');
  }
};

watch(
  () => props.messages.length,
  () => {
    if (isScrolledToBottom.value) {
      scrollToBottom();
    }
  }
);

onMounted(() => {
  scrollToBottom('auto');
});

defineExpose({
  scrollToBottom,
});
</script>
