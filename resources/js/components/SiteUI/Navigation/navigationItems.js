export const serviceNavigation = [
  { key: 'schedule', label: 'Mass & Confession Schedule', href: '#/schedule', description: 'Daily & Sunday mass schedules and confessions' },
  { key: 'sacraments', label: 'Sacraments & Pastoral Care', href: '#/sacraments', description: 'Baptism, Confirmation, Matrimony, and pastoral services' },
  { key: 'ministries', label: 'Shrine Ministries', href: '#/ministries', description: 'Parish organizations and lay pastoral apostolates' },
  { key: 'novenas', aliases: ['novena-details', 'rosary', 'holy-rosary'], label: 'Novenas & Devotions', href: '#/novenas', description: 'Devotions to Nuestra Señora del Pilar' },
  { key: 'news', aliases: ['events'], label: 'News & Announcements', href: '#/news', description: 'Parish bulletin, liturgical calendar, and updates' },
  { key: 'store', label: 'Religious Store', href: '#/store', description: 'Religious articles, candles, and devotional items', soon: true },
  { key: 'forms', label: 'Mass Intention Request', href: '#/forms', description: 'Request prayers and intentions for holy masses online', soon: true },
]

export const primaryNavigation = [
  { key: 'home', label: 'Home', href: '#/home' },
  {
    key: 'services',
    label: 'Parish Services',
    href: '#/schedule',
    aliases: ['schedule', 'sacraments', 'forms', 'ministries', 'novenas', 'novena-details', 'rosary', 'holy-rosary', 'news', 'events'],
    children: serviceNavigation,
  },
  { key: 'about', label: 'About', href: '#/about' },
  { key: 'donations', aliases: ['support', 'donate'], label: 'Support the Shrine', href: '#/donations' },
  { key: 'contact', label: 'Contact', href: '#/contact' },
]

// Maintained for backwards compatibility with SiteFooter and existing consumers
export const moreNavigation = serviceNavigation

export const accountNavigation = [
  { key: 'login', label: 'Sign In', href: '#/login', variant: 'secondary' },
  { key: 'register', label: 'Create Account', href: '#/register', variant: 'primary' },
]

export const isNavigationItemActive = (item, route) => {
  if (!item || !route) return false
  const cleanRoute = String(route).toLowerCase().replace(/^#\/?/, '').split('?')[0].split('/')[0].trim()
  const itemKey = String(item.key).toLowerCase().trim()
  if (itemKey === cleanRoute) return true
  if (Array.isArray(item.aliases)) {
    return item.aliases.some(alias => String(alias).toLowerCase().trim() === cleanRoute)
  }
  return false
}

