<script setup>
import { computed } from 'vue'
import NavItem from './NavItem.vue'
import { isNavigationItemActive } from './navigationItems'

const props = defineProps({
  items: { type: Array, required: true },
  activeRoute: { type: String, default: 'home' },
  open: { type: Boolean, default: false },
})
const emit = defineEmits(['update:open', 'navigate'])
const active = computed(() => props.items.some(item => isNavigationItemActive(item, props.activeRoute)))
const close = () => emit('update:open', false)
const openOnHover = () => {
  if (window.matchMedia('(hover: hover)').matches) emit('update:open', true)
}
const closeOnHover = () => {
  if (window.matchMedia('(hover: hover)').matches) close()
}
const handleFocusOut = event => {
  if (!event.currentTarget.contains(event.relatedTarget)) close()
}
</script>

<template>
  <div class="more-nav" @mouseenter="openOnHover" @mouseleave="closeOnHover" @focusout="handleFocusOut">
    <button class="nav-more" type="button" :class="{ active }" aria-haspopup="menu" :aria-expanded="open" @click="emit('update:open', !open)" @keydown.esc="close">
      More <span aria-hidden="true">▾</span>
    </button>
    <div v-show="open" class="more-menu" role="menu" aria-label="More pages" @keydown.esc="close">
      <NavItem v-for="item in items" :key="item.key" :item="item" :active="isNavigationItemActive(item, activeRoute)" menuitem @navigate="close(); emit('navigate')" />
    </div>
  </div>
</template>
