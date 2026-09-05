<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const parishAerial = '/images/pilar-shrine-aerial.png'
const altar = '/images/pilar-shrine-sanctuary.jpg'
const pillarOfficial = '/images/our-lady-of-the-pillar-official.jpg'
const coronationCrowns = '/images/pilar-coronation-crowns.png'

// Archival Photography Collection (5 Historical Photographs)
const archivePhotos = [
  {
    id: 1,
    src: '/images/archive/archive-parish-church.jpg',
    title: 'Parish Church',
    alt: 'Historical photograph of Our Lady of the Pillar Parish Church facade and sanctuary entrance',
    isFeatured: true
  },
  {
    id: 2,
    src: '/images/archive/archive-parish-interior.jpg',
    title: 'Parish Interior',
    alt: 'Historical photograph of the parish interior with wooden pews and sanctuary altar'
  },
  {
    id: 3,
    src: '/images/archive/archive-our-lady-of-the-pillar.jpg',
    title: 'Our Lady of the Pillar',
    alt: 'Historical portrait of the image of Our Lady of the Pillar and the Infant Jesus'
  },
  {
    id: 4,
    src: '/images/archive/archive-marian-devotion.jpg',
    title: 'Marian Devotion',
    alt: 'Historical photograph of Marian devotion and solemn procession with parishioners'
  },
  {
    id: 5,
    src: '/images/archive/archive-historic-parish.jpg',
    title: 'Historic Parish',
    alt: 'Historical photograph of the parish building and convent grounds'
  }
]

const featuredPhoto = archivePhotos[0]
const supportingPhotos = archivePhotos.slice(1)

// Lightbox Modal State & Handlers
const activeLightboxIndex = ref(null)

const openLightbox = (index) => {
  activeLightboxIndex.value = index
  if (typeof document !== 'undefined') {
    document.body.style.overflow = 'hidden'
  }
}

const closeLightbox = () => {
  activeLightboxIndex.value = null
  if (typeof document !== 'undefined') {
    document.body.style.overflow = ''
  }
}

const prevPhoto = () => {
  if (activeLightboxIndex.value === null) return
  activeLightboxIndex.value = (activeLightboxIndex.value - 1 + archivePhotos.length) % archivePhotos.length
}

const nextPhoto = () => {
  if (activeLightboxIndex.value === null) return
  activeLightboxIndex.value = (activeLightboxIndex.value + 1) % archivePhotos.length
}

const handleKeydown = (e) => {
  if (activeLightboxIndex.value === null) return
  if (e.key === 'Escape') closeLightbox()
  if (e.key === 'ArrowLeft') prevPhoto()
  if (e.key === 'ArrowRight') nextPhoto()
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('keydown', handleKeydown)
  }
})

onUnmounted(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('keydown', handleKeydown)
  }
  if (typeof document !== 'undefined') {
    document.body.style.overflow = ''
  }
})
</script>

<template>
  <div class="about-page">
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

    <!-- Archival Photo Section (Parish Heritage) -->
    <section id="archival-heritage" class="about-archive page-width" aria-label="Parish Heritage and Archival Photographs">
      <div class="section-header-block">
        <span class="section-eyebrow">PARISH HERITAGE</span>
        <h2>A Legacy of Faith Through the Years</h2>
        <div class="gold-rule" aria-hidden="true">✣</div>
        <p class="section-subtitle">
          These archival photographs offer a glimpse into the history, devotion, and community life of Our Lady of the Pillar Parish.
        </p>
      </div>

      <div class="archive-gallery-editorial">
        <!-- 1 Larger Featured Historical Image -->
        <div
          class="archive-featured-card"
          role="button"
          tabindex="0"
          :aria-label="'View full size: ' + featuredPhoto.title"
          @click="openLightbox(0)"
          @keydown.enter="openLightbox(0)"
          @keydown.space.prevent="openLightbox(0)"
        >
          <div class="archive-featured-img-wrap">
            <img :src="featuredPhoto.src" :alt="featuredPhoto.alt" class="archive-img">
            <div class="archive-badge">
              <span class="archive-badge-star" aria-hidden="true">✦</span>
              <span>Featured Archival Photograph</span>
            </div>
            <div class="archive-zoom-hint" aria-hidden="true">
              <span class="zoom-icon">⤢</span>
              <span>Click to Expand</span>
            </div>
          </div>
          <div class="archive-featured-caption">
            <div class="caption-content">
              <span class="caption-eyebrow">Historical Facade</span>
              <h3 class="caption-title">{{ featuredPhoto.title }}</h3>
            </div>
            <span class="caption-prompt">View Photograph ↗</span>
          </div>
        </div>

        <!-- 4 Supporting Historical Images in Responsive Grid -->
        <div class="archive-grid-supporting">
          <div
            v-for="(photo, sIndex) in supportingPhotos"
            :key="photo.id"
            class="archive-card"
            role="button"
            tabindex="0"
            :aria-label="'View full size: ' + photo.title"
            @click="openLightbox(sIndex + 1)"
            @keydown.enter="openLightbox(sIndex + 1)"
            @keydown.space.prevent="openLightbox(sIndex + 1)"
          >
            <div class="archive-card-img-wrap">
              <img :src="photo.src" :alt="photo.alt" class="archive-img">
              <div class="archive-zoom-hint" aria-hidden="true">
                <span class="zoom-icon">⤢</span>
              </div>
            </div>
            <div class="archive-card-caption">
              <h4 class="card-caption-title">{{ photo.title }}</h4>
              <span class="card-caption-link">View ↗</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- 3. Historic Milestone Card (October 12, 2018) -->
    <section id="milestones" class="about-milestone page-width">
      <div class="milestone-card">
        <div class="milestone-crown-bg" aria-hidden="true">
          <img :src="coronationCrowns" alt="Episcopal Coronation Crowns of Our Lady of the Pillar and the Child Jesus" class="milestone-crown-img">
        </div>
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

    <!-- Archival Lightbox Modal -->
    <Teleport to="body">
      <div
        v-if="activeLightboxIndex !== null"
        class="archive-lightbox-overlay"
        role="dialog"
        aria-modal="true"
        :aria-label="archivePhotos[activeLightboxIndex].title"
        @click.self="closeLightbox"
      >
        <div class="archive-lightbox-content">
          <!-- Close Button -->
          <button
            type="button"
            class="lightbox-close-btn"
            aria-label="Close photograph viewer"
            @click="closeLightbox"
          >
            ✕
          </button>

          <!-- Prev Button -->
          <button
            type="button"
            class="lightbox-nav-btn prev"
            aria-label="Previous photograph"
            @click.stop="prevPhoto"
          >
            ‹
          </button>

          <!-- Main Photograph Display -->
          <div class="lightbox-stage">
            <img
              :src="archivePhotos[activeLightboxIndex].src"
              :alt="archivePhotos[activeLightboxIndex].alt"
              class="lightbox-main-img"
            >
          </div>

          <!-- Next Button -->
          <button
            type="button"
            class="lightbox-nav-btn next"
            aria-label="Next photograph"
            @click.stop="nextPhoto"
          >
            ›
          </button>

          <!-- Lightbox Caption Bar -->
          <div class="lightbox-caption-bar">
            <div class="lightbox-caption-text">
              <span class="lightbox-eyebrow">Archival Collection · Diocesan Shrine of Our Lady of the Pillar</span>
              <h3 class="lightbox-title">{{ archivePhotos[activeLightboxIndex].title }}</h3>
            </div>
            <div class="lightbox-counter">
              <span>{{ activeLightboxIndex + 1 }} / {{ archivePhotos.length }}</span>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

