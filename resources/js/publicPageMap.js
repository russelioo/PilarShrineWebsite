import HomePage from './Pages/Public/HomePage.vue'
import AboutPage from './Pages/Public/AboutPage.vue'
import MassSchedulePage from './Pages/Public/MassSchedulePage.vue'
import SacramentsPage from './Pages/Public/SacramentsPage.vue'
import NewsPage from './Pages/Public/NewsPage.vue'
import NovenasPage from './Pages/Public/NovenasPage.vue'
import NovenaDetailsPage from './Pages/Public/NovenaDetailsPage.vue'
import MinistriesPage from './Pages/Public/MinistriesPage.vue'
import StorePage from './Pages/Public/StorePage.vue'
import ContactPage from './Pages/Public/ContactPage.vue'
import FormsPage from './Pages/Public/FormsPage.vue'

export const publicPages = {
  home: {
    component: HomePage,
    heroType: 'video',
    videoSrc: '/videos/pilar-shrine-hero-h264.mp4',
    videoPoster: '/images/church-interior.png',
    eyebrow: 'Welcome to the',
    title: 'Diocesan Shrine and Parish of<br>Our Lady of the Pillar',
    description: 'Home of the Episcopally Crowned Image of Our Lady of the Pillar<br>Patroness of the Town of Pilar, Province of Sorsogon',
    hasRule: true,
    heroActions: [
      { label: '▣ View mass schedule', href: '#/schedule', variant: 'primary' },
      { label: '♙ Sacrament requirements', href: '#/sacraments', variant: 'secondary' },
    ],
  },
  about: {
    component: AboutPage,
    heroType: 'image',
    image: '/images/church-interior.png',
    eyebrow: 'Diocesan Shrine and Parish of Our Lady of the Pillar',
    title: 'History & Heritage',
    description: 'A legacy of faith, devotion, and community spanning over a century and a half in Pilar, Sorsogon.',
    hasRule: true,
    heroActions: [
      { label: '▣ View Mass Schedule', href: '#/schedule', variant: 'primary' },
      { label: '♙ Sacrament Guidelines', href: '#/sacraments', variant: 'secondary' },
    ],
  },
  schedule: {
    component: MassSchedulePage,
    heroType: 'image',
    image: '/images/pilar-mass-schedule.jpg',
    eyebrow: 'Liturgical Services & Worship',
    title: 'Mass & Confession Schedule',
    description: 'Join our parish community in the celebration of the Holy Eucharist and the Sacrament of Reconciliation at the Diocesan Shrine of Our Lady of the Pillar.',
    hasRule: true,
    heroActions: [
      { label: '✍ Request Mass Intention', href: '#/forms', variant: 'primary' },
      { label: '♙ Sacrament Services', href: '#/sacraments', variant: 'secondary' },
    ],
  },
  sacraments: {
    component: SacramentsPage,
    heroType: 'image',
    image: '/images/pilar-shrine-sanctuary.jpg',
    eyebrow: 'Parish Pastoral Care',
    title: 'Sacraments & Pastoral Services',
    description: 'Sacred signs of grace instituted by Christ to strengthen faith, nurture Christian life, and accompany every step of our spiritual journey.',
    hasRule: true,
    heroActions: [
      { label: '▣ View Mass Schedule', href: '#/schedule', variant: 'primary' },
      { label: '✍ Mass Intention Request', href: '#/forms', variant: 'secondary' },
    ],
  },
  news: {
    component: NewsPage,
    heroType: 'image',
    image: 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=1400&q=80',
    eyebrow: 'Parish Bulletin & Events',
    title: 'News & Announcements',
    description: 'Stay updated with liturgical celebrations, shrine activities, parish news, and pastoral announcements from the Diocesan Shrine of Our Lady of the Pillar.',
    hasRule: true,
    heroActions: [
      { label: '📅 Liturgical Schedule', href: '#/schedule', variant: 'primary' },
      { label: '♙ Sacramental Notices', href: '#/sacraments', variant: 'secondary' },
    ],
  },
  novenas: {
    component: NovenasPage,
    heroType: 'image',
    image: '/images/pilar-shrine-sanctuary.jpg',
    eyebrow: 'Patronal Devotions',
    title: 'Novena to Our Lady of the Pillar',
    description: 'Join us in prayer and supplication to Nuestra Señora del Pilar, seeking her motherly intercession and protection.',
    hasRule: true,
    heroActions: [
      { label: '📖 Novena Schedule & Prayers', href: '#/novena-details', variant: 'primary' },
      { label: '▣ Mass Schedule', href: '#/schedule', variant: 'secondary' },
    ],
  },
  'novena-details': {
    component: NovenaDetailsPage,
    heroType: 'image',
    image: '/images/pilar-shrine-sanctuary.jpg',
    eyebrow: 'Annual Fiesta Novena',
    title: 'Nine-Day Novena & Feast Details',
    description: 'Complete schedule of the 9-day novena prayers leading to the Solemnity and Feast of Nuestra Señora del Pilar on October 12.',
    hasRule: true,
    heroActions: [
      { label: '▣ View Mass Schedule', href: '#/schedule', variant: 'primary' },
      { label: '✍ Request Mass Intention', href: '#/forms', variant: 'secondary' },
    ],
  },
  ministries: {
    component: MinistriesPage,
    heroType: 'image',
    image: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1400&q=80',
    eyebrow: 'Parish Apostolates & Service',
    title: 'Shrine Ministries & Organizations',
    description: 'Discover the different pastoral, liturgical, and service ministries serving God and our parish community in Pilar, Sorsogon.',
    hasRule: true,
    heroActions: [
      { label: '✉ Contact Parish Office', href: '#/contact', variant: 'primary' },
      { label: '▣ Mass Schedule', href: '#/schedule', variant: 'secondary' },
    ],
  },
  store: {
    component: StorePage,
    heroType: 'image',
    image: 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=1400&q=85',
    eyebrow: 'Parish Merchandise & Religious Articles',
    title: 'Support Our Shrine<br>Through Meaningful Items',
    description: 'Bring home a sacred reminder of faith, devotion, and prayer while supporting the preservation of our diocesan shrine.',
    hasRule: true,
    heroActions: [
      { label: '✉ Inquire at Office', href: '#/contact', variant: 'primary' },
    ],
  },
  contact: {
    component: ContactPage,
    heroType: 'image',
    image: '/images/pilar-shrine-sanctuary.jpg',
    eyebrow: 'Parish Inquiries & Visitation',
    title: 'Contact & Location',
    description: 'Connect with the parish office, inquire about certificates and sacraments, or plan your pilgrimage to the Diocesan Shrine of Our Lady of the Pillar.',
    hasRule: true,
    heroActions: [
      { label: '▣ Mass Schedule', href: '#/schedule', variant: 'primary' },
      { label: '✍ Mass Intentions', href: '#/forms', variant: 'secondary' },
    ],
  },
  forms: {
    component: FormsPage,
    heroType: 'image',
    image: 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=1400&q=85',
    eyebrow: 'Parish Liturgical Services',
    title: 'Online Mass<br>Intention Request',
    description: 'Submit your Mass intention online. Our parish priests and community will pray for you and your loved ones.',
    hasRule: true,
    heroActions: [
      { label: '▣ View Mass Schedule', href: '#/schedule', variant: 'primary' },
      { label: '✉ Contact Office', href: '#/contact', variant: 'secondary' },
    ],
  },
}

// Route aliases
export const routeAliases = {
  events: 'news',
}

export function getPublicPage(routeKey) {
  const normalizedKey = routeAliases[routeKey] || routeKey
  return publicPages[normalizedKey] || null
}

