<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { primaryNavigation, serviceNavigation } from '../Navigation/navigationItems'

const props = defineProps({ open: { type: Boolean, default: false } })
const emit = defineEmits(['update:open'])
const root = ref(null)
const trigger = ref(null)
const input = ref(null)
const resultList = ref(null)
const query = ref('')
const announcements = ref([])
const loading = ref(false)
const failed = ref(false)
let lastLoaded = 0
let request
let disposed = false

const pageDescriptions = {
  home: 'Welcome to Our Lady of the Pillar Parish',
  about: 'Parish history, 2018 dedication, coronation, photo gallery, and decree',
  donations: 'Donations, giving, and support for the shrine',
  contact: 'Parish office, phone, email, location, and inquiries',
}
const pages = [
  ...primaryNavigation.filter(item => !item.children),
  ...serviceNavigation.filter(item => !item.soon),
  { key: 'rosary', label: 'The Holy Rosary', href: '#/rosary', description: 'Mysteries, meditations, and prayers' },
  { key: 'novena-details', label: 'Novena to Our Lady of the Pillar', href: '#/novena-details', description: 'Nine-day novena prayers and devotion' },
].map(item => ({
  id: `page-${item.key}`, title: item.label, href: item.href, kind: 'Page',
  description: item.description || pageDescriptions[item.key] || '',
}))
const normalize = text => String(text || '').normalize('NFKD').replace(/\p{M}/gu, '').toLowerCase().replace(/\s+/g, ' ').trim()
const searchText = computed(() => normalize(query.value))
const results = computed(() => {
  if (!searchText.value) return ['page-schedule', 'page-sacraments', 'page-news', 'page-contact'].map(id => pages.find(page => page.id === id))
  if (searchText.value.length < 2) return []
  const words = searchText.value.split(' ')
  return [...pages, ...announcements.value]
    .filter(item => words.every(word => normalize(`${item.title} ${item.description}`).includes(word)))
    .map(item => ({ ...item, rank: normalize(item.title).startsWith(searchText.value) ? 0 : words.every(word => normalize(item.title).includes(word)) ? 1 : 2 }))
    .sort((a, b) => a.rank - b.rank)
    .slice(0, 8)
})
const status = computed(() => {
  if (!searchText.value) return 'Popular pages'
  if (searchText.value.length < 2) return 'Type at least 2 characters to search.'
  if (results.value.length) return `${results.value.length} ${results.value.length === 1 ? 'result' : 'results'}`
  return loading.value ? 'Searching announcements…' : 'No results. Try “Mass”, “baptism”, or “contact”.'
})

async function loadAnnouncements() {
  if (loading.value || (lastLoaded && Date.now() - lastLoaded < 60000)) return
  loading.value = true
  failed.value = false
  request = new AbortController()
  const timeout = setTimeout(() => request?.abort(), 10000)
  try {
    // Reuse the public feed so scheduled, expired, and deleted posts stay excluded.
    const response = await fetch('/api/announcements', { headers: { Accept: 'application/json' }, signal: request.signal })
    if (!response.ok) throw new Error('Announcements unavailable')
    const data = await response.json()
    if (!Array.isArray(data.announcements)) throw new Error('Invalid announcements')
    if (disposed) return
    announcements.value = data.announcements.map(item => ({
      id: `announcement-${item.id}`, title: item.title, kind: 'Announcement',
      href: `#/news?announcement=${encodeURIComponent(item.id)}`,
      description: `${item.category || ''} ${item.description || ''}`.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim(),
      date: item.date,
    }))
    lastLoaded = Date.now()
  } catch {
    if (!disposed) failed.value = true
  } finally {
    clearTimeout(timeout)
    if (!disposed) loading.value = false
  }
}

function close(restoreFocus = false) {
  emit('update:open', false)
  if (restoreFocus) nextTick(() => trigger.value?.focus({ preventScroll: true }))
}
function clear() {
  query.value = ''
  input.value?.focus({ preventScroll: true })
}
function navigate(result) {
  if (!result) return
  window.location.hash = result.href
  close()
}
function moveFocus(event) {
  if (!['ArrowDown', 'ArrowUp'].includes(event.key)) return
  const links = [...(resultList.value?.querySelectorAll('a') || [])]
  if (!links.length) return
  event.preventDefault()
  const index = links.indexOf(document.activeElement)
  if (event.key === 'ArrowUp' && index === 0) input.value?.focus({ preventScroll: true })
  else links[index < 0 ? (event.key === 'ArrowDown' ? 0 : links.length - 1) : (index + (event.key === 'ArrowDown' ? 1 : -1) + links.length) % links.length]?.focus({ preventScroll: true })
  if (document.activeElement !== input.value) document.activeElement?.scrollIntoView({ block: 'nearest' })
}
function handleKeydown(event) {
  if (!props.open) return
  if (event.key === 'Escape') {
    event.preventDefault()
    event.stopPropagation()
    close(true)
  } else moveFocus(event)
}
const handleOutside = event => { if (props.open && !root.value?.contains(event.target)) close() }
const handleFocusOut = event => { if (props.open && event.relatedTarget && !root.value?.contains(event.relatedTarget)) close() }
const handleRoute = () => close()

watch(() => props.open, async open => {
  if (!open) return
  query.value = ''
  loadAnnouncements()
  await nextTick()
  if (props.open) input.value?.focus({ preventScroll: true })
})
onMounted(() => {
  document.addEventListener('pointerdown', handleOutside)
  window.addEventListener('hashchange', handleRoute)
})
onBeforeUnmount(() => {
  disposed = true
  request?.abort()
  document.removeEventListener('pointerdown', handleOutside)
  window.removeEventListener('hashchange', handleRoute)
})
</script>

<template>
  <div ref="root" class="nav-search" @focusout="handleFocusOut" @keydown="handleKeydown">
    <button ref="trigger" class="nav-search-trigger" type="button" aria-label="Search parish website" :aria-expanded="open" aria-controls="nav-search-panel" @click="emit('update:open', !open)">
      <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" /><path d="m16 16 4.5 4.5" /></svg>
    </button>
    <Transition name="nav-search-expand">
      <div v-if="open" id="nav-search-panel" class="nav-search-panel">
        <form class="nav-search-field" role="search" aria-label="Search parish website" @submit.prevent="navigate(results[0])">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" /><path d="m16 16 4.5 4.5" /></svg>
          <input ref="input" v-model="query" type="search" aria-label="Search pages and announcements" aria-controls="nav-search-results" placeholder="Search the parish…" autocomplete="off" spellcheck="false" maxlength="120">
          <button v-if="query" type="button" class="search-clear" @click="clear">Clear</button>
          <button type="button" class="search-close" aria-label="Close search" @click="close(true)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg></button>
        </form>
        <div id="nav-search-results" class="nav-search-results">
          <p class="search-status" role="status" aria-live="polite">{{ status }}</p>
          <ul v-if="results.length" ref="resultList" class="search-result-list">
            <li v-for="result in results" :key="result.id">
              <a :href="result.href" :data-analytics-action="result.kind === 'Announcement' ? 'announcement_open' : undefined" @click="close()">
                <span class="search-result-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h9l4 4v14H6zM14 3v5h5M9 12h7M9 16h5" /></svg></span>
                <span class="search-result-copy"><strong>{{ result.title }}</strong><small>{{ result.kind }}{{ result.date ? ` · ${result.date}` : '' }}</small><span class="search-result-description">{{ result.description }}</span></span>
                <span class="search-result-arrow" aria-hidden="true">↗</span>
              </a>
            </li>
          </ul>
          <p v-if="loading && results.length" class="search-feedback">Loading announcements…</p>
          <p v-if="failed" class="search-feedback">Announcements couldn’t load. Page search is available. <button type="button" @click="loadAnnouncements">Retry</button></p>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.nav-search{position:relative;width:44px;height:44px;flex:0 0 44px}
.nav-search svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round;flex-shrink:0}
.nav-search-trigger,.search-close{display:grid;place-items:center;width:44px;height:44px;padding:0;border:0;border-radius:50%;background:#f0f4f8;color:var(--color-primary);cursor:pointer}
.nav-search-trigger:hover,.search-close:hover{background:#e4edf8}
.nav-search-trigger[aria-expanded=true]{visibility:hidden}
.nav-search button:focus-visible,.nav-search a:focus-visible{outline:2px solid var(--color-gold);outline-offset:2px}
.nav-search-panel{position:absolute;z-index:65;top:0;right:0;width:min(430px,calc(100vw - 32px));transform-origin:right top}
.nav-search-field{display:flex;align-items:center;gap:10px;height:44px;padding:0 5px 0 15px;margin:0;border:1px solid #b9cce4;border-radius:24px;background:#f3f6fa;color:var(--color-primary);box-shadow:0 0 0 5px #fff}
.nav-search-field:focus-within{border-color:#4174b5;box-shadow:0 0 0 5px #fff,0 0 0 7px #e5eef9}
.nav-search-field input{width:100%;min-width:0;padding:0;border:0;outline:0;background:transparent;color:#173152;font:inherit;font-size:14px;line-height:1.5;box-shadow:none}
.nav-search-field input::-webkit-search-cancel-button{display:none}
.nav-search-field input::placeholder{color:#7b8da4}
.search-close{width:34px;height:34px;flex:0 0 34px;background:transparent}
.search-close svg{width:17px;height:17px}
.search-clear{border:0;background:transparent;color:#526c8c;padding:6px 0;font:inherit;font-size:11px;cursor:pointer}
.nav-search-results{margin-top:12px;padding:8px;max-height:min(430px,calc(100vh - 150px));max-height:min(430px,calc(100dvh - 150px));overflow-y:auto;overscroll-behavior:contain;border:1px solid #dfe7f0;border-radius:16px;background:#fff;box-shadow:0 16px 40px #102f5726}
.search-status{margin:7px 9px 9px;color:#6f8198;font-size:11px;line-height:1.6}
.search-result-list{list-style:none;padding:0;margin:0}
.search-result-list a{display:flex;align-items:flex-start;gap:11px;padding:11px 9px;border-radius:10px;color:#173152;text-decoration:none;line-height:1.5}
.search-result-list a:hover,.search-result-list a:focus-visible{background:#f0f5fc}
.search-result-icon{display:grid;place-items:center;flex:0 0 32px;height:32px;border-radius:10px;background:#edf3fb;color:#2b5a94}
.search-result-icon svg{width:17px;height:17px}
.search-result-copy{display:flex;flex-direction:column;gap:3px;min-width:0;flex:1}
.search-result-copy strong{font-size:13px;font-weight:700;overflow-wrap:anywhere}
.search-result-copy small{font-size:10px;color:#718198}
.search-result-description{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-size:11px;color:#60748d}
.search-result-arrow{color:#718198;font-size:16px}
.search-feedback{margin:8px 10px;color:#718198;font-size:11px;line-height:1.6}
.search-feedback button{border:0;background:none;padding:0;color:#0b3b82;font:inherit;text-decoration:underline;cursor:pointer}
.nav-search-expand-enter-active,.nav-search-expand-leave-active{transition:opacity .16s ease,transform .16s ease}
.nav-search-expand-enter-from,.nav-search-expand-leave-to{opacity:0;transform:scale(.96)}
@media(max-width:1050px){.nav-search{position:static}.nav-search-panel{top:calc(var(--site-top-strip-height,0px) + (var(--site-nav-height,70px) - 44px)/2);right:var(--space-page);width:calc(100% - var(--space-page)*2);max-width:430px}}
@media(prefers-reduced-motion:reduce){.nav-search-expand-enter-active,.nav-search-expand-leave-active{transition:none}}
</style>
