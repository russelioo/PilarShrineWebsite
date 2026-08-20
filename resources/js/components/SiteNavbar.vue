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
      <span>☎ 0946-869-1254</span><span>✉ olppspilarsorsogon@gmail.com</span>
      <span>⌖ Binanuahan, Pilar, Sorsogon</span>
      <span class="socials">f&nbsp;&nbsp;▶&nbsp;&nbsp;◎</span>
    </div>
    <nav class="navbar page-width">
      <a class="brand" href="#/home">
        <div class="brand-mark">♕</div>
        <div><strong>PILAR SHRINE<br></strong>SORSOGON</div>
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
