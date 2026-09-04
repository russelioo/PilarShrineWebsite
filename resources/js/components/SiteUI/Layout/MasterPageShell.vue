<script setup>
import { computed } from 'vue'
import SiteLayout from './SiteLayout.vue'

const props = defineProps({
  active: { type: String, default: 'home' },
  livestream: { type: Object, default: () => ({ is_live: false, title: null, url: 'https://www.facebook.com/PilarShrineSorsogon' }) },
  heroType: { type: String, default: 'image' }, // 'image', 'video', 'none'
  image: { type: String, default: '' },
  videoSrc: { type: String, default: '' },
  videoPoster: { type: String, default: '' },
  eyebrow: { type: String, default: '' },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  hasRule: { type: Boolean, default: true },
  heroActions: { type: Array, default: () => [] },
  contentClass: { type: String, default: '' },
})

const heroStyle = computed(() => {
  if (props.heroType === 'image' && props.image) {
    return { backgroundImage: `url(${props.image})` }
  }
  return {}
})
</script>

<template>
  <SiteLayout :active="active">
    <!-- Optional Livestream Notification -->
    <a
      v-if="livestream && livestream.is_live"
      class="livestream-alert"
      :href="livestream.url"
      target="_blank"
      rel="noopener noreferrer"
      aria-live="polite"
    >
      <span class="livestream-dot" aria-hidden="true"></span>
      <strong>LIVE NOW</strong>
      <span>{{ livestream.title || 'Watch Pilar Shrine on Facebook' }}</span>
      <b aria-hidden="true">Watch now &rarr;</b>
    </a>

    <!-- Canonical Master Hero -->
    <header
      v-if="heroType !== 'none'"
      class="master-hero"
      :class="[
        `hero-${heroType}`,
        { 'has-video': heroType === 'video' }
      ]"
      :style="heroStyle"
    >
      <video
        v-if="heroType === 'video' && videoSrc"
        class="master-hero-video"
        autoplay
        muted
        loop
        playsinline
        preload="metadata"
        :poster="videoPoster"
        aria-hidden="true"
      >
        <source :src="videoSrc" type="video/mp4" />
      </video>

      <div class="page-width master-hero-copy">
        <span v-if="eyebrow" class="master-hero-eyebrow">{{ eyebrow }}</span>
        <h1 v-if="title" class="master-hero-title" v-html="title"></h1>
        <div v-if="hasRule" class="gold-rule left">✣</div>
        <p v-if="description" class="master-hero-desc" v-html="description"></p>
        
        <div v-if="$slots['hero-actions'] || (heroActions && heroActions.length)" class="master-hero-actions">
          <slot name="hero-actions">
            <a
              v-for="action in heroActions"
              :key="action.label"
              :class="['button', action.variant || 'primary']"
              :href="action.href"
            >
              {{ action.label }}
            </a>
          </slot>
        </div>
      </div>
    </header>

    <!-- Master Page Content Area -->
    <main class="master-page-body" :class="contentClass">
      <slot />
    </main>
  </SiteLayout>
</template>

