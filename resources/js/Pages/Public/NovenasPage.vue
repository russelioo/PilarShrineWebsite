<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import {
  SectionHeader,
  ContentCard,
  SiteButton,
} from '../../components/SiteUI'
import { devotionalResources } from '../../data/devotions'

const pillarOfficial = '/images/our-lady-of-the-pillar-official.jpg'

const activeCategory = ref('all')
const activeResource = ref(null)

const filteredResources = computed(() => {
  if (activeCategory.value === 'all') return devotionalResources
  return devotionalResources.filter(res => res.type === activeCategory.value)
})

const openResource = (resource) => {
  activeResource.value = resource
  document.body.style.overflow = 'hidden'
}

const closeResource = () => {
  activeResource.value = null
  document.body.style.overflow = ''
}

const handleKeydown = (e) => {
  if (e.key === 'Escape' && activeResource.value) {
    closeResource()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})

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

    <!-- 2. Dedicated Section: PRAY • SING • CELEBRATE -->
    <section id="pray-sing-celebrate" class="pray-sing-celebrate-section page-width" aria-labelledby="devotions-section-title">
      <SectionHeader
        id="devotions-section-title"
        eyebrow="PRAY • SING • CELEBRATE"
        title="Official Prayer &amp; Sacred Hymns"
        subtitle="Official devotional prayer and liturgical music honoring Nuestra Señora del Pilar, preserved for communal veneration and sacred celebration."
      />

      <!-- Quick Category Navigation Tabs -->
      <nav class="devotional-filter-nav" role="tablist" aria-label="Devotional categories">
        <button
          type="button"
          role="tab"
          class="devotional-filter-tab"
          :class="{ active: activeCategory === 'all' }"
          :aria-selected="activeCategory === 'all'"
          @click="activeCategory = 'all'"
        >
          <span>All Devotions</span>
          <span class="tab-count-badge">{{ devotionalResources.length }}</span>
        </button>
        <button
          type="button"
          role="tab"
          class="devotional-filter-tab"
          :class="{ active: activeCategory === 'prayer' }"
          :aria-selected="activeCategory === 'prayer'"
          @click="activeCategory = 'prayer'"
        >
          <span>Prayers</span>
        </button>
        <button
          type="button"
          role="tab"
          class="devotional-filter-tab"
          :class="{ active: activeCategory === 'hymn' }"
          :aria-selected="activeCategory === 'hymn'"
          @click="activeCategory = 'hymn'"
        >
          <span>Our Lady of the Pillar Hymns</span>
        </button>
      </nav>

      <!-- Devotional Resources Cards Grid -->
      <div class="devotional-cards-grid">
        <ContentCard
          v-for="resource in filteredResources"
          :key="resource.id"
          class="devotional-resource-card"
          :class="`resource-card-${resource.type}`"
        >
          <div class="card-eyebrow-row">
            <span class="resource-category-pill" :class="resource.type">{{ resource.category }}</span>
            <span class="resource-type-indicator" aria-hidden="true">
              {{ resource.type === 'prayer' ? '📿' : '𝄞' }}
            </span>
          </div>

          <h3 class="resource-title">{{ resource.title }}</h3>
          <p v-if="resource.subtitle" class="resource-subtitle">{{ resource.subtitle }}</p>

          <div class="resource-attribution">
            <span class="attribution-bullet" aria-hidden="true">✦</span>
            <span v-if="resource.composer" class="attribution-text">
              Composed by <strong>{{ resource.composer }}</strong>
            </span>
            <span v-else class="attribution-text">
              {{ resource.metadata }}
            </span>
          </div>

          <p class="resource-desc">{{ resource.shortDescription }}</p>

          <div class="resource-action-footer">
            <SiteButton
              variant="secondary"
              class="resource-open-btn"
              @click="openResource(resource)"
            >
              <span>{{ resource.actionLabel }}</span>
              <svg class="action-btn-arrow" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </SiteButton>
          </div>
        </ContentCard>
      </div>
    </section>

    <!-- 3. Additional Parish Devotions Section -->
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

    <!-- Dedicated Full-Screen Readable Lyrics & Prayer Modal -->
    <Teleport to="body">
      <transition name="reader-fade">
        <div
          v-if="activeResource"
          class="devotion-reader-overlay"
          role="dialog"
          aria-modal="true"
          :aria-labelledby="`reader-title-${activeResource.id}`"
          @click.self="closeResource"
        >
          <div class="devotion-reader-container">
            <!-- Header Bar -->
            <div class="reader-top-bar">
              <div class="reader-category-indicator">
                <span class="reader-category-pill">{{ activeResource.category }}</span>
                <span v-if="activeResource.type === 'hymn'" class="reader-kind-pill">SACRED HYMN</span>
                <span v-else class="reader-kind-pill">OFFICIAL PRAYER</span>
              </div>
              <button
                type="button"
                class="reader-close-action"
                aria-label="Close reading view"
                @click="closeResource"
              >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <line x1="18" y1="6" x2="6" y2="18" />
                  <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
              </button>
            </div>

            <!-- Title & Metadata -->
            <header class="reader-title-block">
              <h2 :id="`reader-title-${activeResource.id}`" class="reader-main-title">
                {{ activeResource.title }}
              </h2>
              <p v-if="activeResource.subtitle" class="reader-sub-title">
                {{ activeResource.subtitle }}
              </p>
              <div class="reader-attribution-row">
                <span v-if="activeResource.composer">
                  Composed by <strong>{{ activeResource.composer }}</strong>
                </span>
                <span v-else>
                  {{ activeResource.metadata }}
                </span>
              </div>
              <div class="reader-gold-separator" aria-hidden="true"></div>
            </header>

            <!-- Scrollable Reading Surface -->
            <div class="reader-reading-surface">
              <div
                v-for="(section, sIdx) in activeResource.sections"
                :key="sIdx"
                class="reader-stanza-block"
                :class="{ 'is-chorus-section': section.isChorus }"
              >
                <div v-if="section.heading" class="stanza-label-badge">
                  <span>{{ section.heading }}</span>
                </div>
                <div class="stanza-lines">
                  <p
                    v-for="(line, lIdx) in section.lines"
                    :key="lIdx"
                    class="stanza-verse-line"
                    :class="{ 'line-spacer': !line }"
                  >
                    {{ line }}
                  </p>
                </div>
              </div>

              <!-- Extra Novena Gateway for Prayer Resource -->
              <div v-if="activeResource.type === 'prayer'" class="reader-novena-gateway">
                <div class="gateway-copy">
                  <strong class="gateway-title">Nine-Day Novena &amp; Gozos</strong>
                  <p class="gateway-desc">
                    Pray the complete daily reflections, preparatory contrition, and historic Gozos to Our Lady of the Pillar.
                  </p>
                </div>
                <a href="#/novena-details" class="gateway-btn" @click="closeResource">
                  Open 9-Day Novena &rarr;
                </a>
              </div>
            </div>

            <!-- Footer Action Bar -->
            <footer class="reader-footer-bar">
              <SiteButton variant="secondary" class="reader-dismiss-button" @click="closeResource">
                Close Reading View
              </SiteButton>
            </footer>
          </div>
        </div>
      </transition>
    </Teleport>
  </div>
</template>
