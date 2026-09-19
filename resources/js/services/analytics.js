// Send only approved page/action keys. Form contents, URLs, and credentials stay out of analytics.
const aliases = { events: 'news', announcements: 'news', support: 'donations', donate: 'donations', 'holy-rosary': 'rosary' }
const pages = new Set(['home', 'about', 'schedule', 'sacraments', 'news', 'novenas', 'novena-details', 'rosary', 'ministries', 'store', 'contact', 'forms', 'donations', 'login', 'register', 'complete-profile'])
let lastPage = null
let queue = Promise.resolve()

function eventId() {
  if (typeof crypto.randomUUID === 'function') return crypto.randomUUID()
  // Older browsers and local HTTP addresses still have getRandomValues.
  const bytes = crypto.getRandomValues(new Uint8Array(16))
  bytes[6] = (bytes[6] & 15) | 64
  bytes[8] = (bytes[8] & 63) | 128
  const hex = Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('')
  return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`
}

const currentPage = () => {
  const route = location.hash.replace(/^#\/?/, '').split(/[/?]/)[0] || 'home'
  return aliases[route] || route
}

function send(event_type, page, action = null) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content
  if (!csrf || !pages.has(page)) return
  // Serial requests preserve the visit session when multiple controls are used quickly.
  queue = queue.then(() => fetch('/api/analytics/events', {
    method: 'POST', credentials: 'same-origin', keepalive: true,
    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
    body: JSON.stringify({ event_id: eventId(), event_type, page, action }),
  })).catch(() => {})
}

export function trackPage() {
  const page = currentPage()
  if (page === lastPage) return
  lastPage = page
  send('page_view', page)
}

export function trackClick(event) {
  const element = event.target.closest?.('[data-analytics-action]')
  const control = event.target.closest?.('button, a, input, select')
  // Nested caption toggles stop their card's click handler and do not open an announcement.
  if (control && control !== element && !control.hasAttribute('data-analytics-action')) return
  if (element) send('action', currentPage(), element.dataset.analyticsAction)
}
