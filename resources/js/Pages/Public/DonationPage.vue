<script setup>
import { onMounted, ref } from 'vue'
import { siteSettings, parishPhoneHref } from '../../services/siteSettings'

const gcashNumber = '09214309753'
const gcashName = 'JOSE BURT SARE'
const copied = ref(false)
const copyToast = ref(false)
let copyTimer = null

const copyGcashNumber = async () => {
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(gcashNumber)
    } else {
      const input = document.createElement('input')
      input.value = gcashNumber
      document.body.appendChild(input)
      input.select()
      document.execCommand('copy')
      document.body.removeChild(input)
    }
    copied.value = true
    copyToast.value = true
    if (copyTimer) clearTimeout(copyTimer)
    copyTimer = setTimeout(() => {
      copied.value = false
      copyToast.value = false
    }, 2800)
  } catch (err) {
    console.error('Failed to copy number:', err)
  }
}

const isAuthenticated = ref(typeof window !== 'undefined' && Boolean(window.__AUTH_USER__))

onMounted(async () => {
  if (!isAuthenticated.value && typeof fetch !== 'undefined') {
    try {
      const res = await fetch('/api/user/profile-status', { headers: { Accept: 'application/json' } })
      if (res.ok) {
        const data = await res.json()
        if (data.authenticated) {
          isAuthenticated.value = true
        }
      }
    } catch {
      // Guest
    }
  }
})

const handleRequestReceipt = () => {
  if (isAuthenticated.value) {
    window.location.assign('/parishioner/donations/request')
  } else {
    window.location.hash = '#/login?redirect=/parishioner/donations/request'
  }
}
</script>

<template>
  <div class="donation-public-page">
    <!-- 1. Scriptural & Pastoral Invocation Banner -->
    <section class="scripture-banner" aria-label="Scripture on Christian Generosity">
      <div class="site-container">
        <div class="scripture-card">
          <div class="marian-cross-icon" aria-hidden="true">✝</div>
          <blockquote class="scripture-quote">
            &ldquo;Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver.&rdquo;
          </blockquote>
          <cite class="scripture-ref">2 Corinthians 9:7</cite>
        </div>
      </div>
    </section>

    <!-- 2. Main Donation Section -->
    <section class="donation-methods-section" id="donation-methods" aria-label="Donation Payment Methods">
      <div class="site-container">
        <div class="section-intro">
          <span class="section-kicker">PARISH STEWARDSHIP</span>
          <h2 class="section-title">Support the Shrine</h2>
          <p class="section-desc">
            Your generous offerings sustain the daily worship, pastoral outreach, sanctuary preservation, and community formation of the Diocesan Shrine and Parish of Our Lady of the Pillar.
          </p>
          <div class="section-divider" aria-hidden="true"></div>
        </div>

        <!-- Two Prominent Payment Cards: GCash & InstaPay -->
        <div class="methods-grid">
          <!-- CARD A: GCash -->
          <article class="payment-card gcash-card" aria-label="GCash Payment Details">
            <div class="card-header-badge">
              <span class="method-tag tag-gcash">E-WALLET</span>
            </div>

            <div class="method-brand">
              <div class="brand-badge-circle gcash-circle" aria-hidden="true">
                <span class="brand-initial">G</span>
              </div>
              <div class="brand-text">
                <h3 class="method-title">GCash</h3>
                <span class="method-subtitle">Direct Mobile Transfer</span>
              </div>
            </div>

            <div class="account-details-box">
              <div class="account-field">
                <span class="field-label">Account Name</span>
                <strong class="account-name-val">{{ gcashName }}</strong>
              </div>

              <div class="account-field">
                <span class="field-label">Mobile Number</span>
                <div class="number-row">
                  <span class="number-text" id="gcash-num-display">{{ gcashNumber }}</span>
                  <button
                    type="button"
                    class="btn-copy-number"
                    :class="{ copied }"
                    @click="copyGcashNumber"
                    aria-label="Copy GCash mobile number"
                    title="Copy GCash Number"
                  >
                    <svg v-if="!copied" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                      <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>{{ copied ? 'Copied!' : 'Copy Number' }}</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Helpful Transfer Steps -->
            <div class="payment-steps">
              <h4 class="steps-title">How to transfer via GCash:</h4>
              <ol class="steps-list">
                <li>Open your <strong>GCash App</strong> and tap <strong>Send Money</strong> &rarr; <em>Express Send</em>.</li>
                <li>Enter or paste mobile number: <strong>09214309753</strong>.</li>
                <li>Verify the recipient name displays <strong>{{ gcashName }}</strong>.</li>
                <li>Enter your donation amount and save a screenshot of the confirmation receipt.</li>
              </ol>
            </div>

            <div class="card-footer-note">
              <span class="secure-icon" aria-hidden="true">🔒</span>
              <span>Always verify recipient details before sending funds.</span>
            </div>
          </article>

          <!-- CARD B: InstaPay QR -->
          <article class="payment-card instapay-card" aria-label="InstaPay QR Code Payment">
            <div class="card-header-badge">
              <span class="method-tag tag-instapay">INTERBANK QR PH</span>
            </div>

            <div class="method-brand">
              <div class="brand-badge-circle instapay-circle" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="7" height="7"></rect>
                  <rect x="14" y="3" width="7" height="7"></rect>
                  <rect x="14" y="14" width="7" height="7"></rect>
                  <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
              </div>
              <div class="brand-text">
                <h3 class="method-title">Pay via InstaPay</h3>
                <span class="method-subtitle">Scan &amp; Pay via Any Bank App</span>
              </div>
            </div>

            <!-- Untouched, Prominently Centered InstaPay QR Code -->
            <div class="qr-presentation-box">
              <div class="qr-frame">
                <img
                  :src="'/images/instapay-qr.png'"
                  alt="Official InstaPay QR Code for Our Lady of the Pillar Shrine"
                  class="instapay-qr-image"
                  width="220"
                  height="250"
                  loading="eager"
                />
              </div>
              <p class="qr-scan-instruction">
                Scan the QR code using your preferred banking or payment application.
              </p>
            </div>

            <div class="verification-notice-callout">
              <div class="callout-icon" aria-hidden="true">ℹ</div>
              <p class="callout-text">
                <strong>Please verify the recipient details before completing your transaction:</strong><br />
                Ensure the recipient name matches the parish shrine administrator (<strong>JOSE BURT SARE</strong>) before sending.
              </p>
            </div>

            <div class="supported-banks-strip">
              <span class="strip-label">Supported via InstaPay QR Ph:</span>
              <span class="bank-pill">BDO</span>
              <span class="bank-pill">BPI</span>
              <span class="bank-pill">Metrobank</span>
              <span class="bank-pill">UnionBank</span>
              <span class="bank-pill">Landbank</span>
              <span class="bank-pill">Maya</span>
              <span class="bank-pill">RCBC</span>
              <span class="bank-pill">&amp; All Major Banks</span>
            </div>
          </article>
        </div>
      </div>
    </section>

    <!-- 3. After Donation / Receipt Request Section -->
    <section class="after-donation-section" aria-label="Donation Verification and Acknowledgment">
      <div class="site-container">
        <div class="receipt-callout-card">
          <div class="callout-content">
            <div class="receipt-icon-badge" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="30" height="30" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
            </div>
            <div class="callout-text-wrap">
              <span class="callout-eyebrow">OFFICIAL ACKNOWLEDGMENT</span>
              <h3 class="callout-heading">Already made a donation?</h3>
              <p class="callout-body">
                If you would like to request a donation acknowledgment or receipt, please submit your donation details through your Parishioner Dashboard.
              </p>
            </div>
          </div>

          <div class="callout-actions">
            <button
              type="button"
              class="btn-request-receipt"
              @click="handleRequestReceipt"
              aria-label="Request Donation Acknowledgment or Receipt"
            >
              <span>Request Acknowledgment / Receipt</span>
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </button>
            <p class="login-context-hint">
              <span v-if="isAuthenticated">✓ You are logged in. The receipt request form will open directly in your dashboard.</span>
              <span v-else>You will be directed to sign in with your parishioner account before submitting your verification details.</span>
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- 4. In-Person & Office Offerings Notice -->
    <section class="office-support-section" aria-label="In-Person Offerings">
      <div class="site-container">
        <div class="office-card">
          <div class="office-icon-wrap" aria-hidden="true">
            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
              <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
          </div>
          <div class="office-info">
            <h4 class="office-title">In-Person Offerings &amp; Mass Intentions</h4>
            <p class="office-desc">
              You may also give your offerings directly at the <strong>Parish Office</strong> during regular operating hours:
            </p>
            <div class="office-schedule-badge-list">
              <span class="schedule-pill">
                <strong>Monday, Wednesday &ndash; Saturday:</strong> 8:00 AM &ndash; 11:30 AM | 1:00 PM &ndash; 5:00 PM
              </span>
              <span class="schedule-pill">
                <strong>Sunday:</strong> 8:30 AM &ndash; 12:00 NN
              </span>
              <span class="schedule-pill pill-closed">
                <strong>Tuesday:</strong> Closed for Day Off &amp; During Holidays
              </span>
            </div>
            <div class="office-meta-links">
              <span>📍 Binanuahan, Pilar, Sorsogon</span>
              <span>☎ <a :href="parishPhoneHref">{{ siteSettings.phone }}</a></span>
              <span>✉ <a :href="'mailto:' + siteSettings.email">{{ siteSettings.email }}</a></span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Floating Copy Toast Notification -->
    <transition name="toast-fade">
      <div v-if="copyToast" class="copy-success-toast" role="status" aria-live="polite">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span>GCash Number (09214309753) copied to clipboard!</span>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.donation-public-page {
  background: var(--bg-soft, #f7fafc);
  color: var(--navy, #062f78);
  font-family: var(--font-body);
  padding-bottom: 70px;
}

/* Scripture Banner */
.scripture-banner {
  padding: 36px 0 16px;
}

.scripture-card {
  max-width: 860px;
  margin: 0 auto;
  background: #ffffff;
  border: 1px solid rgba(214, 170, 62, 0.35);
  border-left: 4px solid var(--gold, #d6aa3e);
  border-radius: 12px;
  padding: 24px 32px;
  text-align: center;
  box-shadow: 0 4px 20px rgba(6, 47, 120, 0.05);
}

.marian-cross-icon {
  font-size: 20px;
  color: var(--gold, #d6aa3e);
  margin-bottom: 6px;
}

.scripture-quote {
  margin: 0 0 8px;
  font-family: var(--font-heading);
  font-style: italic;
  font-size: 16px;
  line-height: 1.6;
  color: var(--navy, #062f78);
}

.scripture-ref {
  display: block;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--gold, #d6aa3e);
}

/* Section Intro */
.donation-methods-section {
  padding: 28px 0 44px;
}

.section-intro {
  text-align: center;
  max-width: 780px;
  margin: 0 auto 36px;
}

.section-kicker {
  display: inline-block;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--gold, #d6aa3e);
  margin-bottom: 8px;
}

.section-title {
  font-family: var(--font-heading);
  font-size: 32px;
  font-weight: 700;
  color: var(--navy, #062f78);
  margin: 0 0 12px;
}

.section-desc {
  font-size: 15px;
  line-height: 1.65;
  color: #4a5568;
  margin: 0;
}

.section-divider {
  width: 54px;
  height: 3px;
  background: var(--gold, #d6aa3e);
  margin: 18px auto 0;
  border-radius: 2px;
}

/* Methods Grid */
.methods-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 32px;
  max-width: 1040px;
  margin: 0 auto;
}

@media (max-width: 860px) {
  .methods-grid {
    grid-template-columns: 1fr;
    gap: 24px;
  }
}

.payment-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 6px 24px rgba(6, 47, 120, 0.06);
  display: flex;
  flex-direction: column;
  position: relative;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.payment-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(6, 47, 120, 0.1);
  border-color: #cbd5e1;
}

.card-header-badge {
  display: flex;
  justify-content: flex-end;
  margin-bottom: 12px;
}

.method-tag {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 3px 10px;
  border-radius: 20px;
}

.tag-gcash {
  background: #ebf8ff;
  color: #007dfe;
  border: 1px solid #bee3f8;
}

.tag-instapay {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.method-brand {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 22px;
  padding-bottom: 18px;
  border-bottom: 1px solid #edf2f7;
}

.brand-badge-circle {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.gcash-circle {
  background: #007dfe;
  color: #ffffff;
}

.brand-initial {
  font-size: 24px;
  font-weight: 800;
  font-family: var(--font-body);
}

.instapay-circle {
  background: #0f172a;
  color: #ffffff;
}

.method-title {
  margin: 0;
  font-family: var(--font-heading);
  font-size: 20px;
  color: var(--navy, #062f78);
  font-weight: 700;
}

.method-subtitle {
  font-size: 12px;
  color: #718096;
}

/* Account Details Box in GCash */
.account-details-box {
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px 22px;
  margin-bottom: 22px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.account-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.field-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748b;
}

.account-name-val {
  font-size: 17px;
  font-weight: 800;
  color: var(--navy, #062f78);
  letter-spacing: 0.02em;
}

.number-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.number-text {
  font-family: 'Courier New', Courier, monospace;
  font-size: 22px;
  font-weight: 800;
  color: #007dfe;
  letter-spacing: 0.06em;
}

.btn-copy-number {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 8px 14px;
  background: #ffffff;
  color: #007dfe;
  border: 1.5px solid #007dfe;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.btn-copy-number:hover {
  background: #ebf8ff;
}

.btn-copy-number.copied {
  background: #16a34a;
  color: #ffffff;
  border-color: #16a34a;
}

/* Payment Steps */
.payment-steps {
  margin-bottom: 20px;
}

.steps-title {
  font-size: 12.5px;
  font-weight: 700;
  color: var(--navy, #062f78);
  margin: 0 0 10px;
}

.steps-list {
  margin: 0;
  padding-left: 20px;
  font-size: 13px;
  line-height: 1.6;
  color: #475569;
}

.steps-list li {
  margin-bottom: 6px;
}

.card-footer-note {
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px dashed #e2e8f0;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11.5px;
  color: #64748b;
}

/* InstaPay QR Box */
.qr-presentation-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-bottom: 18px;
}

.qr-frame {
  background: #ffffff;
  padding: 12px;
  border-radius: 12px;
  border: 2px solid #e2e8f0;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
  display: inline-block;
  margin-bottom: 14px;
}

/* Ensure untouched, non-distorted display of the supplied InstaPay QR image */
.instapay-qr-image {
  display: block;
  width: 200px;
  height: 230px;
  object-fit: contain;
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
}

.qr-scan-instruction {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--navy, #062f78);
  max-width: 320px;
  margin: 0;
  line-height: 1.45;
}

/* Callout Verification */
.verification-notice-callout {
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 8px;
  padding: 12px 14px;
  display: flex;
  gap: 10px;
  align-items: flex-start;
  margin-bottom: 18px;
}

.callout-icon {
  color: #b45309;
  font-weight: 800;
  font-size: 14px;
  line-height: 1.2;
}

.callout-text {
  margin: 0;
  font-size: 11.5px;
  line-height: 1.5;
  color: #92400e;
}

.supported-banks-strip {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 6px;
  padding-top: 14px;
  border-top: 1px dashed #e2e8f0;
  margin-top: auto;
}

.strip-label {
  font-size: 10.5px;
  font-weight: 700;
  color: #64748b;
  width: 100%;
  margin-bottom: 2px;
}

.bank-pill {
  font-size: 10px;
  font-weight: 700;
  background: #f1f5f9;
  color: #334155;
  padding: 2px 7px;
  border-radius: 4px;
}

/* 3. After Donation Section */
.after-donation-section {
  padding: 20px 0 40px;
}

.receipt-callout-card {
  background: linear-gradient(135deg, #ffffff 0%, #f8faff 100%);
  border: 2px solid #bfdbfe;
  border-radius: 16px;
  padding: 34px 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
  box-shadow: 0 8px 25px rgba(11, 88, 181, 0.08);
}

@media (max-width: 900px) {
  .receipt-callout-card {
    flex-direction: column;
    align-items: flex-start;
    padding: 26px 24px;
  }
}

.callout-content {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

.receipt-icon-badge {
  width: 58px;
  height: 58px;
  border-radius: 14px;
  background: #eff6ff;
  color: #1d4ed8;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  border: 1.5px solid #dbeafe;
}

.callout-eyebrow {
  display: inline-block;
  font-size: 10.5px;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--gold, #d6aa3e);
  margin-bottom: 4px;
}

.callout-heading {
  margin: 0 0 6px;
  font-family: var(--font-heading);
  font-size: 22px;
  font-weight: 700;
  color: var(--navy, #062f78);
}

.callout-body {
  margin: 0;
  font-size: 14px;
  line-height: 1.55;
  color: #475569;
  max-width: 580px;
}

.callout-actions {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 8px;
  flex-shrink: 0;
}

@media (max-width: 900px) {
  .callout-actions {
    align-items: flex-start;
    width: 100%;
  }
}

.btn-request-receipt {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 14px 26px;
  background: var(--navy, #062f78);
  color: #ffffff;
  border: 1.5px solid var(--gold, #d6aa3e);
  border-radius: 10px;
  font-size: 14.5px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(6, 47, 120, 0.22);
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-request-receipt:hover {
  background: #093c94;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(6, 47, 120, 0.3);
}

.login-context-hint {
  margin: 0;
  font-size: 11px;
  color: #64748b;
  max-width: 320px;
  text-align: right;
  line-height: 1.4;
}

@media (max-width: 900px) {
  .login-context-hint {
    text-align: left;
    max-width: 100%;
  }
}

/* 4. Office Support Section */
.office-support-section {
  padding: 10px 0 20px;
}

.office-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px 30px;
  display: flex;
  gap: 20px;
  align-items: center;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

@media (max-width: 700px) {
  .office-card {
    flex-direction: column;
    text-align: center;
  }
}

.office-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 12px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  display: grid;
  place-items: center;
  color: var(--navy, #062f78);
  flex-shrink: 0;
}

.office-title {
  margin: 0 0 6px;
  font-size: 16px;
  font-weight: 700;
  color: var(--navy, #062f78);
}

.office-desc {
  margin: 0 0 10px;
  font-size: 13px;
  color: #475569;
  line-height: 1.5;
}

.office-schedule-badge-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 14px;
}

.schedule-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 4px 10px;
  font-size: 12px;
  color: #334155;
  line-height: 1.4;
}

.schedule-pill strong {
  color: var(--navy, #062f78);
  font-weight: 700;
}

.schedule-pill.pill-closed {
  background: #fffbeb;
  border-color: #fde68a;
  color: #92400e;
}

.schedule-pill.pill-closed strong {
  color: #b45309;
}

@media (max-width: 700px) {
  .office-schedule-badge-list {
    justify-content: center;
  }
}

.office-meta-links {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  font-size: 12px;
  color: #64748b;
}

@media (max-width: 700px) {
  .office-meta-links {
    justify-content: center;
  }
}

.office-meta-links a {
  color: #007dfe;
  text-decoration: none;
  font-weight: 600;
}

.office-meta-links a:hover {
  text-decoration: underline;
}

/* Floating copy success toast */
.copy-success-toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 9999;
  background: #0f172a;
  color: #ffffff;
  border: 1px solid #334155;
  border-radius: 8px;
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  font-weight: 600;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
}

.copy-success-toast svg {
  color: #4ade80;
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
