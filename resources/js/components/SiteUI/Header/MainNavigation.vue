<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import NavDropdown from '../Navigation/NavDropdown.vue'
import NavItem from '../Navigation/NavItem.vue'
import { accountNavigation, isNavigationItemActive, moreNavigation, primaryNavigation } from '../Navigation/navigationItems'
import SiteButton from '../UI/SiteButton.vue'
import MobileNavigation from './MobileNavigation.vue'

const props = defineProps({
  active: { type: String, default: 'home' },
  scrolled: { type: Boolean, default: false },
})

const open = ref(false)
const moreOpen = ref(false)
const root = ref(null)

const closeNavigation = () => {
  open.value = false
  moreOpen.value = false
}

const handleOutside = event => {
  if (root.value && !root.value.contains(event.target)) {
    closeNavigation()
  }
}

const handleEscape = event => {
  if (event.key === 'Escape') {
    closeNavigation()
  }
}

watch(open, value => {
  if (window.innerWidth <= 1050) {
    document.body.style.overflow = value ? 'hidden' : ''
  }
})

onMounted(() => {
  document.addEventListener('pointerdown', handleOutside)
  document.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
  document.removeEventListener('pointerdown', handleOutside)
  document.removeEventListener('keydown', handleEscape)
  document.body.style.overflow = ''
})
</script>

<template>
  <nav ref="root" class="navbar site-container" :class="{ scrolled }" aria-label="Main navigation">
    <a class="brand" href="#/home" @click="closeNavigation">
      <img class="brand-logo" :src="'/images/pilar-shrine-logo.png'" alt="Our Lady of the Pillar Parish logo">
      <div><strong>PILAR SHRINE</strong><span>SORSOGON</span></div>
    </a>

    <MobileNavigation :open="open" @toggle="open = !open; moreOpen = false" />

    <div id="site-navigation" class="nav-links" :class="{ open, scrolled }">
      <NavItem
        v-for="item in primaryNavigation"
        :key="item.key"
        :item="item"
        :active="isNavigationItemActive(item, props.active)"
        @navigate="closeNavigation"
      />

      <NavDropdown
        v-model:open="moreOpen"
        :items="moreNavigation"
        :active-route="props.active"
        @navigate="closeNavigation"
      />

      <div class="auth-actions" aria-label="Parish account">
        <SiteButton
          v-for="item in accountNavigation"
          :key="item.key"
          :href="item.href"
          :variant="item.variant"
          :active="isNavigationItemActive(item, props.active)"
          @click="closeNavigation"
        >
          {{ item.label }}
        </SiteButton>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.navbar {
  height: 76px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: height 0.25s ease;
  box-sizing: border-box;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  color: var(--color-primary);
  line-height: 1.05;
  text-decoration: none;
  flex-shrink: 0;
}

.brand-logo {
  width: 43px;
  height: 54px;
  object-fit: contain;
  transition: width 0.25s, height 0.25s;
}

.brand strong,
.brand span {
  display: block;
}

.brand strong {
  font-size: 15px;
}

.brand span {
  font-size: 15px;
  font-weight: 400;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 4px;
}

/* ==========================================================================
   Stable Base Footprint for Top-Level Navigation Links (Home, About, Schedule, Sacraments)
   ========================================================================== */
.nav-links :deep(> a) {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  height: 40px !important;
  min-height: 40px !important;
  max-height: 40px !important;
  padding: 0 14px !important;
  box-sizing: border-box !important;
  border: 1px solid transparent !important;
  border-radius: 9px !important;
  background: transparent;
  color: var(--color-primary) !important;
  font-family: inherit !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  letter-spacing: 0.015em !important;
  text-transform: uppercase !important;
  white-space: nowrap !important;
  line-height: 1 !important;
  text-decoration: none !important;
  cursor: pointer;
  user-select: none;
  transition: background 0.18s ease, color 0.18s ease, border-color 0.18s ease;
}

/* Hover state */
.nav-links :deep(> a:hover) {
  background: #f3f6fa !important;
  color: var(--color-primary) !important;
}

/* Focus and Focus-Visible: Never show outline on mouse click, only on keyboard tab */
.nav-links :deep(> a:focus:not(:focus-visible)) {
  outline: none !important;
  box-shadow: none !important;
}

.nav-links :deep(> a:focus-visible) {
  outline: 2px solid var(--color-gold) !important;
  outline-offset: 2px !important;
}

/* Active navigation state - identical dimensions, zero layout shift */
.nav-links :deep(> a.active) {
  background: var(--color-primary-light) !important;
  color: #0752a4 !important;
  border: 1px solid rgba(11, 59, 130, 0.12) !important;
}

/* ==========================================================================
   Auth Actions
   ========================================================================== */
.auth-actions {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-left: 12px;
}

/* ==========================================================================
   Scrolled Header State
   ========================================================================== */
.navbar.scrolled {
  height: 66px;
}

.navbar.scrolled .brand-logo {
  width: 37px;
  height: 47px;
}

/* ==========================================================================
   Mobile Responsive Styles
   ========================================================================== */
@media (max-width: 1050px) {
  .nav-links {
    position: absolute;
    z-index: 55;
    top: 76px;
    right: 0;
    left: 0;
    display: none;
    max-height: calc(100vh - 76px);
    align-items: stretch;
    gap: 8px;
    padding: 18px 24px 24px;
    overflow-y: auto;
    border-top: 1px solid #e5ebf2;
    background: #ffffff;
    box-shadow: var(--shadow-md);
    flex-direction: column;
  }

  .nav-links.open {
    display: flex;
  }

  .nav-links.scrolled {
    top: 66px;
    max-height: calc(100vh - 66px);
  }

  .nav-links :deep(> a) {
    display: flex !important;
    width: 100% !important;
    height: auto !important;
    min-height: 44px !important;
    max-height: none !important;
    padding: 12px 14px !important;
  }

  .auth-actions {
    width: 100%;
    margin: 8px 0 0;
  }

  .auth-actions :deep(.site-button) {
    flex: 1;
  }
}

@media (max-width: 720px) {
  .navbar {
    height: 74px;
  }

  .navbar.scrolled {
    height: 66px;
  }

  .brand-logo {
    width: 42px;
    height: 54px;
  }

  .brand strong,
  .brand span {
    font-size: 13px;
  }
}
</style>
