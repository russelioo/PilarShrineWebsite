<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import NavDropdown from '../Navigation/NavDropdown.vue'
import UserNavDropdown from './UserNavDropdown.vue'
import NavItem from '../Navigation/NavItem.vue'
import { accountNavigation, isNavigationItemActive, primaryNavigation } from '../Navigation/navigationItems'
import SiteButton from '../UI/SiteButton.vue'
import MobileNavigation from './MobileNavigation.vue'

const props = defineProps({
  active: { type: String, default: 'home' },
  scrolled: { type: Boolean, default: false },
})

const open = ref(false)
const servicesOpen = ref(false)
const root = ref(null)

const authUser = ref(typeof window !== 'undefined' && window.__AUTH_USER__ ? window.__AUTH_USER__ : null)
const csrfToken = computed(() => {
  return typeof document !== 'undefined' ? (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '') : ''
})

const getInitials = (name) => {
  if (!name) return 'P'
  const parts = String(name).trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
  }
  return parts[0].charAt(0).toUpperCase()
}

const checkAuthStatus = async () => {
  if (!authUser.value && typeof fetch !== 'undefined') {
    try {
      const res = await fetch('/api/user/profile-status', { headers: { 'Accept': 'application/json' } })
      if (res.ok) {
        const data = await res.json()
        if (data.authenticated && data.user) {
          authUser.value = data.user
        }
      }
    } catch (e) {
      // Guest
    }
  }
}


const closeNavigation = () => {
  open.value = false
  servicesOpen.value = false
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
  checkAuthStatus()

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
    <a class="brand" href="#/home" @click="closeNavigation" aria-label="Our Lady of the Pillar Parish Home">
      <img class="brand-logo" :src="'/images/pilar-shrine-logo.png'" alt="Our Lady of the Pillar Parish seal" width="46" height="58">
      <div class="brand-copy">
        <strong class="brand-name">Our Lady of the Pillar Parish</strong>
        <span class="brand-sub">Diocesan Shrine &bull; Pilar, Sorsogon</span>
      </div>
    </a>

    <MobileNavigation :open="open" @toggle="open = !open; servicesOpen = false" />

    <div id="site-navigation" class="nav-links" :class="{ open, scrolled }">
      <template v-for="item in primaryNavigation" :key="item.key">
        <!-- Dropdown for items with children (Parish Services) -->
        <NavDropdown
          v-if="item.children"
          v-model:open="servicesOpen"
          :label="item.label"
          :items="item.children"
          :active-route="props.active"
          @navigate="closeNavigation"
        />

        <!-- Standard Nav Link (Home, About, Contact) -->
        <NavItem
          v-else
          :item="item"
          :active="isNavigationItemActive(item, props.active)"
          @navigate="closeNavigation"
        />
      </template>

      <!-- Authenticated User Dropdown Menu -->
      <div v-if="authUser" class="auth-actions" aria-label="Parishioner account">
        <UserNavDropdown :user="authUser" @close-nav="closeNavigation" />
      </div>

      <!-- Guest Auth Actions (Sign In & Prominent Create Account) -->
      <div v-else class="auth-actions" aria-label="Parish account">
        <SiteButton
          v-for="item in accountNavigation"
          :key="item.key"
          :href="item.href"
          :variant="item.variant"
          :class="['nav-auth-btn', `nav-auth-${item.key}`]"
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
  height: 78px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  transition: height 0.25s ease;
  box-sizing: border-box;
}

.brand {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--color-primary);
  line-height: 1.15;
  text-decoration: none;
  flex-shrink: 0;
}

.brand-logo {
  width: 44px;
  height: 56px;
  object-fit: contain;
  transition: width 0.25s, height 0.25s;
}

.brand-copy {
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-width: 0;
}

.brand-name {
  font-family: 'Libre Baskerville', Georgia, serif;
  font-size: 15.5px;
  font-weight: 700;
  color: var(--color-primary);
  line-height: 1.15;
  letter-spacing: -0.01em;
  white-space: nowrap;
}

.brand-sub {
  font-family: Montserrat, -apple-system, sans-serif;
  font-size: 9.5px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #c89b3c;
  margin-top: 3px;
  line-height: 1;
}

.nav-links {
  display: flex;
  align-items: center;
  gap: 5px;
}

/* Base Footprint for Top-Level Navigation Links */
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
  letter-spacing: 0.02em !important;
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

/* Focus and Focus-Visible */
.nav-links :deep(> a:focus:not(:focus-visible)) {
  outline: none !important;
  box-shadow: none !important;
}

.nav-links :deep(> a:focus-visible) {
  outline: 2px solid var(--color-gold) !important;
  outline-offset: 2px !important;
}

/* Active navigation state */
.nav-links :deep(> a.active),
.nav-links :deep(.nav-more.active) {
  background: var(--color-primary-light) !important;
  color: #0752a4 !important;
  border: 1px solid rgba(11, 59, 130, 0.14) !important;
}

/* Auth Actions */
.auth-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-left: 14px;
}

.nav-auth-login {
  border: 1px solid #cbd5e1 !important;
  color: var(--color-primary) !important;
  background: #ffffff !important;
  font-weight: 700 !important;
  letter-spacing: 0.02em !important;
  font-size: 11px !important;
  padding: 0 16px !important;
  height: 38px !important;
  border-radius: 8px !important;
  transition: all 0.18s ease !important;
}

.nav-auth-login:hover {
  background: #f1f5f9 !important;
  border-color: #94a3b8 !important;
  color: #062f78 !important;
}

.nav-auth-login.active {
  background: var(--color-primary-light) !important;
  border-color: var(--color-primary) !important;
  color: var(--color-primary) !important;
}

.nav-auth-register {
  background: linear-gradient(135deg, #062f78 0%, #0c4ea2 100%) !important;
  color: #ffffff !important;
  border: 1px solid #d8aa3c !important;
  box-shadow: 0 4px 14px rgba(6, 47, 120, 0.22) !important;
  font-weight: 700 !important;
  letter-spacing: 0.03em !important;
  font-size: 11px !important;
  padding: 0 18px !important;
  height: 38px !important;
  border-radius: 8px !important;
  transition: all 0.18s ease !important;
}

.nav-auth-register:hover {
  background: linear-gradient(135deg, #083b94 0%, #0e5dbf 100%) !important;
  box-shadow: 0 6px 18px rgba(6, 47, 120, 0.32) !important;
  transform: translateY(-1px);
}

.nav-auth-register.active {
  outline: 2px solid #d8aa3c !important;
  outline-offset: 2px !important;
}

/* Scrolled Header State */
.navbar.scrolled {
  height: 66px;
}

.navbar.scrolled .brand-logo {
  width: 38px;
  height: 48px;
}

/* Mobile Responsive Styles */
@media (max-width: 1050px) {
  .nav-links {
    position: absolute;
    z-index: 55;
    top: 78px;
    right: 0;
    left: 0;
    display: none;
    max-height: calc(100vh - 78px);
    align-items: stretch;
    gap: 8px;
    padding: 20px 24px 28px;
    overflow-y: auto;
    border-top: 1px solid #e2e8f0;
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
    margin: 14px 0 0;
    padding-top: 14px;
    border-top: 1px solid #e2e8f0;
    flex-direction: column;
    gap: 10px;
  }

  .auth-actions :deep(.site-button) {
    width: 100%;
    justify-content: center;
    min-height: 44px !important;
  }
}

@media (max-width: 720px) {
  .navbar {
    height: 70px;
  }

  .navbar.scrolled {
    height: 64px;
  }

  .brand-logo {
    width: 36px;
    height: 46px;
  }

  .brand-name {
    font-size: 13px;
    white-space: normal;
  }

  .brand-sub {
    font-size: 8px;
  }
}
</style>

<style scoped>

/* Authenticated User Nav Actions */
.auth-user-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.nav-user-identity {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 3px 10px 3px 3px;
  border-radius: 20px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
}

.nav-user-avatar {
  width: 28px;
  height: 28px;
  min-width: 28px;
  min-height: 28px;
  max-width: 28px;
  max-height: 28px;
  border-radius: 50%;
  overflow: hidden;
  display: grid;
  place-items: center;
  background: #062f78;
  color: #fff;
  border: 1.5px solid #d8aa3c;
  flex-shrink: 0;
}

.nav-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.nav-avatar-initials {
  font-size: 11px;
  font-weight: 700;
  font-family: Georgia, serif;
}

.nav-user-name {
  font-size: 12px;
  font-weight: 700;
  color: #062f78;
  max-width: 130px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.btn-parish-account {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: linear-gradient(135deg, #062f78 0%, #0c4ea2 100%);
  color: #ffffff !important;
  border: 1px solid #d8aa3c;
  box-shadow: 0 3px 10px rgba(6, 47, 120, 0.2);
  font-weight: 700;
  letter-spacing: 0.02em;
  font-size: 11px;
  text-transform: uppercase;
  padding: 0 16px;
  height: 38px;
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.18s ease;
  white-space: nowrap;
}

.btn-parish-account:hover {
  background: linear-gradient(135deg, #083b94 0%, #0e5dbf 100%);
  box-shadow: 0 5px 14px rgba(6, 47, 120, 0.3);
  transform: translateY(-1px);
}

.btn-parish-account svg {
  color: #f6d588;
}

.nav-logout-form {
  margin: 0;
  padding: 0;
  display: inline-flex;
}

.nav-logout-btn {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #64748b;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.02em;
  height: 38px;
  padding: 0 14px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.18s ease;
  white-space: nowrap;
}

.nav-logout-btn:hover {
  background: #fef2f2;
  border-color: #fca5a5;
  color: #dc2626;
}

@media (max-width: 1050px) {
  .auth-user-actions {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
    margin-left: 0;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
  }
  .nav-user-identity {
    justify-content: center;
  }
  .btn-parish-account, .nav-logout-btn {
    width: 100%;
    justify-content: center;
    box-sizing: border-box;
  }
}

</style>
