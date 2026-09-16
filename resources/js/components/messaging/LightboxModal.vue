<template>
  <div
    v-if="imageUrl"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 backdrop-blur-xs p-4"
    role="dialog"
    aria-modal="true"
    aria-label="Image preview"
    @click.self="$emit('close')"
    @keydown.esc="$emit('close')"
    tabindex="-1"
  >
    <div class="relative max-w-4xl max-h-[90vh] flex flex-col items-center">
      <!-- Top Action Bar -->
      <div class="w-full flex items-center justify-between pb-3 text-white text-sm">
        <span class="truncate font-medium max-w-xs sm:max-w-md">{{ imageName || 'Image preview' }}</span>
        <div class="flex items-center gap-2">
          <a
            :href="downloadUrl || imageUrl"
            target="_blank"
            download
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white transition text-xs font-medium"
            title="Download full image"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            <span>Download</span>
          </a>
          <button
            type="button"
            @click="$emit('close')"
            class="p-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-white transition"
            aria-label="Close image preview"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Image display -->
      <div class="relative overflow-hidden rounded-lg shadow-2xl bg-black/40 flex items-center justify-center">
        <img
          :src="imageUrl"
          :alt="imageName || 'Image preview'"
          class="max-h-[80vh] max-w-full object-contain rounded select-none"
          loading="lazy"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';

defineProps({
  imageUrl: {
    type: String,
    default: null,
  },
  imageName: {
    type: String,
    default: '',
  },
  downloadUrl: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['close']);

const handleKeyDown = (e) => {
  if (e.key === 'Escape') {
    emit('close');
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

