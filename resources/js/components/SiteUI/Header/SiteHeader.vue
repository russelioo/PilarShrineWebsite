<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import MainNavigation from './MainNavigation.vue'
import TopBar from './TopBar.vue'

defineProps({ active: { type: String, default: 'home' } })
const scrolled = ref(false)
const syncScroll = () => { scrolled.value = window.scrollY > 60 }
onMounted(() => { syncScroll(); window.addEventListener('scroll', syncScroll, { passive: true }) })
onUnmounted(() => window.removeEventListener('scroll', syncScroll))
</script>

<template><header class="site-header" :class="{ scrolled }"><TopBar /><MainNavigation :active="active" :scrolled="scrolled" /></header></template>

<style scoped>
.site-header{position:sticky;z-index:50;top:0;border-bottom:1px solid rgba(219,228,238,.8);background:#fff;box-shadow:var(--shadow-sm);transition:box-shadow .2s}.site-header.scrolled{box-shadow:0 10px 30px rgba(12,38,70,.13)}.site-header.scrolled :deep(.top-strip){height:0;opacity:0}
</style>
