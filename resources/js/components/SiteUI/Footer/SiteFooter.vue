<script setup>
import { primaryNavigation, moreNavigation } from '../Navigation/navigationItems'

const quickLinks = [
  ...primaryNavigation,
  ...moreNavigation,
]
</script>

<template>
  <footer class="site-footer" role="contentinfo">
    <!-- Subtle Marian Pattern Overlay -->
    <div class="footer-marian-pattern" aria-hidden="true"></div>

    <div class="site-container footer-main">
      <div class="footer-grid">
        <!-- COLUMN 1: Shrine Information -->
        <section class="footer-col col-info">
          <div class="brand-block">
            <img
              class="footer-logo"
              :src="'/images/pilar-shrine-logo.png'"
              alt="Our Lady of the Pillar Shrine Logo"
              width="44"
              height="55"
              loading="lazy"
            />
            <div class="brand-text">
              <span class="brand-title">OUR LADY OF THE PILLAR</span>
              <span class="brand-sub">DIOCESAN SHRINE &amp; PARISH</span>
            </div>
          </div>

          <div class="info-list">
            <p class="info-item">
              <span class="icon" aria-hidden="true">⌖</span>
              <span>Binanuahan, Pilar, Sorsogon, 4714 Philippines</span>
            </p>
            <p class="info-item">
              <span class="icon" aria-hidden="true">☎</span>
              <a href="tel:+639468691254" class="info-link">0946-869-1254</a>
            </p>
            <p class="info-item">
              <span class="icon" aria-hidden="true">✉</span>
              <a href="mailto:olppspilarsorsogon@gmail.com" class="info-link">olppspilarsorsogon@gmail.com</a>
            </p>
          </div>

          <!-- Official Social Channels -->
          <div class="footer-socials" aria-label="Official Social Media Channels">
            <a
              href="https://www.facebook.com/PilarShrineSorsogon"
              target="_blank"
              rel="noopener noreferrer"
              class="footer-social-link"
              title="Official Facebook Page"
              aria-label="Facebook"
            >
              <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>
            <a
              href="https://www.youtube.com/@PilarShrineSorsogon"
              target="_blank"
              rel="noopener noreferrer"
              class="footer-social-link"
              title="Official YouTube Channel"
              aria-label="YouTube"
            >
              <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
              </svg>
            </a>
            <a
              href="https://www.tiktok.com/@PilarShrineSorsogon"
              target="_blank"
              rel="noopener noreferrer"
              class="footer-social-link"
              title="Official TikTok Account"
              aria-label="TikTok"
            >
              <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" aria-hidden="true">
                <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 3 15.68a6.34 6.34 0 0 0 10.86 4.49 6.27 6.27 0 0 0 1.84-4.49V8.71a8.21 8.21 0 0 0 4.89 1.6V6.86a4.8 4.8 0 0 1-1-.17z"/>
              </svg>
            </a>
          </div>

          <div class="divider-gold" aria-hidden="true"></div>

          <div class="marian-statement">
            <p>A Marian Shrine.</p>
            <p>A Synodal Community.</p>
            <p>A Church on Mission.</p>
          </div>
        </section>

        <!-- COLUMN 2: Quick Links -->
        <section class="footer-col col-links">
          <h3 class="col-heading">QUICK LINKS</h3>
          <div class="divider-heading" aria-hidden="true"></div>

          <nav class="quick-links-nav" aria-label="Footer quick links">
            <ul class="quick-links-list">
              <li v-for="item in quickLinks" :key="item.key">
                <a :href="item.href" class="quick-link">
                  <span class="bullet" aria-hidden="true">›</span>
                  <span>{{ item.label }}</span>
                </a>
              </li>
            </ul>
          </nav>
        </section>

        <!-- COLUMN 3: Find Us (Google Maps) -->
        <section class="footer-col col-map">
          <h3 class="col-heading">FIND US</h3>
          <div class="divider-heading" aria-hidden="true"></div>

          <div class="map-location-meta">
            <span class="pin-icon" aria-hidden="true">📍</span>
            <div>
              <strong>Our Lady of the Pillar Shrine</strong>
              <span>Binanuahan (Poblacion), Pilar, Sorsogon</span>
            </div>
          </div>

          <div class="map-container">
            <iframe
              title="Google Map location of Our Lady of the Pillar Shrine, Binanuahan, Pilar, Sorsogon"
              src="https://maps.google.com/maps?q=12.9235,123.67635&amp;hl=en&amp;z=16&amp;output=embed"
              width="100%"
              height="130"
              style="border:0;"
              allowfullscreen=""
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>

          <a
            href="https://maps.google.com/?q=12.9235,123.67635"
            target="_blank"
            rel="noopener noreferrer"
            class="map-external-link"
          >
            <span>Open in Google Maps</span>
            <span aria-hidden="true">↗</span>
          </a>
        </section>

        <!-- COLUMN 4: Office Hours -->
        <section class="footer-col col-hours">
          <h3 class="col-heading">OFFICE HOURS</h3>
          <div class="divider-heading" aria-hidden="true"></div>

          <div class="hours-card">
            <div class="hours-item">
              <span class="days-label">Wednesday – Monday</span>
              <span class="time-label">8:00 AM – 5:00 PM</span>
            </div>
            <div class="hours-item">
              <span class="days-label">Sunday</span>
              <span class="time-label">8:30 AM – 12:00 NN</span>
            </div>
          </div>

          <div class="office-notice">
            <span class="notice-icon" aria-hidden="true">ℹ</span>
            <p>
              For Mass intentions, sacraments, and certificate inquiries, kindly visit or reach out during parish office hours.
            </p>
          </div>
        </section>
      </div>
    </div>

    <!-- BOTTOM FOOTER BAR -->
    <div class="footer-bottom">
      <div class="site-container">
        <!-- Devotional Marian Invocation -->
        <div class="devotional-line">
          <span class="marian-cross" aria-hidden="true">✦</span>
          <span class="devotional-latin">ORA PRO NOBIS</span>
          <span class="divider-dot" aria-hidden="true">·</span>
          <span class="devotional-english">OUR LADY OF THE PILLAR, PRAY FOR US</span>
          <span class="marian-cross" aria-hidden="true">✦</span>
        </div>

        <div class="bottom-legal">
          <p class="copyright-text">
            © 2026 Diocesan Shrine and Parish of Our Lady of the Pillar · Diocese of Sorsogon. All rights reserved.
          </p>
          <div class="legal-links">
            <a href="#/about">About Shrine</a>
            <span aria-hidden="true">|</span>
            <a href="#/schedule">Mass Schedule</a>
            <span aria-hidden="true">|</span>
            <a href="#/contact">Contact</a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  position: relative;
  background: linear-gradient(180deg, #071f43 0%, #051630 100%);
  color: #ffffff;
  padding-top: 56px;
  overflow: hidden;
  font-family: inherit;
  border-top: 2px solid rgba(214, 170, 62, 0.3);
}

/* Subtle Marian Watermark Pattern */
.footer-marian-pattern {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle at 20% 20%, rgba(214, 170, 62, 0.04) 0%, transparent 40%),
                    radial-gradient(circle at 80% 80%, rgba(11, 90, 181, 0.08) 0%, transparent 40%);
  pointer-events: none;
}

.footer-main {
  position: relative;
  z-index: 2;
  padding-bottom: 48px;
}

.footer-grid {
  display: grid;
  grid-template-columns: 1.25fr 1.15fr 1.25fr 1fr;
  gap: 36px;
  align-items: start;
}

/* Headings */
.col-heading {
  margin: 0;
  font-family: var(--font-serif, "Cinzel", "Libre Baskerville", Georgia, serif);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #f0cd71;
}

.divider-heading {
  width: 32px;
  height: 2px;
  background: #d6aa3e;
  margin: 8px 0 18px;
  border-radius: 1px;
}

/* Column 1: Shrine Info */
.brand-block {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 18px;
}

.footer-logo {
  width: 44px;
  height: 55px;
  object-fit: contain;
  flex-shrink: 0;
}

.brand-text {
  display: flex;
  flex-direction: column;
  line-height: 1.2;
}

.brand-title {
  font-family: var(--font-serif, "Cinzel", "Libre Baskerville", Georgia, serif);
  font-size: 13px;
  font-weight: 700;
  letter-spacing: 0.06em;
  color: #ffffff;
}

.brand-sub {
  font-size: 9.5px;
  font-weight: 600;
  letter-spacing: 0.12em;
  color: #f0cd71;
  text-transform: uppercase;
  margin-top: 2px;
}

.info-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.info-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin: 0;
  font-size: 12px;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.82);
}

.info-item .icon {
  color: #f0cd71;
  font-size: 13px;
  flex-shrink: 0;
  margin-top: 1px;
}

.info-link {
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  transition: color 0.15s ease;
}

.info-link:hover {
  color: #ffffff;
  text-decoration: underline;
}

/* Social Media Channels */
.footer-socials {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-top: 14px;
}

.footer-social-link {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.16);
  color: #ffffff;
  transition: all 0.2s ease;
  text-decoration: none;
}

.footer-social-link:hover {
  background: #f0cd71;
  border-color: #f0cd71;
  color: #061c3b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.35);
}

.footer-social-link:focus-visible {
  outline: 2px solid #f0cd71;
  outline-offset: 2px;
}

.divider-gold {
  width: 100%;
  height: 1px;
  background: rgba(214, 170, 62, 0.2);
  margin: 18px 0 14px;
}

.marian-statement {
  margin: 0;
}

.marian-statement p {
  margin: 0;
  font-size: 11px;
  font-style: italic;
  line-height: 1.6;
  color: rgba(240, 205, 113, 0.9);
}

/* Column 2: Quick Links (Clean 2-Column List) */
.quick-links-nav {
  width: 100%;
}

.quick-links-list {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px 14px;
  list-style: none;
  padding: 0;
  margin: 0;
}

.quick-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 11.5px;
  font-weight: 500;
  text-decoration: none;
  line-height: 1.4;
  transition: color 0.15s ease, transform 0.15s ease;
}

.quick-link .bullet {
  color: #d6aa3e;
  font-size: 14px;
  font-weight: 700;
  line-height: 1;
}

.quick-link:hover {
  color: #ffffff;
  transform: translateX(3px);
}

/* Column 3: Find Us */
.map-location-meta {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 12px;
}

.pin-icon {
  font-size: 14px;
  flex-shrink: 0;
  margin-top: 1px;
}

.map-location-meta strong {
  display: block;
  font-size: 11.5px;
  color: #ffffff;
}

.map-location-meta span {
  display: block;
  font-size: 10.5px;
  color: rgba(255, 255, 255, 0.72);
}

.map-container {
  width: 100%;
  border-radius: 10px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.14);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
  background: #0d284f;
}

.map-container iframe {
  display: block;
}

.map-external-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  margin-top: 10px;
  font-size: 11px;
  font-weight: 600;
  color: #f0cd71;
  text-decoration: none;
  transition: color 0.15s ease;
}

.map-external-link:hover {
  color: #ffffff;
  text-decoration: underline;
}

/* Column 4: Office Hours */
.hours-card {
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.09);
  border-radius: 10px;
  padding: 14px 16px;
}

.hours-item {
  display: flex;
  flex-direction: column;
}

.days-label {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #f0cd71;
}

.time-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #ffffff;
  margin-top: 2px;
}

.office-notice {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  margin-top: 14px;
  padding: 10px 12px;
  background: rgba(6, 75, 169, 0.18);
  border: 1px solid rgba(6, 75, 169, 0.3);
  border-radius: 8px;
}

.notice-icon {
  font-size: 12px;
  color: #f0cd71;
  flex-shrink: 0;
  margin-top: 1px;
}

.office-notice p {
  margin: 0;
  font-size: 10.5px;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.76);
}

/* Bottom Bar */
.footer-bottom {
  position: relative;
  z-index: 2;
  border-top: 1px solid rgba(214, 170, 62, 0.25);
  background: rgba(3, 13, 29, 0.65);
}

.devotional-line {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 18px 0 10px;
  font-family: var(--font-serif, "Cinzel", "Libre Baskerville", Georgia, serif);
  font-size: 11px;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #f0cd71;
  text-align: center;
  flex-wrap: wrap;
}

.marian-cross {
  font-size: 9px;
  color: #d6aa3e;
}

.divider-dot {
  color: rgba(214, 170, 62, 0.5);
}

.devotional-english {
  color: #ffffff;
  font-weight: 600;
}

.bottom-legal {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
  padding: 8px 0 22px;
  font-size: 11px;
  color: rgba(255, 255, 255, 0.6);
}

.copyright-text {
  margin: 0;
}

.legal-links {
  display: flex;
  align-items: center;
  gap: 8px;
}

.legal-links a {
  color: rgba(255, 255, 255, 0.65);
  text-decoration: none;
  transition: color 0.15s ease;
}

.legal-links a:hover {
  color: #ffffff;
  text-decoration: underline;
}

.legal-links span {
  color: rgba(255, 255, 255, 0.25);
}

/* ==========================================================================
   Responsive Breakpoints
   ========================================================================== */
@media (max-width: 1024px) {
  .footer-grid {
    grid-template-columns: 1fr 1fr;
    gap: 36px 40px;
  }
}

@media (max-width: 640px) {
  .site-footer {
    padding-top: 42px;
  }

  .footer-main {
    padding-bottom: 36px;
  }

  .footer-grid {
    grid-template-columns: 1fr;
    gap: 32px;
  }

  .quick-links-list {
    grid-template-columns: 1fr 1fr;
  }

  .devotional-line {
    font-size: 10px;
    gap: 6px;
  }

  .bottom-legal {
    flex-direction: column;
    text-align: center;
    gap: 8px;
  }
}
</style>
