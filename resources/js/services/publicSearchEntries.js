import staticSections from 'virtual:parish-search-content'
import { sacramentOptions } from '../data/sacraments'
import { defaultCategories } from '../data/massSchedules'
import { devotionalResources } from '../data/devotions'
import { additionalDevotions } from '../data/additionalDevotions'
import { rosaryPrayers, rosaryMysteries } from '../data/rosaryData'
import { novenaDays, gaweNinPagsolsol, gozos, gozosResponse } from '../data/novenaPrayers'
import { contentEntry, sectionKey } from './contentSearch'

export { defaultCategories }

export const scheduleEntries = categories => categories.map(category => contentEntry('schedule', category.title,
  [category.tag, ...category.items.map(item => `${item.day}: ${item.time || item.time_display}${item.live ? ' · Livestream' : ''}`)].join(' · '), undefined, {}, 'Mass & confession times'))

const sacramentEntries = sacramentOptions.flatMap(sacrament => {
  const tab = sectionKey(sacrament.name)
  return [
    contentEntry('sacraments', sacrament.title, `${sacrament.description} ${sacrament.advisory}`, undefined, { tab }, `${sacrament.name} guidelines`),
    contentEntry('sacraments', `${sacrament.name}: Required Documents & Prerequisites`, sacrament.requirements.join(' · '), 'sacrament-requirements', { tab }, 'Sacrament requirements'),
    contentEntry('sacraments', `${sacrament.name}: Step-by-Step Pastoral Procedure`, sacrament.process.join(' · '), 'sacrament-procedure', { tab }, 'Sacrament preparation'),
  ]
})
const prayerEntries = [
  ...devotionalResources.map(resource => contentEntry('novenas', resource.title, [resource.subtitle, resource.shortDescription, ...resource.sections.flatMap(section => [section.heading, ...section.lines])].join(' '), `reader-title-${resource.id}`, { resource: resource.id }, resource.category)),
  ...additionalDevotions.map(devotion => contentEntry('novenas', devotion.title, `${devotion.description} ${devotion.schedule}`, undefined, {}, 'Parish devotion')),
  ...Object.values(rosaryPrayers).map(prayer => contentEntry('rosary', prayer.title, prayer.text, prayer.id, {}, 'Rosary prayer')),
  ...Object.entries(rosaryMysteries).flatMap(([key, mystery]) => mystery.mysteries.map(item => contentEntry('rosary', item.title, `${item.meditation} ${item.fruit}`, undefined, { mystery: key }, mystery.title))),
  ...novenaDays.map(day => contentEntry('novena-details', `${day.dayLabel}: ${day.dayTitle}`, [day.subtitle, day.instruction, ...day.paragraphs, day.closingInstruction].join(' '), 'current-prayer-title', { day: day.dayNumber }, 'Pillar novena')),
  contentEntry('novena-details', 'Gawe nin Pagsolsol', gaweNinPagsolsol, 'gawe-nin-pagsolsol', {}, 'Pillar novena'),
  contentEntry('novena-details', 'Gozos', [...gozos, gozosResponse].join(' '), 'gozos', {}, 'Pillar novena'),
]

export function publicSearchEntries({ categories = defaultCategories, announcements = [], directory = {}, settings = {} } = {}) {
  const contactEntries = Object.entries({ address: 'Physical Address', phone: 'Telephone / Mobile', email: 'Electronic Mail', office_hours: 'Office Hours' })
    .filter(([key]) => settings[key]).map(([key, title]) => contentEntry('contact', title, settings[key], undefined, {}, 'Parish contact'))
  const publicDirectory = [
    ...(directory.commissions || []).map(item => contentEntry('ministries', item.name, item.description || '', `modal-title-comm-${item.id || item.slug}`, { commission: item.id || item.slug }, 'Parish commission')),
    ...(directory.ministries || []).map(item => contentEntry('ministries', item.name, [item.description, item.about, item.meeting_schedule, item.meeting_location].filter(Boolean).join(' '), `modal-title-${item.id}`, { ministry: item.id }, 'Parish ministry')),
  ]
  const complete = [...sacramentEntries, ...prayerEntries, ...contactEntries, ...scheduleEntries(categories), ...publicDirectory, ...announcements]
  const titles = new Set(complete.map(entry => `${entry.href.split('?')[0]}:${sectionKey(entry.title)}`))
  return [...complete, ...staticSections.filter(entry => !titles.has(`${entry.href.split('?')[0]}:${sectionKey(entry.title)}`))]
}
