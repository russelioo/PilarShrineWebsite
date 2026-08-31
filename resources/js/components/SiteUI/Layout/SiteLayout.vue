<script setup>
import SiteFooter from '../Footer/SiteFooter.vue'
import SiteHeader from '../Header/SiteHeader.vue'
defineProps({ active: { type: String, default: 'home' } })
</script>
<template><div class="site-layout"><SiteHeader :active="active" /><slot /><SiteFooter /></div></template>
