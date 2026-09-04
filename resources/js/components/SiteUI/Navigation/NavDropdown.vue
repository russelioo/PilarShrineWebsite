<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import NavItem from './NavItem.vue'
import { isNavigationItemActive } from './navigationItems'

const props = defineProps({
  items: { type: Array, required: true },
  activeRoute: { type: String, default: 'home' },
  open: { type: Boolean, default: false },
})

const emit = defineEmits(['update:open', 'navigate'])

const rootEl = ref(null)
const triggerBtn = ref(null)
const menuEl = ref(null)

// Separate isActive (route-based) from isOpen (interaction-based)
const isActive = computed(() =>
  props.items.some(item => isNavigationItemActive(item, props.activeRoute))
)

const isOpen = ref(false)

watch(() => props.open, (val) => {
  isOpen.value = val
})

let closeTimer = null

const setOpen = (val) => {
  if (closeTimer) {
    clearTimeout(closeTimer)
    closeTimer = null
  }
  isOpen.value = val
  emit('update:open', val)
}

const openMenu = () => {
  setOpen(true)
}

const closeMenu = (immediate = false) => {
  if (closeTimer) {
    clearTimeout(closeTimer)
    closeTimer = null
  }
  if (immediate) {
    setOpen(false)
  } else {
    closeTimer = setTimeout(() => {
      setOpen(false)
      closeTimer = null
    }, 100)
  }
}

// Mouse events on wrapper (covers both trigger and dropdown menu)
const onMouseEnter = () => {
  if (window.matchMedia('(hover: hover)').matches) {
    openMenu()
  }
}

const onMouseLeave = () => {
  if (window.matchMedia('(hover: hover)').matches) {
    closeMenu(false)
  }
}

// Click on trigger (supports touch and mouse toggle)
const onTriggerClick = () => {
  if (isOpen.value) {
    closeMenu(true)
  } else {
    openMenu()
  }
}

// Focus handling
const onFocusOut = (event) => {
  if (rootEl.value && !rootEl.value.contains(event.relatedTarget)) {
    closeMenu(true)
  }
}

// Keyboard handling
const onTriggerKeydown = (event) => {
  if (event.key === 'ArrowDown') {
    event.preventDefault()
    openMenu()
    nextTick(() => {
      const firstItem = menuEl.value?.querySelector('a')
      firstItem?.focus()
    })
  } else if (event.key === 'Escape') {
    event.preventDefault()
    closeMenu(true)
  }
}

const onMenuKeydown = (event) => {
  if (event.key === 'Escape') {
    event.preventDefault()
    closeMenu(true)
    triggerBtn.value?.focus()
    return
  }

  if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
    event.preventDefault()
    const links = Array.from(menuEl.value?.querySelectorAll('a') || [])
    if (!links.length) return
    const currentIndex = links.indexOf(document.activeElement)
    let nextIndex = currentIndex
    if (event.key === 'ArrowDown') {
      nextIndex = (currentIndex + 1) % links.length
    } else {
      nextIndex = (currentIndex - 1 + links.length) % links.length
    }
    links[nextIndex]?.focus()
  }
}

// Item clicked: close immediately and navigate
const handleItemClick = () => {
  closeMenu(true)
  emit('navigate')
}

onBeforeUnmount(() => {
  if (closeTimer) {
    clearTimeout(closeTimer)
    closeTimer = null
  }
})
</script>

<template>
  <div
    ref="rootEl"
    class="more-nav"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
    @focusout="onFocusOut"
  >
    <button
      ref="triggerBtn"
      class="nav-more"
      type="button"
      :class="{ active: isActive, 'is-open': isOpen }"
      aria-haspopup="menu"
      :aria-expanded="isOpen"
      aria-label="More navigation links"
      @click="onTriggerClick"
      @keydown="onTriggerKeydown"
    >
      <span>More</span>
      <span class="nav-arrow" aria-hidden="true">▾</span>
    </button>

    <div
      v-show="isOpen"
      ref="menuEl"
      class="more-menu"
      role="menu"
      aria-label="More pages"
      @keydown="onMenuKeydown"
    >
      <NavItem
        v-for="item in items"
        :key="item.key"
        :item="item"
        :active="isNavigationItemActive(item, activeRoute)"
        menuitem
        @navigate="handleItemClick"
      />
    </div>
  </div>
</template>

<style scoped>
.more-nav {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.nav-more {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 40px;
  min-height: 40px;
  max-height: 40px;
  padding: 0 14px;
  box-sizing: border-box;
  border: 1px solid transparent;
  border-radius: 9px;
  background: transparent;
  color: var(--color-primary);
  font-family: inherit;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.015em;
  text-transform: uppercase;
  white-space: nowrap;
  line-height: 1;
  cursor: pointer;
  user-select: none;
  transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
}

.nav-more:hover {
  background: #f3f6fa;
  color: var(--color-primary);
}

.nav-more:focus:not(:focus-visible) {
  outline: none;
  box-shadow: none;
}

.nav-more:focus-visible {
  outline: 2px solid var(--color-gold);
  outline-offset: 2px;
}

/* Active state for MORE trigger when route is inside dropdown */
.nav-more.active {
  background: var(--color-primary-light);
  color: #0752a4;
  border: 1px solid rgba(11, 59, 130, 0.12);
}

.nav-arrow {
  display: inline-block;
  margin-left: 5px;
  font-size: 9px;
  line-height: 1;
  transition: transform 0.2s ease;
}

.nav-more.is-open .nav-arrow {
  transform: rotate(180deg);
}

/* Dropdown Menu Container (Zero gap, anchored directly under MORE) */
.more-menu {
  position: absolute;
  z-index: 100;
  top: 100%;
  right: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
  width: 215px;
  padding: 6px;
  margin: 0;
  border: 1px solid #dce4ec;
  border-radius: 12px;
  background: #ffffff;
  box-shadow: 0 12px 32px rgba(12, 38, 70, 0.14);
}

/* Invisible hover bridge between trigger and menu */
.more-menu::before {
  content: '';
  position: absolute;
  top: -8px;
  left: 0;
  right: 0;
  height: 8px;
  background: transparent;
}

/* Dropdown Menu Items */
.more-menu :deep(a) {
  display: flex;
  align-items: center;
  width: 100%;
  height: 38px;
  padding: 0 14px;
  box-sizing: border-box;
  border: 1px solid transparent;
  border-radius: 8px;
  background: transparent;
  color: var(--color-primary);
  font-family: inherit;
  font-size: 11.5px;
  font-weight: 600;
  letter-spacing: 0.01em;
  text-decoration: none;
  white-space: nowrap;
  line-height: 1;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}

.more-menu :deep(a:hover) {
  background: #f3f6fa;
  color: var(--color-primary);
}

.more-menu :deep(a:focus:not(:focus-visible)) {
  outline: none;
  box-shadow: none;
}

.more-menu :deep(a:focus-visible) {
  outline: 2px solid var(--color-gold);
  outline-offset: -1px;
}

.more-menu :deep(a.active),
.more-menu :deep(a[aria-current="page"]) {
  background: var(--color-primary-light);
  color: #0752a4;
  font-weight: 700;
  border-color: rgba(11, 59, 130, 0.08);
}

/* Mobile adjustments */
@media (max-width: 1050px) {
  .more-nav {
    display: flex;
    width: 100%;
  }

  .nav-more {
    width: 100%;
    justify-content: space-between;
    height: auto;
    min-height: 44px;
    padding: 12px 14px;
  }

  .more-menu {
    position: static;
    width: 100%;
    margin: 4px 0;
    box-shadow: none;
    border-color: #e2e8f0;
  }

  .more-menu::before {
    display: none;
  }
}
</style>
