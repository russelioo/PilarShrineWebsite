<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import AnnouncementCaption from '../../components/AnnouncementCaption.vue'

const defaultNews = [
  {
    id: 1,
    title: 'May Crowning Celebration 2026',
    category: 'Parish Life',
    date: 'May 10, 2026',
    place: 'Church Grounds',
    image: '/images/church-interior.png',
    description: 'Join our parish community for this sacred and joyful celebration in honor of the Blessed Virgin Mary, featuring floral offerings, Marian hymns, and community fellowship.',
    fullText: 'Join our parish community for this sacred and joyful celebration in honor of the Blessed Virgin Mary. The May Crowning is a venerable Marian tradition uniting devotees and families of our shrine in offering flowers, prayers, and hymns to Our Lady of the Pillar. Families and children are encouraged to participate in the floral offering and the community fellowship following the Holy Mass.',
  },
  {
    id: 2,
    title: 'Parish Fiesta Schedule 2026',
    category: 'Liturgical Feast',
    date: 'May 1, 2026',
    place: 'Parish Grounds & Shrine',
    image: '/images/pilar-shrine-aerial.png',
    description: 'Celebrate the vibrant patronal spirit of our shrine with solemn Masses, novena prayers, cultural exhibits, and thanksgiving celebrations for the whole community.',
    fullText: 'Celebrate the vibrant patronal spirit of our shrine with solemn Masses, novena prayers, cultural exhibits, and thanksgiving celebrations. The festivities bring together parishioners, pilgrims, and visitors in expressing gratitude for the continuous maternal protection of Our Lady of the Pillar over our municipality.',
  },
  {
    id: 3,
    title: 'Youth Camp 2026',
    category: 'Youth Ministry',
    date: 'April 20, 2026',
    place: 'Retreat House',
    image: 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=900&q=80',
    description: 'An inspiring spiritual formation weekend for young parishioners focused on faith leadership, communal worship, and active ministry involvement.',
    fullText: 'An inspiring spiritual formation weekend for young parishioners focused on faith leadership, communal worship, and active ministry involvement. The Youth Camp provides young Catholics an opportunity to deepen their relationship with Christ, cultivate Christian fellowship, and develop active roles in shrine ministries.',
  },
]

const defaultAnnouncements = [
  {
    id: 101,
    title: 'Blessed Mother Statue Procession',
    date: 'October 12, 2026',
    place: 'Church Grounds & Town Proper',
    badge: 'Marian Devotion',
    is_pinned: true,
    description: 'Annual Marian floral offering and solemn candlelight procession honoring Nuestra Señora del Pillar.',
    fullText: 'Annual Marian floral offering and solemn candlelight procession honoring Nuestra Señora del Pilar through the church grounds and town proper.',
  },
  {
    id: 102,
    title: 'Holy Week 2026 Schedule',
    date: 'April 8, 2026',
    place: 'Parish Shrine & Chapels',
    badge: 'Liturgical Notice',
    is_pinned: true,
    description: 'Complete schedules for Palm Sunday, Chrism Mass, Visita Iglesia, Seven Last Words, and the Solemn Easter Vigil.',
    fullText: 'Complete schedules for Palm Sunday, Chrism Mass, Visita Iglesia, Seven Last Words, and the Solemn Easter Vigil across Pilar Shrine and barangay chapels.',
  },
  {
    id: 103,
    title: 'Parishioner Dinner Fellowship',
    date: 'March 25, 2026',
    place: 'Parish Pastoral Center',
    badge: 'Community',
    is_pinned: false,
    description: 'An evening of fraternal fellowship and thanksgiving for parish volunteers, pastoral councils, and ministry leaders.',
    fullText: 'An evening of fraternal fellowship and thanksgiving for parish volunteers, pastoral councils, lay ministers, and choir leaders at the Parish Pastoral Center.',
  },
]

const news = ref(defaultNews)
const announcements = ref(defaultAnnouncements)
const isLoading = ref(true)
const activeNewsModal = ref(null)
const liveAnnouncements = ref([])

const openLinkedAnnouncement = (event) => {
  const [page, query = ''] = window.location.hash.split('?')
  if (!['#/news', '#/events'].includes(page)) return
  const id = new URLSearchParams(query).get('announcement')
  if (id) activeNewsModal.value = liveAnnouncements.value.find(item => String(item.id) === id) || null
  else if (event?.type === 'hashchange') activeNewsModal.value = null
}

const closeNewsModal = () => {
  activeNewsModal.value = null
  const url = new URL(window.location.href)
  const [page, query = ''] = url.hash.split('?')
  const params = new URLSearchParams(query)
  if (params.has('announcement')) {
    params.delete('announcement')
    url.hash = page + (params.size ? `?${params}` : '')
    window.history.replaceState(window.history.state, '', url)
  }
}
const handleEscape = event => { if (event.key === 'Escape' && activeNewsModal.value) closeNewsModal() }

const fetchAnnouncements = async () => {
  try {
    const res = await fetch('/api/announcements', { headers: { 'Accept': 'application/json' } })
    if (res.ok) {
      const data = await res.json()
      liveAnnouncements.value = data.announcements || []
      if (data.announcements && data.announcements.length > 0) {
        announcements.value = data.announcements
      }
      if (data.news && data.news.length > 0) {
        news.value = data.news
      }
    }
  } catch (err) {
    // Network or server error; retains default fallback data seamlessly
    console.warn('Could not fetch live announcements, using cached defaults:', err)
  } finally {
    isLoading.value = false
    openLinkedAnnouncement()
  }
}

onMounted(() => {
  fetchAnnouncements()
  window.addEventListener('hashchange', openLinkedAnnouncement)
  window.addEventListener('keydown', handleEscape)
})
onUnmounted(() => {
  window.removeEventListener('hashchange', openLinkedAnnouncement)
  window.removeEventListener('keydown', handleEscape)
})
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
          <article
            v-for="a in announcements"
            :key="a.id || a.title"
            class="news-announcement-card"
            :class="{ 'card-pinned': a.is_pinned }"
            @click="activeNewsModal = a"
            data-analytics-action="announcement_open"
            style="cursor: pointer;"
          >
            <div class="announcement-header">
              <div class="announcement-header-left">
                <span class="announcement-badge">{{ a.badge || a.category }}</span>
                <span v-if="a.is_pinned" class="pinned-tag" title="Pinned Announcement">📌 Pinned</span>
              </div>
              <time class="announcement-date">
                <svg class="news-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>{{ a.date }}</span>
              </time>
            </div>
            <h3>{{ a.title }}</h3>
            <AnnouncementCaption class="announcement-desc" :text="a.description" />
            <div class="announcement-meta">
              <span class="announcement-place">
                <svg class="news-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <span>{{ a.place || 'Diocesan Shrine & Parish' }}</span>
              </span>
              <button class="announcement-read-link" type="button" @click.stop="activeNewsModal = a" data-analytics-action="announcement_open">Read Details &rarr;</button>
            </div>
          </article>
        </div>
      </section>

      <!-- Featured News Article Section (shown if at least 1 news story exists) -->
      <section v-if="news && news.length > 0" class="news-featured-section" aria-labelledby="featured-heading">
        <div class="news-section-header">
          <span class="news-section-eyebrow">Featured Story</span>
          <h2 id="featured-heading">Latest from Our Community</h2>
        </div>

        <article class="news-featured-card">
          <div class="featured-media">
            <img :src="news[0].image" :alt="news[0].title" onerror="this.src='/images/church-interior.png'">
            <span class="featured-tag">{{ news[0].category }}</span>
          </div>
          <div class="featured-content">
            <div class="featured-meta">
              <time class="featured-date">
                <svg class="news-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <circle cx="12" cy="12" r="10"></circle>
                  <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <span>{{ news[0].date }}</span>
              </time>
              <span class="featured-dot">•</span>
              <span class="featured-place">
                <svg class="news-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                  <circle cx="12" cy="10" r="3"></circle>
                </svg>
                <span>{{ news[0].place || 'Diocesan Shrine Grounds' }}</span>
              </span>
            </div>
            <h3>{{ news[0].title }}</h3>
            <div class="gold-rule left small">✣</div>
            <AnnouncementCaption :key="news[0].id || news[0].title" class="featured-desc" :text="news[0].description" initially-expanded />
            <div class="featured-actions">
              <button class="button" type="button" @click="activeNewsModal = news[0]" data-analytics-action="announcement_open">
                Read Full Story
              </button>
              <a class="button secondary" href="#/schedule">View Mass Schedule</a>
            </div>
          </div>
        </article>
      </section>

      <!-- News & Events Grid Section -->
      <section v-if="news && news.length > 1" class="news-grid-section" aria-labelledby="recent-news-heading">
        <div class="news-section-header">
          <span class="news-section-eyebrow">Parish Updates &amp; Stories</span>
          <h2 id="recent-news-heading">Recent News &amp; Events</h2>
        </div>

        <div class="news-cards-grid">
          <article v-for="n in news" :key="n.id || n.title" class="modern-news-card">
            <div class="card-media">
              <img :src="n.image" :alt="n.title" onerror="this.src='/images/church-interior.png'">
              <span class="card-category-badge">{{ n.category }}</span>
            </div>
            <div class="card-body">
              <div class="card-meta">
                <time class="meta-date">
                  <svg class="news-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                  </svg>
                  <span>{{ n.date }}</span>
                </time>
                <span class="meta-place">
                  <svg class="news-meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                  <span>{{ n.place || 'Diocesan Shrine' }}</span>
                </span>
              </div>
              <h3>{{ n.title }}</h3>
              <AnnouncementCaption class="card-summary" :text="n.description" />
              <div class="card-footer">
                <button class="button secondary card-cta" type="button" @click="activeNewsModal = n" data-analytics-action="announcement_open">
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
    <div v-if="activeNewsModal" class="news-modal-backdrop" @click.self="closeNewsModal" role="dialog" aria-modal="true" :aria-label="activeNewsModal.title">
      <div class="news-modal-card">
        <button class="modal-close-btn" type="button" @click="closeNewsModal" aria-label="Close article">✕</button>
        <div v-if="activeNewsModal.images && activeNewsModal.images.length > 1" class="modal-gallery-wrap">
          <img v-for="(img, idx) in activeNewsModal.images" :key="idx" :src="img" :alt="activeNewsModal.title">
          <span class="modal-badge">{{ activeNewsModal.category || activeNewsModal.badge }}</span>
        </div>
        <div v-else-if="activeNewsModal.image" class="modal-image-wrap">
          <img :src="activeNewsModal.image" :alt="activeNewsModal.title" onerror="this.style.display='none'">
          <span class="modal-badge">{{ activeNewsModal.category || activeNewsModal.badge }}</span>
        </div>
        <div class="modal-body">
          <div class="modal-meta">
            <time>◷ {{ activeNewsModal.date }}</time>
            <span>•</span>
            <span>⌖ {{ activeNewsModal.place || 'Diocesan Shrine & Parish' }}</span>
            <span v-if="activeNewsModal.is_pinned" class="pinned-tag" style="margin-left: 8px;">📌 Pinned</span>
          </div>
          <h2>{{ activeNewsModal.title }}</h2>
          <div class="gold-rule left small">✣</div>
          <AnnouncementCaption class="modal-lead" :text="activeNewsModal.description" initially-expanded />
          <AnnouncementCaption v-if="activeNewsModal.fullText && activeNewsModal.fullText !== activeNewsModal.description" class="modal-fulltext" :text="activeNewsModal.fullText" initially-expanded />
          <div class="modal-actions">
            <button class="button secondary" type="button" @click="closeNewsModal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.news-announcement-card {
  padding: 24px;
  border-radius: 14px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-top: 3px solid var(--color-primary, #062f78);
  box-shadow: 0 4px 18px rgba(10, 37, 64, 0.05);
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
  min-height: 220px;
  text-align: left;
}

.news-announcement-card:hover {
  transform: translateY(-3px);
  background: #ffffff;
  border-color: #cbd5e1;
  box-shadow: 0 10px 28px rgba(10, 37, 64, 0.1);
}

.news-announcement-card.card-pinned {
  border-top: 3px solid var(--color-gold, #d6aa3e) !important;
  border-left: 1px solid #e2e8f0 !important;
  box-shadow: 0 4px 20px rgba(214, 170, 62, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
}

.news-announcement-card.card-pinned:hover {
  border-color: var(--color-gold, #d6aa3e) !important;
  box-shadow: 0 10px 28px rgba(214, 170, 62, 0.2);
}

.announcement-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-bottom: 14px;
}

.announcement-header-left {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.announcement-badge {
  padding: 3px 10px;
  border-radius: 20px;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  color: #1d4ed8;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.pinned-tag {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fde68a;
  border-radius: 20px;
  padding: 3px 9px;
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}

.announcement-date {
  color: #64748b;
  font-size: 11.5px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  white-space: nowrap;
}

.news-announcement-card h3 {
  color: var(--color-primary, #062f78);
  font-family: var(--font-heading);
  font-size: 17px;
  font-weight: 700;
  margin: 0 0 10px;
  line-height: 1.35;
}

.announcement-desc {
  margin: 0 0 16px;
  flex-grow: 1;
}

.announcement-meta {
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px solid #edf2f7;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  gap: 12px !important;
  width: 100%;
}

.announcement-place {
  color: #64748b;
  font-size: 11.5px;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 60%;
}

.announcement-read-link {
  padding: 0;
  border: 0;
  background: none;
  font-family: inherit;
  cursor: pointer;
  font-size: 11.5px;
  font-weight: 700;
  color: var(--color-accent, #0b58b5);
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-left: auto;
  transition: transform 0.15s ease, color 0.15s ease;
}

.news-announcement-card:hover .announcement-read-link {
  color: var(--color-primary, #062f78);
  transform: translateX(3px);
  text-decoration: none;
}

.modal-gallery-wrap {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  position: relative;
  max-height: 280px;
  overflow: hidden;
  background: #0f172a;
}
.modal-gallery-wrap img {
  width: 100%;
  height: 280px;
  object-fit: cover;
}
@media (max-width: 640px) {
  .modal-gallery-wrap {
    grid-template-columns: 1fr;
    max-height: 420px;
  }
  .modal-gallery-wrap img {
    height: 210px;
  }
}
</style>
