<script setup>
defineProps({ eyebrow: String, title: { type: String, required: true }, description: String, image: String, overlay: { type: Boolean, default: true } })
</script>
<template><section class="site-page-hero" :class="{ 'has-overlay': overlay }" :style="image ? { backgroundImage: `url(${image})` } : undefined"><div class="site-container"><span v-if="eyebrow">{{ eyebrow }}</span><h1>{{ title }}</h1><p v-if="description">{{ description }}</p><slot /></div></section></template>
