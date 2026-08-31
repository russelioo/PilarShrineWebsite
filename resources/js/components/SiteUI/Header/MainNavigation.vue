<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import NavDropdown from '../Navigation/NavDropdown.vue'
import NavItem from '../Navigation/NavItem.vue'
import { accountNavigation, isNavigationItemActive, moreNavigation, primaryNavigation } from '../Navigation/navigationItems'
import SiteButton from '../UI/SiteButton.vue'
import MobileNavigation from './MobileNavigation.vue'

const props = defineProps({ active: { type: String, default: 'home' }, scrolled: { type: Boolean, default: false } })
const open = ref(false)
const moreOpen = ref(false)
const root = ref(null)

const closeNavigation = () => { open.value = false; moreOpen.value = false }
const handleOutside = event => { if (root.value && !root.value.contains(event.target)) closeNavigation() }
const handleEscape = event => { if (event.key === 'Escape') closeNavigation() }

watch(open, value => {
  if (window.innerWidth <= 1050) document.body.style.overflow = value ? 'hidden' : ''
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
      <NavItem v-for="item in primaryNavigation" :key="item.key" :item="item" :active="isNavigationItemActive(item, props.active)" @navigate="closeNavigation" />
      <NavDropdown v-model:open="moreOpen" :items="moreNavigation" :active-route="props.active" @navigate="closeNavigation" />
      <div class="auth-actions" aria-label="Parish account">
        <SiteButton v-for="item in accountNavigation" :key="item.key" :href="item.href" :variant="item.variant" :active="isNavigationItemActive(item, props.active)" @click="closeNavigation">{{ item.label }}</SiteButton>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.navbar{height:76px;display:flex;align-items:center;justify-content:space-between;transition:height .25s ease}.brand{display:flex;align-items:center;gap:10px;color:var(--color-primary);line-height:1.05}.brand-logo{width:43px;height:54px;object-fit:contain;transition:width .25s,height .25s}.brand strong,.brand span{display:block}.brand strong{font-size:15px}.brand span{font-size:15px;font-weight:400}.nav-links{display:flex;align-items:center;gap:4px}.nav-links :deep(> a),.nav-links :deep(.nav-more){padding:11px 13px;border:0;border-radius:9px;background:transparent;color:var(--color-primary);font-size:11px;font-weight:700;letter-spacing:.015em;text-transform:uppercase;white-space:nowrap;transition:background .18s,color .18s}.nav-links :deep(> a:hover),.nav-links :deep(> a:focus-visible),.nav-links :deep(.nav-more:hover),.nav-links :deep(.nav-more:focus-visible){background:#f3f6fa}.nav-links :deep(> a.active),.nav-links :deep(.nav-more.active){background:var(--color-primary-light);color:#0752a4}.nav-links :deep(a:focus-visible),.nav-links :deep(button:focus-visible){outline:2px solid var(--color-gold);outline-offset:2px}.nav-links :deep(.more-nav){position:relative}.nav-links :deep(.nav-more){display:inline-flex;min-height:40px;align-items:center;gap:5px;cursor:pointer}.nav-links :deep(.more-menu){position:absolute;z-index:60;top:calc(100% + 10px);right:0;display:grid;width:205px;padding:8px;border:1px solid #dce4ec;border-radius:12px;background:#fff;box-shadow:var(--shadow-md)}.nav-links :deep(.more-menu a){padding:11px 13px;border-radius:8px;color:var(--color-primary);font-size:11px;font-weight:600}.nav-links :deep(.more-menu a:hover),.nav-links :deep(.more-menu a[aria-current=page]){background:var(--color-primary-light)}.auth-actions{display:flex;align-items:center;gap:9px;margin-left:12px}.navbar.scrolled{height:66px}.navbar.scrolled .brand-logo{width:37px;height:47px}@media(max-width:1050px){.nav-links{position:absolute;z-index:55;top:76px;right:0;left:0;display:none;max-height:calc(100vh - 76px);align-items:stretch;gap:8px;padding:18px 24px 24px;overflow-y:auto;border-top:1px solid #e5ebf2;background:#fff;box-shadow:var(--shadow-md);flex-direction:column}.nav-links.open{display:flex}.nav-links.scrolled{top:66px;max-height:calc(100vh - 66px)}.nav-links :deep(> a),.nav-links :deep(.nav-more){display:flex;width:100%;padding:13px}.nav-links :deep(.nav-more){justify-content:space-between}.nav-links :deep(.more-menu){position:static;width:100%;margin:4px 0;box-shadow:none}.auth-actions{width:100%;margin:8px 0 0}.auth-actions :deep(.site-button){flex:1}}@media(max-width:720px){.navbar{height:74px}.navbar.scrolled{height:66px}.brand-logo{width:42px;height:54px}.brand strong,.brand span{font-size:13px}}
</style>
