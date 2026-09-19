import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import { parse } from 'vue/compiler-sfc'
import { contentEntry } from '../resources/js/services/contentSearch.js'

// Index public page content only. Navigation, account pages and admin data are excluded.
const sources = [
  ['about', 'Pages/Public/AboutPage.vue', 'About the parish'],
  ['about', 'components/ParishHeritage.vue', 'Parish milestones'],
  ['home', 'Pages/Public/HomePage.vue', 'Home'],
  ['contact', 'Pages/Public/ContactPage.vue', 'Parish contact'],
  ['donations', 'Pages/Public/DonationPage.vue', 'Support the shrine'],
  ['novenas', 'Pages/Public/NovenasPage.vue', 'Prayers & devotions'],
  ['novena-details', 'Pages/Public/NovenaDetailsPage.vue', 'Pillar novena'],
  ['rosary', 'Pages/Public/RosaryPage.vue', 'Holy Rosary'],
]
const excludedTags = new Set(['nav', 'form', 'button', 'svg', 'script', 'style'])
const plainText = node => node.type === 2 ? node.content : node.tag === 'br' ? ' ' : (node.children || []).map(plainText).join('')
const hasDynamicText = node => node.type === 5 || (node.children || []).some(hasDynamicText)

export function extractSections(source, page, label) {
  const root = parse(source).descriptor.template?.ast
  const records = []
  let current = null
  function walk(node) {
    if (node.type !== 1) { for (const child of node.children || []) walk(child); return }
    if (excludedTags.has(node.tag) || /^[A-Z]/.test(node.tag)) return
    if (node.props.some(prop => prop.name === 'for' || (prop.name === 'role' && prop.value?.content === 'dialog') || (prop.name === 'aria-hidden' && prop.value?.content === 'true'))) return
    if (/^h[2-4]$/.test(node.tag)) {
      current = null
      const title = plainText(node).replace(/\s+/g, ' ').trim()
      if (title && !hasDynamicText(node)) {
        current = contentEntry(page, title, '', undefined, {}, label)
        records.push(current)
      }
      return
    }
    if (current && ['p', 'li', 'blockquote', 'dd'].includes(node.tag)) {
      current.text += ` ${plainText(node).replace(/\s+/g, ' ').trim()}`
      return
    }
    for (const child of node.children || []) walk(child)
  }
  if (root) walk(root)
  return records.map(record => ({ ...record, text: record.text.trim() }))
}

export default function searchContentPlugin() {
  const moduleId = 'virtual:parish-search-content'
  const resolvedId = `\0${moduleId}`
  let files = []
  return {
    name: 'parish-content-search',
    configResolved(config) { files = sources.map(([page, file, label]) => [page, resolve(config.root, 'resources/js', file), label]) },
    resolveId(id) { if (id === moduleId) return resolvedId },
    load(id) {
      if (id !== resolvedId) return
      const entries = files.flatMap(([page, file, label]) => {
        this.addWatchFile(file)
        return extractSections(readFileSync(file, 'utf8'), page, label)
      })
      const unique = [...new Map(entries.map(entry => [entry.id, entry])).values()]
      return `export default ${JSON.stringify(unique)}`
    },
    handleHotUpdate({ file, server }) {
      if (!files.some(([, path]) => path === file)) return
      const module = server.moduleGraph.getModuleById(resolvedId)
      if (module) server.moduleGraph.invalidateModule(module)
    },
  }
}
