<script setup>
import { ref } from 'vue'

const formData = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})

const isSubmitted = ref(false)

const handleSubmit = () => {
  if (!formData.value.name || !formData.value.message) return
  isSubmitted.value = true
  setTimeout(() => {
    formData.value = { name: '', email: '', phone: '', subject: '', message: '' }
    isSubmitted.value = false
  }, 4000)
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
                <p><a href="mailto:olppspilarsorsogon@gmail.com">olppspilarsorsogon@gmail.com</a></p>
                <span class="sub-text">Inquiries, records, and administrative communications</span>
              </div>
            </div>

            <div class="contact-card">
              <div class="card-icon" aria-hidden="true">◷</div>
              <div class="card-content">
                <h3>Office Hours</h3>
                <p><strong>Tuesday – Sunday</strong>: 8:00 AM – 12:00 NN | 1:30 PM – 5:00 PM</p>
                <span class="sub-text text-amber">Monday: Office Closed (Maintenance & Day of Rest)</span>
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

        <!-- Contact Form Side -->
        <div class="contact-form-panel">
          <div class="form-container-card">
            <div class="form-card-header">
              <h3>Send us a Message</h3>
              <p>Fill out this form and our parish staff will respond to your inquiry.</p>
            </div>

            <div v-if="isSubmitted" class="alert-success" role="alert">
              <span class="success-icon">✓</span>
              <div>
                <strong>Thank you for contacting us!</strong>
                <p>Your message has been received. Our parish office will get back to you shortly.</p>
              </div>
            </div>

            <form v-else class="contact-inquiry-form" @submit.prevent="handleSubmit">
              <div class="form-group">
                <label for="contact-name">Full Name <span class="required">*</span></label>
                <input
                  id="contact-name"
                  v-model="formData.name"
                  type="text"
                  placeholder="e.g. Juan dela Cruz"
                  required
                />
              </div>

              <div class="form-row two-col">
                <div class="form-group">
                  <label for="contact-email">Email Address</label>
                  <input
                    id="contact-email"
                    v-model="formData.email"
                    type="email"
                    placeholder="your.email@example.com"
                  />
                </div>
                <div class="form-group">
                  <label for="contact-phone">Phone Number</label>
                  <input
                    id="contact-phone"
                    v-model="formData.phone"
                    type="tel"
                    placeholder="09XX XXX XXXX"
                  />
                </div>
              </div>

              <div class="form-group">
                <label for="contact-subject">Subject</label>
                <select id="contact-subject" v-model="formData.subject">
                  <option value="" disabled selected>Select a subject</option>
                  <option value="General Inquiry">General Inquiry</option>
                  <option value="Sacraments / Certificates">Sacrament Inquiries or Certificate Request</option>
                  <option value="Mass Intentions">Mass Intention Follow-up</option>
                  <option value="Pilgrimage & Group Visit">Pilgrimage & Group Visitation</option>
                  <option value="Donation & Shrine Support">Donations & Shrine Support</option>
                  <option value="Pastoral Care">Pastoral Counseling or Home Visitation</option>
                </select>
              </div>

              <div class="form-group">
                <label for="contact-message">Message <span class="required">*</span></label>
                <textarea
                  id="contact-message"
                  v-model="formData.message"
                  rows="5"
                  placeholder="How can our parish community assist you?"
                  required
                ></textarea>
              </div>

              <button type="submit" class="button submit-btn">
                <span>Send Message</span>
                <span aria-hidden="true">➤</span>
              </button>
            </form>

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

/* Form Panel */
.form-container-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid rgba(14, 50, 95, 0.1);
  box-shadow: 0 10px 30px rgba(14, 50, 95, 0.07);
  padding: 2.25rem;
}

@media (max-width: 640px) {
  .form-container-card {
    padding: 1.5rem;
  }
}

.form-card-header {
  margin-bottom: 1.75rem;
  border-bottom: 1px solid rgba(14, 50, 95, 0.08);
  padding-bottom: 1.25rem;
}

.form-card-header h3 {
  font-family: var(--font-serif, "Cinzel", "Playfair Display", Georgia, serif);
  font-size: 1.5rem;
  color: var(--blue, #0e325f);
  margin: 0 0 0.35rem;
}

.form-card-header p {
  font-size: 0.9375rem;
  color: var(--text-muted, #55687d);
  margin: 0;
}

.alert-success {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: #ecfdf5;
  border: 1px solid #10b981;
  color: #065f46;
  padding: 1.25rem 1.5rem;
  border-radius: 10px;
  margin-bottom: 1.5rem;
}

.success-icon {
  font-size: 1.5rem;
  font-weight: bold;
  background: #10b981;
  color: #fff;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: grid;
  place-items: center;
}

.contact-inquiry-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row.two-col {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.25rem;
}

@media (min-width: 640px) {
  .form-row.two-col {
    grid-template-columns: 1fr 1fr;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--blue, #0e325f);
}

.required {
  color: #dc2626;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 0.9375rem;
  font-family: inherit;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #f8fafc;
  color: #1e293b;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  background: #ffffff;
  border-color: var(--bright, #1b5cb8);
  box-shadow: 0 0 0 3px rgba(27, 92, 184, 0.15);
}

.submit-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.875rem 1.5rem;
  font-size: 1rem;
  font-weight: 600;
  background: var(--blue, #0e325f);
  color: #ffffff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 0.5rem;
  transition: background 0.2s ease, transform 0.15s ease;
}

.submit-btn:hover {
  background: var(--bright, #1b5cb8);
  transform: translateY(-1px);
}

.quick-links-footer {
  margin-top: 2rem;
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

