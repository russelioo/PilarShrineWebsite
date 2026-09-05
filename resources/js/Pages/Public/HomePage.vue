<script setup>
const parishAerial = '/images/pilar-shrine-aerial.png'
const pillarOfficial = '/images/our-lady-of-the-pillar-official.jpg'

// Pastoral & Community Highlights
const pastoralHighlights = [
  {
    icon: '✦',
    badge: 'Monthly Devotion',
    title: 'Every 12th of the Month',
    desc: 'Solemn Holy Mass at 5:00 PM followed by a procession in honor of Nuestra Señora del Pilar.',
    link: '#/schedule',
    linkText: 'Liturgical Schedule',
  },
  {
    icon: '👑',
    badge: 'Parish Fiesta',
    title: 'October 12 Solemnity',
    desc: 'Annual Feast Day of Our Lady of the Pillar, preceded by the Solemn Nine-Day Novena from Oct 3–11.',
    link: '#/novenas',
    linkText: 'Novena & Feast',
  },
  {
    icon: '⌖',
    badge: 'Shrine Office',
    title: 'Parish Office Hours',
    desc: 'Mon, Wed–Sat: 8:00 AM – 11:30 AM & 1:00 PM – 5:00 PM (Sun 8:30 AM – 12:00 NN). Closed on Tuesdays & Holidays.',
    link: '#/contact',
    linkText: 'Office & Inquiries',
  },
  {
    icon: '✍',
    badge: 'Mass Intentions',
    title: 'Online Mass Requests',
    desc: 'Offer prayers of thanksgiving, healing, birthday, or departed loved ones in shrine Holy Masses.',
    link: '#/forms',
    linkText: 'Request Intention',
  },
]

// Featured News from parish records
const featuredNews = [
  {
    title: 'May Crowning Celebration 2025',
    category: 'Parish Life',
    date: 'May 10, 2025',
    place: 'Church Grounds',
    image: 'https://images.unsplash.com/photo-1473177104440-ffee2f376098?auto=format&fit=crop&w=900&q=80',
    description: 'Join our parish community for this sacred floral offering, Marian hymns, and community fellowship.',
  },
  {
    title: 'Parish Fiesta Schedule',
    category: 'Liturgical Feast',
    date: 'May 1, 2025',
    place: 'Parish Grounds',
    image: 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=900&q=80',
    description: 'Celebrate the vibrant patronal spirit of our shrine with solemn Masses, novenas, and thanksgiving celebrations.',
  },
  {
    title: 'Blessed Mother Statue Procession',
    category: 'Upcoming Notice',
    date: 'May 10, 2025',
    place: 'Town Proper',
    image: 'https://images.unsplash.com/photo-1515169067868-5387ec356754?auto=format&fit=crop&w=900&q=80',
    description: 'Annual Marian floral offering and solemn candlelight procession honoring Nuestra Señora del Pilar.',
  },
]
</script>

<template>
  <div class="home-page">
    <!-- 1. Editorial 3-Column Composition: About / Center Image / Mass Schedule -->
    <section class="home-editorial-section page-width" aria-label="Shrine Overview and Mass Schedule">
      <div class="home-editorial-grid">
        <!-- LEFT: About Our Parish -->
        <article class="home-about-card">
          <div class="card-header-block">
            <span class="home-card-eyebrow">About Our Parish</span>
            <h2 class="home-card-title">Spiritual Home &amp; Cultural Anchor</h2>
            <div class="editorial-gold-bar" aria-hidden="true"></div>
          </div>
          <p class="home-card-text">
            Established in 1862, the Diocesan Shrine and Parish of Our Lady of the Pillar serves as the spiritual home and cultural anchor for the Catholic community in Pilar, Sorsogon. Under the patronage of Nuestra Señora del Pilar, holding the distinct privilege of enshrined distinction as the only crowned image in both the province and the Diocese of Sorsogon. Our parish stands as a testament to generations of faith, community life, and religious tradition in the municipality.
          </p>
          <div class="home-card-action">
            <a class="button secondary" href="#/about">Read More &rarr;</a>
          </div>
        </article>

        <!-- CENTER: Large Shrine Aerial Image (Visual Focal Point) -->
        <div class="home-aerial-card">
          <div class="aerial-frame">
            <img
              :src="parishAerial"
              alt="Aerial view of the Diocesan Shrine and Parish of Our Lady of the Pillar in Pilar, Sorsogon"
              class="home-aerial-img"
              loading="lazy"
            >
            <div class="aerial-gradient-overlay" aria-hidden="true"></div>
            <div class="home-aerial-badge">
              <span class="badge-tag">Diocesan Shrine</span>
              <strong class="badge-title">Our Lady of the Pillar</strong>
              <small class="badge-sub">Binanuahan, Pilar, Sorsogon · Est. 1862</small>
            </div>
          </div>
        </div>

        <!-- RIGHT: Mass Schedule -->
        <aside class="home-schedule-card" aria-label="Liturgical Mass Schedule">
          <div class="card-header-block">
            <span class="home-card-eyebrow">Liturgical Worship</span>
            <h2 class="home-card-title">Mass Schedule</h2>
            <div class="editorial-gold-bar" aria-hidden="true"></div>
          </div>

          <div class="home-schedule-group">
            <span class="schedule-group-title">Daily Mass</span>
            <ul class="schedule-list">
              <li>
                <span class="schedule-day">Monday &amp; Wednesday</span>
                <span class="schedule-time">5:00 PM</span>
              </li>
              <li>
                <span class="schedule-day">Tuesday, Thursday &amp; Friday</span>
                <span class="schedule-time">6:00 AM</span>
              </li>
              <li>
                <span class="schedule-day">Saturday</span>
                <span class="schedule-time">6:00 AM</span>
              </li>
              <li class="anticipated-row">
                <span class="schedule-day">Anticipated Mass (Sat)</span>
                <span class="schedule-time highlight-time">5:00 PM</span>
              </li>
            </ul>
          </div>

          <div class="home-schedule-group sunday-group">
            <span class="schedule-group-title">Sunday Mass</span>
            <ul class="schedule-list">
              <li>
                <span class="schedule-day">Holy Mass</span>
                <span class="schedule-time">5:00 AM</span>
              </li>
              <li>
                <span class="schedule-day">Holy Mass</span>
                <span class="schedule-time">7:30 AM</span>
              </li>
              <li>
                <span class="schedule-day">Holy Mass</span>
                <span class="schedule-time">5:00 PM</span>
              </li>
            </ul>
          </div>

          <div class="home-schedule-action">
            <a class="schedule-full-link" href="#/schedule">
              <span>View Full Liturgical Schedule</span>
              <span class="arrow-icon" aria-hidden="true">&rarr;</span>
            </a>
          </div>
        </aside>
      </div>
    </section>

    <!-- 2. Important Parish Information: 4 Pastoral Highlights -->
    <section class="home-highlights-section page-width" aria-label="Parish Life Highlights">
      <div class="home-section-header">
        <span class="home-section-eyebrow">Parish Life &amp; Devotions</span>
        <h2 class="home-section-title">Serving Faith &amp; Community</h2>
        <div class="gold-rule" aria-hidden="true">✣</div>
        <p class="home-section-desc">Join our parish community in sacred worship, Marian pilgrimage, and active Christian fellowship in Pilar, Sorsogon.</p>
      </div>

      <div class="home-highlights-grid">
        <article v-for="item in pastoralHighlights" :key="item.title" class="home-highlight-card">
          <div class="highlight-top">
            <span class="highlight-icon" aria-hidden="true">{{ item.icon }}</span>
            <span class="highlight-badge">{{ item.badge }}</span>
          </div>
          <h3 class="highlight-title">{{ item.title }}</h3>
          <p class="highlight-desc">{{ item.desc }}</p>
          <div class="highlight-footer">
            <a :href="item.link" class="highlight-action">
              <span>{{ item.linkText }}</span>
              <span aria-hidden="true">&rarr;</span>
            </a>
          </div>
        </article>
      </div>
    </section>

    <!-- 3. Featured Devotional / Novena Section -->
    <section class="home-marian-section page-width" aria-label="Featured Marian Devotion">
      <div class="home-marian-card">
        <div class="marian-visual-col">
          <div class="marian-img-frame">
            <img
              :src="pillarOfficial"
              alt="Episcopally Crowned Image of Our Lady of the Pillar"
              class="marian-card-img"
              loading="lazy"
            >
            <div class="marian-img-badge">
              <span>Enshrined Titular</span>
              <strong>Nuestra Señora del Pilar</strong>
              <small>Diocesan Shrine &amp; Parish · Pilar, Sorsogon</small>
            </div>
          </div>
        </div>

        <div class="marian-content-col">
          <span class="marian-eyebrow">Devotional Life &amp; Prayer</span>
          <h2 class="marian-title">Novena to Our Lady of the Pillar</h2>
          <div class="editorial-gold-bar" aria-hidden="true"></div>
          <p class="marian-desc">
            Deepen your spiritual life through daily prayer, meditation, and thanksgiving. Under the maternal protection of Nuestra Señora del Pilar, generations of parishioners and pilgrims find comfort, guidance, and peace.
          </p>

          <div class="marian-devotional-dates">
            <div class="devotional-date-box">
              <span class="box-date">October 3–11</span>
              <span class="box-label">Solemn Nine-Day Novena</span>
            </div>
            <div class="devotional-date-box highlight-date">
              <span class="box-date">October 12</span>
              <span class="box-label">Annual Patronal Solemnity</span>
            </div>
          </div>

          <div class="marian-actions">
            <a class="button" href="#/novena-details">Pray the Novena</a>
            <a class="button secondary" href="#/novenas">All Devotions</a>
          </div>
        </div>
      </div>
    </section>

    <!-- 4. Official Shrine Prayer: Prayer to Our Lady of the Pillar -->
    <section class="home-prayer-section page-width" aria-label="Prayer to Our Lady of the Pillar">
      <div class="home-prayer-card">
        <div class="home-prayer-inner">
          <div class="prayer-card-header">
            <span class="prayer-eyebrow">DAILY DEVOTION</span>
            <h2 class="prayer-title">Prayer to Our Lady of the Pillar</h2>
            <div class="prayer-gold-rule" aria-hidden="true">
              <span class="prayer-rule-line"></span>
              <span class="prayer-rule-cross">✣</span>
              <span class="prayer-rule-line"></span>
            </div>
            <p class="prayer-shrine-subtitle">Diocesan Shrine and Parish of Our Lady of the Pillar · Pilar, Sorsogon</p>
          </div>

          <div class="prayer-body-content">
            <p class="prayer-stanza">Most Holy Virgin of the Pillar,</p>

            <p class="prayer-stanza">to your care we confide</p>

            <p class="prayer-stanza">the needs of our families,</p>

            <p class="prayer-stanza">the joys of children,</p>

            <p class="prayer-stanza">the dreams of youth,</p>

            <p class="prayer-stanza">the anxieties of adults,</p>

            <p class="prayer-stanza">the sufferings of the sick,</p>

            <p class="prayer-stanza">and our preparation for life’s end.</p>

            <p class="prayer-stanza">To you we entrust the perseverance of our priests and religious.</p>

            <p class="prayer-stanza">Ever increase our faith,</p>

            <p class="prayer-stanza">give assurance to our hope,</p>

            <p class="prayer-stanza">and enliven our charity.</p>

            <p class="prayer-stanza prayer-amen">Amen.</p>

            <p class="prayer-stanza prayer-invocation">Our Lady of the Pillar, pray for us.</p>
          </div>

          <div class="prayer-footer-action">
            <a href="#/novenas" class="button secondary prayer-action-btn">
              <span>View Novenas &amp; Sacred Hymns</span>
              <span aria-hidden="true">&rarr;</span>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- 5. Featured News & Announcements -->
    <section class="home-news-section page-width" aria-label="Recent News and Announcements">
      <div class="home-section-header">
        <span class="home-section-eyebrow">Parish Bulletin</span>
        <h2 class="home-section-title">News &amp; Community Updates</h2>
        <div class="gold-rule" aria-hidden="true">✣</div>
      </div>

      <div class="home-news-grid">
        <article v-for="item in featuredNews" :key="item.title" class="home-news-card">
          <div class="news-img-wrap">
            <img :src="item.image" :alt="item.title" loading="lazy">
            <span class="news-tag">{{ item.category }}</span>
          </div>
          <div class="news-content-wrap">
            <div class="news-meta-row">
              <span class="news-meta-item">◷ {{ item.date }}</span>
              <span class="news-meta-dot">•</span>
              <span class="news-meta-item">⌖ {{ item.place }}</span>
            </div>
            <h3 class="news-item-title">{{ item.title }}</h3>
            <p class="news-item-desc">{{ item.description }}</p>
            <div class="news-item-footer">
              <a class="news-more-link" href="#/news">
                <span>Read Full Announcement</span>
                <span aria-hidden="true">&rarr;</span>
              </a>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- 5. Pilgrimage & Shrine Welcome Banner -->
    <section class="home-cta-section page-width" aria-label="Visit the Shrine">
      <div class="home-cta-banner">
        <div class="cta-copy-wrap">
          <span class="cta-eyebrow">Pilgrimage &amp; Sacred Worship</span>
          <h2 class="cta-heading">Visit the Diocesan Shrine of Our Lady of the Pillar</h2>
          <p class="cta-subtext">
            Experience the sacred peace, Eucharistic liturgy, and Marian devotion in Binanuahan, Pilar, Sorsogon. All pilgrims and faithful are warmly welcomed.
          </p>
        </div>
        <div class="cta-actions-wrap">
          <a class="button" href="#/schedule">Mass Schedule</a>
          <a class="button secondary" href="#/sacraments">Sacraments</a>
          <a class="button secondary" href="#/contact">Contact Us</a>
        </div>
      </div>
    </section>
  </div>
</template>

