<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import AuthPortal from './components/AuthPortal.vue'
import NovenaSlider from './components/NovenaSlider.vue'
import SiteLayout from './components/SiteUI/Layout/SiteLayout.vue'
import PageHero from './components/SiteUI/UI/PageHero.vue'
import SectionTitle from './components/SiteUI/UI/SectionHeader.vue'

const route = ref('home')
const syncRoute = () => { route.value = location.hash.replace(/^#\//, '').split('/')[0] || 'home'; scrollTo(0, 0) }
onMounted(() => { syncRoute(); addEventListener('hashchange', syncRoute) })
onUnmounted(() => removeEventListener('hashchange', syncRoute))

const active = computed(() => route.value)
const livestream = ref({ is_live: false, title: null, url: 'https://www.facebook.com/PilarShrineSorsogon' })
let livestreamTimer

const refreshLivestream = async () => {
  try {
    const response = await fetch('/api/livestream-status', { headers: { Accept: 'application/json' } })
    if (response.ok) livestream.value = await response.json()
  } catch {
    // Keep the last known state when the status endpoint is temporarily unavailable.
  }
}

onMounted(() => {
  refreshLivestream()
  livestreamTimer = setInterval(refreshLivestream, 15000)
})
onUnmounted(() => clearInterval(livestreamTimer))
const church = '/images/church-interior.png'
const parishAerial = '/images/pilar-shrine-aerial.png'
const altar = '/images/pilar-shrine-sanctuary.jpg'
const massScheduleImage = '/images/pilar-mass-schedule.jpg'
const pillarOfficial = '/images/our-lady-of-the-pillar-official.jpg'
const prayer = 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=1400&q=85'
const news = [
  {
    title: 'May Crowning Celebration 2025',
    category: 'Parish Life',
    date: 'May 10, 2025',
    place: 'Church Grounds',
    image: 'https://images.unsplash.com/photo-1473177104440-ffee2f376098?auto=format&fit=crop&w=900&q=80',
    description: 'Join our parish community for this sacred and joyful celebration in honor of the Blessed Virgin Mary, featuring floral offerings, Marian hymns, and community fellowship.',
    fullText: 'Join our parish community for this sacred and joyful celebration in honor of the Blessed Virgin Mary. The May Crowning is a venerable Marian tradition uniting devotees and families of our shrine in offering flowers, prayers, and hymns to Our Lady of the Pillar. Families and children are encouraged to participate in the floral offering and the community fellowship following the Holy Mass.',
  },
  {
    title: 'Parish Fiesta Schedule',
    category: 'Liturgical Feast',
    date: 'May 1, 2025',
    place: 'Parish Grounds',
    image: 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=900&q=80',
    description: 'Celebrate the vibrant patronal spirit of our shrine with solemn Masses, novena prayers, cultural exhibits, and thanksgiving celebrations for the whole community.',
    fullText: 'Celebrate the vibrant patronal spirit of our shrine with solemn Masses, novena prayers, cultural exhibits, and thanksgiving celebrations. The festivities bring together parishioners, pilgrims, and visitors in expressing gratitude for the continuous maternal protection of Our Lady of the Pillar over our municipality.',
  },
  {
    title: 'Youth Camp 2025',
    category: 'Youth Ministry',
    date: 'April 20, 2025',
    place: 'Retreat House',
    image: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=900&q=80',
    description: 'An inspiring spiritual formation weekend for young parishioners focused on faith leadership, communal worship, and active ministry involvement.',
    fullText: 'An inspiring spiritual formation weekend for young parishioners focused on faith leadership, communal worship, and active ministry involvement. The Youth Camp provides young Catholics an opportunity to deepen their relationship with Christ, cultivate Christian fellowship, and develop active roles in shrine ministries.',
  },
]

const announcements = [
  {
    title: 'Blessed Mother Statue Procession',
    date: 'May 10, 2025',
    place: 'Church Grounds & Town Proper',
    badge: 'Upcoming',
    description: 'Annual Marian floral offering and solemn candlelight procession honoring Nuestra Señora del Pillar.',
  },
  {
    title: 'Holy Week 2025 Schedule',
    date: 'April 8, 2025',
    place: 'Parish Shrine & Chapels',
    badge: 'Liturgical Notice',
    description: 'Complete schedules for Palm Sunday, Chrism Mass, Visita Iglesia, Seven Last Words, and the Solemn Easter Vigil.',
  },
  {
    title: 'Parishioner Dinner Fellowship',
    date: 'March 25, 2025',
    place: 'Parish Pastoral Center',
    badge: 'Community',
    description: 'An evening of fraternal fellowship and thanksgiving for parish volunteers, pastoral councils, and ministry leaders.',
  },
]

const activeNewsModal = ref(null)
const products = ['Pillar Statue','Pillar Rosary','Marian Prayer Book','Novena Booklet','Pillar Medal','Parish Shirt','Devotional Candle','Souvenir Keychain']
const productImages = [prayer,'https://images.unsplash.com/photo-1602173574767-37ac01994b2a?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1603561596112-db1d3d140b8c?auto=format&fit=crop&w=600&q=80']
const dailyScheduleLabels = new Set([
  'Tuesday, Thursday and Friday',
  'Saturday',
  'Anticipated Mass (Saturday)',
])

const splitScheduleLine = (line) => {
  const separator = ' — '
  const position = line.indexOf(separator)

  return position === -1
    ? { label: line, details: '' }
    : { label: line.slice(0, position), details: line.slice(position + separator.length) }
}

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
  <SiteLayout :active="active">
  <a
    v-if="livestream.is_live"
    class="livestream-alert"
    :href="livestream.url"
    target="_blank"
    rel="noopener noreferrer"
    aria-live="polite"
  >
    <span class="livestream-dot" aria-hidden="true"></span>
    <strong>LIVE NOW</strong>
    <span>{{ livestream.title || 'Watch Pilar Shrine on Facebook' }}</span>
    <b aria-hidden="true">Watch now &rarr;</b>
  </a>
  <main>
    <template v-if="route === 'home'">
      <section class="home-hero">
        <video class="home-hero-video" autoplay muted loop playsinline preload="metadata" :poster="'/images/church-interior.png'" aria-hidden="true">
          <source :src="'/videos/pilar-shrine-hero-h264.mp4'" type="video/mp4; codecs=avc1.640028">
        </video>
        <div class="page-width hero-copy"><em>Welcome to the</em><h1>Diocesan Shrine and Parish of<br>Our Lady of the Pillar </h1><div class="gold-rule left">✣</div><p>Home of the Episcopally Crowned Image of Our Lady of the Pillar<br>Patroness of the Town of Pilar, Province of Sorsogon</p><div><a class="button" href="#/schedule">▣ View mass schedule</a><a class="button secondary" href="#/sacraments">♙ Sacrament requirements</a></div></div>
      </section>
      <section class="intro-grid page-width"><article><h3>About our parish</h3><p>Established in 1862, the Diocesan Shrine and Parish of Our Lady of the Pillar serves as the spiritual home and cultural anchor for the Catholic community in Pilar, Sorsogon. Under the patronage of Nuestra Señora del Pilar, holding the distinct privilege of enshrined distinction as the only crowned image in both the province and the Diocese of Sorsogon. Our parish stands as a testament to generations of faith, community life, and religious tradition in the municipality.</p><a class="button secondary" href="#/about">Read more</a></article><img :src="parishAerial" alt="Aerial view of Our Lady of the Pillar Shrine"><aside><h3>Mass Schedule</h3><h4 class="schedule-group">Daily Mass</h4><p><b>Monday & Wednesday</b> — 5:00 PM</p><p><b>Tuesday, Thursday & Friday</b> — 6:00 AM</p><p><b>Saturday</b> — 6:00 AM</p><p><b>Anticipated Mass (Saturday)</b> — 5:00 PM</p><h4 class="schedule-group">Sunday Mass</h4><p><b>5:00 AM</b> — Holy Mass</p><p><b>7:30 AM</b> — Holy Mass</p><p><b>5:00 PM</b> — Holy Mass</p></aside></section>
    </template>

    <template v-else-if="route === 'about'">
      <div class="about-page">
        <!-- About Hero Section (visual parity with Home hero) -->
        <section class="about-hero" :style="{ backgroundImage: `url(${church})` }">
          <div class="page-width hero-copy">
            <em>Heritage &amp; History</em>
            <h1>About Our Parish &amp; Shrine</h1>
            <div class="gold-rule left">✣</div>
            <p>Established in 1862, the Diocesan Shrine and Parish of Our Lady of the Pillar serves as the spiritual home and cultural anchor for the Catholic community in Pilar, Sorsogon.</p>
          </div>
        </section>

        <!-- 1. About Introduction & Founding Overview -->
        <section class="about-intro page-width">
          <div class="about-intro-grid">
            <article class="about-intro-content">
              <span class="section-eyebrow">About Our Parish</span>
              <h2>Spiritual Home &amp; Cultural Anchor of Pilar</h2>
              <div class="editorial-gold-bar" aria-hidden="true"></div>
              <p class="lead">
                Established in 1862, the Diocesan Shrine and Parish of Our Lady of the Pillar serves as the spiritual home and cultural anchor for the Catholic community in Pilar, Sorsogon. Under the patronage of Nuestra Señora del Pilar, holding the distinct privilege of enshrined distinction as the only crowned image in both the province and the Diocese of Sorsogon.
              </p>
              <p>
                Our parish stands as a testament to generations of faith, community life, and religious tradition in the municipality. From its early missionary settlements to its solemn elevation as a diocesan shrine, it remains a sanctuary of prayer, peace, and spiritual comfort for all devotees.
              </p>

              <!-- Quick Facts Grid -->
              <div class="about-quick-facts">
                <div class="fact-card">
                  <span class="fact-icon">✣</span>
                  <div>
                    <strong>Established 1862</strong>
                    <small>Over 160 years of Catholic tradition</small>
                  </div>
                </div>
                <div class="fact-card">
                  <span class="fact-icon">♛</span>
                  <div>
                    <strong>Diocesan Shrine</strong>
                    <small>Only crowned image in Sorsogon</small>
                  </div>
                </div>
                <div class="fact-card">
                  <span class="fact-icon">⌖</span>
                  <div>
                    <strong>Pilar, Sorsogon</strong>
                    <small>Diocese of Sorsogon, Philippines</small>
                  </div>
                </div>
              </div>
            </article>

            <div class="about-intro-visual">
              <div class="visual-frame">
                <img :src="parishAerial" alt="Aerial view of Our Lady of the Pillar Shrine">
                <div class="visual-caption">
                  <span>Diocesan Shrine</span>
                  <strong>Our Lady of the Pillar</strong>
                  <small>Binanuahan, Pilar, Sorsogon · Established 1862</small>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 2. Parish History Section -->
        <section id="history" class="about-history page-width">
          <div class="section-header-block">
            <span class="section-eyebrow">Our Heritage</span>
            <h2>The History of Our Parish</h2>
            <div class="gold-rule" aria-hidden="true">✣</div>
            <p class="section-subtitle">Traced through centuries of faith, missionary devotion, and the foundation of our community.</p>
          </div>

          <!-- History Chapter 1 -->
          <article class="history-chapter">
            <div class="chapter-badge">
              <span>1569 – 1574</span>
              <b>Early Evangelization</b>
            </div>
            <div class="chapter-content">
              <h3>The Seeds of Christianity in Ibalon</h3>
              <p class="drop-cap-paragraph">
                The seed of Christianity was sowed in the Bicol Region in 1569 in the person of Captain Luis Enriquez de Guzman and Fray Alonzo Jimenez, an Augustinian friar This was probably during the Legazpi-Urdaneta expedition.
              </p>
              <p>
                The cross and the sword, symbolized by Urdaneta and Legazpi respectively, went together hand and hand throughout the islands, the "cross soothing the wounds inflicted by the sword." In Sorsogon, known as the "Ibalon of the region of the Camarines," evangelization by missionary friars started around 1574 and this spread throughout the province.
              </p>
            </div>
          </article>

          <!-- History Chapter 2 -->
          <article class="history-chapter">
            <div class="chapter-badge">
              <span>1635 – 1861</span>
              <b>Mission &amp; Civil Decree</b>
            </div>
            <div class="chapter-content">
              <h3>The Abucay-Catamlangan Mission &amp; Civil Organization</h3>
              <p>
                The missionaries, besides preaching the gospel and baptizing the people, also taught the rudiments of house building and the art of civilized living among the natives. Around 1635, a missionary settlement, known as "Abucay-Catamlangan Mission" was organized by a certain Father Bartolome de Espritu Santo, OFM. Due to its proximity, this settlement was under the jurisdiction of Cagsawa, Albay. But on August 6, 1861, by virtue of a decree from the Superior Government of Manila, Pilar was formally organized as a town which included the barrios of Putiao, Sto. Niño, Sapa and Catamlangan of Cagsawa (now Daraga) Albay, and of Inang and Panlatuan of Albay.
              </p>
            </div>
          </article>

          <!-- History Chapter 3 (Two-column layout with sidebar) -->
          <article class="history-chapter history-two-col">
            <div class="chapter-col-main">
              <div class="chapter-badge">
                <span>1861 – 1862</span>
                <b>Titular Patroness &amp; Parroquia</b>
              </div>
              <div class="chapter-content">
                <h3>The Sacred Images &amp; Foundation of the Parish</h3>
                <p>
                  In the sitio of Sto. Niño, previously Langatong and now Binanuahan, a wealthy businessman, Felix Milleza, touched by Christianity, donated a foot-high image of the Child Jesus holding the cross to the people. There was already a "capilla" constructed through the well-attested religiosity of the people. This image was first entrusted under the care of Capitan Luis Loriaga and was supposed to be handed down to the next hermano or hermana until changes of ecclesiastical officials. The custom was delegated only to the hermano mayor but was stopped when it reached the hands of a family in Binanuahan who claimed ownership of the image. The real owner is the Church, in other words, the people who comprise the Church. An ecclesiastical memorandum to Sto. Niño ordered the assignment of a Curate named Padre Presbetero Eduardo as the first parish priest of the newly organized pueblo.
                </p>
                <p>
                  The gobernadorcillo, Sabas Milleza, brother of Felix Milleza, also donated an image of "Our Lady of the Pillar" to the people, just like the image of the Sto. Niño. In 1861, the town, then called Sto. Niño, adopted the name Pilar, in honor of the then new-born infant princess, Pilar, daughter of the rulers of Spain, King Philip II and Queen Isabel I. The town became a full-fledged parish, with Our Lady of the Pillar as its titular. This was probably in 1862 because of "parroquia de Pillar 1862" as inscribed in the two bells in the parish. The Parish of Pilar is located east of Donsol and west of Castilla, 56 kilometers from the provincial capital.
                </p>
              </div>
            </div>

            <!-- Supporting Sidebar / Visual Card -->
            <aside class="history-sidebar">
              <div class="sidebar-visual">
                <img :src="altar" alt="Sanctuary and altar of Our Lady of the Pillar Shrine">
              </div>
              <div class="sidebar-info">
                <div class="quote-badge">
                  <span class="quote-symbol">❝</span>
                  <strong>parroquia de Pillar 1862</strong>
                  <p>Inscribed upon the two historic parish bells, marking the canonical founding of our parish community.</p>
                </div>
                <div class="sidebar-meta">
                  <div>
                    <b>Jurisdiction</b>
                    <span>East of Donsol &amp; West of Castilla</span>
                  </div>
                  <div>
                    <b>Distance</b>
                    <span>56 km from provincial capital</span>
                  </div>
                </div>
              </div>
            </aside>
          </article>
        </section>

        <!-- 3. Historic Milestone Card (October 12, 2018) -->
        <section id="milestones" class="about-milestone page-width">
          <div class="milestone-card">
            <div class="milestone-watermark" aria-hidden="true">2018</div>
            <div class="milestone-header">
              <span class="milestone-eyebrow">✦ Important Historical Date &amp; Parish Milestone</span>
              <time class="milestone-date" datetime="2018-10-12">October 12, 2018</time>
              <h2 class="milestone-title">Solemn Dedication, Declaration as Diocesan Shrine &amp; Episcopal Coronation</h2>
            </div>
            <p class="milestone-lead">
              Solemn Dedication of the Church, Declaration as Diocesan Shrine and Episcopal Coronation of the Image of Our Lady of the Pillar
            </p>
            <div class="milestone-badges">
              <div class="milestone-badge-item">
                <span class="badge-icon">✣</span>
                <div>
                  <strong>Solemn Dedication</strong>
                  <small>Solemn Dedication of the Church</small>
                </div>
              </div>
              <div class="milestone-badge-item">
                <span class="badge-icon">♛</span>
                <div>
                  <strong>Diocesan Shrine</strong>
                  <small>Declaration as Diocesan Shrine</small>
                </div>
              </div>
              <div class="milestone-badge-item">
                <span class="badge-icon">👑</span>
                <div>
                  <strong>Episcopal Coronation</strong>
                  <small>Episcopal Coronation of the Image of Our Lady of the Pillar</small>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 4. Sacred Titular: Our Lady of the Pillar Section -->
        <section class="about-titular page-width">
          <div class="about-titular-grid">
            <div class="titular-content">
              <span class="section-eyebrow">Our Patroness &amp; Titular</span>
              <h2>Nuestra Señora del Pilar</h2>
              <h3 class="titular-subtitle">The Episcopally Crowned Mother &amp; Patroness of Pilar</h3>
              <div class="editorial-gold-bar" aria-hidden="true"></div>
              <p>
                Under the loving patronage of Our Lady of the Pillar, the faithful of Pilar have found comfort, protection, and spiritual renewal across generations. The enshrined image holds the distinct privilege of being the only crowned image in both the province and the Diocese of Sorsogon.
              </p>

              <!-- Devotional Highlights -->
              <div class="titular-devotions">
                <div class="devotion-item">
                  <i class="devotion-icon" aria-hidden="true">◷</i>
                  <div>
                    <strong>Monthly Devotion to Our Lady of the Pillar</strong>
                    <span>Every 12th of the Month · 5:00 PM Holy Mass &amp; 6:00 PM Procession</span>
                  </div>
                </div>
                <div class="devotion-item">
                  <i class="devotion-icon" aria-hidden="true">✦</i>
                  <div>
                    <strong>Annual Solemnity &amp; Parish Fiesta</strong>
                    <span>October 12 · Preceded by the Solemn Novena from October 3–11</span>
                  </div>
                </div>
              </div>

              <div class="titular-actions">
                <a class="button" href="#/novenas">View Marian Novena</a>
                <a class="button secondary" href="#/schedule">Mass Schedule</a>
              </div>
            </div>

            <div class="titular-visual">
              <div class="titular-image-card">
                <img :src="pillarOfficial" alt="Episcopally Crowned Image of Our Lady of the Pillar">
                <div class="titular-badge">
                  <span>Enshrined Titular</span>
                  <strong>Nuestra Señora del Pilar</strong>
                  <small>Patroness of the Town of Pilar, Province of Sorsogon</small>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- 5. Pilgrimage & Shrine Welcome Banner -->
        <section class="about-pilgrimage page-width">
          <div class="pilgrimage-banner">
            <div class="pilgrimage-copy">
              <span>Pilgrimage &amp; Worship</span>
              <h2>Visit the Diocesan Shrine &amp; Parish</h2>
              <p>Experience the sacred peace and Marian grace of Our Lady of the Pillar in Binanuahan, Pilar, Sorsogon. All pilgrims and faithful are warmly welcomed.</p>
            </div>
            <div class="pilgrimage-actions">
              <a class="button" href="#/schedule">Liturgical Schedule</a>
              <a class="button secondary" href="#/sacraments">Sacraments</a>
              <a class="button secondary" href="#/contact">Contact Us</a>
            </div>
          </div>
        </section>
      </div>
    </template>

    <template v-else-if="route === 'schedule'">
      <div class="schedule-page">
        <!-- Mass Schedule Hero Section (Home visual parity) -->
        <section class="schedule-hero" :style="{ backgroundImage: `url(${massScheduleImage})` }">
          <div class="page-width hero-copy">
            <em>Liturgical Services</em>
            <h1>Mass &amp; Liturgical Schedule</h1>
            <div class="gold-rule left">✣</div>
            <p>Join our parish community in sacred prayer and worship. Mass schedules may adjust during parish feasts and solemnities.</p>
          </div>
        </section>

        <div class="page-width schedule-content-wrap">
          <!-- Notice / Important Information Banner -->
          <div class="schedule-notice-banner" role="note">
            <span class="notice-icon" aria-hidden="true">ℹ</span>
            <div class="notice-text">
              <strong>Liturgical Observance &amp; Livestream Notice</strong>
              <p>Mass schedules may change on holy days of obligation, solemnities, and special parish occasions. Sunday Holy Masses at <b>7:30 AM</b> and <b>5:00 PM</b> are broadcast live on our official Facebook page.</p>
            </div>
          </div>

          <!-- Two-Column Schedule Layout -->
          <div class="schedule-layout">
            <!-- Left Column: Schedule Cards -->
            <div class="schedule-list">
              <!-- Daily Mass -->
              <article class="schedule-card">
                <div class="card-header-row">
                  <span class="card-icon" aria-hidden="true">◷</span>
                  <div>
                    <h2>Daily Mass</h2>
                    <span class="card-tag">Monday to Saturday</span>
                  </div>
                </div>
                <div class="schedule-details-list">
                  <div class="schedule-item">
                    <span class="item-day">Monday &amp; Wednesday</span>
                    <span class="item-time">5:00 PM — Holy Mass</span>
                  </div>
                  <div class="schedule-item">
                    <span class="item-day">Tuesday, Thursday &amp; Friday</span>
                    <span class="item-time">6:00 AM — Holy Mass</span>
                  </div>
                  <div class="schedule-item">
                    <span class="item-day">Saturday</span>
                    <span class="item-time">6:00 AM — Holy Mass</span>
                  </div>
                  <div class="schedule-item highlighted">
                    <span class="item-day">Anticipated Mass (Saturday)</span>
                    <span class="item-time">5:00 PM — Anticipated Sunday Mass</span>
                  </div>
                </div>
              </article>

              <!-- Sunday Mass -->
              <article class="schedule-card">
                <div class="card-header-row">
                  <span class="card-icon" aria-hidden="true">✝</span>
                  <div>
                    <h2>Sunday Mass</h2>
                    <span class="card-tag">Lord's Day Celebrations</span>
                  </div>
                </div>
                <div class="schedule-details-list">
                  <div class="schedule-item">
                    <span class="item-day">Early Morning</span>
                    <span class="item-time">5:00 AM — Holy Mass</span>
                  </div>
                  <div class="schedule-item">
                    <span class="item-day">Morning (Live)</span>
                    <span class="item-time">7:30 AM — Holy Mass <span class="live-badge">FB Live</span></span>
                  </div>
                  <div class="schedule-item">
                    <span class="item-day">Afternoon (Live)</span>
                    <span class="item-time">5:00 PM — Holy Mass <span class="live-badge">FB Live</span></span>
                  </div>
                </div>
              </article>

              <!-- Sacrament of Reconciliation (Confession) -->
              <article class="schedule-card">
                <div class="card-header-row">
                  <span class="card-icon" aria-hidden="true">✦</span>
                  <div>
                    <h2>Sacrament of Reconciliation</h2>
                    <span class="card-tag">Confession &amp; Spiritual Healing</span>
                  </div>
                </div>
                <div class="schedule-details-list">
                  <div class="schedule-item">
                    <span class="item-day">Every First Thursday of the Month</span>
                    <span class="item-time">5:00 PM — Confession</span>
                  </div>
                </div>
              </article>

              <!-- Monthly Devotion to Our Lady of the Pillar -->
              <article class="schedule-card">
                <div class="card-header-row">
                  <span class="card-icon" aria-hidden="true">♛</span>
                  <div>
                    <h2>Monthly Devotion to Our Lady of the Pillar</h2>
                    <span class="card-tag">Patronal Devotional Day</span>
                  </div>
                </div>
                <div class="schedule-details-list">
                  <div class="schedule-item">
                    <span class="item-day">Every 12th of the Month</span>
                    <span class="item-time">5:00 PM — Holy Mass</span>
                  </div>
                  <div class="schedule-item">
                    <span class="item-day">Procession</span>
                    <span class="item-time">6:00 PM — Marian Procession</span>
                  </div>
                </div>
              </article>

              <!-- Special Liturgical Activities -->
              <article class="schedule-card">
                <div class="card-header-row">
                  <span class="card-icon" aria-hidden="true">▦</span>
                  <div>
                    <h2>Special Liturgical Activities</h2>
                    <span class="card-tag">Monthly Observances &amp; Chapels</span>
                  </div>
                </div>
                <div class="schedule-details-list">
                  <div class="schedule-item">
                    <span class="item-day">Every First Tuesday</span>
                    <span class="item-time">6:00 AM — Healing Mass</span>
                  </div>
                  <div class="schedule-item">
                    <span class="item-day">Every First Monday</span>
                    <span class="item-time">6:00 AM — Misa sa Campo Santo</span>
                  </div>
                  <div class="schedule-item">
                    <span class="item-day">First Saturday</span>
                    <span class="item-time">6:00 AM — Mass at Our Lady of Fatima Chapel (Banuyo)</span>
                  </div>
                  <div class="schedule-item">
                    <span class="item-day">Every First Friday</span>
                    <span class="item-time">Holy Hour after Holy Mass</span>
                  </div>
                </div>
              </article>
            </div>

            <!-- Right Column: Supporting Shrine Visual & Parish Office Panel -->
            <aside class="schedule-sidebar">
              <!-- Shrine Visual Card -->
              <div class="sidebar-visual-card">
                <img :src="altar" alt="Sanctuary and Altar of Our Lady of the Pillar Shrine">
                <div class="sidebar-visual-caption">
                  <span>Our Place of Worship</span>
                  <strong>Our Lady of the Pillar Shrine</strong>
                  <small>Diocesan Shrine &amp; Parish · Pilar, Sorsogon</small>
                </div>
              </div>

              <!-- Parish Office Supporting Panel -->
              <div class="parish-office-card">
                <div class="office-header">
                  <span class="section-eyebrow">Parish Information</span>
                  <h3>Parish Office &amp; Services</h3>
                </div>
                <p class="office-desc">For Mass intentions, certificate requests, or sacrament inquiries, please visit or contact our parish office.</p>
                
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
                  <a class="button" href="#/forms">Request Mass Intention</a>
                  <a class="button secondary" href="#/sacraments">Sacrament Requirements</a>
                </div>
              </div>
            </aside>
          </div>
        </div>
      </div>
    </template>

    <template v-else-if="route === 'sacraments'">
      <div class="sacraments-page">
        <!-- Sacraments Hero Section (Full Home visual parity) -->
        <section class="sacraments-hero" :style="{ backgroundImage: `url(${church})` }">
          <div class="page-width hero-copy">
            <em>Sacred Mysteries &amp; Celebrations</em>
            <h1>Parish Sacraments &amp; Guidelines</h1>
            <div class="gold-rule left">✣</div>
            <p>
              The Sacraments of the Church are visible signs of God’s grace instituted by Christ.
              Discover sacramental preparations, canonical guidelines, and pastoral requirements at the Diocesan Shrine of Our Lady of the Pillar.
            </p>
          </div>
        </section>

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

              <!-- Parish Office Supporting Panel (Brand parity with Mass Schedule) -->
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

    <template v-else-if="route === 'news' || route === 'events'">
      <div class="news-page">
        <!-- News Hero Section (Full Home visual parity) -->
        <section class="news-hero" :style="{ backgroundImage: `url(${parishAerial})` }">
          <div class="page-width hero-copy">
            <em>Parish Life &amp; Announcements</em>
            <h1>News &amp; Announcements</h1>
            <div class="gold-rule left">✣</div>
            <p>
              Stay informed with parish news, upcoming events, liturgical celebrations, announcements, and community activities.
            </p>
          </div>
        </section>

        <div class="page-width news-content-wrap">
          <!-- Important Announcements Section -->
          <section class="news-announcements-section" aria-labelledby="announcements-heading">
            <div class="news-section-header">
              <span class="news-section-eyebrow">Latest Advisories</span>
              <h2 id="announcements-heading">Parish Announcements</h2>
            </div>

            <div class="news-announcements-grid">
              <article v-for="a in announcements" :key="a.title" class="news-announcement-card">
                <div class="announcement-header">
                  <span class="announcement-badge">{{ a.badge }}</span>
                  <time class="announcement-date">◷ {{ a.date }}</time>
                </div>
                <h3>{{ a.title }}</h3>
                <p class="announcement-desc">{{ a.description }}</p>
                <div class="announcement-meta">
                  <span class="announcement-place">⌖ {{ a.place }}</span>
                </div>
              </article>
            </div>
          </section>

          <!-- Featured News Article Section -->
          <section class="news-featured-section" aria-labelledby="featured-heading">
            <div class="news-section-header">
              <span class="news-section-eyebrow">Featured Story</span>
              <h2 id="featured-heading">Latest from Our Community</h2>
            </div>

            <article class="news-featured-card">
              <div class="featured-media">
                <img :src="news[0].image" :alt="news[0].title">
                <span class="featured-tag">{{ news[0].category }}</span>
              </div>
              <div class="featured-content">
                <div class="featured-meta">
                  <time class="featured-date">◷ {{ news[0].date }}</time>
                  <span class="featured-dot">•</span>
                  <span class="featured-place">⌖ {{ news[0].place }}</span>
                </div>
                <h3>{{ news[0].title }}</h3>
                <div class="gold-rule left small">✣</div>
                <p class="featured-desc">{{ news[0].description }}</p>
                <div class="featured-actions">
                  <button class="button" type="button" @click="activeNewsModal = news[0]">
                    Read Full Story
                  </button>
                  <a class="button secondary" href="#/events">View All Events</a>
                </div>
              </div>
            </article>
          </section>

          <!-- News & Events Grid Section -->
          <section class="news-grid-section" aria-labelledby="recent-news-heading">
            <div class="news-section-header">
              <span class="news-section-eyebrow">Parish Updates &amp; Events</span>
              <h2 id="recent-news-heading">Recent News &amp; Events</h2>
            </div>

            <div class="news-cards-grid">
              <article v-for="n in news" :key="n.title" class="modern-news-card">
                <div class="card-media">
                  <img :src="n.image" :alt="n.title">
                  <span class="card-category-badge">{{ n.category }}</span>
                </div>
                <div class="card-body">
                  <div class="card-meta">
                    <time class="meta-date">◷ {{ n.date }}</time>
                    <span class="meta-place">⌖ {{ n.place }}</span>
                  </div>
                  <h3>{{ n.title }}</h3>
                  <p class="card-summary">{{ n.description }}</p>
                  <div class="card-footer">
                    <button class="button secondary card-cta" type="button" @click="activeNewsModal = n">
                      Read more &rarr;
                    </button>
                  </div>
                </div>
              </article>
            </div>
          </section>

          <!-- Inquiries / Pastoral Notice Footer Banner -->
          <div class="news-inquiry-banner" role="note">
            <div class="inquiry-icon" aria-hidden="true">✉</div>
            <div class="inquiry-content">
              <strong>Have Parish News or an Announcement to Share?</strong>
              <p>
                Parish ministries, chapels, and apostolates can submit liturgical notices and event write-ups to the Parish Office for inclusion in Sunday announcements and online updates.
              </p>
            </div>
            <a class="button" href="#/contact">Contact Parish Office</a>
          </div>
        </div>

        <!-- Article Detail Modal Dialog -->
        <div v-if="activeNewsModal" class="news-modal-backdrop" @click.self="activeNewsModal = null" role="dialog" aria-modal="true" :aria-label="activeNewsModal.title">
          <div class="news-modal-card">
            <button class="modal-close-btn" type="button" @click="activeNewsModal = null" aria-label="Close article">✕</button>
            <div class="modal-image-wrap">
              <img :src="activeNewsModal.image" :alt="activeNewsModal.title">
              <span class="modal-badge">{{ activeNewsModal.category }}</span>
            </div>
            <div class="modal-body">
              <div class="modal-meta">
                <time>◷ {{ activeNewsModal.date }}</time>
                <span>•</span>
                <span>⌖ {{ activeNewsModal.place }}</span>
              </div>
              <h2>{{ activeNewsModal.title }}</h2>
              <div class="gold-rule left small">✣</div>
              <p class="modal-lead">{{ activeNewsModal.description }}</p>
              <p class="modal-fulltext">{{ activeNewsModal.fullText }}</p>
              <div class="modal-actions">
                <button class="button secondary" type="button" @click="activeNewsModal = null">Close Article</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <template v-else-if="route === 'novenas'">
      <PageHero class="novena-landing-hero" eyebrow="Prayer & devotion" title="Novenas & Prayer Devotions" description="Deepen your faith through daily prayer and Marian devotion." :image="altar"/><section class="page-width novena-feature-section"><div class="feature-card"><img class="novena-official-image" :src="pillarOfficial" alt="Official image of Our Lady of the Pillar with the Child Jesus"><div class="novena-feature-copy"><span>Featured novena</span><h1>Our Lady of the<br>Pillar Novena</h1><p>Join us in a nine-day journey of prayer and preparation for the Feast of Our Lady of the Pillar.</p><div class="mini-features"><b><i aria-hidden="true">◷</i> Novena Schedule<br><small>October 3–11</small></b><b><i aria-hidden="true">✦</i> Feast Day<br><small>October 12</small></b></div><a class="button" href="#/novena-details">View novena details</a></div></div></section>
    </template>

    <template v-else-if="route === 'novena-details'">
      <section class="novena-page soft-page">
        <SectionTitle title="Novena to Our Lady of the Pillar" subtitle="Nine days of prayer in preparation for the parish feast."/>
        <div class="page-width">
          <div class="novena-calendar">
            <div><span>Novena begins</span><strong>October 3</strong></div>
            <div><span>Nine days of prayer</span><strong>October 3–11</strong></div>
            <div class="feast-date"><span>Solemnity & feast day</span><strong>October 12</strong></div>
          </div>
          <NovenaSlider />
          <aside class="feast-callout">
            <span>After the nine-day novena</span>
            <h2>Feast of Our Lady of the Pillar</h2>
            <time>October 12</time>
            <p>Join the parish community as we honor Nuestra Señora del Pilar, our patroness, in prayer and celebration.</p>
          </aside>
        </div>
      </section>
    </template>

    <template v-else-if="route === 'store'">
      <section class="store-hero" :style="{backgroundImage:`linear-gradient(90deg,#fff 15%,rgba(255,255,255,.62)),url(${prayer})`}"><div class="page-width"><span>Parish merchandise</span><h1>Support Our Shrine<br>Through Meaningful Items</h1><p>Bring home a reminder of faith, devotion, and prayer.</p></div></section><section class="shop page-width"><aside><h3>Categories</h3><a v-for="c in ['All Items','Statues & Images','Rosaries','Prayer Books','Novena Booklets','Medals & Keychains','Apparel','Candles','Souvenirs']" :key="c">◇ {{ c }}</a></aside><div><h2>All items</h2><div class="product-grid"><article v-for="(p,i) in products" :key="p"><img :src="productImages[i]"><h3>{{ p }}</h3><b>₱{{ [950,250,120,25,80,350,120,70][i] }}.00</b><button>View item &nbsp; 🛒</button></article></div></div></section>
    </template>

    <template v-else-if="route === 'forms'">
      <section class="intention-hero" :style="{backgroundImage:`linear-gradient(90deg,#fff 10%,rgba(255,255,255,.35)),url(${prayer})`}"><div class="page-width"><h1>Online Mass<br>Intention Request</h1><div class="gold-rule left">✣</div><p>Submit your Mass intention online.<br>We will pray for you and your loved ones.</p></div></section><div class="form-layout page-width"><form class="request-form" @submit.prevent><h2>Mass Intention Details</h2><div class="form-row"><label>Name of Requester *<input placeholder="Enter your full name"></label><label>Contact Number *<input placeholder="09XX XXX XXXX"></label><label>Email Address<input type="email" placeholder="Enter your email address"></label></div><label>Intention Type *</label><div class="choice-row"><button v-for="x in ['Thanksgiving','Healing','Birthday','Anniversary','Death Anniversary','Special Intention','Other']" :key="x" type="button">♡<small>{{ x }}</small></button></div><label>Name/s or Intention Being Offered *<input placeholder="Enter name/s or intention"></label><div class="form-row two"><label>Preferred Mass Date *<input type="date"></label><label>Preferred Mass Schedule *<select><option>Select Mass Schedule</option></select></label></div><label>Additional Message or Prayer Intention<textarea placeholder="Write your message or prayer intention..."></textarea></label><button class="button submit">➤ Submit request</button></form><aside class="info-panel"><h2>About Mass Intentions</h2><p>A Mass intention is a special prayer offered during the Holy Mass for a particular intention.</p><hr><h2>How it works</h2><h3>♢ Submit your request</h3><p>Fill out the form with your intention details.</p><h3>♢ We will confirm</h3><p>Our parish office will review your request.</p><h3>♡ We pray for you</h3><p>Our priests and community will pray for your intention.</p></aside></div>
    </template>

    <AuthPortal v-else-if="route === 'login' || route === 'register'" :mode="route" />
    <template v-else><section class="soft-page"><SectionTitle :title="route.replace('-', ' ')" subtitle="This static page is ready for parish content."/></section></template>
  </main>
  </SiteLayout>
</template>

<style src="./components/SiteUI/Theme/designTokens.css"></style>
<style src="./parish.css"></style>
<style src="./modern.css"></style>

<style>

.schedule-group {
  margin: 20px 0 5px;
  color: #9b7628;
  font-size: 11px;
  letter-spacing: 0;
  text-transform: uppercase;
}

.schedule-group:first-of-type {
  margin-top: 0;
}

.schedule-day {
  color: var(--blue);
  font-weight: 700;
}

.novena-feature-section {
  padding-bottom: 70px;
}

.feature-card .novena-official-image {
  object-fit: contain;
  object-position: center;
  background: #293535;
}

.tabs button {
  cursor: pointer;
  transition: border-color .2s, background .2s, transform .2s, box-shadow .2s;
}

.tabs button:first-child {
  border-color: var(--line);
  border-bottom-color: transparent;
}

.tabs button.active,
.tabs button:hover,
.tabs button:focus-visible {
  border-bottom-color: var(--bright);
  background: #f8fbfe;
  transform: translateY(-2px);
  box-shadow: 0 12px 27px rgba(14, 50, 95, .14);
}

.tabs button:focus-visible {
  outline: 2px solid var(--gold);
  outline-offset: 3px;
}

.tabs .sacrament-icon {
  display: grid;
  width: 42px;
  height: 42px;
  margin: 0 auto;
  place-items: center;
  border-radius: 50%;
  background: #edf4fb;
  color: var(--blue);
  font-size: 24px;
  line-height: 1;
}

.tabs button.active .sacrament-icon {
  background: var(--blue);
  color: #fff;
}

.novena-page .section-title {
  padding-bottom: 25px;
}

.novena-calendar {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  overflow: hidden;
  border: 1px solid #dce6ef;
  border-radius: 12px;
  background: #fff;
  box-shadow: var(--shadow);
}

.novena-calendar div {
  padding: 22px 28px;
  border-right: 1px solid #dce6ef;
}

.novena-calendar div:last-child {
  border-right: 0;
}

.novena-calendar span,
.feast-callout > span {
  display: block;
  margin-bottom: 7px;
  color: #65778b;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
}

.novena-calendar strong {
  color: var(--blue);
  font: 700 19px 'Libre Baskerville', serif;
}

.novena-calendar .feast-date {
  background: var(--blue);
}

.novena-calendar .feast-date span,
.novena-calendar .feast-date strong {
  color: #fff;
}

.feast-callout {
  position: relative;
  margin-top: 42px;
  padding: 38px 200px 38px 42px;
  overflow: hidden;
  border-radius: 14px;
  background: linear-gradient(120deg, #062f78, #0758b8);
  color: #fff;
  box-shadow: 0 14px 30px rgba(6, 47, 120, .2);
}

.feast-callout::after {
  position: absolute;
  right: 45px;
  top: 50%;
  content: '12';
  color: rgba(255, 255, 255, .13);
  font: 700 130px/1 'Libre Baskerville', serif;
  transform: translateY(-50%);
}

.feast-callout > span {
  color: #e8c96e;
}

.feast-callout h2 {
  margin: 8px 0;
  color: #fff;
  font: 700 27px 'Libre Baskerville', serif;
}

.feast-callout time {
  color: #f2d77f;
  font-size: 18px;
  font-weight: 700;
}

.feast-callout p {
  max-width: 650px;
  margin-bottom: 0;
  line-height: 1.7;
}

@media (max-width: 720px) {
  .novena-calendar {
    grid-template-columns: 1fr;
  }

  .novena-calendar div {
    border-right: 0;
    border-bottom: 1px solid #dce6ef;
  }

  .feast-callout {
    padding: 30px 26px;
  }

  .feast-callout::after {
    right: 10px;
    opacity: .5;
  }

}

.home-hero {
  position: relative;
  overflow: hidden;
  isolation: isolate;
  background: #dfeaf2;
}

.home-hero::before {
  position: absolute;
  z-index: 1;
  inset: 0;
  content: '';
  background: linear-gradient(90deg, rgba(239, 247, 255, .94) 0%, rgba(239, 247, 255, .68) 45%, rgba(0, 16, 38, .16));
}

.home-hero-video {
  position: absolute;
  z-index: 0;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.home-hero .hero-copy {
  position: relative;
  z-index: 2;
}

@media (prefers-reduced-motion: reduce) {
  .home-hero-video {
    display: none;
  }

  .livestream-dot {
    animation: none;
  }
}

.livestream-alert {
  position: sticky;
  z-index: 25;
  top: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-height: 46px;
  padding: 9px 20px;
  background: #b4232c;
  color: #fff;
  text-align: center;
  box-shadow: 0 5px 14px rgba(80, 0, 5, .2);
}

.livestream-alert:hover,
.livestream-alert:focus-visible {
  background: #941b23;
  color: #fff;
}

.livestream-dot {
  width: 11px;
  height: 11px;
  flex: 0 0 11px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 0 0 0 rgba(255, 255, 255, .75);
  animation: live-pulse 1.5s ease-out infinite;
}

@keyframes live-pulse {
  70% { box-shadow: 0 0 0 9px rgba(255, 255, 255, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); }
}

@media (max-width: 720px) {
  .livestream-alert {
    flex-wrap: wrap;
    gap: 5px 8px;
    font-size: 13px;
  }

  .livestream-alert b {
    width: 100%;
    font-size: 11px;
  }
}
</style>
