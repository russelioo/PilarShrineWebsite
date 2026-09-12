<script setup>
import { onMounted, ref } from 'vue'

const defaultCategories = [
  {
    title: 'Daily Mass',
    tag: 'Monday to Saturday',
    icon: '◷',
    items: [
      { id: 4, day: 'Monday & Wednesday', time: '5:00 PM — Holy Mass', highlighted: false, live: false },
      { id: 5, day: 'Tuesday, Thursday & Friday', time: '6:00 AM — Holy Mass', highlighted: false, live: false },
      { id: 6, day: 'Saturday', time: '6:00 AM — Holy Mass', highlighted: false, live: false },
      { id: 7, day: 'Anticipated Mass (Saturday)', time: '5:00 PM — Anticipated Sunday Mass', highlighted: true, live: false },
    ],
  },
  {
    title: 'Sunday Mass',
    tag: "Lord's Day Celebrations",
    icon: '✝',
    items: [
      { id: 8, day: 'Early Morning', time: '5:00 AM — Holy Mass', highlighted: false, live: false },
      { id: 1, day: 'Morning (Live)', time: '7:30 AM — Holy Mass', highlighted: false, live: true },
      { id: 9, day: 'Afternoon (Live)', time: '5:00 PM — Holy Mass', highlighted: false, live: true },
    ],
  },
  {
    title: 'Sacrament of Reconciliation',
    tag: 'Confession & Spiritual Healing',
    icon: '✦',
    items: [
      { id: 10, day: 'Every First Thursday of the Month', time: '5:00 PM — Confession', highlighted: false, live: false },
    ],
  },
  {
    title: 'Monthly Devotion to Our Lady of the Pillar',
    tag: 'Patronal Devotional Day',
    icon: '♛',
    items: [
      { id: 11, day: 'Every 12th of the Month', time: '5:00 PM — Holy Mass', highlighted: false, live: false },
      { id: 12, day: 'Procession', time: '6:00 PM — Marian Procession', highlighted: false, live: false },
    ],
  },
  {
    title: 'Special Liturgical Activities',
    tag: 'Monthly Observances & Chapels',
    icon: '▦',
    items: [
      { id: 13, day: 'Every First Tuesday', time: '6:00 AM — Healing Mass', highlighted: false, live: false },
      { id: 14, day: 'Every First Monday', time: '6:00 AM — Misa sa Campo Santo', highlighted: false, live: false },
      { id: 15, day: 'First Saturday', time: '6:00 AM — Mass at Our Lady of Fatima Chapel (Banuyo)', highlighted: false, live: false },
      { id: 16, day: 'Every First Friday', time: 'Holy Hour after Holy Mass', highlighted: false, live: false },
    ],
  },
]

const categories = ref(defaultCategories)
const isLoading = ref(true)

const fetchSchedules = async () => {
  try {
    const res = await fetch('/api/mass-schedules', {
      headers: { 'Accept': 'application/json' },
    })
    if (res.ok) {
      const data = await res.json()
      if (data.categories && Array.isArray(data.categories) && data.categories.length > 0) {
        // Only override if valid categorized items are returned
        const validCategories = data.categories.filter(c => c.items && c.items.length > 0)
        if (validCategories.length > 0) {
          categories.value = validCategories
        }
      }
    }
  } catch (err) {
    console.warn('Could not load live schedules, using cached defaults:', err)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchSchedules()
})
</script>

<template>
  <div class="schedule-page">
    <div class="page-width schedule-content-wrap">
      <!-- Notice / Important Information Banner -->
      <div class="schedule-notice-banner" role="note">
        <span class="notice-icon" aria-hidden="true">ℹ</span>
        <div class="notice-text">
          <strong>Liturgical Observance &amp; Livestream Notice</strong>
          <p>Mass schedules may change on holy days of obligation, solemnities, and special parish occasions. Sunday Holy Masses at <b>7:30 AM</b> and <b>5:00 PM</b> are broadcast live on our official Facebook page.</p>
        </div>
      </div>

      <!-- Two-Column Schedule Layout -->
      <div class="schedule-layout">
        <!-- Left Column: Schedule Cards -->
        <div class="schedule-list">
          <article
            v-for="card in categories"
            :key="card.title"
            class="schedule-card"
          >
            <div class="card-header-row">
              <span class="card-icon" aria-hidden="true">{{ card.icon || '✝' }}</span>
              <div>
                <h2>{{ card.title }}</h2>
                <span class="card-tag">{{ card.tag }}</span>
              </div>
            </div>
            <div class="schedule-details-list">
              <div
                v-for="item in card.items"
                :key="item.id || item.day + item.time"
                class="schedule-item"
                :class="{ highlighted: item.highlighted }"
              >
                <span class="item-day">{{ item.day }}</span>
                <span class="item-time">
                  {{ item.time }}
                  <span v-if="item.live" class="live-badge">FB Live</span>
                </span>
              </div>
            </div>
          </article>
        </div>
      </div>
    </div>
  </div>
</template>
