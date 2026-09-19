import { nextTick, onMounted, onBeforeUnmount } from 'vue'
import { sectionKey } from './contentSearch'

export const searchParameters = () => new URLSearchParams(location.hash.split('?')[1] || '')

// Interactive pages use the same query on first load, repeated searches, and Back/Forward.
export function useSearchTarget(apply) {
  const sync = () => apply(searchParameters())
  onMounted(sync)
  onMounted(() => {
    window.addEventListener('hashchange', sync)
    window.addEventListener('parish-search-navigate', sync)
  })
  onBeforeUnmount(() => {
    window.removeEventListener('hashchange', sync)
    window.removeEventListener('parish-search-navigate', sync)
  })
  return sync
}

let observer
let expiry
let generation = 0
export function cancelSearchScroll() {
  generation++
  observer?.disconnect()
  clearTimeout(expiry)
  document.querySelectorAll('.search-section-target').forEach(element => element.classList.remove('search-section-target'))
}

export async function scrollToSearchSection() {
  cancelSearchScroll()
  const section = searchParameters().get('section')
  if (!section) return
  const current = generation
  await nextTick()
  if (current !== generation) return
  const findAndScroll = () => {
    if (document.querySelector('.master-page-body [aria-busy="true"]')) return false
    const candidates = [...document.querySelectorAll('.master-page-body h2, .master-page-body h3, .master-page-body h4, [data-search-section], [role="dialog"] h2')]
    const target = candidates.find(element => element.dataset.searchSection === section || element.id === section || sectionKey(element.textContent) === section)
    if (!target) return false
    let details = target.closest('details')
    while (details) { details.open = true; details = details.parentElement.closest('details') }
    target.classList.add('search-section-target')
    const focus = target.matches('details') ? target.querySelector('summary') : target
    if (focus && !focus.hasAttribute('tabindex')) focus.setAttribute('tabindex', '-1')
    focus?.focus({ preventScroll: true })
    target.scrollIntoView({ block: 'start', behavior: 'instant' })
    observer?.disconnect()
    clearTimeout(expiry)
    return true
  }
  if (findAndScroll()) return
  // Schedules and ministries arrive asynchronously. Wait for their actual rendered content.
  observer = new MutationObserver(findAndScroll)
  observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['aria-busy'] })
  expiry = setTimeout(() => observer?.disconnect(), 15000)
}
