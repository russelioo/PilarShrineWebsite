<script setup>
import { computed, nextTick, onUnmounted, ref } from 'vue'

const coronationCrowns = '/images/pilar-coronation-crowns.png'
const coronationDecree = '/images/episcopal-coronation-decree-2018.png'

const decreePhoto = {
  src: coronationDecree,
  title: 'Episcopal Coronation Decree',
  alt: 'Episcopal coronation decree for Nuestra Señora del Pilar, issued October 2, 2018, by Bishop Arturo M. Bastes.',
  width: 734,
  height: 979,
}

const celebrationPhotos = [
  {
    src: '/images/milestones-2018/01-crowned-our-lady.jpg',
    title: 'Our Lady of the Pillar',
    alt: 'The crowned image of Our Lady of the Pillar holding the Child Jesus, dressed in blue and white.',
    width: 640,
    height: 960,
  },
  {
    src: '/images/milestones-2018/02-after-the-coronation.jpg',
    title: 'After the Coronation',
    alt: 'A bishop in blue vestments standing beside the crowned image of Our Lady of the Pillar.',
    width: 960,
    height: 641,
  },
  {
    src: '/images/milestones-2018/03-crowning-our-lady.jpg',
    title: 'The Crowning of Our Lady',
    alt: 'A bishop placing a gold crown on the image of Our Lady of the Pillar.',
    width: 960,
    height: 674,
  },
  {
    src: '/images/milestones-2018/04-presentation-of-the-crown.jpg',
    title: 'Presentation of the Crown',
    alt: 'A bishop raising the coronation crown on a red cushion before the congregation.',
    width: 960,
    height: 640,
  },
  {
    src: '/images/milestones-2018/05-preparing-the-image.jpg',
    title: 'Crowning of Jesus',
    alt: 'A member of the clergy crowning the Child Jesus held by Our Lady of the Pillar.',
    width: 960,
    height: 649,
  },
  {
    src: '/images/milestones-2018/06-coronation-crown.jpg',
    title: 'The Coronation Crown',
    alt: 'The gold coronation crown resting on a red velvet cushion.',
    width: 960,
    height: 640,
  },
  {
    src: '/images/milestones-2018/07-entrance-of-the-image.jpg',
    title: 'Enthronement of the Image',
    alt: 'Attendants enthroning the image of Our Lady of the Pillar in front of the clergy.',
    width: 960,
    height: 640,
  },
  {
    src: '/images/milestones-2018/08-community-celebration.jpg',
    title: 'A Community in Celebration',
    alt: 'Parishioners welcoming the image of Our Lady of the Pillar into the crowded church.',
    width: 960,
    height: 640,
  },
  {
    src: '/images/milestones-2018/09-procession-of-the-image.jpg',
    title: 'Procession of the Image',
    alt: 'Devotees carrying the image of Our Lady of the Pillar on a flower-covered platform through the church.',
    width: 640,
    height: 960,
  },
  {
    src: '/images/milestones-2018/10-dedication-of-the-altar.jpg',
    title: 'Dedication of the Altar',
    alt: 'Clergy gathered at the altar during the church dedication, with a flame rising from a vessel on the altar.',
    width: 960,
    height: 640,
  },
]

const heritageViewer = ref(null)
const viewerPhotos = ref([decreePhoto])
const viewerIndex = ref(0)
const activeViewerPhoto = computed(() => viewerPhotos.value[viewerIndex.value])
let viewerBodyOverflow = null

const openHeritageViewer = async (photos, index = 0) => {
  if (!heritageViewer.value || heritageViewer.value.open) return
  viewerPhotos.value = photos
  viewerIndex.value = index
  await nextTick()
  if (!heritageViewer.value || heritageViewer.value.open) return
  viewerBodyOverflow = document.body.style.overflow
  heritageViewer.value.showModal()
  document.body.style.overflow = 'hidden'
}

const openDecree = () => openHeritageViewer([decreePhoto])

const closeHeritageViewer = () => {
  heritageViewer.value?.close()
}

const restoreViewerScroll = () => {
  if (viewerBodyOverflow === null) return
  document.body.style.overflow = viewerBodyOverflow
  viewerBodyOverflow = null
}

const previousViewerPhoto = () => {
  viewerIndex.value = (viewerIndex.value - 1 + viewerPhotos.value.length) % viewerPhotos.value.length
}

const nextViewerPhoto = () => {
  viewerIndex.value = (viewerIndex.value + 1) % viewerPhotos.value.length
}

onUnmounted(() => {
  restoreViewerScroll()
})
</script>

<template>
  <div class="parish-heritage">
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

    <section id="dedication-gallery" class="about-celebration page-width" aria-labelledby="celebration-heading">
      <div class="section-header-block celebration-heading">
        <span class="section-eyebrow"><time datetime="2018-10-12">October 12, 2018</time> · Parish Photo Album</span>
        <h2 id="celebration-heading">Dedication of the Church and Declaration as Diocesan Shrine and Episcopal Coronation of Our Lady of the Pillar</h2>
        <div class="gold-rule" aria-hidden="true">✣</div>
        <p class="section-subtitle">A look back at this milestone in the life of our parish. Select a photograph to view the full image.</p>
      </div>
      <div class="celebration-gallery">
        <figure v-for="(photo, index) in celebrationPhotos" :key="photo.src" class="celebration-photo">
          <button
            type="button"
            class="celebration-photo-button"
            :aria-label="'View photo ' + (index + 1) + ' of ' + celebrationPhotos.length + ': ' + photo.title"
            aria-haspopup="dialog"
            aria-controls="heritage-viewer"
            @click="openHeritageViewer(celebrationPhotos, index)"
            data-analytics-action="gallery_open"
          >
            <img :src="photo.src" :alt="photo.alt" :width="photo.width" :height="photo.height" loading="lazy" decoding="async">
          </button>
          <figcaption>{{ photo.title }}</figcaption>
        </figure>
      </div>
    </section>

    <!-- Episcopal Coronation Decree -->
    <section id="coronation-decree" class="about-decree page-width" aria-labelledby="decree-heading">
      <div class="decree-card">
        <div class="decree-copy">
          <span class="section-eyebrow">From the Diocese of Sorsogon</span>
          <h2 id="decree-heading">The Decree of Episcopal Coronation</h2>
          <div class="editorial-gold-bar" aria-hidden="true"></div>
          <p>
            This decree proclaims the episcopal coronation of the image of Nuestra Señora del Pilar of Pilar, Sorsogon, following the petition of the Catholic community of Our Lady of the Pillar Parish.
          </p>
          <dl class="decree-details">
            <div>
              <dt>Issued</dt>
              <dd><time datetime="2018-10-02">October 2, 2018</time> · Feast of the Guardian Angels</dd>
            </div>
            <div>
              <dt>Issued by</dt>
              <dd>Most Rev. Arturo M. Bastes, SVD, DD<span>Bishop of Sorsogon</span></dd>
            </div>
          </dl>
          <button class="button" type="button" aria-haspopup="dialog" aria-controls="heritage-viewer" @click="openDecree" data-analytics-action="decree_open">
            View Full Decree
          </button>
        </div>
        <figure class="decree-document">
          <button class="decree-preview" type="button" aria-label="View full coronation decree" aria-haspopup="dialog" aria-controls="heritage-viewer" @click="openDecree" data-analytics-action="decree_open">
            <img
              :src="coronationDecree"
              alt="Decree of the episcopal coronation of Nuestra Señora del Pilar, issued on October 2, 2018, and signed by Bishop Arturo M. Bastes and Chancellor Antonio G. Lorilla."
              width="734"
              height="979"
              loading="lazy"
              decoding="async"
            >
          </button>
          <figcaption>Episcopal Coronation Decree · October 2, 2018</figcaption>
        </figure>
      </div>
    </section>

    <Teleport to="body">
      <dialog
        id="heritage-viewer"
        ref="heritageViewer"
        class="heritage-viewer"
        :class="{ 'heritage-viewer--gallery': viewerPhotos.length > 1 }"
        :aria-label="activeViewerPhoto.title"
        @click.self="closeHeritageViewer"
        @cancel.prevent="closeHeritageViewer"
        @close="restoreViewerScroll"
        @keydown.left.prevent="previousViewerPhoto"
        @keydown.right.prevent="nextViewerPhoto"
      >
        <button class="heritage-close-button" type="button" aria-label="Close image viewer" autofocus @click="closeHeritageViewer">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <path d="m6 6 12 12M18 6 6 18" />
          </svg>
        </button>
        <img
          class="heritage-viewer-image"
          :src="activeViewerPhoto.src"
          :alt="activeViewerPhoto.alt"
          :width="activeViewerPhoto.width"
          :height="activeViewerPhoto.height"
        >
        <template v-if="viewerPhotos.length > 1">
          <button class="heritage-nav-button heritage-nav-previous" type="button" aria-label="Previous photograph" @click="previousViewerPhoto">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5-7 7 7 7" /></svg>
          </button>
          <button class="heritage-nav-button heritage-nav-next" type="button" aria-label="Next photograph" @click="nextViewerPhoto">
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 5 7 7-7 7" /></svg>
          </button>
          <p class="heritage-viewer-counter" role="status" aria-atomic="true">
            {{ viewerIndex + 1 }} / {{ viewerPhotos.length }}
            <span class="heritage-photo-title">{{ activeViewerPhoto.title }}</span>
          </p>
        </template>
      </dialog>
    </Teleport>
  </div>
</template>

<style scoped>
.about-celebration {
  padding-bottom: 70px;
}

.celebration-heading {
  max-width: 980px;
  margin-bottom: 32px;
}

.celebration-heading h2 {
  font-size: clamp(24px, 2.5vw, 34px);
  line-height: 1.3;
}

.celebration-gallery {
  column-count: 3;
  column-gap: 20px;
}

.celebration-photo {
  break-inside: avoid;
  margin: 0 0 20px;
  border: 1px solid var(--line);
  border-radius: 12px;
  background: #fff;
  overflow: hidden;
}

.celebration-photo-button {
  display: block;
  width: 100%;
  padding: 0;
  border: 0;
  background: none;
  cursor: zoom-in;
}

.celebration-photo-button:focus-visible {
  outline: 3px solid var(--gold);
  outline-offset: -3px;
}

.celebration-photo img {
  display: block;
  width: 100%;
  height: auto;
}

.celebration-photo figcaption {
  padding: 12px 16px;
  color: var(--blue);
  font-size: 13px;
  font-weight: 600;
  line-height: 1.5;
}

.about-decree {
  padding: 0 0 70px;
}

.decree-card {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 0.9fr);
  align-items: center;
  gap: clamp(28px, 5vw, 64px);
  padding: clamp(24px, 4vw, 48px);
  border: 1px solid #e8dec6;
  border-radius: 22px;
  background: #fffdf5;
}

.decree-copy h2 {
  margin: 10px 0 16px;
  color: var(--blue);
  font: 700 clamp(26px, 2.7vw, 38px) / 1.2 var(--font-heading);
}

.decree-copy p {
  margin: 0;
  color: #4b6077;
  font-size: 15px;
  line-height: 1.75;
}

.decree-details {
  display: grid;
  gap: 18px;
  margin: 24px 0;
  padding: 22px 0;
  border-top: 1px solid #e8dec6;
  border-bottom: 1px solid #e8dec6;
}

.decree-details dt {
  margin-bottom: 5px;
  color: #806022;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.decree-details dd {
  margin: 0;
  color: var(--ink);
  font-size: 14px;
  line-height: 1.6;
}

.decree-details dd span {
  display: block;
  color: var(--muted);
}

.decree-document {
  width: 100%;
  max-width: 420px;
  justify-self: center;
  margin: 0;
}

.decree-preview {
  display: block;
  width: 100%;
  padding: 0;
  border: 0;
  background: none;
  cursor: zoom-in;
}

.decree-preview:focus-visible {
  outline: 3px solid var(--blue);
  outline-offset: 5px;
}

.decree-document img {
  display: block;
  width: 100%;
  height: auto;
  border: 6px solid #fff;
  box-shadow: 0 12px 32px rgba(61, 46, 15, 0.14);
}

.decree-document figcaption {
  margin-top: 14px;
  color: var(--muted);
  font-size: 12px;
  line-height: 1.5;
  text-align: center;
}

.heritage-viewer {
  inset: 0;
  width: 100%;
  height: 100vh;
  height: 100dvh;
  max-width: none;
  max-height: none;
  box-sizing: border-box;
  margin: 0;
  padding: 24px;
  border: 0;
  border-radius: 0;
  background: transparent;
  overflow: hidden;
}

.heritage-viewer[open] {
  display: grid;
  place-items: center;
}

.heritage-viewer::backdrop {
  background: rgba(0, 0, 0, 0.94);
}

.heritage-close-button,
.heritage-nav-button {
  position: absolute;
  z-index: 1;
  display: grid;
  place-items: center;
  width: 44px;
  height: 44px;
  padding: 0;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  background: rgba(20, 20, 20, 0.8);
  color: #fff;
  cursor: pointer;
}

.heritage-close-button {
  top: 16px;
  right: 16px;
}

.heritage-close-button:hover,
.heritage-nav-button:hover {
  background: #333;
}

.heritage-close-button:focus-visible,
.heritage-nav-button:focus-visible {
  outline: 2px solid #fff;
  outline-offset: 3px;
}

.heritage-viewer-image {
  display: block;
  width: auto;
  height: auto;
  max-width: 100%;
  max-height: calc(100vh - 48px);
  max-height: calc(100dvh - 48px);
  object-fit: contain;
}

.heritage-viewer--gallery {
  padding: 56px 68px;
}

.heritage-viewer--gallery .heritage-viewer-image {
  max-height: calc(100vh - 112px);
  max-height: calc(100dvh - 112px);
}

.heritage-nav-button {
  top: 50%;
  transform: translateY(-50%);
}

.heritage-nav-previous {
  left: 16px;
}

.heritage-nav-next {
  right: 16px;
}

.heritage-viewer-counter {
  position: absolute;
  bottom: 16px;
  left: 16px;
  right: 16px;
  margin: 0;
  color: #fff;
  font-size: 13px;
  line-height: 1.5;
  text-align: center;
  pointer-events: none;
}

.heritage-photo-title {
  margin-left: 12px;
  color: #d0d0d0;
}

@media (max-width: 760px) {
  .about-celebration {
    padding-bottom: 45px;
  }

  .celebration-gallery {
    column-count: 2;
    column-gap: 12px;
  }

  .celebration-photo {
    margin-bottom: 12px;
  }

  .heritage-viewer--gallery {
    padding-left: 12px;
    padding-right: 12px;
  }

  .heritage-photo-title {
    display: block;
    margin-left: 0;
  }

  .about-decree {
    padding-bottom: 45px;
  }

  .decree-card {
    grid-template-columns: minmax(0, 1fr);
  }
}

@media (max-width: 480px) {
  .celebration-gallery {
    column-count: 1;
  }
}
</style>
