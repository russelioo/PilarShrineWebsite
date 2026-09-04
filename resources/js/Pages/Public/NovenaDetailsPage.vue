<script setup>
import { ref, computed, nextTick } from 'vue'
import {
  gaweNinPagsolsol,
  novenaMilestones,
  gozos,
  gozosResponse,
  novenaDays,
} from '../../data/novenaPrayers'

const activeDayIndex = ref(0)
const readerRef = ref(null)

const activeDay = computed(() => novenaDays[activeDayIndex.value])

const selectDay = (index) => {
  activeDayIndex.value = index
  nextTick(() => {
    if (readerRef.value) {
      readerRef.value.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
    }
  })
}

const prevDay = () => {
  if (activeDayIndex.value > 0) {
    selectDay(activeDayIndex.value - 1)
  }
}

const nextDay = () => {
  if (activeDayIndex.value < novenaDays.length - 1) {
    selectDay(activeDayIndex.value + 1)
  }
}
</script>

<template>
  <div class="novena-details-page">
    <div class="page-width">
      <!-- Back to Novenas Navigation Action -->
      <div class="novena-back-nav">
        <a href="#/novenas" class="button secondary novena-back-btn">
          <svg viewBox="0 0 20 20" fill="currentColor" class="back-arrow-icon" aria-hidden="true">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
          </svg>
          <span>Back to Novenas</span>
        </a>
      </div>

      <!-- 1. Novena Milestones Bar (Immediately below hero) -->
      <section class="novena-milestone-bar" aria-label="Novena Key Dates and Milestones">
        <div
          v-for="(milestone, idx) in novenaMilestones"
          :key="idx"
          class="novena-milestone-card"
          :class="{ 'novena-milestone-card-feast': milestone.isFeast }"
        >
          <span class="novena-milestone-eyebrow">{{ milestone.eyebrow }}</span>
          <strong class="novena-milestone-date">{{ milestone.date }}</strong>
          <p class="novena-milestone-desc">{{ milestone.description }}</p>
        </div>
      </section>

      <!-- 2. Nine-Day Selector & Progress Section -->
      <section class="novena-experience-section" aria-label="Daily Novena Reading Experience">
        <!-- Progress Indicator -->
        <div class="novena-progress-card">
          <div class="novena-progress-meta">
            <span class="progress-label">DAY {{ activeDay.dayNumber }} OF 9</span>
            <span class="progress-date">{{ activeDay.fullDate }} · {{ activeDay.dayTitle }}</span>
          </div>
          <div
            class="progress-track"
            role="progressbar"
            :aria-valuenow="activeDay.dayNumber"
            aria-valuemin="1"
            aria-valuemax="9"
            :aria-valuetext="`Day ${activeDay.dayNumber} of 9`"
          >
            <div
              class="progress-fill"
              :style="{ width: `${(activeDay.dayNumber / 9) * 100}%` }"
            ></div>
          </div>
        </div>

        <!-- 9-Day Navigation Bar -->
        <nav class="novena-day-nav-wrapper" aria-label="Select Novena Day">
          <div class="novena-day-nav-track" role="tablist">
            <button
              v-for="(day, index) in novenaDays"
              :key="day.dayNumber"
              type="button"
              role="tab"
              :aria-selected="activeDayIndex === index"
              :class="{ active: activeDayIndex === index }"
              class="day-nav-btn"
              @click="selectDay(index)"
            >
              <span class="day-nav-num">Day {{ day.dayNumber }}</span>
              <strong class="day-nav-date">{{ day.shortDate }}</strong>
            </button>
          </div>
        </nav>

        <!-- 3. Focused Devotional Prayer Reader Surface (Max 880-920px centered) -->
        <article ref="readerRef" class="novena-prayer-reader" aria-labelledby="current-prayer-title">
          <!-- Preparatory Prayer Accordion (Gawe nin Pagsolsol) -->
          <details class="devotional-accordion prep-accordion" open>
            <summary class="accordion-summary">
              <div class="summary-meta">
                <span class="summary-eyebrow">PRAYER BEFORE EACH DAY</span>
                <strong class="summary-title">Gawe nin Pagsolsol (Act of Contrition)</strong>
              </div>
              <span class="accordion-icon" aria-hidden="true"></span>
            </summary>
            <div class="accordion-content">
              <p class="prayer-prose">{{ gaweNinPagsolsol }}</p>
            </div>
          </details>

          <!-- Current Day Header & Reading -->
          <div class="current-prayer-header">
            <div class="current-day-badge">
              <span class="day-badge-dot" aria-hidden="true"></span>
              DAY {{ activeDay.dayNumber }} · {{ activeDay.fullDate.toUpperCase() }}
            </div>
            <h2 id="current-prayer-title" class="current-prayer-title">{{ activeDay.dayTitle }}</h2>
            <p class="current-prayer-subtitle">{{ activeDay.subtitle }}</p>
            <div class="prayer-instruction-box">
              <svg class="instruction-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
              </svg>
              <span>{{ activeDay.instruction }}</span>
            </div>
          </div>

          <!-- Main Prayer Text Body -->
          <div class="current-prayer-body">
            <div class="section-label-wrapper">
              <span class="prayer-section-tag">Pamibi</span>
              <div class="prayer-section-line" aria-hidden="true"></div>
            </div>

            <div class="prayer-prose-container">
              <p
                v-for="(paragraph, pIdx) in activeDay.paragraphs"
                :key="pIdx"
                class="prayer-prose"
              >
                {{ paragraph }}
              </p>
            </div>

            <div class="prayer-closing-box">
              <p class="prayer-closing-text">{{ activeDay.closingInstruction }}</p>
            </div>
          </div>

          <!-- Day Navigation Controls (Previous Day / Next Day only — NO BACK BUTTON) -->
          <div class="novena-day-pagination" role="navigation" aria-label="Novena Day Navigation">
            <button
              type="button"
              class="pagination-btn pagination-prev"
              :disabled="activeDayIndex === 0"
              @click="prevDay"
            >
              <svg viewBox="0 0 20 20" fill="currentColor" class="nav-arrow" aria-hidden="true">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
              <span>Previous Day</span>
            </button>

            <span class="pagination-indicator">
              Day {{ activeDay.dayNumber }} of 9
            </span>

            <button
              type="button"
              class="pagination-btn pagination-next"
              :disabled="activeDayIndex === 8"
              @click="nextDay"
            >
              <span>Next Day</span>
              <svg viewBox="0 0 20 20" fill="currentColor" class="nav-arrow" aria-hidden="true">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>

          <!-- Concluding Gozos Devotional Accordion -->
          <details class="devotional-accordion gozos-accordion">
            <summary class="accordion-summary">
              <div class="summary-meta">
                <span class="summary-eyebrow">PRAYER AFTER EACH DAY</span>
                <strong class="summary-title">Gozos to Nuestra Señora del Pilar</strong>
              </div>
              <span class="accordion-icon" aria-hidden="true"></span>
            </summary>
            <div class="accordion-content">
              <div class="gozos-antiphon-box">
                <p class="gozos-antiphon-call">O Birhen na mamuraway, Reyna nin mga linalang!</p>
                <p class="gozos-antiphon-resp"><strong>{{ gozosResponse }}</strong></p>
              </div>

              <div class="gozos-stanzas-grid">
                <div
                  v-for="(stanza, sIdx) in gozos"
                  :key="sIdx"
                  class="gozos-stanza-item"
                >
                  <span class="stanza-number">{{ sIdx + 1 }}</span>
                  <p class="stanza-lines">{{ stanza }}</p>
                  <strong class="stanza-response">{{ gozosResponse }}</strong>
                </div>
              </div>
            </div>
          </details>
        </article>
      </section>

      <!-- 4. Solemnity Feast Day Callout (At the bottom) -->
      <aside class="novena-feast-banner" aria-label="Patronal Feast Day Announcement">
        <div class="feast-banner-content">
          <span class="feast-banner-eyebrow">AFTER THE NINE-DAY NOVENA</span>
          <h2 class="feast-banner-title">Feast of Our Lady of the Pillar</h2>
          <div class="feast-banner-date-badge">
            <svg viewBox="0 0 20 20" fill="currentColor" class="calendar-icon" aria-hidden="true">
              <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
            </svg>
            <span>October 12 · Solemn Pontifical Masses</span>
          </div>
          <p class="feast-banner-text">
            Join the entire parish community, visiting devotees, and pilgrims as we honor Nuestra Señora del Pilar, our beloved patroness, in prayer, Eucharistic celebrations, and joyful thanksgiving.
          </p>
          <div class="feast-banner-action">
            <a href="#/schedule" class="btn-feast-schedule">
              View Feast Mass Schedule &rarr;
            </a>
          </div>
        </div>
        <div class="feast-banner-watermark" aria-hidden="true">12</div>
      </aside>
    </div>
  </div>
</template>
