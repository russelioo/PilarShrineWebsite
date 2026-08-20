<script setup>
import { ref } from 'vue'

defineProps({ active: { type: String, default: 'home' } })
const open = ref(false)

const links = [
  ['home', 'Home'], ['about', 'About Us'], ['sacraments', 'Sacraments'],
  ['schedule', 'Mass Schedule'], ['ministries', 'Ministries'], ['events', 'Events'],
  ['news', 'News'], ['novenas', 'Novenas'], ['store', 'Store'], ['contact', 'Contact'],
]
</script>

<template>
  <header class="site-header">
    <div class="top-strip">
      <span>☎ (02) 1234 5678</span><span>✉ info@olopshrine.org</span>
      <span>⌖ Banuyo, Pilar, Sorsogon</span>
      <span class="socials">f&nbsp;&nbsp;▶&nbsp;&nbsp;◎</span>
    </div>
    <nav class="navbar page-width">
      <a class="brand" href="#/home">
        <div class="brand-mark">♕</div>
        <div><strong>OUR LADY OF<br>THE PILLAR SHRINE</strong><small>A Parish of Faith, Love and Service</small></div>
      </a>
      <button class="menu-button" aria-label="Toggle menu" @click="open = !open">☰</button>
      <div class="nav-links" :class="{ open }">
        <a v-for="[key, label] in links" :key="key" :href="`#/${key}`" :class="{ active: active === key }" @click="open = false">{{ label }}</a>
        <a class="outline-action" href="#/novenas">Request prayer</a>
        <a class="solid-action" href="#/forms">Online forms</a>
      </div>
    </nav>
  </header>
</template>
