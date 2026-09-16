<template>
  <div
    class="flex flex-col mb-4.5 transition-all"
    :class="cluster.isMine ? 'items-end' : 'items-start'"
  >
    <!-- Sender Header (Shown once per incoming cluster) -->
    <div
      v-if="!cluster.isMine"
      class="flex items-center gap-2 mb-1.5 px-1 select-none"
    >
      <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold overflow-hidden flex-shrink-0 shadow-2xs">
        <img
          v-if="cluster.senderAvatar"
          :src="cluster.senderAvatar"
          :alt="cluster.senderName"
          class="w-full h-full object-cover rounded-full"
          referrerpolicy="no-referrer"
          @error="e => e.target.style.display = 'none'"
        />
        <span v-else>{{ cluster.senderInitials || getInitials(cluster.senderName) }}</span>
      </div>
      <span class="text-xs font-bold text-slate-800">{{ cluster.senderName }}</span>
      <span
        v-if="cluster.senderRole"
        class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-medium"
      >
        {{ cluster.senderRole }}
      </span>
    </div>

    <!-- Grouped Bubbles for this Sender -->
    <div
      class="flex flex-col space-y-1 w-full"
      :class="cluster.isMine ? 'items-end' : 'items-start'"
    >
      <div
        v-for="(msg, msgIdx) in cluster.messages"
        :key="msg.id || msg.temp_id || msgIdx"
        class="max-w-[75%] sm:max-w-[68%] md:max-w-[60%] px-4 py-2.5 shadow-2xs relative group transition-all"
        :class="[
          cluster.isMine
            ? 'bg-[#062f78] text-white'
            : 'bg-slate-100 text-slate-900 border border-slate-200/70',
          getBubbleCorners(msgIdx, cluster.messages.length, cluster.isMine)
        ]"
      >
        <!-- Message Text Body -->
        <div
          v-if="msg.body"
          class="text-[15px] leading-relaxed whitespace-pre-wrap break-words select-text font-normal"
          :class="cluster.isMine ? 'text-white' : 'text-slate-800'"
        >
          {{ msg.body }}
        </div>

        <!-- Attachments Container -->
        <div v-if="msg.attachments && msg.attachments.length > 0" class="mt-2 space-y-2">
          <!-- Image Attachments -->
          <div
            v-if="getImageAttachments(msg).length > 0"
            class="grid gap-1.5"
            :class="getImageAttachments(msg).length > 1 ? 'grid-cols-2' : 'grid-cols-1'"
          >
            <button
              v-for="img in getImageAttachments(msg)"
              :key="img.id || img.name"
              type="button"
              @click="$emit('preview-image', img)"
              class="relative overflow-hidden rounded-xl bg-black/10 aspect-video sm:aspect-4/3 w-full group/img cursor-pointer focus:outline-hidden"
            >
              <img
                :src="img.preview_url || img.url"
                :alt="img.name"
                class="w-full h-full object-cover rounded-xl transition duration-200 group-hover/img:scale-105"
                loading="lazy"
              />
              <div class="absolute inset-0 bg-black/0 group-hover/img:bg-black/25 transition flex items-center justify-center">
                <span class="opacity-0 group-hover/img:opacity-100 bg-black/60 text-white rounded-full p-1.5 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/>
                  </svg>
                </span>
              </div>
            </button>
          </div>

          <!-- Document / File Attachments -->
          <div v-if="getFileAttachments(msg).length > 0" class="space-y-1.5">
            <a
              v-for="file in getFileAttachments(msg)"
              :key="file.id || file.name"
              :href="file.download_url || file.url"
              target="_blank"
              download
              class="flex items-center gap-3 p-2.5 rounded-xl transition border text-xs"
              :class="cluster.isMine
                ? 'bg-white/10 hover:bg-white/15 border-white/20 text-white'
                : 'bg-white hover:bg-slate-50 border-slate-200 text-slate-800 shadow-2xs'"
            >
              <div
                class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 font-bold"
                :class="cluster.isMine ? 'bg-white/20 text-white' : 'bg-blue-50 text-[#062f78]'"
              >
                <svg v-if="isPdf(file.name)" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-9.5 8.5c0 .83-.67 1.5-1.5 1.5H7v2H5.5V9H8c.83 0 1.5.67 1.5 1.5v1zm5 2c0 .83-.67 1.5-1.5 1.5h-2.5V9H13c.83 0 1.5.67 1.5 1.5v3zm4-3H17v1h1.5V13H17v1.5h-1.5V9h3v1.5zM7 10.5h1v1H7v-1zm5.5 1.5h1v1h-1v-1z"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="font-medium truncate">{{ file.name }}</p>
                <p class="text-[11px] opacity-75">{{ file.size_formatted || formatBytes(file.size) }}</p>
              </div>
              <div class="opacity-80 hover:opacity-100 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
              </div>
            </a>
          </div>
        </div>

        <!-- Failure state & retry inside bubble -->
        <div v-if="msg.status === 'failed'" class="mt-1 flex items-center justify-end">
          <button
            type="button"
            @click="$emit('retry-message', msg)"
            class="text-[11px] text-red-200 hover:text-white underline cursor-pointer"
          >
            Failed · Click to retry
          </button>
        </div>
      </div>
    </div>

    <!-- Cluster Timestamp & Delivery Status (Displayed once at bottom of cluster) -->
    <div
      class="flex items-center gap-1.5 mt-1 text-[12px] text-slate-400 select-none px-1"
      :class="cluster.isMine ? 'justify-end' : 'justify-start'"
    >
      <span>{{ lastMessageTime }}</span>

      <!-- Status indicator for outgoing messages -->
      <template v-if="cluster.isMine">
        <!-- Sending spinner -->
        <span v-if="latestMessage.status === 'sending'" class="inline-flex items-center gap-1 text-slate-400">
          <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </span>

        <!-- Read (Double tick gold/blue) -->
        <span
          v-else-if="latestMessage.status === 'read' || latestMessage.read || latestMessage.read_at"
          class="inline-flex items-center text-[#d8aa3c]"
          title="Read"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <svg class="w-3.5 h-3.5 -ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
        </span>

        <!-- Sent (Single tick) -->
        <span v-else class="inline-flex items-center text-slate-400" title="Sent">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
        </span>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  cluster: {
    type: Object,
    required: true,
  },
  currentUserId: {
    type: Number,
    required: true,
  },
});

defineEmits(['preview-image', 'retry-message']);

const latestMessage = computed(() => {
  const msgs = props.cluster.messages;
  return msgs[msgs.length - 1] || {};
});

const lastMessageTime = computed(() => {
  const msg = latestMessage.value;
  if (!msg.created_at) return msg.time_formatted || msg.time || '';
  try {
    const d = new Date(msg.created_at);
    return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true });
  } catch {
    return msg.time_formatted || msg.time || '';
  }
});

const getBubbleCorners = (idx, total, isMine) => {
  if (total === 1) {
    return isMine ? 'rounded-2xl rounded-br-xs' : 'rounded-2xl rounded-bl-xs';
  }
  if (isMine) {
    if (idx === 0) return 'rounded-2xl rounded-br-sm';
    if (idx === total - 1) return 'rounded-2xl rounded-tr-sm rounded-br-xs';
    return 'rounded-2xl rounded-r-sm';
  } else {
    if (idx === 0) return 'rounded-2xl rounded-bl-sm';
    if (idx === total - 1) return 'rounded-2xl rounded-tl-sm rounded-bl-xs';
    return 'rounded-2xl rounded-l-sm';
  }
};

const getImageAttachments = (msg) => {
  return (msg.attachments || []).filter((a) => a.kind === 'image');
};

const getFileAttachments = (msg) => {
  return (msg.attachments || []).filter((a) => a.kind !== 'image');
};

const isPdf = (name) => {
  return name && name.toLowerCase().endsWith('.pdf');
};

const formatBytes = (bytes) => {
  if (!bytes || bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const getInitials = (name) => {
  if (!name) return 'U';
  return name.split(' ').map((w) => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase();
};
</script>
