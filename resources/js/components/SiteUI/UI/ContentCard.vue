<script setup>
defineProps({
  tag: {
    type: String,
    default: 'article',
  },
})
</script>

<template>
  <component :is="tag" class="content-card">
    <slot />
  </component>
</template>
