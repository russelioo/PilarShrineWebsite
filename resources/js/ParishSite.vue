<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import AuthPortal from './components/AuthPortal.vue'
import MasterPageShell from './components/SiteUI/Layout/MasterPageShell.vue'
import { getPublicPage } from './publicPageMap'

const route = ref('home')
const syncRoute = () => {
  route.value = location.hash.replace(/^#\//, '').split('/')[0] || 'home'
  scrollTo(0, 0)
}
onMounted(() => {
  syncRoute()
  addEventListener('hashchange', syncRoute)
})
onUnmounted(() => removeEventListener('hashchange', syncRoute))

const livestream = ref({ is_live: false, title: null, url: 'https://www.facebook.com/PilarShrineSorsogon' })
let livestreamTimer

const refreshLivestream = async () => {
  try {
    const response = await fetch('/api/livestream-status', { headers: { Accept: 'application/json' } })
    if (response.ok) livestream.value = await response.json()
  } catch {
    // Keep the last known state when the status endpoint is temporarily unavailable.
  }
}

onMounted(() => {
  refreshLivestream()
  livestreamTimer = setInterval(refreshLivestream, 15000)
})
onUnmounted(() => clearInterval(livestreamTimer))

const currentPage = computed(() => getPublicPage(route.value))
</script>

<template>
  <!-- Authentication Portal (Login / Register) -->
  <AuthPortal
    v-if="route === 'login' || route === 'register'"
    :mode="route"
  />

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
</style>
