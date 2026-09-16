<template>
  <div class="border-t border-slate-200 bg-white px-6 py-3.5 flex-shrink-0 z-10">
    <!-- Selected Attachments Chips (if any selected) -->
    <div
      v-if="selectedFiles.length > 0"
      class="flex flex-wrap gap-2 mb-2 p-2 bg-slate-50 rounded-xl border border-slate-200/80"
    >
      <div
        v-for="(file, idx) in selectedFiles"
        :key="idx"
        class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs shadow-2xs font-medium text-slate-700"
      >
        <span v-if="isImage(file)" class="text-blue-600">🖼️</span>
        <span v-else class="text-amber-600">📎</span>
        <span class="truncate max-w-[150px]">{{ file.name }}</span>
        <span class="text-[10px] text-slate-400">({{ formatBytes(file.size) }})</span>
        <button
          type="button"
          @click="removeFile(idx)"
          class="ml-1 text-slate-400 hover:text-red-500 transition cursor-pointer"
          aria-label="Remove attachment"
        >
          ✕
        </button>
      </div>
    </div>

    <!-- Error message banner if quota or size exceeded -->
    <div
      v-if="errorMessage"
      class="mb-2 p-2 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700 flex items-center justify-between"
    >
      <span>{{ errorMessage }}</span>
      <button type="button" @click="errorMessage = ''" class="text-red-500 font-bold ml-2 cursor-pointer">✕</button>
    </div>

    <!-- Quick Emoji Bar (Toggled) -->
    <div
      v-if="showEmojiPicker"
      class="mb-2 p-2 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-2 overflow-x-auto text-xl shadow-2xs"
    >
      <button
        v-for="emoji in quickEmojis"
        :key="emoji"
        type="button"
        @click="insertEmoji(emoji)"
        class="p-1 hover:bg-white rounded-lg transition transform hover:scale-120 cursor-pointer"
      >
        {{ emoji }}
      </button>
    </div>

    <!-- Main Composer Input Bar: [ 📎 ]  [ Type a message...   🙂 ]  [ ➤ ] -->
    <div class="flex items-end gap-2.5">
      <!-- Hidden file input (supports both images and docs) -->
      <input
        ref="fileInput"
        type="file"
        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.txt,.doc,.docx,.xls,.xlsx,.csv"
        multiple
        class="hidden"
        @change="handleFileSelect"
      />

      <!-- 📎 Attachment Button -->
      <button
        type="button"
        @click="$refs.fileInput.click()"
        class="p-2.5 rounded-full text-slate-400 hover:text-[#062f78] hover:bg-slate-100 transition flex-shrink-0 cursor-pointer mb-0.5"
        title="Attach files or photos"
        :disabled="isSending"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
        </svg>
      </button>

      <!-- Rounded Text Input Container with embedded Emoji button -->
      <div
        class="flex-1 min-w-0 flex items-center gap-2 bg-slate-100 rounded-2xl px-4 py-1.5 border border-slate-200/80 focus-within:bg-white focus-within:border-slate-300 focus-within:ring-2 focus-within:ring-[#062f78]/10 transition"
      >
        <textarea
          ref="textareaRef"
          v-model="text"
          rows="1"
          maxlength="5000"
          placeholder="Type a message..."
          class="flex-1 bg-transparent border-0 focus:outline-hidden resize-none text-[15px] text-slate-800 placeholder:text-slate-400 max-h-32 py-1 leading-relaxed"
          @keydown="handleKeyDown"
          @input="adjustHeight"
          :disabled="isSending"
        ></textarea>

        <!-- 🙂 Emoji Button inside right edge of input -->
        <button
          type="button"
          @click="showEmojiPicker = !showEmojiPicker"
          class="p-1 rounded-full text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 transition flex-shrink-0 cursor-pointer"
          :class="{ 'text-[#062f78] bg-slate-200/70': showEmojiPicker }"
          title="Add emoji"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </button>
      </div>

      <!-- ➤ Send Button -->
      <button
        type="button"
        @click="submitMessage"
        :disabled="!canSend || isSending"
        class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 transition mb-0.5 shadow-xs"
        :class="canSend && !isSending
          ? 'bg-[#062f78] hover:bg-[#0d4399] text-white active:scale-95 cursor-pointer'
          : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
        aria-label="Send message"
      >
        <svg v-if="isSending" class="w-4 h-4 animate-spin text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else class="w-4 h-4 ml-0.5 text-white" fill="currentColor" viewBox="0 0 24 24">
          <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue';

const props = defineProps({
  dailyUsage: {
    type: Object,
    default: () => ({ images: 0, files: 0, max_images: 3, max_files: 3 }),
  },
  isSending: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['send']);

const text = ref('');
const textareaRef = ref(null);
const fileInput = ref(null);
const selectedFiles = ref([]);
const errorMessage = ref('');
const showEmojiPicker = ref(false);

const quickEmojis = ['🙏', '👍', '❤️', '😊', '⛪', '✝️', '🕊️', '👏', '🎉', '🤝'];

const canSend = computed(() => {
  return text.value.trim().length > 0 || selectedFiles.value.length > 0;
});

const isImage = (file) => {
  return file.type.startsWith('image/') || /\.(jpg|jpeg|png|gif|webp)$/i.test(file.name);
};

const formatBytes = (bytes) => {
  if (!bytes) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

const handleFileSelect = (event) => {
  errorMessage.value = '';
  const files = Array.from(event.target.files || []);

  for (const file of files) {
    if (file.size > 10 * 1024 * 1024) {
      errorMessage.value = `File "${file.name}" exceeds 10MB limit.`;
      continue;
    }
    const alreadySelected = selectedFiles.value.some((f) => f.name === file.name && f.size === file.size);
    if (!alreadySelected) {
      selectedFiles.value.push(file);
    }
  }

  // Clear input so same file can be selected again if removed
  event.target.value = '';
};

const removeFile = (idx) => {
  selectedFiles.value.splice(idx, 1);
};

const insertEmoji = (emoji) => {
  text.value += emoji;
  showEmojiPicker.value = false;
  adjustHeight();
  textareaRef.value?.focus();
};

const adjustHeight = () => {
  nextTick(() => {
    if (!textareaRef.value) return;
    textareaRef.value.style.height = 'auto';
    textareaRef.value.style.height = Math.min(textareaRef.value.scrollHeight, 120) + 'px';
  });
};

const handleKeyDown = (event) => {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault();
    submitMessage();
  }
};

const submitMessage = () => {
  if (!canSend.value || props.isSending) return;

  const payload = {
    body: text.value.trim(),
    files: [...selectedFiles.value],
  };

  // Clear immediately (optimistic UI)
  text.value = '';
  selectedFiles.value = [];
  errorMessage.value = '';
  showEmojiPicker.value = false;

  nextTick(() => {
    if (textareaRef.value) {
      textareaRef.value.style.height = 'auto';
    }
  });

  emit('send', payload);
};

defineExpose({
  focus: () => textareaRef.value?.focus(),
  clear: () => {
    text.value = '';
    selectedFiles.value = [];
    adjustHeight();
  },
});
</script>
