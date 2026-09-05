export const primaryNavigation = [
  { key: 'home', label: 'Home', href: '#/home' },
  { key: 'about', label: 'About', href: '#/about' },
  { key: 'schedule', label: 'Mass Schedule', href: '#/schedule' },
  { key: 'sacraments', label: 'Sacraments', href: '#/sacraments' },
]

export const moreNavigation = [
  { key: 'events', aliases: ['news'], label: 'Events & News', href: '#/events' },
  { key: 'ministries', label: 'Ministries', href: '#/ministries' },
  { key: 'novenas', aliases: ['novena-details'], label: 'Novenas & Devotions', href: '#/novenas' },
  { key: 'store', label: 'Store', href: '#/store' },
  { key: 'contact', aliases: ['forms'], label: 'Contact', href: '#/contact' },
]

export const accountNavigation = [
  { key: 'login', label: 'Sign in', href: '#/login', variant: 'secondary' },
  { key: 'register', label: 'Sign up', href: '#/register', variant: 'primary' },
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
