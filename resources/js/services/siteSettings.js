import { computed, reactive } from 'vue'

const initialSettings = typeof window === 'undefined' ? {} : window.__SITE_SETTINGS__ || {}

export const siteSettings = reactive({
  address: '', phone: '', email: '', office_hours: '',
  facebook_url: '', youtube_url: '', tiktok_url: '',
  ...initialSettings,
})

export const parishPhoneHref = computed(() => `tel:${siteSettings.phone.replace(/[^+\d]/g, '')}`)
export const parishGmailUrl = computed(() => `https://mail.google.com/mail/?view=cm&fs=1&to=${encodeURIComponent(siteSettings.email)}&su=Parish%20Inquiry%20-%20Pilar%20Shrine`)
export const officeHoursRows = computed(() => siteSettings.office_hours.split('\n').filter(Boolean).map(line => {
  const separator = line.indexOf(':')
  return separator < 0 ? { days: '', hours: line } : { days: line.slice(0, separator), hours: line.slice(separator + 1).trim() }
}))

export async function refreshSiteSettings() {
  try {
    const response = await fetch('/api/site-settings', { headers: { Accept: 'application/json' }, cache: 'no-store' })
    if (!response.ok) return
    const values = await response.json()
    for (const key of Object.keys(siteSettings)) {
      if (typeof values[key] === 'string') siteSettings[key] = values[key]
    }
  } catch {
    // Continue displaying the values included with the page when offline.
  }
}
