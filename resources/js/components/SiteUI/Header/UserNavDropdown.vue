<script setup>
import { computed, nextTick, onBeforeUnmount, ref } from 'vue'

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['close-nav'])

const rootEl = ref(null)
const triggerBtn = ref(null)
const menuEl = ref(null)
const isOpen = ref(false)

const csrfToken = computed(() => {
  return typeof document !== 'undefined'
    ? document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    : ''
})

const getInitials = (name) => {
  if (!name) return 'P'
  const parts = String(name).trim().split(/\s+/)
  if (parts.length >= 2) {
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase()
  }
  return parts[0].charAt(0).toUpperCase()
}

const imageLoadFailed = ref(false)
const onImageError = () => {
  imageLoadFailed.value = true
}

let closeTimer = null

const openMenu = () => {
  if (closeTimer) {
    clearTimeout(closeTimer)
    closeTimer = null
  }
  isOpen.value = true
}

const closeMenu = (immediate = false) => {
  if (closeTimer) {
    clearTimeout(closeTimer)
    closeTimer = null
  }
  if (immediate) {
    isOpen.value = false
  } else {
    closeTimer = setTimeout(() => {
      isOpen.value = false
      closeTimer = null
    }, 120)
  }
}

const toggleMenu = () => {
  if (isOpen.value) {
    closeMenu(true)
  } else {
    openMenu()
  }
}

const onMouseEnter = () => {
  if (typeof window !== 'undefined' && window.matchMedia('(hover: hover)').matches) {
    openMenu()
  }
}

const onMouseLeave = () => {
  if (typeof window !== 'undefined' && window.matchMedia('(hover: hover)').matches) {
    closeMenu(false)
  }
}

const onFocusOut = (event) => {
  if (rootEl.value && !rootEl.value.contains(event.relatedTarget)) {
    closeMenu(true)
  }
}

const onTriggerKeydown = (event) => {
  if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
    event.preventDefault()
    openMenu()
    nextTick(() => {
      const firstLink = menuEl.value?.querySelector('a, button')
      firstLink?.focus()
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
  }
}

const handleNavigate = () => {
  closeMenu(true)
  emit('close-nav')
}

onBeforeUnmount(() => {
  if (closeTimer) clearTimeout(closeTimer)
})
</script>

<template>
  <div
    ref="rootEl"
    class="user-dropdown-wrap"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
    @focusout="onFocusOut"
  >
    <!-- Navbar Trigger Button: Avatar + Name + Dropdown Caret -->
    <button
      ref="triggerBtn"
      type="button"
      class="user-dropdown-trigger"
      :class="{ 'is-open': isOpen }"
      aria-haspopup="menu"
      :aria-expanded="isOpen"
      :aria-label="`Account menu for ${user.name || 'Parishioner'}`"
      @click="toggleMenu"
      @keydown="onTriggerKeydown"
    >
      <div class="user-trigger-avatar">
        <img
          v-if="user.avatar && !imageLoadFailed"
          :src="user.avatar"
          :alt="user.name"
          class="user-avatar-img"
          referrerpolicy="no-referrer"
          @error="onImageError"
        />
        <span v-else class="user-avatar-initials">{{ getInitials(user.name) }}</span>
      </div>

      <span class="user-trigger-name">{{ user.name || 'Parishioner' }}</span>

      <svg class="dropdown-caret" viewBox="0 0 20 20" width="14" height="14" fill="currentColor" aria-hidden="true">
        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <div
      v-show="isOpen"
      ref="menuEl"
      class="user-dropdown-menu"
      role="menu"
      aria-label="User Account Menu"
      @keydown="onMenuKeydown"
    >
      <!-- Identity Header Card -->
      <div class="menu-user-header">
        <div class="menu-user-avatar">
          <img
            v-if="user.avatar && !imageLoadFailed"
            :src="user.avatar"
            :alt="user.name"
            class="user-avatar-img"
            referrerpolicy="no-referrer"
            @error="onImageError"
          />
          <span v-else class="user-avatar-initials">{{ getInitials(user.name) }}</span>
        </div>
        <div class="menu-user-meta">
          <strong class="menu-user-name">{{ user.name || 'Parishioner' }}</strong>
          <span v-if="user.email" class="menu-user-email">{{ user.email }}</span>
          <span class="menu-user-badge">Parishioner Account</span>
        </div>
      </div>

      <div class="menu-divider" role="separator"></div>

      <!-- Action Items -->
      <a href="/portal" class="menu-item menu-item-primary" role="menuitem" @click="handleNavigate">
        <div class="menu-item-icon portal-icon">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
          </svg>
        </div>
        <div class="menu-item-text">
          <strong>My Parish Account</strong>
          <small>Portal dashboard &amp; requests</small>
        </div>
        <span class="menu-arrow">&rarr;</span>
      </a>

      <a href="/parishioner/profile-settings" class="menu-item" role="menuitem" @click="handleNavigate">
        <div class="menu-item-icon">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <div class="menu-item-text">
          <strong>Profile &amp; Settings</strong>
          <small>Personal details &amp; residence</small>
        </div>
      </a>

      <div class="menu-divider" role="separator"></div>

      <!-- Sign Out Form -->
      <form method="POST" action="/parishioner/logout" class="menu-logout-form">
        <input type="hidden" name="_token" :value="csrfToken">
        <button type="submit" class="menu-item menu-item-logout" role="menuitem">
          <div class="menu-item-icon logout-icon">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </div>
          <div class="menu-item-text">
            <strong>Sign Out</strong>
          </div>
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.user-dropdown-wrap {
  position: relative;
  display: inline-flex;
  align-items: center;
}

/* Trigger Button */
.user-dropdown-trigger {
  display: inline-flex;
  align-items: center;
  gap: 9px;
  height: 42px;
  padding: 0 12px 0 5px;
  border-radius: 24px;
  background: #f8fafc;
  border: 1px solid #cbd5e1;
  color: var(--color-primary, #062f78);
  font-family: inherit;
  cursor: pointer;
  user-select: none;
  transition: all 0.18s ease;
  box-sizing: border-box;
}

.user-dropdown-trigger:hover,
.user-dropdown-trigger.is-open {
  background: #f1f5f9;
  border-color: #94a3b8;
  box-shadow: 0 2px 8px rgba(6, 47, 120, 0.08);
}

.user-dropdown-trigger:focus-visible {
  outline: 2px solid #d8aa3c;
  outline-offset: 2px;
}

.user-trigger-avatar {
  width: 32px;
  height: 32px;
  min-width: 32px;
  min-height: 32px;
  max-width: 32px;
  max-height: 32px;
  border-radius: 50%;
  overflow: hidden;
  display: grid;
  place-items: center;
  background: #062f78;
  color: #ffffff;
  border: 1.5px solid #d8aa3c;
  flex-shrink: 0;
  box-sizing: border-box;
}

.user-avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.user-avatar-initials {
  font-size: 11px;
  font-weight: 700;
  font-family: var(--font-body);
}

.user-trigger-name {
  font-size: 12.5px;
  font-weight: 700;
  color: #062f78;
  max-width: 140px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dropdown-caret {
  color: #64748b;
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.user-dropdown-trigger.is-open .dropdown-caret {
  transform: rotate(180deg);
  color: #062f78;
}

/* Dropdown Menu Container */
.user-dropdown-menu {
  position: absolute;
  z-index: 120;
  top: calc(100% + 8px);
  right: 0;
  width: 270px;
  background: #ffffff;
  border: 1px solid #dce4ec;
  border-radius: 12px;
  box-shadow: 0 14px 36px rgba(12, 38, 70, 0.16);
  padding: 8px;
  box-sizing: border-box;
  animation: dropdownFade 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

.user-dropdown-menu::before {
  content: '';
  position: absolute;
  top: -8px;
  left: 0;
  right: 0;
  height: 8px;
  background: transparent;
}

@keyframes dropdownFade {
  from {
    opacity: 0;
    transform: translateY(-6px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* User Identity Header inside Dropdown */
.menu-user-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background: #f8fafc;
  border-radius: 8px;
}

.menu-user-avatar {
  width: 38px;
  height: 38px;
  min-width: 38px;
  min-height: 38px;
  max-width: 38px;
  max-height: 38px;
  border-radius: 50%;
  overflow: hidden;
  border: 2px solid #d8aa3c;
  display: grid;
  place-items: center;
  background: #062f78;
  color: #ffffff;
  flex-shrink: 0;
}

.menu-user-meta {
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.menu-user-name {
  font-size: 13px;
  font-weight: 700;
  color: #062f78;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.menu-user-email {
  font-size: 11px;
  color: #64748b;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  margin-top: 1px;
}

.menu-user-badge {
  display: inline-block;
  margin-top: 4px;
  padding: 1px 6px;
  border-radius: 10px;
  background: #eaf2fb;
  color: #062f78;
  font-size: 9px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  width: fit-content;
}

.menu-divider {
  height: 1px;
  background: #edf2f7;
  margin: 6px 4px;
}

/* Menu Items */
.menu-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 9px 12px;
  box-sizing: border-box;
  border-radius: 8px;
  text-decoration: none;
  color: var(--color-primary, #062f78);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease;
  text-align: left;
  font-family: inherit;
}

.menu-item:hover {
  background: #f1f5f9;
}

.menu-item:focus-visible {
  outline: 2px solid #d8aa3c;
  outline-offset: -1px;
}

.menu-item-primary {
  background: #f0f7ff;
}

.menu-item-primary:hover {
  background: #e2effe;
}

.menu-item-icon {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background: #eaf2fb;
  color: #062f78;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.menu-item-primary .menu-item-icon {
  background: #062f78;
  color: #f6d588;
}

.menu-item-text {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.menu-item-text strong {
  font-size: 12px;
  font-weight: 700;
  color: #062f78;
}

.menu-item-text small {
  font-size: 10px;
  color: #64748b;
}

.menu-arrow {
  color: #94a3b8;
  font-size: 12px;
  transition: transform 0.15s ease;
}

.menu-item-primary:hover .menu-arrow {
  transform: translateX(2px);
  color: #062f78;
}

/* Logout Item */
.menu-logout-form {
  margin: 0;
  padding: 0;
  width: 100%;
}

.menu-item-logout {
  color: #8b2635;
}

.menu-item-logout .logout-icon {
  background: #fff1f2;
  color: #e11d48;
}

.menu-item-logout:hover {
  background: #fff1f2;
}

.menu-item-logout:hover .menu-item-text strong {
  color: #be123c;
}

/* Mobile Responsiveness */
@media (max-width: 1050px) {
  .user-dropdown-wrap {
    width: 100%;
  }

  .user-dropdown-trigger {
    width: 100%;
    justify-content: space-between;
    height: 48px;
    padding: 0 14px 0 8px;
  }

  .user-dropdown-menu {
    position: static;
    width: 100%;
    box-shadow: none;
    border-color: #e2e8f0;
    margin-top: 6px;
  }

  .user-dropdown-menu::before {
    display: none;
  }
}
</style>
