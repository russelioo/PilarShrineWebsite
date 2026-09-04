<script setup>
import { computed, ref } from 'vue'

const altar = '/images/pilar-shrine-sanctuary.jpg'

const sacramentOptions = [
  {
    name: 'Baptism',
    title: 'The Sacrament of Holy Baptism',
    icon: 'baptism',
    imageAlt: 'Baptism celebration at Pilar Shrine',
    category: 'Sacrament of Christian Initiation',
    categoryShort: 'Initiation',
    description: 'The sacrament of Baptism is the gateway to Christian life and discipleship. Through water and the Holy Spirit, the baptized are cleansed of sin, reborn as children of God, and incorporated into Christ and His Church.',
    advisory: 'Baptismal seminars are conducted regularly. Parents and godparents must attend the catechetical seminar prior to the scheduled date of Baptism. Please register at the parish office at least two weeks in advance.',
    requirements: [
      'PSA Birth Certificate of the child (original & photocopy)',
      'Photocopy of Valid Government ID of Parents or Legal Guardians',
      'Godparents (at least 2 Catholic sponsors, minimum 16 years old, who have received Confirmation)',
      'Certificate of Attendance in Pre-Baptismal Seminar (Parents & Godparents)',
    ],
    process: [
      'Submit documentary requirements at the Parish Office',
      'Attend the mandatory Pre-Baptismal Catechetical Seminar',
      'Confirm the Baptism schedule with the parish staff',
      'Celebration of the Holy Sacrament of Baptism in the Shrine',
    ],
  },
  {
    name: 'Wedding',
    title: 'The Sacrament of Holy Matrimony',
    icon: 'wedding',
    imageAlt: 'Catholic wedding ceremony at Pilar Shrine',
    category: 'Sacrament of Service & Communion',
    categoryShort: 'Matrimony',
    description: 'Holy Matrimony unites a man and woman in a lifelong covenant of faithful love and partnership before God, blessed with the grace to assist each other in mutual holiness and family life.',
    advisory: 'Couples must make preliminary reservations at the Parish Office at least 3 to 6 months prior to their prospective wedding date to allow adequate time for canonical interviews, marriage banns publication, and Pre-Cana formation.',
    requirements: [
      'Recent Baptismal & Confirmation Certificates with annotation "For Marriage Purposes" (issued within 6 months)',
      'Valid Marriage License from Local Civil Registrar (or PSA Marriage Certificate if civilly married)',
      'Canonical Interview with the Parish Priest or Assigned Clergy',
      'Certificate of Attendance in Pre-Cana Marriage Preparation Seminar',
      'Publication of Marriage Banns in respective home parishes for three consecutive Sundays',
      'Photocopies of Valid IDs of the Bride, Groom, and Principal Sponsors',
    ],
    process: [
      'Visit the Parish Office to reserve date and initiate canonical inquiry',
      'Submit all required legal and ecclesiastical certificates',
      'Attend the Canonical Interview and the Pre-Cana Seminar',
      'Complete the liturgical wedding rehearsal with parish ministers',
      'Solemn Celebration of the Sacrament of Holy Matrimony',
    ],
  },
  {
    name: 'Confirmation',
    title: 'The Sacrament of Confirmation',
    icon: 'confirmation',
    imageAlt: 'Confirmation celebration at Pilar Shrine',
    category: 'Sacrament of Christian Initiation',
    categoryShort: 'Initiation',
    description: 'Confirmation deepens baptismal grace and seals the candidate with the sevenfold gifts of the Holy Spirit, empowering them to bear authentic witness to the Gospel in word, deed, and courageous Christian service.',
    advisory: 'Candidates must have received the Sacraments of Baptism and First Holy Communion and be properly disposed through catechetical instruction and the Sacrament of Reconciliation.',
    requirements: [
      'Baptismal Certificate with Church seal (original & photocopy)',
      'First Holy Communion Certificate',
      'Certificate of Completion in Confirmation Catechetical Formation',
      'Qualified Catholic Confirmation Sponsor (Godparent)',
      'Recent 2x2 ID photo and completed parish registration form',
    ],
    process: [
      'Submit registration form and sacramental documents to Parish Office',
      'Complete catechetical classes and spiritual formation sessions',
      'Participate in the spiritual recollection and Sacrament of Reconciliation',
      'Solemn Conferral of the Sacrament of Confirmation by the Bishop or Delegate',
    ],
  },
  {
    name: 'Anointing',
    title: 'Anointing of the Sick & Viaticum',
    icon: 'anointing',
    imageAlt: 'Anointing of the sick ministry',
    category: 'Sacrament of Healing',
    categoryShort: 'Healing',
    description: 'The Sacrament of the Anointing of the Sick offers divine grace, spiritual fortitude, peace, and healing to the faithful experiencing critical illness, advanced age, or preparing for major surgery.',
    advisory: 'This sacrament may be administered at home, in hospitals, or in the church. In emergencies or imminent danger of death, please contact the Parish Office or emergency parish line immediately at any hour.',
    requirements: [
      'Full Name, age, and medical condition of the person receiving the sacrament',
      'Complete residence address or hospital room details',
      'Contact information and mobile number of immediate family member or caregiver',
      'Indication if the patient is conscious and able to receive Holy Communion (Viaticum)',
    ],
    process: [
      'Contact the Parish Office or pastoral emergency line',
      'Provide patient details, complete address, and current medical status',
      'Coordinate the priest’s pastoral visit and prepare prayerful surroundings',
      'Liturgical celebration of the Anointing of the Sick and prayer of faith',
    ],
  },
  {
    name: 'Funeral',
    title: 'Catholic Funeral Rites & Commendation',
    icon: 'funeral',
    imageAlt: 'Catholic funeral rites at Pilar Shrine',
    category: 'Sacrament of Christian Burial',
    categoryShort: 'Burial Rites',
    description: 'Catholic Funeral Rites commend the departed Christian to the boundless mercy and love of God, offering spiritual comfort, biblical consolation, and the resurrection hope of eternal life to mourning families.',
    advisory: 'Please coordinate with the Parish Office before finalizing burial and interment schedules to ensure priest availability for the Funeral Mass, blessings, and cemetery commendation.',
    requirements: [
      'Official PSA Death Certificate or Local Civil Registrar Certificate',
      'Full name, birth date, and date of passing of the deceased',
      'Preferred date, time, and burial ground location',
      'Contact information of the family coordinator or authorized representative',
    ],
    process: [
      'Visit or call the Parish Office to coordinate schedule and priest availability',
      'Submit death documentation and family coordinator information',
      'Confirm liturgical arrangements, readings, and choir support',
      'Celebration of the Funeral Mass / Blessings and Rite of Committal at the cemetery',
    ],
  },
]

const selectedSacramentIndex = ref(0)
const selectedSacrament = computed(() => sacramentOptions[selectedSacramentIndex.value])
</script>

<template>
  <div class="sacraments-page">
    <div class="page-width sacraments-content-wrap">
      <!-- Pastoral Guidance & Scheduling Banner -->
      <div class="sacraments-notice-banner" role="note">
        <span class="notice-icon" aria-hidden="true">ℹ</span>
        <div class="notice-text">
          <strong>Pastoral Guidance &amp; Advance Scheduling</strong>
          <p>
            Sacraments are sacred encounters with God and the Christian community. Parishioners and couples are encouraged to coordinate with the Parish Office early to fulfill document verification, canonical interviews, and sacramental seminars.
          </p>
        </div>
      </div>

      <!-- Sacrament Selector (Modern, refined interactive tabs) -->
      <div class="sacraments-selector-wrap">
        <div class="sacraments-tabs" role="tablist" aria-label="Select sacrament to view guidelines">
          <button
            v-for="(option, index) in sacramentOptions"
            :key="option.name"
            type="button"
            role="tab"
            :class="['sacrament-tab-btn', { active: selectedSacramentIndex === index }]"
            :aria-selected="selectedSacramentIndex === index"
            :aria-controls="`sacrament-panel-${index}`"
            :id="`sacrament-tab-${index}`"
            @click="selectedSacramentIndex = index"
          >
            <span class="tab-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path v-if="option.icon === 'baptism'" d="M12 2S6.5 8.4 6.5 13a5.5 5.5 0 0 0 11 0C17.5 8.4 12 2 12 2Z"/>
                <g v-else-if="option.icon === 'wedding'"><circle cx="9" cy="12" r="5"/><circle cx="15" cy="12" r="5"/></g>
                <path v-else-if="option.icon === 'confirmation'" d="M13 2c.7 4-3 5.2-3 9a3 3 0 0 0 6 0c2 2 3 4 3 6a7 7 0 0 1-14 0c0-3.5 2-6.6 5-9-.5 3 .5 4.6 2 5.5C9.5 8 13 6.3 13 2Z"/>
                <g v-else-if="option.icon === 'anointing'"><path d="M8 3h8M10 3v5l-3 4v8h10v-8l-3-4V3"/><path d="M9 13h6"/></g>
                <g v-else><path d="M12 3v18M7 8h10"/><path d="M5 21h14"/></g>
              </svg>
            </span>
            <span class="tab-label-group">
              <span class="tab-name">{{ option.name }}</span>
              <span class="tab-sub">{{ option.categoryShort }}</span>
            </span>
          </button>
        </div>
      </div>

      <!-- Two-Column Sacramental Detail Showcase -->
      <div
        class="sacraments-detail-layout"
        role="tabpanel"
        :id="`sacrament-panel-${selectedSacramentIndex}`"
        :aria-labelledby="`sacrament-tab-${selectedSacramentIndex}`"
        aria-live="polite"
      >
        <!-- Left Column: Sacramental Information & Guidelines -->
        <article class="sacrament-main-card">
          <header class="sacrament-card-header">
            <div class="sacrament-title-badge-row">
              <span class="sacrament-category-tag">{{ selectedSacrament.category }}</span>
              <span class="sacrament-badge-pill">Parish Guidelines</span>
            </div>
            <h2>{{ selectedSacrament.title }}</h2>
            <div class="gold-rule left small">✣</div>
            <p class="sacrament-lead-desc">{{ selectedSacrament.description }}</p>
          </header>

          <!-- Requirements Section -->
          <section class="sacrament-sub-section">
            <div class="sub-section-header">
              <span class="sub-section-icon" aria-hidden="true">✓</span>
              <div>
                <h3>Required Documents &amp; Prerequisites</h3>
                <span class="sub-section-hint">Please prepare original and clear photocopies for submission</span>
              </div>
            </div>
            <ul class="requirements-checklist">
              <li v-for="(req, rIdx) in selectedSacrament.requirements" :key="rIdx">
                <span class="check-bullet" aria-hidden="true">✔</span>
                <span>{{ req }}</span>
              </li>
            </ul>
          </section>

          <!-- Process & Procedure Section -->
          <section class="sacrament-sub-section">
            <div class="sub-section-header">
              <span class="sub-section-icon" aria-hidden="true">➔</span>
              <div>
                <h3>Step-by-Step Pastoral Procedure</h3>
                <span class="sub-section-hint">Order of preparation, registration, and celebration</span>
              </div>
            </div>
            <ol class="procedure-steps-list">
              <li v-for="(step, sIdx) in selectedSacrament.process" :key="sIdx">
                <span class="step-num" aria-hidden="true">{{ sIdx + 1 }}</span>
                <div class="step-content">
                  <strong>{{ step }}</strong>
                </div>
              </li>
            </ol>
          </section>

          <!-- Pastoral Advisory Callout inside Left Column -->
          <div class="sacrament-advisory-callout">
            <span class="callout-icon" aria-hidden="true">✦</span>
            <div class="callout-content">
              <strong>Pastoral Advisory for {{ selectedSacrament.name }}</strong>
              <p>{{ selectedSacrament.advisory }}</p>
            </div>
          </div>
        </article>

        <!-- Right Column: Shrine Sanctuary Visual & Parish Office Panel -->
        <aside class="sacrament-sidebar">
          <!-- Framed Altar Visual Card -->
          <div class="sidebar-visual-card">
            <img :src="altar" alt="Altar and Sanctuary of Our Lady of the Pillar Diocesan Shrine">
            <div class="sidebar-visual-caption">
              <span>Sanctuary of Grace</span>
              <strong>Diocesan Shrine Sanctuary</strong>
              <small>Our Lady of the Pillar Parish · Binanuahan, Pilar</small>
            </div>
          </div>

          <!-- Parish Office Supporting Panel -->
          <div class="parish-office-card">
            <div class="office-header">
              <span class="section-eyebrow">Parish Information</span>
              <h3>Parish Office &amp; Services</h3>
            </div>
            <p class="office-desc">
              To register for sacraments, request canonical certificates, or schedule counseling and seminars, please visit or contact our parish office.
            </p>

            <div class="office-meta-list">
              <div class="office-meta-item">
                <span class="meta-icon" aria-hidden="true">⌖</span>
                <div>
                  <b>Location</b>
                  <span>Binanuahan, Pilar, Sorsogon</span>
                </div>
              </div>
              <div class="office-meta-item">
                <span class="meta-icon" aria-hidden="true">☏</span>
                <div>
                  <b>Contact Number</b>
                  <span>0946-869-1254</span>
                </div>
              </div>
              <div class="office-meta-item">
                <span class="meta-icon" aria-hidden="true">✉</span>
                <div>
                  <b>Email Address</b>
                  <span>olppspilarsorsogon@gmail.com</span>
                </div>
              </div>
            </div>

            <div class="office-actions">
              <a class="button" href="#/forms">Request Form / Certificate</a>
              <a class="button secondary" href="#/contact">Contact Parish Office</a>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

