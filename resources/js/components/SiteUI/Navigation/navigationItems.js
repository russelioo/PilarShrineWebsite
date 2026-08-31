export const primaryNavigation = [
  { key: 'home', label: 'Home', href: '#/home' },
  { key: 'about', label: 'About', href: '#/about' },
  { key: 'schedule', label: 'Mass Schedule', href: '#/schedule' },
  { key: 'sacraments', label: 'Sacraments', href: '#/sacraments' },
]

export const moreNavigation = [
  { key: 'events', aliases: ['news'], label: 'Events & News', href: '#/events' },
  { key: 'ministries', label: 'Ministries', href: '#/ministries' },
  { key: 'novenas', aliases: ['novena-details'], label: 'Novenas', href: '#/novenas' },
  { key: 'store', label: 'Store', href: '#/store' },
  { key: 'contact', label: 'Contact', href: '#/contact' },
]

export const accountNavigation = [
  { key: 'login', label: 'Sign in', href: '#/login', variant: 'secondary' },
  { key: 'register', label: 'Sign up', href: '#/register', variant: 'primary' },
]

export const isNavigationItemActive = (item, route) =>
  item.key === route || item.aliases?.includes(route)
