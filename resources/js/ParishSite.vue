<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import SiteNavbar from './components/SiteNavbar.vue'
import SiteFooter from './components/SiteFooter.vue'
import AuthPortal from './components/AuthPortal.vue'
import SectionTitle from './components/SectionTitle.vue'

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
const prayer = 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=1400&q=85'
const news = [
  { title:'May Crowning Celebration 2025', date:'May 10, 2025', place:'Church Grounds', image:'https://images.unsplash.com/photo-1473177104440-ffee2f376098?auto=format&fit=crop&w=900&q=80' },
  { title:'Parish Fiesta Schedule', date:'May 1, 2025', place:'Parish Grounds', image:'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=900&q=80' },
  { title:'Youth Camp 2025', date:'April 20, 2025', place:'Retreat House', image:'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=900&q=80' },
]
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
</script>

<template>
  <SiteNavbar :active="active" />
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
          <source :src="'/videos/pilar-shrine-hero.mp4'" type="video/mp4">
        </video>
        <div class="page-width hero-copy"><em>Welcome to the</em><h1>Diocesan Shrine and Parish of<br>Our Lady of the Pillar </h1><div class="gold-rule left">✣</div><p>Home of the Episcopally Crowned Image of Our Lady of the Pillar<br>Patroness of the Town of Pilar, Province of Sorsogon</p><div><a class="button" href="#/schedule">▣ View mass schedule</a><a class="button secondary" href="#/sacraments">♙ Request sacrament</a></div></div>
      </section>
      <section class="help-panel page-width"><h2>How can we help you?</h2><div class="quick-grid"><a v-for="item in ['Mass Schedule','Mass Intention','Baptism Request','Wedding Request','Sacraments Info','Donate Online','Events & Calendar','Contact Parish']" :key="item" href="#/schedule"><b>♢</b>{{ item }}</a></div></section>
      <section class="intro-grid page-width"><article><h3>About our parish</h3><p>Established in 1862, the Diocesan Shrine and Parish of Our Lady of the Pillar serves as the spiritual home and cultural anchor for the Catholic community in Pilar, Sorsogon. Under the patronage of Nuestra Señora del Pilar, holding the distinct privilege of enshrined distinction as the only crowned image in both the province and the Diocese of Sorsogon. Our parish stands as a testament to generations of faith, community life, and religious tradition in the municipality.</p><a class="button secondary" href="#/about">Read more</a></article><img :src="parishAerial" alt="Aerial view of Our Lady of the Pillar Shrine"><aside><h3>Mass Schedule</h3><h4 class="schedule-group">Daily Mass</h4><p><b>Monday & Wednesday</b> — 5:00 PM</p><p><b>Tuesday, Thursday & Friday</b> — 6:00 AM</p><p><b>Saturday</b> — 6:00 AM</p><p><b>Anticipated Mass (Saturday)</b> — 5:00 PM</p><h4 class="schedule-group">Sunday Mass</h4><p><b>5:00 AM</b> — Holy Mass</p><p><b>7:30 AM</b> — Holy Mass</p><p><b>5:00 PM</b> — Holy Mass</p></aside></section>
    </template>

    <template v-else-if="route === 'about'">
      <section class="soft-page">
        <SectionTitle eyebrow="Home  ›  About Us" title="About Our Parish"/>
        <div class="page-width">
          <img class="wide-image" :src="altar" alt="Interior of church">
          <div class="values">
            <article>
              <div>
                <h2>Our History</h2>
                <p>The seed of Christianity was sowed in the Bicol Region in 1569 in the person of Captain Luis Enriquez de Guzman and Fray Alonzo Jimenez, an Augustinian friar This was probably during the Legazpi-Urdaneta expedition.</p>
                <p>The cross and the sword, symbolized by Urdaneta and Legazpi respectively, went together hand and hand throughout the islands, the "cross soothing the wounds inflicted by the sword." In Sorsogon, known as the "Ibalon of the region of the Camarines," evangelization by missionary friars started around 1574 and this spread throughout the province.</p>
                <p>The missionaries, besides preaching the gospel and baptizing the people, also taught the rudiments of house building and the art of civilized living among the natives. Around 1635, a missionary settlement, known as "Abucay-Catamlangan Mission" was organized by a certain Father Bartolome de Espritu Santo, OFM. Due to its proximity, this settlement was under the jurisdiction of Cagsawa, Albay. But on August 6, 1861, by virtue of a decree from the Superior Government of Manila, Pilar was formally organized as a town which included the barrios of Putiao, Sto. Niño, Sapa and Catamlangan of Cagsawa (now Daraga) Albay, and of Inang and Panlatuan of Albay.</p>
                <p>In the sitio of Sto. Niño, previously Langatong and now Binanuahan, a wealthy businessman, Felix Milleza, touched by Christianity, donated a foot-high image of the Child Jesus holding the cross to the people. There was already a "capilla" constructed through the well-attested religiosity of the people. This image was first entrusted under the care of Capitan Luis Loriaga and was supposed to be handed down to the next hermano or hermana until changes of ecclesiastical officials. The custom was delegated only to the hermano mayor but was stopped when it reached the hands of a family in Binanuahan who claimed ownership of the image. The real owner is the Church, in other words, the people who comprise the Church. An ecclesiastical memorandum to Sto. Niño ordered the assignment of a Curate named Padre Presbetero Eduardo as the first parish priest of the newly organized pueblo.</p>
                <p>The gobernadorcillo, Sabas Milleza, brother of Felix Milleza, also donated an image of "Our Lady of the Pillar" to the people, just like the image of the Sto. Niño. In 1861, the town, then called Sto. Niño, adopted the name Pilar, in honor of the then new-born infant princess, Pilar, daughter of the rulers of Spain, King Philip II and Queen Isabel I. The town became a full-fledged parish, with Our Lady of the Pillar as its titular. This was probably in 1862 because of "parroquia de Pillar 1862" as inscribed in the two bells in the parish. The Parish of Pilar is located east of Donsol and west of Castilla, 56 kilometers from the provincial capital.</p>
              </div>
            </article>
          </div>
          <section class="history-milestones" aria-labelledby="milestones-title">
            <span>Parish milestone</span>
            <h2 id="milestones-title">Important Historical Dates / Milestones</h2>
            <div>
              <time datetime="2018-10-12">October 12, 2018</time>
              <p>Solemn Dedication of the Church, Declaration as Diocesan Shrine and Episcopal Coronation of the Image of Our Lady of the Pillar</p>
            </div>
          </section>
        </div>
      </section>
    </template>

    <template v-else-if="route === 'schedule'">
      <section class="soft-page"><SectionTitle eyebrow="Home  ›  Mass Schedule" title="Mass & Liturgical Schedule"/><div class="schedule-layout page-width"><div><article class="schedule-card" v-for="s in [['Daily Mass','Monday and Wednesday','5:00 PM — Holy Mass|Tuesday, Thursday and Friday — 6:00 AM — Holy Mass|Saturday — 6:00 AM — Holy Mass|Anticipated Mass (Saturday) — 5:00 PM'],['Sunday Mass','Sunday','5:00 AM — Holy Mass|7:30 AM — Holy Mass (FB Live) |5:00 PM — Holy Mass (FB Live)'],['Confession','Every First Thursday of the Month','5:00 PM'],['Monthly Devotion to Our Lady of the Pillar','Every 12th of the Month','5:00 PM — Mass|6:00 PM — Procession'],['Other Liturgical Activities','Special monthly observances','Every First Tuesday — Healing Mass — 6:00 AM|Every First Monday — Misa sa Campo Santo — 6:00 AM|First Saturday — Mass at Our Lady of Fatima Chapel (Banuyo) — 6:00 AM|Every First Friday — Holy Hour after Mass']]" :key="s[0]"><i>♢</i><div><h2>{{ s[0] }}</h2><b>{{ s[1] }}</b><p v-for="line in s[2].split('|')" :key="line"><template v-if="s[0] === 'Daily Mass' && dailyScheduleLabels.has(splitScheduleLine(line).label)"><span class="schedule-day">{{ splitScheduleLine(line).label }}</span> — {{ splitScheduleLine(line).details }}</template><template v-else>{{ line }}</template></p></div></article></div><img class="tall-image" :src="massScheduleImage" alt="Altar at Our Lady of the Pillar Shrine"></div></section>
    </template>

    <template v-else-if="route === 'sacraments'">
      <section class="soft-page"><SectionTitle title="Sacraments"/><div class="tabs page-width"><button v-for="x in ['Baptism','Wedding','Confirmation','Anointing','Funeral']" :key="x">♢<b>{{ x }}</b></button></div><div class="sacrament-layout page-width"><article><h1>Baptism</h1><p class="lead">The sacrament of Baptism is the gateway to Christian life and discipleship.</p><hr><h2>Requirements</h2><ul><li>Birth Certificate (PSA)</li><li>Photocopy of ID of Parents/Guardians</li><li>Godparents (2, at least 16 years old)</li><li>Baptism Seminar Certificate</li></ul><hr><h2>Process</h2><ol><li>Submit requirements</li><li>Attend Baptism Seminar</li><li>Schedule the Baptism</li><li>Celebration of the Sacrament</li></ol><a class="button" href="#/forms">Request Baptism</a></article><img :src="prayer" alt="Baptism"></div></section>
    </template>

    <template v-else-if="route === 'news' || route === 'events'">
      <section class="soft-page"><SectionTitle eyebrow="Home  ›  News & Announcements" title="News & Announcements"/><div class="headline-list page-width"><b>Blessed Mother Statue Procession</b> • May 10, 2025<br><b>Holy Week 2025 Schedule</b> • April 8, 2025<br><b>Parishioner Dinner Fellowship</b> • March 25, 2025</div><div class="card-grid page-width"><article class="news-card" v-for="n in news" :key="n.title"><img :src="n.image"><div><h2>{{ n.title }}</h2><p>▣ {{ n.date }} &nbsp; | &nbsp; ⌖ {{ n.place }}</p><p>Join our parish community for this faith-filled celebration and fellowship.</p><button class="button secondary">Read more</button></div></article></div></section>
    </template>

    <template v-else-if="route === 'novenas'">
      <section class="devotion-hero" :style="{backgroundImage:`linear-gradient(90deg,#fff 10%,rgba(255,255,255,.7)),url(${altar})`}"><div class="page-width"><h1>Novenas &<br>Prayer Devotions</h1><div class="gold-rule left">✣</div><p>Deepen your faith through daily prayer<br>and Marian devotion.</p></div></section><section class="page-width"><div class="feature-card"><img :src="prayer"><div><span>Featured novena</span><h1>Our Lady of the<br>Pillar Novena</h1><p>Join us in a nine-day journey of prayer and preparation for the Feast of Our Lady of the Pillar.</p><div class="mini-features"><b>▣ Novena Schedule<br><small>May 3 – May 11, 2025</small></b><b>▤ Prayer Guide<br><small>Daily prayers & reflections</small></b></div><a class="button">View novena details</a></div></div><h2 class="center serif">Other Devotions & Novenas</h2><div class="devotion-grid"><article v-for="d in ['Holy Rosary','First Friday Devotion','Eucharistic Adoration','Divine Mercy Chaplet','Holy Hour','Marian Prayers']" :key="d"><img :src="prayer"><h3>{{ d }}</h3><p>A time of prayer, silence and reflection.</p><button>View details</button></article></div></section>
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
  <SiteFooter />
</template>

<style src="./parish.css"></style>

<style>
.values article:first-child {
  display: block;
  border: 0;
  padding: 0;
}

.values article:first-child > i {
  display: none;
}

.values article:first-child h2 {
  margin: 0 0 20px;
  font-size: 27px;
}

.values article:first-child p {
  color: #34445a;
  font-size: 15px;
  line-height: 1.85;
  white-space: pre-line;
}

.values article:not(:first-child) {
  display: none;
}

.history-milestones {
  max-width: 750px;
  margin: 0 auto 70px;
  padding: 28px 30px;
  border-left: 4px solid var(--gold);
  background: #fff;
  box-shadow: 0 10px 25px rgba(14, 50, 95, .08);
}

.history-milestones > span {
  color: #9b7628;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
}

.history-milestones h2 {
  margin: 8px 0 20px;
  font-size: 22px;
}

.history-milestones div {
  display: grid;
  grid-template-columns: 145px 1fr;
  gap: 18px;
  align-items: start;
}

.history-milestones time {
  color: var(--blue);
  font-weight: 700;
}

.history-milestones p {
  margin: 0;
  color: #34445a;
  line-height: 1.65;
}

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

@media (max-width: 720px) {
  .history-milestones {
    padding: 22px;
  }

  .history-milestones div {
    grid-template-columns: 1fr;
    gap: 7px;
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
