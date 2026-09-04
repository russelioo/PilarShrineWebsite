<script setup>
const pillarOfficial = '/images/our-lady-of-the-pillar-official.jpg'

const additionalDevotions = [
  {
    icon: '📿',
    category: 'Daily Marian Meditation',
    title: 'The Holy Rosary',
    description: 'Meditate upon the sacred mysteries of Christ and the Blessed Virgin Mary, prayed communally before each Holy Mass at the shrine.',
    schedule: 'Daily · 30 minutes before Mass',
    actionLabel: 'View Mass Times',
    actionHref: '#/schedule',
  },
  {
    icon: '♡',
    category: 'Immaculate Heart of Mary',
    title: 'First Saturday Devotion',
    description: 'Fulfill the request of Our Lady of Fatima for five consecutive first Saturdays with the Rosary, meditation, and reception of Communion.',
    schedule: 'Every 1st Saturday · 6:00 AM Mass',
    actionLabel: 'Liturgical Schedule',
    actionHref: '#/schedule',
  },
  {
    icon: '♛',
    category: 'Patronal Shrine Day',
    title: 'Monthly 12th Devotion',
    description: 'A solemn monthly pilgrimage honoring Nuestra Señora del Pilar with special novena intentions, Holy Mass, and candlelight procession.',
    schedule: 'Every 12th of the Month · 5:00 PM',
    actionLabel: 'Feast & Novena Details',
    actionHref: '#/novena-details',
  },
]
</script>

<template>
  <div class="novenas-landing-page">
    <!-- 1. Featured Novena Section -->
    <section class="novena-featured-section page-width">
      <div class="novena-featured-card">
        <!-- Visual Column (40–45%) -->
        <div class="novena-image-frame">
          <img
            class="novena-official-image"
            :src="pillarOfficial"
            alt="Official Enshrined Image of Our Lady of the Pillar with the Child Jesus"
            loading="lazy"
          >
          <div class="novena-image-caption">
            <span class="caption-tag">Enshrined Titular</span>
            <strong>Nuestra Señora del Pilar</strong>
            <small>Diocesan Shrine &amp; Parish · Pilar, Sorsogon</small>
          </div>
        </div>

        <!-- Content Column (55–60%) -->
        <div class="novena-feature-copy">
          <span class="feature-eyebrow">Featured Novena</span>
          <h2 class="feature-title">Our Lady of the<br>Pillar Novena</h2>
          <div class="feature-gold-bar" aria-hidden="true"></div>
          <p class="feature-desc">
            Join our shrine community in a sacred nine-day journey of prayer, thanksgiving, and maternal intercession in preparation for the solemn Feast of Our Lady of the Pillar.
          </p>

          <!-- Compact Information Pills -->
          <div class="novena-info-pills">
            <div class="info-pill">
              <span class="pill-icon" aria-hidden="true">◷</span>
              <div class="pill-meta">
                <span class="pill-label">Novena Schedule</span>
                <strong class="pill-value">October 3–11</strong>
              </div>
            </div>
            <div class="info-pill highlight">
              <span class="pill-icon" aria-hidden="true">✦</span>
              <div class="pill-meta">
                <span class="pill-label">Feast Day Solemnity</span>
                <strong class="pill-value">October 12</strong>
              </div>
            </div>
          </div>

          <!-- Featured Action CTA -->
          <div class="feature-actions">
            <a class="button" href="#/novena-details">
              View Novena Details &rarr;
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- 2. Additional Parish Devotions Section -->
    <section class="devotions-section page-width">
      <div class="section-header-block">
        <span class="section-eyebrow">Devotional Life</span>
        <h2 class="section-heading">Additional Parish Devotions</h2>
        <div class="editorial-gold-bar" aria-hidden="true"></div>
        <p class="section-subtext">
          Participate in the rich communal prayer traditions, Marian devotions, and liturgical observances celebrated at the Diocesan Shrine of Our Lady of the Pillar.
        </p>
      </div>

      <div class="devotions-grid">
        <article
          v-for="devotion in additionalDevotions"
          :key="devotion.title"
          class="devotion-card"
        >
          <div class="devotion-card-top">
            <span class="devotion-icon" aria-hidden="true">{{ devotion.icon }}</span>
            <span class="devotion-category">{{ devotion.category }}</span>
          </div>

          <h3 class="devotion-title">{{ devotion.title }}</h3>
          <p class="devotion-desc">{{ devotion.description }}</p>

          <div class="devotion-schedule">
            <i class="schedule-clock" aria-hidden="true">◷</i>
            <span>{{ devotion.schedule }}</span>
          </div>

          <div class="devotion-footer">
            <a :href="devotion.actionHref" class="devotion-link">
              {{ devotion.actionLabel }} <span aria-hidden="true">&rarr;</span>
            </a>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

