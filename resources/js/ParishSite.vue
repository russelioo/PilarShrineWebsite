<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import SiteNavbar from './components/SiteNavbar.vue'
import SiteFooter from './components/SiteFooter.vue'
import SectionTitle from './components/SectionTitle.vue'

const route = ref('home')
const syncRoute = () => { route.value = location.hash.replace(/^#\//, '').split('/')[0] || 'home'; scrollTo(0, 0) }
onMounted(() => { syncRoute(); addEventListener('hashchange', syncRoute) })
onUnmounted(() => removeEventListener('hashchange', syncRoute))

const active = computed(() => route.value)
const church = '/images/church-interior.png'
const altar = 'https://images.unsplash.com/photo-1519491050282-cf00c82424b4?auto=format&fit=crop&w=1600&q=85'
const prayer = 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=1400&q=85'
const news = [
  { title:'May Crowning Celebration 2025', date:'May 10, 2025', place:'Church Grounds', image:'https://images.unsplash.com/photo-1473177104440-ffee2f376098?auto=format&fit=crop&w=900&q=80' },
  { title:'Parish Fiesta Schedule', date:'May 1, 2025', place:'Parish Grounds', image:'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=900&q=80' },
  { title:'Youth Camp 2025', date:'April 20, 2025', place:'Retreat House', image:'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=900&q=80' },
]
const products = ['Pillar Statue','Pillar Rosary','Marian Prayer Book','Novena Booklet','Pillar Medal','Parish Shirt','Devotional Candle','Souvenir Keychain']
const productImages = [prayer,'https://images.unsplash.com/photo-1602173574767-37ac01994b2a?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1603561596112-db1d3d140b8c?auto=format&fit=crop&w=600&q=80']
</script>

<template>
  <SiteNavbar :active="active" />
  <main>
    <template v-if="route === 'home'">
      <section class="home-hero" :style="{ backgroundImage:`linear-gradient(90deg,rgba(239,247,255,.96) 0%,rgba(239,247,255,.68) 43%,rgba(0,0,0,.05)),url(${church})` }">
        <div class="page-width hero-copy"><em>Welcome to</em><h1>Diocesan Shrine and Parish of<br>Our Lady of the Pillar </h1><div class="gold-rule left">✣</div><p>A community of believers journeying together<br>in faith, hope and love.</p><div><a class="button" href="#/schedule">▣ View mass schedule</a><a class="button secondary" href="#/sacraments">♙ Request sacrament</a></div></div>
      </section>
      <section class="help-panel page-width"><h2>How can we help you?</h2><div class="quick-grid"><a v-for="item in ['Mass Schedule','Mass Intention','Baptism Request','Wedding Request','Sacraments Info','Donate Online','Events & Calendar','Contact Parish']" :key="item" href="#/schedule"><b>♢</b>{{ item }}</a></div></section>
      <section class="intro-grid page-width"><article><h3>About our parish</h3><p>Established in 1862, the Diocesan Shrine and Parish of Our Lady of the Pillar serves as the spiritual home and cultural anchor for the Catholic community in Pilar, Sorsogon. Under the patronage of Nuestra Señora del Pilar, holding the distinct privilege of enshrined distinction as the only crowned image in both the province and the Diocese of Sorsogon. Our parish stands as a testament to generations of faith, community life, and religious tradition in the municipality.</p><a class="button secondary" href="#/about">Read more</a></article><img :src="altar" alt="Church altar"><aside><h3>Mass schedule today</h3><p><b>6:00 AM</b> — Holy Mass</p><p><b>8:00 AM</b> — Holy Mass</p><p><b>10:00 AM</b> — Solemn Mass</p><p><b>5:00 PM</b> — Holy Mass</p><h3>Confession</h3><p>Saturday, 4:00 PM – 5:00 PM</p></aside></section>
    </template>

    <template v-else-if="route === 'about'">
      <section class="soft-page"><SectionTitle eyebrow="Home  ›  About Us" title="About Our Parish"/><div class="page-width"><img class="wide-image" :src="altar" alt="Interior of church"><div class="values"><article v-for="v in [['Our History','Rooted in devotion to our Blessed Mother, our parish has been a beacon of faith and hope for generations.'],['Mission','To proclaim Christ and make disciples through worship, formation and service.'],['Vision',`A vibrant parish community witnessing God's love, growing in holiness and mission.`]]" :key="v[0]"><i>✥</i><div><h2>{{ v[0] }}</h2><p>{{ v[1] }}</p></div></article></div></div><div class="stats"><div><b>1954</b><span>Year Established</span></div><div><b>8,500+</b><span>Parishioners</span></div><div><b>25+</b><span>Ministries</span></div></div></section>
    </template>

    <template v-else-if="route === 'schedule'">
      <section class="soft-page"><SectionTitle eyebrow="Home  ›  Mass Schedule" title="Mass & Liturgical Schedule"/><div class="schedule-layout page-width"><div><article class="schedule-card" v-for="s in [['Daily Mass','Monday – Friday','6:00 AM — Holy Mass|12:00 NN — Holy Mass|Saturday — 7:00 AM'],['Sunday Mass','Sunday','6:00 AM — Holy Mass|8:00 AM — Holy Mass|10:00 AM — Solemn Mass|4:00 PM — Holy Mass|6:00 PM — Holy Mass'],['Confession','Saturday','3:00 PM – 4:00 PM|or by appointment'],['Adoration','Every Thursday','after 7:00 AM Mass|8:00 AM – 6:00 PM']]" :key="s[0]"><i>♢</i><div><h2>{{ s[0] }}</h2><b>{{ s[1] }}</b><p v-for="line in s[2].split('|')" :key="line">{{ line }}</p></div></article></div><img class="tall-image" :src="altar" alt="Church sanctuary"></div></section>
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

    <template v-else><section class="soft-page"><SectionTitle :title="route.replace('-', ' ')" subtitle="This static page is ready for parish content."/></section></template>
  </main>
  <SiteFooter />
</template>

<style src="./parish.css"></style>
