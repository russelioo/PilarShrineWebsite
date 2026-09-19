<script setup>
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import {
  rosaryPrayers,
  rosaryMysteries,
  weeklyMysterySchedule,
  getTodayMysteryKey,
  getTodayDayName,
  generateRosarySteps,
} from '../../data/rosaryData'

// State for active mystery selection in the guide
const selectedMysteryKey = ref(getTodayMysteryKey())
const selectedTab = ref('today') // 'today' | 'joyful' | 'luminous' | 'sorrowful' | 'glorious'
const activeDecadeIndex = ref(0) // 0 to 4
const isLargeText = ref(false)

// State for Interactive "Start Rosary" Guided Prayer Mode
const isGuidedMode = ref(false)
const guidedStepIndex = ref(0) // 0 to 32
const guidedMysteryKey = ref(getTodayMysteryKey())
const guidedHailMaryCount = ref(1) // For counting within 3 Hail Marys or 10 Hail Marys

const guidedContainerRef = ref(null)
const guideDecadeRef = ref(null)

// Current date and recommended mysteries
const todayDate = new Date()
const todayDayName = computed(() => getTodayDayName(todayDate))
const todayMysteryKey = computed(() => getTodayMysteryKey(todayDate))
const todayMystery = computed(() => rosaryMysteries[todayMysteryKey.value])

// Currently viewed mystery in the guide
const currentMystery = computed(() => {
  const key = selectedTab.value === 'today' ? todayMysteryKey.value : selectedMysteryKey.value
  return rosaryMysteries[key] || rosaryMysteries.joyful
})

// Guided mode steps
const guidedSteps = computed(() => generateRosarySteps(guidedMysteryKey.value))
const currentGuidedStep = computed(() => guidedSteps.value[guidedStepIndex.value] || guidedSteps.value[0])

// Select mystery tab in guide
const selectTab = (tabKey) => {
  selectedTab.value = tabKey
  if (tabKey !== 'today') {
    selectedMysteryKey.value = tabKey
  }
}

// Decade navigation in guide view
const selectDecade = (index) => {
  activeDecadeIndex.value = index
  nextTick(() => {
    if (guideDecadeRef.value) {
      guideDecadeRef.value.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
    }
  })
}

const prevDecade = () => {
  if (activeDecadeIndex.value > 0) {
    selectDecade(activeDecadeIndex.value - 1)
  }
}

const nextDecade = () => {
  if (activeDecadeIndex.value < 4) {
    selectDecade(activeDecadeIndex.value + 1)
  }
}

// Enter Interactive Guided Prayer Mode
const startRosary = (mysteryKey = null, initialStep = 0) => {
  guidedMysteryKey.value = mysteryKey || (selectedTab.value === 'today' ? todayMysteryKey.value : selectedMysteryKey.value)
  guidedStepIndex.value = initialStep
  guidedHailMaryCount.value = 1
  isGuidedMode.value = true
  document.body.style.overflow = 'hidden'
  nextTick(() => {
    if (guidedContainerRef.value) {
      guidedContainerRef.value.scrollTop = 0
    }
  })
}

const startDecadeGuided = (decadeNumber) => {
  // Decade 1 starts at step index 5 (Step 6)
  // Decade 2 starts at step index 10 (Step 11)
  // Decade 3 starts at step index 15 (Step 16)
  // Decade 4 starts at step index 20 (Step 21)
  // Decade 5 starts at step index 25 (Step 26)
  const stepMap = [5, 10, 15, 20, 25]
  const targetStep = stepMap[decadeNumber - 1] ?? 5
  startRosary(currentMystery.value.key, targetStep)
}

const exitGuidedMode = () => {
  isGuidedMode.value = false
  document.body.style.overflow = ''
}

// Guided mode navigation
const nextGuidedStep = () => {
  if (guidedStepIndex.value < guidedSteps.value.length - 1) {
    guidedStepIndex.value++
    guidedHailMaryCount.value = 1
    nextTick(() => {
      if (guidedContainerRef.value) {
        guidedContainerRef.value.scrollTop = 0
      }
    })
  }
}

const prevGuidedStep = () => {
  if (guidedStepIndex.value > 0) {
    guidedStepIndex.value--
    guidedHailMaryCount.value = 1
    nextTick(() => {
      if (guidedContainerRef.value) {
        guidedContainerRef.value.scrollTop = 0
      }
    })
  }
}

const setGuidedHailMary = (count) => {
  guidedHailMaryCount.value = count
}

// Keyboard shortcuts for prayer companion
const handleKeydown = (e) => {
  if (isGuidedMode.value) {
    if (e.key === 'Escape') {
      exitGuidedMode()
    } else if (e.key === 'ArrowRight') {
      nextGuidedStep()
    } else if (e.key === 'ArrowLeft') {
      prevGuidedStep()
    }
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})

// Progress dots helper
const getDecadeDots = (decadeNum) => {
  // Returns array of 5 booleans (true for filled, false for empty)
  return [1, 2, 3, 4, 5].map(i => i <= decadeNum)
}

// Format prayer prose with paragraphs
const getPrayerParagraphs = (text) => {
  if (!text) return []
  return text.split('\n\n').filter(p => p.trim())
}
</script>

<template>
  <div class="rosary-page" :class="{ 'large-text-active': isLargeText }">
    <div class="page-width">
      <!-- Back to Devotions Navigation -->
      <nav class="rosary-back-nav" aria-label="Return to devotions">
        <a href="#/novenas" class="button secondary rosary-back-btn">
          <svg viewBox="0 0 20 20" fill="currentColor" class="back-arrow-icon" aria-hidden="true">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
          </svg>
          <span>Back to Devotions &amp; Novenas</span>
        </a>

        <!-- Accessibility / Text Size Controls -->
        <div class="rosary-header-actions">
          <button
            type="button"
            class="text-size-toggle-btn"
            :class="{ active: isLargeText }"
            :aria-pressed="isLargeText"
            title="Toggle larger, comfortable prayer font size"
            @click="isLargeText = !isLargeText"
          >
            <span class="toggle-icon-label" aria-hidden="true">A</span>
            <span class="toggle-icon-label lg" aria-hidden="true">A+</span>
            <span class="toggle-text">{{ isLargeText ? 'Standard Font' : 'Larger Font' }}</span>
          </button>
        </div>
      </nav>

      <!-- 1. Hero Introduction Card -->
      <header class="rosary-hero-card">
        <div class="rosary-hero-content">
          <div class="rosary-eyebrow-row">
            <span class="rosary-eyebrow-badge">DAILY MARIAN MEDITATION</span>
            <span class="rosary-shrine-tag">Diocesan Shrine of Our Lady of the Pillar</span>
          </div>

          <h1 class="rosary-hero-title">The Holy Rosary</h1>
          <p class="rosary-hero-subtitle">A step-by-step guide to praying the Rosary</p>
          <div class="rosary-gold-rule" aria-hidden="true"></div>

          <p class="rosary-hero-intro">
            The Rosary is a Marian prayer that invites us to meditate on the mysteries of the life, death, and resurrection of Jesus Christ with the Blessed Virgin Mary.
          </p>

          <div class="rosary-hero-actions">
            <button
              type="button"
              class="button rosary-start-btn"
              @click="startRosary(todayMysteryKey, 0)"
            >
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="btn-play-icon" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <polygon points="10 8 16 12 10 16 10 8" fill="currentColor"></polygon>
              </svg>
              <span>Start Today’s Rosary ({{ todayMystery.shortName }})</span>
            </button>

            <button
              type="button"
              class="button secondary rosary-browse-btn"
              @click="startRosary(currentMystery.key, 0)"
            >
              <span>Guided Rosary Companion &rarr;</span>
            </button>
          </div>
        </div>

        <div class="rosary-hero-visual" aria-hidden="true">
          <div class="bead-circle-art">
            <span class="bead-center-cross">📿</span>
          </div>
        </div>
      </header>

      <!-- 2. "Which Mysteries Do I Pray Today?" Section -->
      <section class="today-mystery-section" aria-labelledby="today-mystery-heading">
        <div class="today-mystery-card">
          <div class="today-mystery-badge-bar">
            <div class="today-tag-group">
              <span class="today-live-dot" aria-hidden="true"></span>
              <span class="today-label">TODAY</span>
              <strong class="today-day-name">{{ todayDayName }}</strong>
            </div>
            <span class="today-schedule-hint">Traditional Catholic Weekly Schedule</span>
          </div>

          <div class="today-mystery-body">
            <div class="today-mystery-meta">
              <span class="today-set-subtitle">Recommended Mysteries</span>
              <h2 id="today-mystery-heading" class="today-mystery-title">{{ todayMystery.title }}</h2>
              <p class="today-mystery-desc">{{ todayMystery.description }}</p>
            </div>

            <!-- List of the 5 today's mysteries -->
            <ol class="today-mysteries-list">
              <li
                v-for="item in todayMystery.items"
                :key="item.number"
                class="today-mystery-list-item"
              >
                <span class="item-num">{{ item.number }}</span>
                <div class="item-info">
                  <strong class="item-title">{{ item.title }}</strong>
                  <p class="item-meditation">{{ item.meditation }}</p>
                </div>
              </li>
            </ol>
          </div>

          <div class="today-mystery-footer">
            <button
              type="button"
              class="button today-start-action"
              @click="startRosary(todayMysteryKey, 0)"
            >
              <svg viewBox="0 0 20 20" fill="currentColor" class="footer-btn-icon" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
              </svg>
              <span>Start Today’s Rosary</span>
            </button>
            <div class="today-schedule-preview">
              <span class="preview-label">Weekly Order:</span>
              <span class="preview-days">Mon &amp; Sat: Joyful · Tue &amp; Fri: Sorrowful · Wed &amp; Sun: Glorious · Thu: Luminous</span>
            </div>
          </div>
        </div>
      </section>

      <!-- 3. Interactive Mystery Selector (5 Cards) -->
      <section class="mystery-selector-section" aria-labelledby="mystery-selector-heading">
        <div class="section-title-block">
          <span class="section-eyebrow">THE SACRED MYSTERIES</span>
          <h2 id="mystery-selector-heading" class="section-heading">Mysteries of the Rosary</h2>
          <div class="editorial-gold-bar" aria-hidden="true"></div>
          <p class="section-subtext">
            Select a set of mysteries below to explore its meditations, scripture reflections, or begin praying that specific decade.
          </p>
        </div>

        <!-- 5 Selectable Mystery Cards/Tabs -->
        <div class="mystery-tabs-grid" role="tablist" aria-label="Rosary Mystery Categories">
          <!-- Today's Mysteries Tab -->
          <button
            type="button"
            role="tab"
            class="mystery-tab-card"
            :class="{ active: selectedTab === 'today' }"
            :aria-selected="selectedTab === 'today'"
            @click="selectTab('today')"
          >
            <div class="tab-card-top">
              <span class="tab-badge-today">TODAY'S</span>
              <span class="tab-day-indicator">{{ todayDayName }}</span>
            </div>
            <strong class="tab-card-title">{{ todayMystery.shortName }}</strong>
            <span class="tab-card-sub">Recommended Today</span>
          </button>

          <!-- Joyful Mysteries Tab -->
          <button
            type="button"
            role="tab"
            class="mystery-tab-card"
            :class="{ active: selectedTab === 'joyful' }"
            :aria-selected="selectedTab === 'joyful'"
            @click="selectTab('joyful')"
          >
            <div class="tab-card-top">
              <span class="tab-icon">🕊</span>
              <span class="tab-schedule-chip">Mon &amp; Sat</span>
            </div>
            <strong class="tab-card-title">Joyful</strong>
            <span class="tab-card-sub">The Incarnation &amp; Early Life</span>
          </button>

          <!-- Luminous Mysteries Tab -->
          <button
            type="button"
            role="tab"
            class="mystery-tab-card"
            :class="{ active: selectedTab === 'luminous' }"
            :aria-selected="selectedTab === 'luminous'"
            @click="selectTab('luminous')"
          >
            <div class="tab-card-top">
              <span class="tab-icon">✦</span>
              <span class="tab-schedule-chip">Thursday</span>
            </div>
            <strong class="tab-card-title">Luminous</strong>
            <span class="tab-card-sub">The Public Ministry of Christ</span>
          </button>

          <!-- Sorrowful Mysteries Tab -->
          <button
            type="button"
            role="tab"
            class="mystery-tab-card"
            :class="{ active: selectedTab === 'sorrowful' }"
            :aria-selected="selectedTab === 'sorrowful'"
            @click="selectTab('sorrowful')"
          >
            <div class="tab-card-top">
              <span class="tab-icon">✝</span>
              <span class="tab-schedule-chip">Tue &amp; Fri</span>
            </div>
            <strong class="tab-card-title">Sorrowful</strong>
            <span class="tab-card-sub">The Passion &amp; Crucifixion</span>
          </button>

          <!-- Glorious Mysteries Tab -->
          <button
            type="button"
            role="tab"
            class="mystery-tab-card"
            :class="{ active: selectedTab === 'glorious' }"
            :aria-selected="selectedTab === 'glorious'"
            @click="selectTab('glorious')"
          >
            <div class="tab-card-top">
              <span class="tab-icon">👑</span>
              <span class="tab-schedule-chip">Wed &amp; Sun</span>
            </div>
            <strong class="tab-card-title">Glorious</strong>
            <span class="tab-card-sub">Resurrection &amp; Heaven</span>
          </button>
        </div>

        <!-- Active Mystery Details Display -->
        <div class="mystery-display-surface">
          <header class="mystery-display-header">
            <div class="display-title-group">
              <span class="display-eyebrow">{{ currentMystery.daysLabel }} · MEDITATION THEME</span>
              <h3 class="display-title">{{ currentMystery.title }}</h3>
              <p class="display-desc">{{ currentMystery.description }}</p>
            </div>
            <button
              type="button"
              class="button secondary pray-all-btn"
              @click="startRosary(currentMystery.key, 0)"
            >
              <span>Pray All 5 Decades &rarr;</span>
            </button>
          </header>

          <!-- 5 Mystery Cards for the Active Set -->
          <div class="mysteries-cards-grid">
            <article
              v-for="item in currentMystery.items"
              :key="item.number"
              class="mystery-detail-card"
            >
              <div class="mystery-card-header">
                <span class="mystery-number-badge">Mystery {{ item.number }}</span>
                <span v-if="item.fruit" class="mystery-fruit-badge">Fruit: {{ item.fruit }}</span>
              </div>

              <h4 class="mystery-card-title">{{ item.title }}</h4>
              <p class="mystery-card-meditation">{{ item.meditation }}</p>

              <div class="mystery-card-footer">
                <span class="mystery-scripture" v-if="item.scripture">
                  <svg viewBox="0 0 20 20" fill="currentColor" class="scripture-icon" aria-hidden="true">
                    <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
                  </svg>
                  <span>{{ item.scripture }}</span>
                </span>

                <button
                  type="button"
                  class="pray-decade-btn"
                  @click="startDecadeGuided(item.number)"
                >
                  <span>Pray this decade</span>
                  <span aria-hidden="true">&rarr;</span>
                </button>
              </div>
            </article>
          </div>
        </div>
      </section>

      <!-- 4. Complete Rosary Order: Introductory Steps 1 to 5 -->
      <section class="rosary-order-section" aria-labelledby="rosary-order-heading">
        <div class="section-title-block">
          <span class="section-eyebrow">STEP-BY-STEP ORDER OF PRAYER</span>
          <h2 id="rosary-order-heading" class="section-heading">How to Pray the Holy Rosary</h2>
          <div class="editorial-gold-bar" aria-hidden="true"></div>
          <p class="section-subtext">
            Follow the traditional Catholic sequence of prayer, beginning with the introductory prayers on the Crucifix and first beads.
          </p>
        </div>

        <div class="steps-container">
          <!-- STEP 1 — THE SIGN OF THE CROSS -->
          <article class="prayer-step-card">
            <div class="step-badge-col">
              <span class="step-number">1</span>
              <span class="step-label">STEP 1</span>
            </div>
            <div class="step-content-col">
              <div class="step-header">
                <h3 class="step-title">The Sign of the Cross</h3>
                <span class="step-position-pill">On the Crucifix</span>
              </div>
              <p class="step-instruction">In the name of the Father, and of the Son, and of the Holy Spirit. Amen.</p>
              <div class="prayer-prose-box">
                <p class="prayer-prose-line">“In the name of the Father, and of the Son, and of the Holy Spirit. Amen.”</p>
              </div>
            </div>
          </article>

          <!-- STEP 2 — THE APOSTLES’ CREED -->
          <article class="prayer-step-card">
            <div class="step-badge-col">
              <span class="step-number">2</span>
              <span class="step-label">STEP 2</span>
            </div>
            <div class="step-content-col">
              <div class="step-header">
                <h3 class="step-title">The Apostles’ Creed</h3>
                <span class="step-position-pill">Still on the Crucifix</span>
              </div>
              <p class="step-instruction">Profess the foundational truths of our Catholic faith.</p>
              <div class="prayer-prose-box">
                <p
                  v-for="(p, idx) in getPrayerParagraphs(rosaryPrayers.apostlesCreed.text)"
                  :key="idx"
                  class="prayer-prose-line"
                >
                  {{ p }}
                </p>
              </div>
            </div>
          </article>

          <!-- STEP 3 — THE LORD’S PRAYER -->
          <article class="prayer-step-card">
            <div class="step-badge-col">
              <span class="step-number">3</span>
              <span class="step-label">STEP 3</span>
            </div>
            <div class="step-content-col">
              <div class="step-header">
                <h3 class="step-title">The Lord’s Prayer</h3>
                <span class="step-position-pill">First Large Bead</span>
              </div>
              <p class="step-instruction">Pray the Lord’s Prayer for the intentions of the Holy Father and the universal Church.</p>
              <div class="prayer-prose-box">
                <p
                  v-for="(p, idx) in getPrayerParagraphs(rosaryPrayers.ourFather.text)"
                  :key="idx"
                  class="prayer-prose-line"
                >
                  {{ p }}
                </p>
              </div>
            </div>
          </article>

          <!-- STEP 4 — THREE HAIL MARYS -->
          <article class="prayer-step-card">
            <div class="step-badge-col">
              <span class="step-number">4</span>
              <span class="step-label">STEP 4</span>
            </div>
            <div class="step-content-col">
              <div class="step-header">
                <h3 class="step-title">Three Hail Marys</h3>
                <span class="step-position-pill">Three Small Beads</span>
              </div>
              <p class="step-instruction">Pray three Hail Marys for an increase of faith, hope, and charity.</p>
              
              <!-- Three Intentions Tracker -->
              <div class="three-intentions-row">
                <div class="intention-pill">
                  <span class="intent-num">1</span>
                  <span class="intent-name">For Faith</span>
                </div>
                <div class="intention-pill">
                  <span class="intent-num">2</span>
                  <span class="intent-name">For Hope</span>
                </div>
                <div class="intention-pill">
                  <span class="intent-num">3</span>
                  <span class="intent-name">For Charity</span>
                </div>
              </div>

              <div class="prayer-prose-box">
                <p
                  v-for="(p, idx) in getPrayerParagraphs(rosaryPrayers.hailMary.text)"
                  :key="idx"
                  class="prayer-prose-line"
                >
                  {{ p }}
                </p>
              </div>
            </div>
          </article>

          <!-- STEP 5 — GLORY BE -->
          <article class="prayer-step-card">
            <div class="step-badge-col">
              <span class="step-number">5</span>
              <span class="step-label">STEP 5</span>
            </div>
            <div class="step-content-col">
              <div class="step-header">
                <h3 class="step-title">Glory Be</h3>
                <span class="step-position-pill">On the Chain before the Medal</span>
              </div>
              <p class="step-instruction">Praise the Most Holy Trinity before announcing the first decade.</p>
              <div class="prayer-prose-box">
                <p
                  v-for="(p, idx) in getPrayerParagraphs(rosaryPrayers.gloryBe.text)"
                  :key="idx"
                  class="prayer-prose-line"
                >
                  {{ p }}
                </p>
              </div>
            </div>
          </article>
        </div>
      </section>

      <!-- 5. The Five Decades Section -->
      <section ref="guideDecadeRef" class="five-decades-section" aria-labelledby="five-decades-heading">
        <div class="section-title-block">
          <span class="section-eyebrow">MEDITATION &amp; REPETITION</span>
          <h2 id="five-decades-heading" class="section-heading">The Five Decades</h2>
          <div class="editorial-gold-bar" aria-hidden="true"></div>
          <p class="section-subtext">
            Each decade consists of announcing the sacred mystery, praying the Lord’s Prayer, ten Hail Marys, the Glory Be, and concluding with the Fatima Prayer.
          </p>
        </div>

        <!-- Decade Progress & Navigation Bar -->
        <div class="decade-selector-bar">
          <div class="decade-progress-header">
            <span class="current-decade-label">Decade {{ activeDecadeIndex + 1 }} of 5</span>
            <div class="decade-dots-indicator" aria-hidden="true">
              <span
                v-for="(filled, dotIdx) in getDecadeDots(activeDecadeIndex + 1)"
                :key="dotIdx"
                class="decade-dot"
                :class="{ filled }"
              ></span>
            </div>
          </div>

          <!-- Decade Quick Jump Buttons -->
          <div class="decade-pills-row" role="tablist" aria-label="Jump to Decade">
            <button
              v-for="(mysteryItem, idx) in currentMystery.items"
              :key="idx"
              type="button"
              role="tab"
              class="decade-pill-btn"
              :class="{ active: activeDecadeIndex === idx }"
              :aria-selected="activeDecadeIndex === idx"
              @click="selectDecade(idx)"
            >
              <span class="pill-num">Decade {{ idx + 1 }}</span>
              <strong class="pill-title">{{ mysteryItem.title }}</strong>
            </button>
          </div>
        </div>

        <!-- Decade Detailed Container -->
        <article class="decade-active-surface">
          <!-- A. Announce the Mystery & B. Meditation -->
          <header class="decade-surface-header">
            <div class="decade-surface-badge-row">
              <span class="decade-tag">DECADE {{ activeDecadeIndex + 1 }} OF 5</span>
              <span class="decade-mystery-group">{{ currentMystery.title }}</span>
            </div>

            <h3 class="decade-mystery-title">
              {{ currentMystery.items[activeDecadeIndex].number }}. {{ currentMystery.items[activeDecadeIndex].title }}
            </h3>

            <div class="decade-meditation-box">
              <span class="meditation-icon" aria-hidden="true">✦</span>
              <div class="meditation-text-content">
                <span class="meditation-label">Sacred Meditation</span>
                <p class="meditation-prose">{{ currentMystery.items[activeDecadeIndex].meditation }}</p>
                <small v-if="currentMystery.items[activeDecadeIndex].fruit" class="meditation-fruit">
                  Spiritual Fruit: <strong>{{ currentMystery.items[activeDecadeIndex].fruit }}</strong>
                </small>
              </div>
            </div>
          </header>

          <!-- Decade Prayers Flow (C, D, E, F) -->
          <div class="decade-prayers-flow">
            <!-- C. Our Father -->
            <div class="flow-item">
              <div class="flow-item-header">
                <span class="flow-badge">C</span>
                <strong class="flow-title">The Lord’s Prayer (Our Father)</strong>
                <span class="flow-bead-note">Large Bead</span>
              </div>
              <div class="flow-prayer-box">
                <p
                  v-for="(p, idx) in getPrayerParagraphs(rosaryPrayers.ourFather.text)"
                  :key="idx"
                  class="flow-prayer-line"
                >
                  {{ p }}
                </p>
              </div>
            </div>

            <!-- D. Ten Hail Marys with Bead Tracker -->
            <div class="flow-item">
              <div class="flow-item-header">
                <span class="flow-badge">D</span>
                <strong class="flow-title">Ten Hail Marys</strong>
                <span class="flow-bead-note">10 Small Beads</span>
              </div>

              <!-- 10 Visual Bead Trackers -->
              <div class="decade-bead-track" aria-label="10 Hail Mary beads">
                <span
                  v-for="b in 10"
                  :key="b"
                  class="bead-circle-indicator"
                  :title="`Hail Mary bead ${b} of 10`"
                >
                  {{ b }}
                </span>
              </div>

              <div class="flow-prayer-box">
                <p
                  v-for="(p, idx) in getPrayerParagraphs(rosaryPrayers.hailMary.text)"
                  :key="idx"
                  class="flow-prayer-line"
                >
                  {{ p }}
                </p>
              </div>
            </div>

            <!-- E. Glory Be -->
            <div class="flow-item">
              <div class="flow-item-header">
                <span class="flow-badge">E</span>
                <strong class="flow-title">Glory Be</strong>
                <span class="flow-bead-note">On the Chain</span>
              </div>
              <div class="flow-prayer-box">
                <p
                  v-for="(p, idx) in getPrayerParagraphs(rosaryPrayers.gloryBe.text)"
                  :key="idx"
                  class="flow-prayer-line"
                >
                  {{ p }}
                </p>
              </div>
            </div>

            <!-- F. Fatima Prayer -->
            <div class="flow-item flow-item-fatima">
              <div class="flow-item-header">
                <span class="flow-badge fatima">F</span>
                <strong class="flow-title">The Fatima Prayer</strong>
                <span class="flow-bead-note">Requested by Our Lady of Fatima</span>
              </div>
              <div class="flow-prayer-box">
                <p
                  v-for="(p, idx) in getPrayerParagraphs(rosaryPrayers.fatimaPrayer.text)"
                  :key="idx"
                  class="flow-prayer-line"
                >
                  {{ p }}
                </p>
              </div>
            </div>
          </div>

          <!-- Decade Pagination Controls -->
          <footer class="decade-pagination-bar">
            <button
              type="button"
              class="decade-nav-btn"
              :disabled="activeDecadeIndex === 0"
              @click="prevDecade"
            >
              <svg viewBox="0 0 20 20" fill="currentColor" class="nav-arrow" aria-hidden="true">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              <span>Previous Decade</span>
            </button>

            <span class="decade-step-counter">
              Decade {{ activeDecadeIndex + 1 }} of 5
            </span>

            <button
              type="button"
              class="decade-nav-btn"
              :disabled="activeDecadeIndex === 4"
              @click="nextDecade"
            >
              <span>Next Decade</span>
              <svg viewBox="0 0 20 20" fill="currentColor" class="nav-arrow" aria-hidden="true">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </footer>
        </article>
      </section>

      <!-- 6. Concluding Rosary Prayers Section -->
      <section class="concluding-prayers-section" aria-labelledby="concluding-heading">
        <div class="section-title-block">
          <span class="section-eyebrow">CONCLUSION OF THE ROSARY</span>
          <h2 id="concluding-heading" class="section-heading">Concluding Prayers</h2>
          <div class="editorial-gold-bar" aria-hidden="true"></div>
          <p class="section-subtext">
            After completing the five decades, conclude the Holy Rosary with the Hail, Holy Queen, the Closing Rosary Prayer, and the Sign of the Cross.
          </p>
        </div>

        <div class="concluding-cards-grid">
          <!-- Hail, Holy Queen -->
          <article class="concluding-card">
            <header class="concluding-card-header">
              <span class="card-eyebrow">MARIAN ANTIPHON</span>
              <h3 class="card-title">Hail, Holy Queen (Salve Regina)</h3>
            </header>
            <div class="prayer-prose-box">
              <p
                v-for="(p, idx) in getPrayerParagraphs(rosaryPrayers.hailHolyQueen.text)"
                :key="idx"
                class="prayer-prose-line"
              >
                {{ p }}
              </p>
            </div>
          </article>

          <!-- Closing Rosary Prayer -->
          <article class="concluding-card">
            <header class="concluding-card-header">
              <span class="card-eyebrow">CLOSING ORATION</span>
              <h3 class="card-title">Closing Rosary Prayer (Oremus)</h3>
            </header>
            <div class="prayer-prose-box">
              <p
                v-for="(p, idx) in getPrayerParagraphs(rosaryPrayers.closingPrayer.text)"
                :key="idx"
                class="prayer-prose-line"
              >
                {{ p }}
              </p>
            </div>
          </article>

          <!-- Final Sign of the Cross -->
          <article class="concluding-card concluding-card-final">
            <header class="concluding-card-header">
              <span class="card-eyebrow">FINAL BLESSING</span>
              <h3 class="card-title">The Sign of the Cross</h3>
            </header>
            <div class="prayer-prose-box">
              <p class="prayer-prose-line">
                “In the name of the Father, and of the Son, and of the Holy Spirit. Amen.”
              </p>
            </div>
          </article>
        </div>
      </section>

      <!-- 7. Complete Prayer Library / Reference Accordion -->
      <section class="prayers-library-section" aria-labelledby="prayers-library-heading">
        <div class="section-title-block">
          <span class="section-eyebrow">REFERENCE LIBRARY</span>
          <h2 id="prayers-library-heading" class="section-heading">Complete Catholic English Prayers</h2>
          <div class="editorial-gold-bar" aria-hidden="true"></div>
          <p class="section-subtext">
            All eight traditional prayers used in the Holy Rosary, presented in their complete English liturgical form.
          </p>
        </div>

        <div class="prayers-accordion-list">
          <details
            v-for="(prayer, key) in rosaryPrayers"
            :key="key"
            class="devotional-accordion prayer-accordion-item"
          >
            <summary class="accordion-summary">
              <div class="summary-meta">
                <span class="summary-eyebrow">{{ prayer.latinTitle || 'TRADITIONAL PRAYER' }}</span>
                <strong class="summary-title">{{ prayer.title }}</strong>
              </div>
              <span class="accordion-icon" aria-hidden="true"></span>
            </summary>
            <div class="accordion-content">
              <div class="prayer-prose-box">
                <p
                  v-for="(paragraph, pIdx) in getPrayerParagraphs(prayer.text)"
                  :key="pIdx"
                  class="prayer-prose-line"
                >
                  {{ paragraph }}
                </p>
              </div>
            </div>
          </details>
        </div>
      </section>

      <!-- 8. Mass & Shrine Devotion Footer Callout -->
      <aside class="rosary-shrine-banner" aria-label="Shrine Rosary and Mass Schedule">
        <div class="shrine-banner-content">
          <span class="shrine-banner-eyebrow">COMMUNAL PARISH PRAYER</span>
          <h2 class="shrine-banner-title">Pray with the Shrine Community</h2>
          <div class="shrine-banner-schedule-pill">
            <svg viewBox="0 0 20 20" fill="currentColor" class="clock-icon" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
            </svg>
            <span>Daily · 30 minutes before every Holy Mass</span>
          </div>
          <p class="shrine-banner-desc">
            Devotees and parishioners gather to pray the Holy Rosary communally before every Holy Mass at the Diocesan Shrine of Our Lady of the Pillar in Pilar, Sorsogon.
          </p>
          <div class="shrine-banner-actions">
            <a href="#/schedule" class="button secondary banner-schedule-btn">
              View Shrine Mass Times &rarr;
            </a>
            <a href="#/novena-details" class="button secondary banner-novena-btn">
              9-Day Pillar Novena &rarr;
            </a>
          </div>
        </div>
      </aside>
    </div>

    <!-- ==================================================================== -->
    <!-- INTERACTIVE "START ROSARY" GUIDED PRAYER COMPANION (FULL-SCREEN MODAL) -->
    <!-- ==================================================================== -->
    <Teleport to="body">
      <transition name="reader-fade">
        <div
          v-if="isGuidedMode"
          class="guided-rosary-overlay"
          role="dialog"
          aria-modal="true"
          aria-labelledby="guided-rosary-step-title"
          @click.self="exitGuidedMode"
        >
          <div ref="guidedContainerRef" class="guided-rosary-container" :class="{ 'large-text-active': isLargeText }">
            <!-- Top Sticky Navigation & Progress Header -->
            <header class="guided-top-bar">
              <div class="guided-meta-group">
                <span class="guided-decade-pill">{{ currentGuidedStep.part }}</span>
                <span class="guided-step-counter">Step {{ currentGuidedStep.stepIndex }} of 33</span>
                <span class="guided-mystery-badge">{{ rosaryMysteries[guidedMysteryKey].shortName }} Mysteries</span>
              </div>

              <div class="guided-right-controls">
                <!-- Text size toggle inside guided mode -->
                <button
                  type="button"
                  class="guided-font-toggle"
                  :class="{ active: isLargeText }"
                  :aria-pressed="isLargeText"
                  title="Toggle font size"
                  @click="isLargeText = !isLargeText"
                >
                  <span aria-hidden="true">{{ isLargeText ? 'A' : 'A+' }}</span>
                </button>

                <!-- Close guided view button -->
                <button
                  type="button"
                  class="guided-close-btn"
                  aria-label="Exit Guided Rosary"
                  @click="exitGuidedMode"
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                  </svg>
                </button>
              </div>
            </header>

            <!-- Progress Bar Tracker -->
            <div
              class="guided-progress-track"
              role="progressbar"
              :aria-valuenow="currentGuidedStep.stepIndex"
              aria-valuemin="1"
              aria-valuemax="33"
              :aria-valuetext="`Step ${currentGuidedStep.stepIndex} of 33`"
            >
              <div
                class="guided-progress-fill"
                :style="{ width: `${(currentGuidedStep.stepIndex / 33) * 100}%` }"
              ></div>
            </div>

            <!-- Main Reading & Prayer Surface -->
            <main class="guided-content-surface">
              <!-- Bead Location Pill -->
              <div class="guided-bead-pill">
                <span class="bead-icon" aria-hidden="true">📿</span>
                <span>{{ currentGuidedStep.beadLabel }}</span>
              </div>

              <!-- Step Title -->
              <h2 id="guided-rosary-step-title" class="guided-main-title">
                {{ currentGuidedStep.title }}
              </h2>

              <!-- Instruction Text -->
              <p class="guided-instruction">{{ currentGuidedStep.instruction }}</p>

              <!-- Special Card if announcing Mystery -->
              <div v-if="currentGuidedStep.isMysteryCard" class="guided-mystery-showcase">
                <div class="showcase-meta">
                  <span class="showcase-fruit" v-if="currentGuidedStep.fruit">Fruit: {{ currentGuidedStep.fruit }}</span>
                  <span class="showcase-scripture" v-if="currentGuidedStep.scripture">📖 {{ currentGuidedStep.scripture }}</span>
                </div>
                <div class="showcase-gold-bar" aria-hidden="true"></div>
                <p class="showcase-meditation">{{ currentGuidedStep.meditation }}</p>
              </div>

              <!-- Repetition / Bead Counter for 3 Hail Marys -->
              <div v-if="currentGuidedStep.repetitions" class="guided-repetitions-tracker">
                <span class="repetitions-label">Bead Counter:</span>
                <div class="repetitions-buttons">
                  <button
                    v-for="rep in currentGuidedStep.repetitions"
                    :key="rep.count"
                    type="button"
                    class="rep-btn"
                    :class="{ active: guidedHailMaryCount === rep.count }"
                    @click="setGuidedHailMary(rep.count)"
                  >
                    <span class="rep-number">{{ rep.count }}</span>
                    <span class="rep-intention">{{ rep.intention }}</span>
                  </button>
                </div>
              </div>

              <!-- Repetition / Bead Counter for 10 Hail Marys -->
              <div v-if="currentGuidedStep.sectionType === 'hail-marys'" class="guided-beads-10-tracker">
                <div class="beads-10-header">
                  <span class="beads-10-label">Hail Mary Bead {{ guidedHailMaryCount }} of 10</span>
                  <span class="beads-10-sub" v-if="currentGuidedStep.currentMystery">
                    Meditating on: <strong>{{ currentGuidedStep.currentMystery.title }}</strong>
                  </span>
                </div>
                <div class="beads-10-row" role="group" aria-label="10 Hail Mary Beads">
                  <button
                    v-for="b in 10"
                    :key="b"
                    type="button"
                    class="bead-10-btn"
                    :class="{ active: guidedHailMaryCount === b, completed: b < guidedHailMaryCount }"
                    :aria-label="`Bead ${b} of 10`"
                    @click="setGuidedHailMary(b)"
                  >
                    {{ b }}
                  </button>
                </div>
              </div>

              <!-- Prayer Text Display -->
              <div class="guided-prayer-prose-container">
                <p
                  v-for="(p, pIdx) in getPrayerParagraphs(currentGuidedStep.prayerText)"
                  :key="pIdx"
                  class="guided-prayer-prose"
                >
                  {{ p }}
                </p>
              </div>
            </main>

            <!-- Bottom Sticky Navigation Bar -->
            <footer class="guided-footer-bar">
              <button
                type="button"
                class="guided-nav-btn prev-btn"
                :disabled="guidedStepIndex === 0"
                @click="prevGuidedStep"
              >
                <svg viewBox="0 0 20 20" fill="currentColor" class="nav-arrow" aria-hidden="true">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                <span>Previous</span>
              </button>

              <!-- Step Indicator Pill & Dropdown -->
              <div class="guided-jump-menu-wrapper">
                <label for="guided-step-select" class="sr-only">Jump to step</label>
                <select
                  id="guided-step-select"
                  class="guided-step-select"
                  :value="guidedStepIndex"
                  @change="guidedStepIndex = Number($event.target.value); guidedHailMaryCount = 1;"
                >
                  <option
                    v-for="(step, sIdx) in guidedSteps"
                    :key="step.stepIndex"
                    :value="sIdx"
                  >
                    {{ step.stepIndex }}. {{ step.title }} ({{ step.part }})
                  </option>
                </select>
              </div>

              <button
                type="button"
                class="guided-nav-btn next-btn"
                @click="guidedStepIndex === 32 ? exitGuidedMode() : nextGuidedStep()"
              >
                <span>{{ guidedStepIndex === 32 ? 'Finish Rosary' : 'Next Prayer' }}</span>
                <svg v-if="guidedStepIndex < 32" viewBox="0 0 20 20" fill="currentColor" class="nav-arrow" aria-hidden="true">
                  <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
                <span v-else class="check-mark" aria-hidden="true">✓</span>
              </button>
            </footer>
          </div>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

