export const normalizeSearch = value => String(value || '').normalize('NFKD').replace(/\p{M}/gu, '').toLowerCase().replace(/[^\p{L}\p{N}]+/gu, ' ').trim()
export const sectionKey = value => normalizeSearch(value).replace(/\s+/g, '-')
export const contentLink = (page, section, params = {}) => `#/${page}?${new URLSearchParams({ ...params, section })}`

export function contentEntry(page, title, text, section = sectionKey(title), params = {}, kind = 'Section') {
  return { id: `${page}-${section}-${JSON.stringify(params)}`, title, text: String(text || '').replace(/\s+/g, ' ').trim(), href: contentLink(page, section, params), kind }
}

function excerpt(text, query) {
  if (text.length <= 170) return text
  // Find the matching passage, including matches deep inside a prayer or article.
  const words = normalizeSearch(query).split(' ')
  const tokens = [...text.matchAll(/[\p{L}\p{N}]+/gu)]
  const match = tokens.find(token => words.some(word => normalizeSearch(token[0]).includes(word)))
  let start = Math.max(0, (match?.index || 0) - 45)
  if (start) start = text.indexOf(' ', start) + 1
  const end = Math.min(text.length, start + 170)
  return `${start ? '…' : ''}${text.slice(start, end).trim()}${end < text.length ? '…' : ''}`
}

export function searchContent(entries, query, limit = 8) {
  const term = normalizeSearch(query)
  if (term.length < 2) return []
  const words = term.split(' ')
  return entries.map(entry => {
    const title = normalizeSearch(entry.title)
    const text = normalizeSearch(entry.text)
    if (!words.every(word => `${title} ${text}`.includes(word))) return null
    const rank = title === term ? 0 : title.startsWith(term) ? 1 : title.includes(term) ? 2 : words.every(word => title.includes(word)) ? 3 : text.includes(term) ? 4 : 5
    return { ...entry, rank, description: excerpt(entry.text, query) }
  }).filter(Boolean).sort((a, b) => a.rank - b.rank).slice(0, limit)
}
