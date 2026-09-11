<script setup>
import { ref, computed } from 'vue'

const parishEmail = 'olppspilarsorsogon@gmail.com'
const isCopied = ref(false)
const selectedTopic = ref('General Parish Inquiry')

const topics = [
  'General Parish Inquiry',
  'Sacraments & Certificate Request',
  'Mass Intention Follow-up',
  'Pilgrimage & Group Visitation',
  'Donation & Shrine Support',
  'Pastoral Care & Sick Call Request',
]

const subjectParam = computed(() => {
  return encodeURIComponent(`${selectedTopic.value} - Pilar Shrine`)
})

const gmailUrl = computed(() => {
  return `https://mail.google.com/mail/?view=cm&fs=1&to=${parishEmail}&su=${subjectParam.value}`
})

const yahooUrl = computed(() => {
  return `https://compose.mail.yahoo.com/?to=${parishEmail}&subj=${subjectParam.value}`
})

const outlookUrl = computed(() => {
  return `https://outlook.live.com/mail/0/deeplink/compose?to=${parishEmail}&subject=${subjectParam.value}`
})

const defaultMailUrl = computed(() => {
  return `mailto:${parishEmail}?subject=${subjectParam.value}`
})

const copyEmail = async () => {
  try {
    if (navigator.clipboard && navigator.clipboard.writeText) {
      await navigator.clipboard.writeText(parishEmail)
    } else {
      const el = document.createElement('textarea')
      el.value = parishEmail
      el.setAttribute('readonly', '')
      el.style.position = 'absolute'
      el.style.left = '-9999px'
      document.body.appendChild(el)
      el.select()
      document.execCommand('copy')
      document.body.removeChild(el)
    }
    isCopied.value = true
    setTimeout(() => {
      isCopied.value = false
    }, 2500)
  } catch (err) {
    console.error('Failed to copy email:', err)
  }
}
</script>

<template>
  <div class="contact-page-wrap">
    <div class="page-width py-12">
      <div class="contact-grid">
        <!-- Contact Information Side -->
        <div class="contact-info-panel">
          <div class="contact-header">
            <span class="contact-eyebrow">Get in Touch</span>
            <h2 class="contact-title">Parish Office & Shrine Administration</h2>
            <div class="gold-rule left">✣</div>
            <p class="contact-desc">
              We welcome pilgrims, parishioners, and visitors to the Diocesan Shrine and Parish of Our Lady of the Pillar. Reach out to us for sacramental services, mass intentions, pilgrimage arrangements, or pastoral inquiries.
            </p>
          </div>

          <div class="contact-card-list">
            <div class="contact-card">
              <div class="card-icon" aria-hidden="true">⌖</div>
              <div class="card-content">
                <h3>Physical Address</h3>
                <p>Binanuahan, Pilar, Sorsogon, 4714 Philippines</p>
                <span class="sub-text">Diocese of Sorsogon</span>
              </div>
            </div>

            <div class="contact-card">
              <div class="card-icon" aria-hidden="true">☏</div>
              <div class="card-content">
                <h3>Telephone / Mobile</h3>
                <p><a href="tel:+639468691254">0946-869-1254</a></p>
                <span class="sub-text">Available during parish office hours</span>
              </div>
            </div>

            <div class="contact-card">
              <div class="card-icon" aria-hidden="true">✉</div>
              <div class="card-content">
                <h3>Electronic Mail</h3>
                <p>
                  <a
                    :href="gmailUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="email-main-link"
                    title="Click to compose directly in Gmail"
                  >
                    {{ parishEmail }}
                  </a>
                </p>
                <div class="card-email-pills">
                  <a :href="gmailUrl" target="_blank" rel="noopener noreferrer" class="provider-chip gmail-chip" title="Compose in Gmail">
                    <span>Gmail</span>
                    <span class="chip-arrow" aria-hidden="true">↗</span>
                  </a>
                  <a :href="yahooUrl" target="_blank" rel="noopener noreferrer" class="provider-chip yahoo-chip" title="Compose in Yahoo Mail">
                    <span>Yahoo</span>
                    <span class="chip-arrow" aria-hidden="true">↗</span>
                  </a>
                  <button type="button" @click="copyEmail" class="provider-chip copy-chip" :title="isCopied ? 'Copied' : 'Copy address'">
                    <span>{{ isCopied ? 'Copied! ✓' : 'Copy 📋' }}</span>
                  </button>
                </div>
                <span class="sub-text">Direct email redirection for inquiries, records, and administrative communications</span>
              </div>
            </div>

            <div class="contact-card">
              <div class="card-icon" aria-hidden="true">◷</div>
              <div class="card-content">
                <h3>Office Hours</h3>
                <p><strong>Monday, Wednesday – Saturday</strong>: 8:00 AM – 11:30 AM | 1:00 PM – 5:00 PM</p>
                <p><strong>Sunday</strong>: 8:30 AM – 12:00 NN</p>
                <span class="sub-text text-amber">Tuesday: Closed for Day Off and During Holidays</span>
              </div>
            </div>

            <div class="contact-card">
              <div class="card-icon" aria-hidden="true">🌐</div>
              <div class="card-content">
                <h3>Official Social Media</h3>
                <div class="contact-social-channels">
                  <p>
                    <a href="https://www.facebook.com/PilarShrineSorsogon" target="_blank" rel="noopener noreferrer">
                      Facebook: @PilarShrineSorsogon
                    </a>
                  </p>
                  <p>
                    <a href="https://www.youtube.com/@PilarShrineSorsogon" target="_blank" rel="noopener noreferrer">
                      YouTube: @PilarShrineSorsogon
                    </a>
                  </p>
                  <p>
                    <a href="https://www.tiktok.com/@PilarShrineSorsogon" target="_blank" rel="noopener noreferrer">
                      TikTok: @PilarShrineSorsogon
                    </a>
                  </p>
                </div>
                <span class="sub-text">Livestreams, novena broadcasts, video reflections & announcements</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Direct Email Redirection Hub Side -->
        <div class="contact-form-panel">
          <div class="form-container-card email-hub-card">
            <div class="form-card-header">
              <div class="email-hub-tag">
                <span class="hub-spark" aria-hidden="true">✦</span>
                <span>Direct Email Redirection</span>
              </div>
              <h3 class="email-hub-title">Email Our Parish Office</h3>
              <p>
                Click your preferred email service below to be directed straight to your compose screen addressed to
                <strong class="email-highlight">{{ parishEmail }}</strong>.
              </p>
            </div>

            <!-- Optional Topic / Subject Selection -->
            <div class="topic-selector-box">
              <label for="topic-select" class="topic-label">
                <span class="topic-label-text">Select Inquiry Topic</span>
                <span class="topic-label-hint">(Pre-fills subject line)</span>
              </label>
              <div class="topic-select-wrap">
                <select id="topic-select" v-model="selectedTopic" class="topic-select">
                  <option v-for="t in topics" :key="t" :value="t">{{ t }}</option>
                </select>
              </div>
            </div>

            <!-- Email Providers List -->
            <div class="email-providers-stack">
              <!-- 1. Google Gmail (Recommended) -->
              <a
                :href="gmailUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="provider-btn-card gmail-provider"
                title="Compose directly in Google Gmail"
              >
                <div class="provider-brand-badge gmail-badge">
                  <svg viewBox="0 0 24 24" width="28" height="28" fill="none" aria-hidden="true">
                    <path d="M22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6zm-2 0l-8 5-8-5h16zm0 12H4V8l8 5 8-5v10z" fill="#EA4335"/>
                  </svg>
                </div>
                <div class="provider-info">
                  <div class="provider-name-row">
                    <span class="provider-title">Google Gmail</span>
                    <span class="recommended-badge">Recommended</span>
                  </div>
                  <span class="provider-desc">Directly opens Gmail compose in a new tab or app</span>
                </div>
                <div class="provider-action">
                  <span>Open Gmail</span>
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </div>
              </a>

              <!-- 2. Yahoo Mail -->
              <a
                :href="yahooUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="provider-btn-card yahoo-provider"
                title="Compose in Yahoo Mail"
              >
                <div class="provider-brand-badge yahoo-badge">
                  <svg viewBox="0 0 24 24" width="28" height="28" fill="none" aria-hidden="true">
                    <rect width="24" height="24" rx="6" fill="#6001D2"/>
                    <path d="M7 6.5L10.5 13V18H13.5V13L17 6.5H13.8L12 10.5L10.2 6.5H7ZM18.5 15.5C18.5 16.3 17.8 17 17 17C16.2 17 15.5 16.3 15.5 15.5C15.5 14.7 16.2 14 17 14C17.8 14 18.5 14.7 18.5 15.5Z" fill="white"/>
                  </svg>
                </div>
                <div class="provider-info">
                  <div class="provider-name-row">
                    <span class="provider-title">Yahoo Mail</span>
                  </div>
                  <span class="provider-desc">Direct compose in Yahoo Mail web client</span>
                </div>
                <div class="provider-action">
                  <span>Open Yahoo</span>
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </div>
              </a>

              <!-- 3. Outlook / Hotmail -->
              <a
                :href="outlookUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="provider-btn-card outlook-provider"
                title="Compose in Microsoft Outlook"
              >
                <div class="provider-brand-badge outlook-badge">
                  <svg viewBox="0 0 24 24" width="28" height="28" fill="none" aria-hidden="true">
                    <rect width="24" height="24" rx="6" fill="#0078D4"/>
                    <path d="M7 6H17C17.55 6 18 6.45 18 7V17C18 17.55 17.55 18 17 18H7C6.45 18 6 17.55 6 17V7C6 6.45 6.45 6 7 6Z" stroke="white" stroke-width="1.5"/>
                    <path d="M6 7.5L12 12L18 7.5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </div>
                <div class="provider-info">
                  <div class="provider-name-row">
                    <span class="provider-title">Outlook / Hotmail</span>
                  </div>
                  <span class="provider-desc">Direct compose in Microsoft Outlook.com</span>
                </div>
                <div class="provider-action">
                  <span>Open Outlook</span>
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </div>
              </a>

              <!-- 4. Default Mail Client (mailto) -->
              <a
                :href="defaultMailUrl"
                class="provider-btn-card default-provider"
                title="Open system default email client"
              >
                <div class="provider-brand-badge default-badge">
                  <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                    <polyline points="22,6 12,13 2,6"></polyline>
                  </svg>
                </div>
                <div class="provider-info">
                  <div class="provider-name-row">
                    <span class="provider-title">Default Email App</span>
                  </div>
                  <span class="provider-desc">Open Apple Mail, Windows Mail, Thunderbird, etc.</span>
                </div>
                <div class="provider-action">
                  <span>Open App</span>
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                  </svg>
                </div>
              </a>
            </div>

            <!-- Copy Address Bar -->
            <div class="copy-address-container">
              <div class="copy-helper-label">Or copy address manually:</div>
              <div class="copy-address-bar">
                <span class="copy-email-code">{{ parishEmail }}</span>
                <button
                  type="button"
                  class="copy-action-btn"
                  :class="{ copied: isCopied }"
                  @click="copyEmail"
                >
                  <svg v-if="!isCopied" viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                  </svg>
                  <svg v-else viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  <span>{{ isCopied ? 'Copied to Clipboard! ✓' : 'Copy Email' }}</span>
                </button>
              </div>
            </div>

            <!-- Secretariat Office Notice -->
            <div class="secretariat-notice">
              <span class="notice-icon" aria-hidden="true">ℹ</span>
              <p>
                Emails are attended to during parish office hours (Mon, Wed–Sat 8:00 AM – 5:00 PM; Sun 8:30 AM – 12:00 NN). For emergency sick calls or viaticum, call directly at <a href="tel:+639468691254">0946-869-1254</a>.
              </p>
            </div>

            <!-- Quick Services Footer -->
            <div class="quick-links-footer">
              <span class="quick-title">Quick Services:</span>
              <div class="quick-tags">
                <a href="#/schedule" class="quick-tag">📅 Mass Schedule</a>
                <a href="#/forms" class="quick-tag">✍ Mass Intentions</a>
                <a href="#/sacraments" class="quick-tag">🕊 Sacraments</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.contact-page-wrap {
  background: var(--bg-soft, #f4f8fc);
  color: var(--text, #1c2738);
}

.py-12 {
  padding-top: 3.5rem;
  padding-bottom: 4.5rem;
}

.contact-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2.5rem;
}

@media (min-width: 960px) {
  .contact-grid {
    grid-template-columns: 1fr 1.15fr;
    gap: 3.5rem;
    align-items: start;
  }
}

/* Information Panel */
.contact-eyebrow {
  display: inline-block;
  font-size: 0.8125rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--gold, #c5a059);
  margin-bottom: 0.5rem;
}

.contact-title {
  font-family: var(--font-serif, "Cinzel", "Playfair Display", Georgia, serif);
  font-size: clamp(1.6rem, 3vw, 2.25rem);
  font-weight: 700;
  color: var(--blue, #0e325f);
  line-height: 1.25;
  margin: 0 0 0.5rem;
}

.contact-desc {
  font-size: 1rem;
  line-height: 1.65;
  color: var(--text-muted, #4b5d73);
  margin: 1rem 0 2rem;
}

.contact-card-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.contact-card {
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  padding: 1.25rem 1.5rem;
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid rgba(14, 50, 95, 0.08);
  box-shadow: 0 4px 16px rgba(14, 50, 95, 0.04);
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.contact-card:hover {
  transform: translateY(-2px);
  border-color: rgba(197, 160, 89, 0.4);
  box-shadow: 0 8px 24px rgba(14, 50, 95, 0.08);
}

.card-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: rgba(14, 50, 95, 0.06);
  color: var(--blue, #0e325f);
  display: grid;
  place-items: center;
  font-size: 1.35rem;
  flex-shrink: 0;
}

.card-content h3 {
  font-size: 0.9375rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--gold, #9b7628);
  margin: 0 0 0.25rem;
}

.card-content p {
  font-size: 1.05rem;
  font-weight: 600;
  color: var(--blue, #0e325f);
  margin: 0 0 0.25rem;
  line-height: 1.4;
}

.card-content p a {
  color: inherit;
  text-decoration: none;
  transition: color 0.15s ease;
}

.card-content p a:hover {
  color: var(--bright, #1b5cb8);
  text-decoration: underline;
}

.card-content .sub-text {
  font-size: 0.8125rem;
  color: var(--text-muted, #62748a);
  display: block;
}

.card-content .text-amber {
  color: #b45309;
  font-weight: 500;
}

/* Left Card Email Action Pills */
.card-email-pills {
  display: flex;
  align-items: center;
  gap: 6px;
  margin: 6px 0 8px;
  flex-wrap: wrap;
}

.provider-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 9px;
  border-radius: 14px;
  font-size: 0.75rem;
  font-weight: 700;
  text-decoration: none;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #0e325f;
  cursor: pointer;
  transition: all 0.15s ease;
}

.provider-chip:hover {
  transform: translateY(-1px);
}

.gmail-chip:hover {
  border-color: #ea4335;
  color: #ea4335;
  background: #fff5f5;
}

.yahoo-chip:hover {
  border-color: #6001d2;
  color: #6001d2;
  background: #faf5ff;
}

.copy-chip:hover {
  border-color: #0e325f;
  color: #0e325f;
  background: #eef4fb;
}

/* Email Hub Card */
.email-hub-card {
  background: #ffffff;
  border-radius: 18px;
  border: 1px solid rgba(14, 50, 95, 0.1);
  box-shadow: 0 12px 36px rgba(14, 50, 95, 0.08);
  padding: 2.25rem;
}

@media (max-width: 640px) {
  .email-hub-card {
    padding: 1.5rem;
  }
}

.form-card-header {
  margin-bottom: 1.5rem;
  border-bottom: 1px solid rgba(14, 50, 95, 0.08);
  padding-bottom: 1.25rem;
}

.email-hub-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #fbf5e6;
  border: 1px solid rgba(216, 170, 60, 0.4);
  color: #8c6819;
  font-size: 0.725rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 4px 10px;
  border-radius: 20px;
  margin-bottom: 0.75rem;
}

.hub-spark {
  color: #d8aa3c;
  font-size: 0.8rem;
}

.email-hub-title {
  font-family: var(--font-serif, "Cinzel", "Playfair Display", Georgia, serif);
  font-size: 1.6rem;
  color: var(--blue, #0e325f);
  margin: 0 0 0.4rem;
}

.form-card-header p {
  font-size: 0.9375rem;
  color: var(--text-muted, #55687d);
  line-height: 1.55;
  margin: 0;
}

.email-highlight {
  color: #0e325f;
  background: #f1f5f9;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 0.95em;
  font-weight: 700;
}

/* Topic Selector */
.topic-selector-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 12px 16px;
  margin-bottom: 1.5rem;
}

.topic-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.topic-label-text {
  font-size: 0.85rem;
  font-weight: 700;
  color: #0e325f;
}

.topic-label-hint {
  font-size: 0.75rem;
  color: #64748b;
}

.topic-select {
  width: 100%;
  padding: 10px 14px;
  font-size: 0.9rem;
  font-family: inherit;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #ffffff;
  color: #0e325f;
  font-weight: 600;
  cursor: pointer;
  box-sizing: border-box;
}

.topic-select:focus {
  outline: none;
  border-color: #1b5cb8;
  box-shadow: 0 0 0 3px rgba(27, 92, 184, 0.15);
}

/* Providers Stack */
.email-providers-stack {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
  margin-bottom: 1.5rem;
}

.provider-btn-card {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  text-decoration: none;
  color: inherit;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease, border-color 0.2s ease;
}

.provider-btn-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(14, 50, 95, 0.08);
}

.provider-brand-badge {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: #f8fafc;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.gmail-provider {
  border-color: rgba(234, 67, 53, 0.3);
  background: linear-gradient(135deg, #ffffff 0%, #fffcfc 100%);
}

.gmail-provider:hover {
  border-color: #ea4335;
  box-shadow: 0 10px 24px rgba(234, 67, 53, 0.12);
}

.gmail-provider:hover .provider-action {
  background: #ea4335;
  color: #ffffff;
}

.yahoo-provider:hover {
  border-color: #6001d2;
  box-shadow: 0 10px 24px rgba(96, 1, 210, 0.12);
}

.yahoo-provider:hover .provider-action {
  background: #6001d2;
  color: #ffffff;
}

.outlook-provider:hover {
  border-color: #0078d4;
  box-shadow: 0 10px 24px rgba(0, 120, 212, 0.12);
}

.outlook-provider:hover .provider-action {
  background: #0078d4;
  color: #ffffff;
}

.default-provider:hover {
  border-color: #0e325f;
  box-shadow: 0 10px 24px rgba(14, 50, 95, 0.12);
}

.default-provider:hover .provider-action {
  background: #0e325f;
  color: #ffffff;
}

.provider-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.provider-name-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.provider-title {
  font-size: 1rem;
  font-weight: 700;
  color: #0e325f;
}

.recommended-badge {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fde68a;
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
  padding: 2px 8px;
  border-radius: 12px;
  letter-spacing: 0.04em;
}

.provider-desc {
  font-size: 0.8125rem;
  color: #64748b;
  line-height: 1.35;
}

.provider-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  border-radius: 20px;
  background: #f1f5f9;
  color: #0e325f;
  font-size: 0.8125rem;
  font-weight: 700;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

/* Copy Address Bar */
.copy-address-container {
  background: #f8fafc;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
  padding: 12px 16px;
  margin-bottom: 1.25rem;
}

.copy-helper-label {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
  margin-bottom: 6px;
}

.copy-address-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.copy-email-code {
  font-family: monospace;
  font-size: 0.9375rem;
  font-weight: 700;
  color: #0e325f;
}

.copy-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background: #0e325f;
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-size: 0.8125rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.copy-action-btn:hover {
  background: #1b5cb8;
}

.copy-action-btn.copied {
  background: #10b981;
}

/* Notice */
.secretariat-notice {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 10px;
  padding: 12px 14px;
  color: #1e40af;
  font-size: 0.8125rem;
  line-height: 1.5;
  margin-bottom: 1rem;
}

.notice-icon {
  font-size: 1rem;
  font-weight: bold;
  color: #2563eb;
  flex-shrink: 0;
}

.secretariat-notice a {
  color: #1d4ed8;
  font-weight: 700;
  text-decoration: underline;
}

/* Quick Services Footer */
.quick-links-footer {
  margin-top: 1.5rem;
  padding-top: 1.25rem;
  border-top: 1px dashed rgba(14, 50, 95, 0.15);
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.quick-title {
  font-size: 0.8125rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-muted, #64748b);
}

.quick-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.quick-tag {
  display: inline-flex;
  align-items: center;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--blue, #0e325f);
  background: #edf4fb;
  padding: 0.35rem 0.75rem;
  border-radius: 9999px;
  text-decoration: none;
  transition: background 0.15s ease, color 0.15s ease;
}

.quick-tag:hover {
  background: var(--blue, #0e325f);
  color: #ffffff;
}
</style>

