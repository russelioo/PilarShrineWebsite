/**
 * Philippine Standard Geographic Code (PSGC) API Service
 * Endpoint: https://psgc.gitlab.io/api/
 *
 * Provides real-time cascading administrative lookup with in-memory caching
 * and built-in offline fallbacks for high availability and offline resilience.
 */

const BASE_URL = 'https://psgc.gitlab.io/api'
const memoryCache = new Map()

// Built-in fallbacks for standard Philippine regions
export const FALLBACK_REGIONS = [
  { code: '010000000', name: 'Ilocos Region (Region I)', shortName: 'Region I' },
  { code: '020000000', name: 'Cagayan Valley (Region II)', shortName: 'Region II' },
  { code: '030000000', name: 'Central Luzon (Region III)', shortName: 'Region III' },
  { code: '040000000', name: 'CALABARZON (Region IV-A)', shortName: 'Region IV-A' },
  { code: '170000000', name: 'MIMAROPA Region', shortName: 'MIMAROPA' },
  { code: '050000000', name: 'Bicol Region (Region V)', shortName: 'Region V' },
  { code: '060000000', name: 'Western Visayas (Region VI)', shortName: 'Region VI' },
  { code: '070000000', name: 'Central Visayas (Region VII)', shortName: 'Region VII' },
  { code: '080000000', name: 'Eastern Visayas (Region VIII)', shortName: 'Region VIII' },
  { code: '090000000', name: 'Zamboanga Peninsula (Region IX)', shortName: 'Region IX' },
  { code: '100000000', name: 'Northern Mindanao (Region X)', shortName: 'Region X' },
  { code: '110000000', name: 'Davao Region (Region XI)', shortName: 'Region XI' },
  { code: '120000000', name: 'SOCCSKSARGEN (Region XII)', shortName: 'Region XII' },
  { code: '130000000', name: 'National Capital Region (NCR)', shortName: 'NCR' },
  { code: '140000000', name: 'Cordillera Administrative Region (CAR)', shortName: 'CAR' },
  { code: '160000000', name: 'Caraga (Region XIII)', shortName: 'Region XIII' },
  { code: '150000000', name: 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)', shortName: 'BARMM' },
]

export const FALLBACK_BICOL_PROVINCES = [
  { code: '050500000', name: 'Albay' },
  { code: '051600000', name: 'Camarines Norte' },
  { code: '051700000', name: 'Camarines Sur' },
  { code: '052000000', name: 'Catanduanes' },
  { code: '054100000', name: 'Masbate' },
  { code: '056200000', name: 'Sorsogon' },
]

export const FALLBACK_SORSOGON_MUNICIPALITIES = [
  { code: '056202000', name: 'Barcelona' },
  { code: '056203000', name: 'Bulan' },
  { code: '056204000', name: 'Bulusan' },
  { code: '056205000', name: 'Casiguran' },
  { code: '056206000', name: 'Castilla' },
  { code: '056207000', name: 'Donsol' },
  { code: '056208000', name: 'Gubat' },
  { code: '056209000', name: 'Irosin' },
  { code: '056210000', name: 'Juban' },
  { code: '056211000', name: 'Magallanes' },
  { code: '056212000', name: 'Matnog' },
  { code: '056213000', name: 'Pilar' },
  { code: '056214000', name: 'Prieto Diaz' },
  { code: '056215000', name: 'Santa Magdalena' },
  { code: '056216000', name: 'City of Sorsogon' },
]

export const FALLBACK_PILAR_BARANGAYS = [
  'Abucay', 'Agas', 'Antipolo', 'Bagacay', 'Bayas', 'Bayawas', 'Binanuahan', 'Cabiguan',
  'Cagdongon', 'Calateo', 'Comapo-capo', 'Danlog', 'Dao', 'Dapdap', 'Del Rosario',
  'Esmeralda', 'Esperanza', 'Ginablan', 'Guiron', 'Inang', 'Inapugan', 'Leona', 'Lipason',
  'Lourdes', 'Lubiano', 'Lumbang', 'Lungib', 'Mabanate', 'Malbog', 'Marifosque (Pob.)',
  'Mercedes', 'Migabod', 'Naspi', 'Palanas', 'Pangpang', 'Pinagsalog', 'Pineda', 'Poctol',
  'Pudo', 'Putiao', 'Sacnangan', 'Salvacion', 'San Antonio (Millabas)', 'San Antonio (Sapa)',
  'San Jose', 'San Rafael', 'Santa Fe'
].map((name, i) => ({
  code: '056213' + String(i + 1).padStart(3, '0'),
  name,
}))

export const FALLBACK_NCR_CITIES = [
  { code: '133900000', name: 'City of Manila' },
  { code: '137401000', name: 'City of Mandaluyong' },
  { code: '137402000', name: 'City of Marikina' },
  { code: '137403000', name: 'City of Pasig' },
  { code: '137404000', name: 'Quezon City' },
  { code: '137405000', name: 'City of San Juan' },
  { code: '137501000', name: 'City of Caloocan' },
  { code: '137502000', name: 'City of Malabon' },
  { code: '137503000', name: 'City of Navotas' },
  { code: '137504000', name: 'City of Valenzuela' },
  { code: '137601000', name: 'City of Las Piñas' },
  { code: '137602000', name: 'City of Makati' },
  { code: '137603000', name: 'City of Muntinlupa' },
  { code: '137604000', name: 'City of Parañaque' },
  { code: '137605000', name: 'Pasay City' },
  { code: '137606000', name: 'Pateros' },
  { code: '137607000', name: 'City of Taguig' },
]

/**
 * Helper to fetch with timeout and error tolerance
 */
async function fetchJsonSafe(url, timeoutMs = 4000) {
  if (memoryCache.has(url)) {
    return memoryCache.get(url)
  }

  try {
    const controller = new AbortController()
    const timer = setTimeout(() => controller.abort(), timeoutMs)

    const res = await fetch(url, { signal: controller.signal })
    clearTimeout(timer)

    if (!res.ok) throw new Error('HTTP ' + res.status)
    const data = await res.json()
    memoryCache.set(url, data)
    return data
  } catch (err) {
    console.warn('[PSGC] Failed to fetch from ' + url + ', using fallback:', err?.message)
    return null
  }
}

/**
 * Fetch all Philippine regions
 */
export async function getRegions() {
  const data = await fetchJsonSafe(BASE_URL + '/regions.json')
  if (Array.isArray(data) && data.length > 0) {
    return data.map((r) => ({
      code: r.code,
      name: r.regionName ? (r.name + ' (' + r.regionName + ')') : r.name,
      rawName: r.name,
    }))
  }
  return FALLBACK_REGIONS.map((r) => ({
    code: r.code,
    name: r.name,
    rawName: r.name,
  }))
}

/**
 * Fetch provinces for a given region.
 * Handles NCR which has 0 provinces.
 */
export async function getProvinces(regionCode) {
  if (!regionCode) return []
  if (regionCode === '130000000') return [] // NCR has no provinces

  const data = await fetchJsonSafe(BASE_URL + '/regions/' + regionCode + '/provinces.json')
  if (Array.isArray(data)) {
    return data.map((p) => ({
      code: p.code,
      name: p.name,
    }))
  }

  // Fallback for Region V
  if (regionCode === '050000000') {
    return FALLBACK_BICOL_PROVINCES
  }

  return []
}

/**
 * Fetch cities and municipalities.
 * If provinceCode is provided, fetches under province.
 * If NCR or independent city (no province), fetches under region.
 */
export async function getCitiesMunicipalities(regionCode, provinceCode) {
  if (!regionCode && !provinceCode) return []

  let url = null
  if (provinceCode && provinceCode !== 'NCR_NO_PROVINCE') {
    url = BASE_URL + '/provinces/' + provinceCode + '/cities-municipalities.json'
  } else if (regionCode) {
    url = BASE_URL + '/regions/' + regionCode + '/cities-municipalities.json'
  }

  const data = url ? await fetchJsonSafe(url) : null
  if (Array.isArray(data) && data.length > 0) {
    return data.map((c) => ({
      code: c.code,
      name: c.name,
      isCity: c.isCity ?? false,
    }))
  }

  // Fallbacks
  if (provinceCode === '056200000') {
    return FALLBACK_SORSOGON_MUNICIPALITIES
  }
  if (regionCode === '130000000') {
    return FALLBACK_NCR_CITIES
  }

  return []
}

/**
 * Fetch barangays for a given city or municipality code.
 */
export async function getBarangays(cityMunCode) {
  if (!cityMunCode) return []

  const data = await fetchJsonSafe(BASE_URL + '/cities-municipalities/' + cityMunCode + '/barangays.json')
  if (Array.isArray(data) && data.length > 0) {
    return data.map((b) => ({
      code: b.code,
      name: b.name,
    }))
  }

  // Fallback for Pilar
  if (cityMunCode === '056213000') {
    return FALLBACK_PILAR_BARANGAYS
  }

  return []
}
