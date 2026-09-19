<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import AuthPortal from './components/AuthPortal.vue'
import SiteLayout from './components/SiteUI/Layout/SiteLayout.vue'
import MasterPageShell from './components/SiteUI/Layout/MasterPageShell.vue'
import { getPublicPage } from './publicPageMap'
import { siteSettings, refreshSiteSettings } from './services/siteSettings'

const route = ref('home')
const syncRoute = () => {
  const hashWithoutPrefix = location.hash.replace(/^#\/?/, '')
  const cleanPath = hashWithoutPrefix.split('?')[0].split('/')[0]
  route.value = cleanPath || 'home'
  scrollTo(0, 0)
}
onMounted(() => {
  syncRoute()
  addEventListener('hashchange', syncRoute)
})
onUnmounted(() => removeEventListener('hashchange', syncRoute))

const livestream = ref({ is_live: false, title: null, url: siteSettings.facebook_url })
let livestreamTimer = null
let livestreamClosingTimer = null
let notificationTimer = null

const refreshLivestream = async () => {
  const requestedAt = performance.now()
  try {
    const res = await fetch('/api/livestream-status', { cache: 'no-store', headers: { Accept: 'application/json' } })
    if (res.ok) {
      livestream.value = await res.json()
      clearTimeout(livestreamClosingTimer)
      if (livestream.value.is_live && livestream.value.closes_at && livestream.value.server_time) {
        const remaining = Date.parse(livestream.value.closes_at) - Date.parse(livestream.value.server_time)
          - (performance.now() - requestedAt)
        // Close on schedule even if a later status refresh fails or the visitor's clock is wrong.
        if (remaining <= 0) {
          livestream.value = { ...livestream.value, is_live: false }
        } else {
          livestreamClosingTimer = setTimeout(() => {
            livestream.value = { ...livestream.value, is_live: false }
            refreshLivestream()
          }, remaining)
        }
      }
    }
  } catch {
    // Keep the last known state when the status endpoint is temporarily unavailable.
  }
}

const welcomeNotification = ref(null)

const dismissNotification = () => {
  if (notificationTimer) {
    clearTimeout(notificationTimer)
    notificationTimer = null
  }
  welcomeNotification.value = null
}

const checkNotification = () => {
  if (typeof window === 'undefined') return
  const urlParams = new URLSearchParams(window.location.search)
  if (urlParams.has('welcome') || urlParams.has('registered')) {
    welcomeNotification.value = {
      type: 'success',
      title: 'Welcome to Our Lady of the Pillar Parish!',
      message: 'Your parishioner account has been created successfully.',
      showPortalLink: true,
    }
    urlParams.delete('welcome')
    urlParams.delete('registered')
    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '') + window.location.hash
    window.history.replaceState({}, '', newUrl)

    if (notificationTimer) clearTimeout(notificationTimer)
    notificationTimer = setTimeout(dismissNotification, 8000)
  } else if (urlParams.has('login')) {
    const userName = window.__AUTH_USER__?.name || 'Parishioner'
    welcomeNotification.value = {
      type: 'info',
      title: `Welcome back, ${userName}!`,
      message: 'You are signed in to Our Lady of the Pillar Parish.',
      showPortalLink: true,
    }
    urlParams.delete('login')
    const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '') + window.location.hash
    window.history.replaceState({}, '', newUrl)

    if (notificationTimer) clearTimeout(notificationTimer)
    notificationTimer = setTimeout(dismissNotification, 8000)
  }
}

onMounted(() => {
  refreshSiteSettings()
  addEventListener('focus', refreshSiteSettings)
  addEventListener('focus', refreshLivestream)
  refreshLivestream()
  livestreamTimer = setInterval(refreshLivestream, 15000)
  checkNotification()
})

onUnmounted(() => {
  removeEventListener('focus', refreshSiteSettings)
  removeEventListener('focus', refreshLivestream)
  clearInterval(livestreamTimer)
  clearTimeout(livestreamClosingTimer)
  if (notificationTimer) clearTimeout(notificationTimer)
})

const currentPage = computed(() => getPublicPage(route.value))
</script>

<template>
  <!-- Subtle Floating Welcome / Login Notice for Official Website -->
  <transition name="toast-slide">
    <aside
      v-if="welcomeNotification"
      class="public-welcome-toast"
      role="status"
      aria-live="polite"
    >
      <div class="toast-card">
        <div class="toast-icon-wrap" :class="'icon-' + (welcomeNotification.type || 'info')">
          <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
        </div>
        <div class="toast-content">
          <div class="toast-header-row">
            <h4 class="toast-title">{{ welcomeNotification.title }}</h4>
            <button
              type="button"
              class="toast-dismiss-btn"
              @click="dismissNotification"
              aria-label="Close notification"
            >
              <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
          <p class="toast-message">{{ welcomeNotification.message }}</p>
          <div v-if="welcomeNotification.showPortalLink" class="toast-footer">
            <a href="/portal" class="toast-portal-btn">
              <span>My Parish Account</span>
              <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </aside>
  </transition>

  <!-- Authentication Portal (Login / Register / Profile Completion) with Unified Header and Footer -->
  <SiteLayout
    v-if="route === 'login' || route === 'register' || route === 'complete-profile'"
    :active="route"
  >
    <AuthPortal :mode="route" />
  </SiteLayout>

  <!-- One Master Page Shell for ALL Public Pages -->
  <MasterPageShell
    v-else-if="currentPage"
    :active="route"
    :livestream="livestream"
    :hero-type="currentPage.heroType"
    :image="currentPage.image"
    :video-src="currentPage.videoSrc"
    :video-poster="currentPage.videoPoster"
    :eyebrow="currentPage.eyebrow"
    :title="currentPage.title"
    :description="currentPage.description"
    :has-rule="currentPage.hasRule"
    :hero-actions="currentPage.heroActions"
  >
    <component :is="currentPage.component" />
  </MasterPageShell>

  <!-- Fallback Shell for Any Unmapped Public Pages -->
  <MasterPageShell
    v-else
    :active="route"
    :livestream="livestream"
    hero-type="image"
    image="/images/church-interior.png"
    eyebrow="Diocesan Shrine and Parish of Our Lady of the Pillar"
    :title="route.replace('-', ' ')"
    description="This static page is ready for parish content."
  >
    <section class="soft-page">
      <div class="page-width" style="padding: 80px 0; text-align: center;">
        <h2 style="color: var(--blue, #0e325f); text-transform: capitalize; margin-bottom: 12px;">{{ route.replace('-', ' ') }}</h2>
        <p style="color: var(--text-muted, #55687d);">This static page is ready for parish content.</p>
      </div>
    </section>
  </MasterPageShell>
</template>

<style src="./components/SiteUI/Theme/designTokens.css"></style>
<style src="./parish.css"></style>
<style src="./modern.css"></style>

<style>
/* Global Shrine & Shell Utility Styles */
.schedule-group {
  margin: 20px 0 5px;
  color: #9b7628;
  font-size: 11px;
  letter-spacing: 0;
  text-transform: uppercase;
}

.schedule-group:first-of-type {
  margin-top: 0;
}

.schedule-day {
  color: var(--blue);
  font-weight: 700;
}

.novena-feature-section {
  padding-bottom: 70px;
}

.feature-card .novena-official-image {
  object-fit: contain;
  object-position: center;
  background: #293535;
}

.tabs button {
  cursor: pointer;
  transition: border-color .2s, background .2s, transform .2s, box-shadow .2s;
}

.tabs button:first-child {
  border-color: var(--line);
  border-bottom-color: transparent;
}

.tabs button.active,
.tabs button:hover,
.tabs button:focus-visible {
  border-bottom-color: var(--bright);
  background: #f8fbfe;
  transform: translateY(-2px);
  box-shadow: 0 12px 27px rgba(14, 50, 95, .14);
}

.tabs button:focus-visible {
  outline: 2px solid var(--gold);
  outline-offset: 3px;
}

.tabs .sacrament-icon {
  display: grid;
  width: 42px;
  height: 42px;
  margin: 0 auto;
  place-items: center;
  border-radius: 50%;
  background: #edf4fb;
  color: var(--blue);
  font-size: 24px;
  line-height: 1;
}

.tabs button.active .sacrament-icon {
  background: var(--blue);
  color: #fff;
}


.livestream-alert {
  position: sticky;
  z-index: 25;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-height: 46px;
  padding: 9px 20px;
  background: #b4232c;
  color: #fff;
  text-align: center;
  box-shadow: 0 5px 14px rgba(80, 0, 5, .2);
}

.livestream-alert:hover,
.livestream-alert:focus-visible {
  background: #941b23;
  color: #fff;
}

.livestream-dot {
  width: 11px;
  height: 11px;
  flex: 0 0 11px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 0 0 0 rgba(255, 255, 255, .75);
  animation: live-pulse 1.5s ease-out infinite;
}

@keyframes live-pulse {
  70% { box-shadow: 0 0 0 9px rgba(255, 255, 255, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
}

@media (max-width: 720px) {
  .livestream-alert {
    flex-wrap: wrap;
    gap: 5px 8px;
    font-size: 13px;
  }

  .livestream-alert b {
    width: 100%;
    font-size: 11px;
  }
}

/* ===== Public Welcome Floating Toast ===== */
.public-welcome-toast {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 99999;
  max-width: 420px;
  width: calc(100vw - 32px);
  pointer-events: auto;
}

.toast-card {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 16px 18px;
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid rgba(6, 47, 120, 0.12);
  border-left: 4px solid #d8aa3c;
  border-radius: 12px;
  box-shadow: 0 16px 40px -8px rgba(6, 47, 120, 0.2), 0 4px 14px rgba(0, 0, 0, 0.08);
  font-family: var(--font-body);
  color: #0f172a;
}

.toast-icon-wrap {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  background: #f0fdf4;
  color: #16a34a;
  border: 1px solid #bbf7d0;
  margin-top: 1px;
}

.toast-icon-wrap.icon-info {
  background: #eff6ff;
  color: #2563eb;
  border-color: #bfdbfe;
}

.toast-content {
  flex: 1;
  min-width: 0;
}

.toast-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.toast-title {
  margin: 0;
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  line-height: 1.35;
  letter-spacing: -0.01em;
}

.toast-message {
  margin: 4px 0 0;
  font-size: 12.5px;
  color: #64748b;
  line-height: 1.45;
}

.toast-dismiss-btn {
  background: transparent;
  border: none;
  width: 26px;
  height: 26px;
  border-radius: 6px;
  display: grid;
  place-items: center;
  cursor: pointer;
  color: #94a3b8;
  padding: 0;
  flex-shrink: 0;
  transition: all 0.15s ease;
}

.toast-dismiss-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
}

.toast-footer {
  margin-top: 10px;
}

.toast-portal-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: #062f78;
  color: #ffffff !important;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.18s ease;
  box-shadow: 0 2px 6px rgba(6, 47, 120, 0.25);
}

.toast-portal-btn:hover {
  background: #0a3da0;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(6, 47, 120, 0.35);
  color: #ffffff !important;
}

.toast-portal-btn svg {
  transition: transform 0.15s ease;
}

.toast-portal-btn:hover svg {
  transform: translateX(2px);
}

/* Toast Slide Transition */
.toast-slide-enter-active,
.toast-slide-leave-active {
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-slide-enter-from {
  opacity: 0;
  transform: translateY(-20px) scale(0.96);
}

.toast-slide-leave-to {
  opacity: 0;
  transform: translateY(-16px) scale(0.96);
}

@media (max-width: 640px) {
  .public-welcome-toast {
    top: 12px;
    right: 12px;
    left: 12px;
    width: auto;
    max-width: none;
  }
}
</style>
