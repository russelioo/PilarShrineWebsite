<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import MainNavigation from './MainNavigation.vue'
import TopBar from './TopBar.vue'

defineProps({ active: { type: String, default: 'home' } })
const scrolled = ref(false)
const syncScroll = () => { scrolled.value = window.scrollY > 30 }
onMounted(() => { syncScroll(); window.addEventListener('scroll', syncScroll, { passive: true }) })
onUnmounted(() => window.removeEventListener('scroll', syncScroll))
</script>

<template>
  <header class="site-header" :class="{ scrolled }">
    <TopBar />
    <MainNavigation :active="active" />
  </header>
</template>

<style scoped>
.site-header {
  --site-top-strip-height: 30px;
  --site-nav-height: 78px;
  position: sticky;
  z-index: 50;
  /* Let the contact strip scroll out without changing the document height. */
  top: calc(0px - var(--site-top-strip-height));
  border-bottom: 1px solid rgba(219, 228, 238, .8);
  background: #fff;
  box-shadow: var(--shadow-sm);
  transition: box-shadow .2s;
}

.site-header.scrolled {
  box-shadow: 0 10px 30px rgba(12, 38, 70, .13);
}

@media (max-width: 720px) {
  .site-header {
    --site-top-strip-height: 0px;
    --site-nav-height: 70px;
  }
}
</style>
