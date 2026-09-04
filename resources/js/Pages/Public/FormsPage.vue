<script setup>
import { ref } from 'vue'

const intentionTypes = [
  'Thanksgiving',
  'Healing',
  'Birthday',
  'Anniversary',
  'Death Anniversary',
  'Special Intention',
  'Other',
]

const selectedType = ref('Thanksgiving')

const form = ref({
  requesterName: '',
  contactNumber: '',
  email: '',
  intentionType: 'Thanksgiving',
  intentionOffered: '',
  preferredDate: '',
  preferredSchedule: '',
  message: '',
})

const isSubmitted = ref(false)

const selectType = (type) => {
  selectedType.value = type
  form.value.intentionType = type
}

const handleSubmit = () => {
  if (!form.value.requesterName || !form.value.contactNumber || !form.value.intentionOffered || !form.value.preferredDate) {
    return
  }
  isSubmitted.value = true
  setTimeout(() => {
    form.value = {
      requesterName: '',
      contactNumber: '',
      email: '',
      intentionType: 'Thanksgiving',
      intentionOffered: '',
      preferredDate: '',
      preferredSchedule: '',
      message: '',
    }
    selectedType.value = 'Thanksgiving'
    isSubmitted.value = false
  }, 5000)
}
</script>

<template>
  <div class="forms-page-wrap">
    <div class="page-width py-12">
      <div class="form-layout">
        <!-- Main Form -->
        <div class="form-main-card">
          <div class="form-card-header">
            <span class="eyebrow-label">Shrine Liturgical Intentions</span>
            <h2>Mass Intention Details</h2>
            <div class="gold-rule left">✣</div>
            <p class="form-instruction">
              Please provide the intention details below. All submitted intentions are remembered and offered during the Holy Sacrifice of the Mass at the Diocesan Shrine of Our Lady of the Pillar.
            </p>
          </div>

          <div v-if="isSubmitted" class="alert-success" role="alert">
            <div class="success-icon">✓</div>
            <div>
              <h3>Mass Intention Submitted Successfully!</h3>
              <p>Your request has been received by the Shrine Parish Office. We will keep your intentions in our prayers during the Holy Mass.</p>
            </div>
          </div>

          <form v-else class="request-form-body" @submit.prevent="handleSubmit">
            <div class="form-row three-col">
              <label>
                Name of Requester <span class="required">*</span>
                <input
                  v-model="form.requesterName"
                  type="text"
                  placeholder="Enter your full name"
                  required
                />
              </label>
              <label>
                Contact Number <span class="required">*</span>
                <input
                  v-model="form.contactNumber"
                  type="tel"
                  placeholder="09XX XXX XXXX"
                  required
                />
              </label>
              <label>
                Email Address
                <input
                  v-model="form.email"
                  type="email"
                  placeholder="Enter your email address"
                />
              </label>
            </div>

            <div class="field-block">
              <label class="block-label">Intention Type <span class="required">*</span></label>
              <div class="choice-row">
                <button
                  v-for="x in intentionTypes"
                  :key="x"
                  type="button"
                  :class="{ active: selectedType === x }"
                  @click="selectType(x)"
                >
                  <span class="choice-heart">♡</span>
                  <small>{{ x }}</small>
                </button>
              </div>
            </div>

            <div class="field-block">
              <label>
                Name/s or Intention Being Offered <span class="required">*</span>
                <input
                  v-model="form.intentionOffered"
                  type="text"
                  placeholder="Enter individual name(s), family name, or specific intention"
                  required
                />
              </label>
            </div>

            <div class="form-row two-col">
              <label>
                Preferred Mass Date <span class="required">*</span>
                <input
                  v-model="form.preferredDate"
                  type="date"
                  required
                />
              </label>
              <label>
                Preferred Mass Schedule <span class="required">*</span>
                <select v-model="form.preferredSchedule" required>
                  <option value="" disabled selected>Select Mass Schedule</option>
                  <optgroup label="Sunday Masses">
                    <option value="Sunday 5:00 AM">Sunday — 5:00 AM (Holy Mass)</option>
                    <option value="Sunday 7:30 AM">Sunday — 7:30 AM (Holy Mass)</option>
                    <option value="Sunday 5:00 PM">Sunday — 5:00 PM (Holy Mass)</option>
                  </optgroup>
                  <optgroup label="Weekday & Saturday Masses">
                    <option value="Monday 5:00 PM">Monday — 5:00 PM</option>
                    <option value="Tuesday 6:00 AM">Tuesday — 6:00 AM</option>
                    <option value="Wednesday 5:00 PM">Wednesday — 5:00 PM</option>
                    <option value="Thursday 6:00 AM">Thursday — 6:00 AM</option>
                    <option value="Friday 6:00 AM">Friday — 6:00 AM</option>
                    <option value="Saturday 6:00 AM">Saturday — 6:00 AM</option>
                    <option value="Saturday Anticipated 5:00 PM">Saturday Anticipated — 5:00 PM</option>
                  </optgroup>
                </select>
              </label>
            </div>

            <div class="field-block">
              <label>
                Additional Message or Prayer Intention
                <textarea
                  v-model="form.message"
                  rows="4"
                  placeholder="Write any additional message or specific prayer intention..."
                ></textarea>
              </label>
            </div>

            <button type="submit" class="button submit-request-btn">
              <span>Submit Mass Intention</span>
              <span aria-hidden="true">➤</span>
            </button>
          </form>
        </div>

        <!-- Info Sidebar -->
        <aside class="info-panel">
          <div class="info-panel-header">
            <h3>About Mass Intentions</h3>
            <div class="gold-rule left">✣</div>
          </div>
          <p>
            The Holy Sacrifice of the Mass is the highest form of prayer in the Catholic Church. Having a Mass offered for a loved one, living or deceased, or for a special intention is an ancient and venerable act of faith.
          </p>

          <hr class="panel-divider" />

          <h3 class="panel-subtitle">How it works</h3>
          <div class="step-list">
            <div class="step-item">
              <span class="step-icon">♢</span>
              <div>
                <strong>Submit your request</strong>
                <p>Fill out the form with your intention details and preferred schedule.</p>
              </div>
            </div>
            <div class="step-item">
              <span class="step-icon">♢</span>
              <div>
                <strong>Parish verification</strong>
                <p>Our parish office reviews your submission and enters it into the liturgical calendar.</p>
              </div>
            </div>
            <div class="step-item">
              <span class="step-icon">♡</span>
              <div>
                <strong>Shrine community prays</strong>
                <p>Our shrine priests and congregants join in prayer for your intentions during the Mass.</p>
              </div>
            </div>
          </div>

          <div class="office-help-box">
            <h4>Need In-Person Assistance?</h4>
            <p>You can also visit the Parish Office during regular hours or call us at <strong>0946-869-1254</strong>.</p>
            <a href="#/contact" class="link-contact">Contact Parish Office →</a>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<style scoped>
.forms-page-wrap {
  background: var(--bg-soft, #f4f8fc);
}

.py-12 {
  padding-top: 3.5rem;
  padding-bottom: 4.5rem;
}

.form-layout {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
  align-items: start;
}

@media (min-width: 960px) {
  .form-layout {
    grid-template-columns: 1fr 340px;
    gap: 2.5rem;
  }
}

.form-main-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid rgba(14, 50, 95, 0.1);
  box-shadow: 0 8px 24px rgba(14, 50, 95, 0.05);
  padding: 2.25rem;
}

@media (max-width: 640px) {
  .form-main-card {
    padding: 1.5rem;
  }
}

.eyebrow-label {
  display: inline-block;
  font-size: 0.8125rem;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--gold, #c5a059);
  margin-bottom: 0.35rem;
}

.form-card-header h2 {
  font-family: var(--font-serif, "Cinzel", "Playfair Display", Georgia, serif);
  font-size: clamp(1.5rem, 2.5vw, 1.85rem);
  color: var(--blue, #0e325f);
  margin: 0 0 0.5rem;
}

.form-instruction {
  font-size: 0.9375rem;
  line-height: 1.6;
  color: var(--text-muted, #55687d);
  margin: 0.85rem 0 1.5rem;
}

.alert-success {
  display: flex;
  align-items: flex-start;
  gap: 1.25rem;
  background: #ecfdf5;
  border: 1px solid #10b981;
  color: #065f46;
  padding: 1.5rem;
  border-radius: 12px;
  margin-top: 1rem;
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
  flex-shrink: 0;
}

.alert-success h3 {
  margin: 0 0 0.25rem;
  font-size: 1.1rem;
  color: #065f46;
}

.alert-success p {
  margin: 0;
  font-size: 0.9375rem;
  line-height: 1.5;
}

.request-form-body {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-row {
  display: grid;
  gap: 1.25rem;
}

.form-row.three-col {
  grid-template-columns: 1fr;
}

@media (min-width: 768px) {
  .form-row.three-col {
    grid-template-columns: 1.3fr 1fr 1.1fr;
  }
}

.form-row.two-col {
  grid-template-columns: 1fr;
}

@media (min-width: 640px) {
  .form-row.two-col {
    grid-template-columns: 1fr 1fr;
  }
}

.field-block {
  display: flex;
  flex-direction: column;
}

label {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--blue, #0e325f);
}

.required {
  color: #dc2626;
}

input,
select,
textarea {
  width: 100%;
  padding: 0.75rem 1rem;
  font-size: 0.9375rem;
  font-family: inherit;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #f8fafc;
  color: #1e293b;
  box-sizing: border-box;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

input:focus,
select:focus,
textarea:focus {
  outline: none;
  background: #ffffff;
  border-color: var(--bright, #1b5cb8);
  box-shadow: 0 0 0 3px rgba(27, 92, 184, 0.15);
}

.block-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--blue, #0e325f);
  margin-bottom: 0.5rem;
}

.choice-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.65rem;
}

@media (min-width: 640px) {
  .choice-row {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (min-width: 1024px) {
  .choice-row {
    grid-template-columns: repeat(7, 1fr);
  }
}

.choice-row button {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 0.75rem 0.5rem;
  min-height: 70px;
  background: #ffffff;
  border: 1px solid #d9e2ec;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.choice-row button:hover {
  border-color: var(--gold, #c5a059);
  background: #fdfbf7;
  transform: translateY(-2px);
}

.choice-row button.active {
  border-color: var(--gold, #c5a059);
  background: #fdfbf7;
  box-shadow: 0 4px 12px rgba(197, 160, 89, 0.2);
  outline: 2px solid var(--gold, #c5a059);
  outline-offset: -1px;
}

.choice-heart {
  font-size: 1.25rem;
  color: var(--gold, #9b7628);
  line-height: 1;
  margin-bottom: 0.35rem;
}

.choice-row small {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--blue, #0e325f);
  text-align: center;
  line-height: 1.2;
}

.submit-request-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.95rem 1.5rem;
  font-size: 1rem;
  font-weight: 600;
  background: var(--blue, #0e325f);
  color: #ffffff;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 0.75rem;
  transition: background 0.2s ease, transform 0.15s ease;
}

.submit-request-btn:hover {
  background: var(--bright, #1b5cb8);
  transform: translateY(-1px);
}

/* Info Sidebar */
.info-panel {
  background: #ffffff;
  border: 1px solid rgba(14, 50, 95, 0.1);
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(14, 50, 95, 0.05);
  padding: 2rem;
}

.info-panel-header h3 {
  font-family: var(--font-serif, "Cinzel", "Playfair Display", Georgia, serif);
  font-size: 1.25rem;
  color: var(--blue, #0e325f);
  margin: 0 0 0.5rem;
}

.info-panel p {
  font-size: 0.875rem;
  line-height: 1.65;
  color: var(--text-muted, #4b5d73);
  margin: 0;
}

.panel-divider {
  margin: 1.5rem 0;
  border: 0;
  border-top: 1px solid rgba(14, 50, 95, 0.08);
}

.panel-subtitle {
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--gold, #9b7628);
  margin: 0 0 1rem;
}

.step-list {
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
}

.step-item {
  display: flex;
  gap: 0.85rem;
  align-items: flex-start;
}

.step-icon {
  font-size: 1.1rem;
  color: var(--gold, #9b7628);
  line-height: 1.2;
}

.step-item strong {
  display: block;
  font-size: 0.875rem;
  color: var(--blue, #0e325f);
  margin-bottom: 0.2rem;
}

.step-item p {
  font-size: 0.8125rem;
  color: var(--text-muted, #62748a);
  line-height: 1.45;
}

.office-help-box {
  margin-top: 1.75rem;
  padding: 1.25rem;
  background: #edf4fb;
  border-radius: 10px;
  border: 1px solid rgba(14, 50, 95, 0.08);
}

.office-help-box h4 {
  font-size: 0.875rem;
  color: var(--blue, #0e325f);
  margin: 0 0 0.35rem;
}

.office-help-box p {
  font-size: 0.8125rem;
  color: var(--text-muted, #4b5d73);
  margin: 0 0 0.75rem;
}

.link-contact {
  display: inline-block;
  font-size: 0.8125rem;
  font-weight: 700;
  color: var(--bright, #1b5cb8);
  text-decoration: none;
}

.link-contact:hover {
  text-decoration: underline;
}
</style>

