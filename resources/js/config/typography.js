/**
 * Pilar Shrine Centralized Typography System (Single Source of Truth)
 *
 * To change the font across the entire website:
 * 1. Change the `primary` font value below (e.g. 'Manrope', 'Inter', 'Plus Jakarta Sans'), OR
 * 2. Change `--font-primary` in `resources/js/components/SiteUI/Theme/designTokens.css`.
 *
 * Both methods are synchronized! No individual Vue component editing required.
 */
export const typography = {
  primary: 'Manrope',
  heading: 'var(--font-primary)',
  body: 'var(--font-primary)',
  fallback: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif",
  weights: {
    regular: 400,
    medium: 500,
    semibold: 600,
    bold: 700,
    extrabold: 800,
  },
  lineHeights: {
    tight: 1.15,
    snug: 1.35,
    normal: 1.5,
    relaxed: 1.7,
    loose: 1.9,
  },
  letterSpacing: {
    tighter: '-0.03em',
    tight: '-0.015em',
    normal: '0em',
    wide: '0.05em',
    wider: '0.1em',
    widest: '0.15em',
  }
}

/**
 * Synchronizes the JavaScript typography configuration to the document's :root CSS variables.
 * @param {typeof typography} config 
 */
export function applyTypographyConfig(config = typography) {
  if (typeof document === 'undefined') return

  const root = document.documentElement
  const fallback = config.fallback || "sans-serif"
  const primaryVal = `'${config.primary}', ${fallback}`
  
  // Heading & Body inherit --font-primary unless explicitly set to an independent custom family
  const headingVal = (!config.heading || config.heading === 'var(--font-primary)' || config.heading === config.primary)
    ? 'var(--font-primary)'
    : (config.heading.includes(',') ? config.heading : `'${config.heading}', ${fallback}`)

  const bodyVal = (!config.body || config.body === 'var(--font-primary)' || config.body === config.primary)
    ? 'var(--font-primary)'
    : (config.body.includes(',') ? config.body : `'${config.body}', ${fallback}`)

  root.style.setProperty('--font-primary', primaryVal)
  root.style.setProperty('--font-heading', headingVal)
  root.style.setProperty('--font-body', bodyVal)

  if (config.weights) {
    if (config.weights.regular) root.style.setProperty('--font-weight-regular', String(config.weights.regular))
    if (config.weights.medium) root.style.setProperty('--font-weight-medium', String(config.weights.medium))
    if (config.weights.semibold) root.style.setProperty('--font-weight-semibold', String(config.weights.semibold))
    if (config.weights.bold) root.style.setProperty('--font-weight-bold', String(config.weights.bold))
    if (config.weights.extrabold) root.style.setProperty('--font-weight-extrabold', String(config.weights.extrabold))
  }

  if (config.lineHeights) {
    if (config.lineHeights.tight) root.style.setProperty('--line-height-tight', String(config.lineHeights.tight))
    if (config.lineHeights.snug) root.style.setProperty('--line-height-snug', String(config.lineHeights.snug))
    if (config.lineHeights.normal) root.style.setProperty('--line-height-normal', String(config.lineHeights.normal))
    if (config.lineHeights.relaxed) root.style.setProperty('--line-height-relaxed', String(config.lineHeights.relaxed))
    if (config.lineHeights.loose) root.style.setProperty('--line-height-loose', String(config.lineHeights.loose))
  }

  if (config.letterSpacing) {
    if (config.letterSpacing.tighter) root.style.setProperty('--letter-spacing-tighter', config.letterSpacing.tighter)
    if (config.letterSpacing.tight) root.style.setProperty('--letter-spacing-tight', config.letterSpacing.tight)
    if (config.letterSpacing.normal) root.style.setProperty('--letter-spacing-normal', config.letterSpacing.normal)
    if (config.letterSpacing.wide) root.style.setProperty('--letter-spacing-wide', config.letterSpacing.wide)
    if (config.letterSpacing.wider) root.style.setProperty('--letter-spacing-wider', config.letterSpacing.wider)
    if (config.letterSpacing.widest) root.style.setProperty('--letter-spacing-widest', config.letterSpacing.widest)
  }
}
