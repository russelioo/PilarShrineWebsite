<script setup>
defineProps({
  item: { type: Object, required: true },
  active: { type: Boolean, default: false },
  menuitem: { type: Boolean, default: false },
})
defineEmits(['navigate'])
</script>

<template>
  <a :href="item.href" :class="{ active }" :aria-current="active ? 'page' : undefined" :role="menuitem ? 'menuitem' : undefined" @click="$emit('navigate')">{{ item.label }}</a>
</template>
