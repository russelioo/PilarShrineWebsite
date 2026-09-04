<script setup>
import { ref } from 'vue'

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
</script>

<template>
  <div class="news-page">
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

