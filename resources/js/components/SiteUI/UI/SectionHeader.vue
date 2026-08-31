<script setup>defineProps({ eyebrow: String, title: String, subtitle: String })</script>
<template><div class="section-title"><span v-if="eyebrow">{{ eyebrow }}</span><h1>{{ title }}</h1><div class="gold-rule" aria-hidden="true">✣</div><p v-if="subtitle">{{ subtitle }}</p></div></template>
