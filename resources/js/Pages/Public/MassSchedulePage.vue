<script setup>
import { computed, onMounted, ref } from 'vue'

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

const categoryThemes = {
  'Daily Mass': 'daily',
  'Sunday Mass': 'sunday',
  'Sacrament of Reconciliation': 'confession',
  'Monthly Devotion to Our Lady of the Pillar': 'devotion',
  'Special Liturgical Activities': 'special',
}

const scheduleCards = computed(() => categories.value.map((category, index) => ({
  ...category,
  headingId: `worship-heading-${index}`,
  theme: categoryThemes[category.title] || 'daily',
  items: category.items.map(item => {
    const value = String(item.time || item.time_display || '')
    const separator = value.indexOf(' — ')
    const time = separator < 0 ? value : value.slice(0, separator)

    return {
      ...item,
      timeLabel: time,
      serviceLabel: separator < 0 ? '' : value.slice(separator + 3),
      hasClockTime: /^\d{1,2}(?::\d{2})?\s*(?:AM|PM)(?:\s*[-–—]\s*\d{1,2}(?::\d{2})?\s*(?:AM|PM))?$/i.test(time),
    }
  }),
})))

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
    <div class="page-width worship-content">
      <aside class="worship-notice" role="note">
        <svg class="worship-notice-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <circle cx="12" cy="12" r="9" />
          <path d="M12 10v6M12 7v1" />
        </svg>
        <div>
          <strong>Before your visit</strong>
          <p>Mass schedules may change on holy days of obligation, solemnities, and special parish occasions.</p>
        </div>
        <a class="worship-notice-link" href="#/contact">Contact the Parish <span aria-hidden="true">↗</span></a>
      </aside>

      <div class="worship-grid" :aria-busy="isLoading">
        <article
          v-for="card in scheduleCards"
          :key="card.title"
          class="worship-card"
          :class="`worship-card--${card.theme}`"
          :aria-labelledby="card.headingId"
        >
          <header class="worship-card-header">
            <span class="worship-card-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <template v-if="card.theme === 'daily'">
                  <circle cx="12" cy="12" r="9" /><path d="M12 6v6l4 2" />
                </template>
                <template v-else-if="card.theme === 'sunday'">
                  <path d="M12 3v18M6 8h12" />
                </template>
                <template v-else-if="card.theme === 'confession'">
                  <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z" />
                  <path d="M12 9v7M9 12h6" />
                </template>
                <template v-else-if="card.theme === 'devotion'">
                  <path d="m3 6 4 4 5-7 5 7 4-4-3 12H6L3 6ZM6 21h12" />
                </template>
                <template v-else>
                  <rect x="3" y="5" width="18" height="16" rx="2" /><path d="M16 3v4M8 3v4M3 11h18M8 15h2M14 15h2" />
                </template>
              </svg>
            </span>
            <div>
              <span class="worship-card-tag">{{ card.tag }}</span>
              <h2 :id="card.headingId">{{ card.title }}</h2>
            </div>
          </header>

          <ul class="worship-rows">
            <li
              v-for="item in card.items"
              :key="item.id || item.day + item.time"
              class="worship-row"
              :class="{ 'worship-row--highlighted': item.highlighted, 'worship-row--note': !item.hasClockTime }"
            >
              <div class="worship-row-description">
                <span class="worship-day">{{ item.day }}</span>
                <span v-if="item.serviceLabel" class="worship-service">{{ item.serviceLabel }}</span>
              </div>
              <div class="worship-row-time">
                <strong :class="{ 'worship-time': item.hasClockTime, 'worship-time-note': !item.hasClockTime }">{{ item.timeLabel }}</strong>
                <span v-if="item.live" class="worship-live-badge">
                  <svg viewBox="0 0 20 20" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="2" y="4" width="11" height="12" rx="2" /><path d="m13 8 5-3v10l-5-3" /></svg>
                  Livestream
                </span>
              </div>
            </li>
          </ul>

          <a v-if="card.items.some(item => item.live)" class="worship-stream-link" href="https://www.facebook.com/PilarShrineSorsogon" target="_blank" rel="noopener noreferrer">
            Watch livestreamed Masses on Facebook <span aria-hidden="true">↗</span>
          </a>
        </article>
      </div>
    </div>
  </div>
</template>

<style scoped>
.worship-content {
  padding: 40px 0 72px;
}

.worship-notice {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 28px;
  padding: 20px 24px;
  border: 1px solid #dbe7f2;
  border-radius: 14px;
  background: #f4f8fc;
}

.worship-notice-icon {
  width: 24px;
  height: 24px;
  flex-shrink: 0;
  color: var(--blue);
}

.worship-notice strong {
  display: block;
  margin-bottom: 4px;
  color: var(--blue);
  font-size: 14px;
}

.worship-notice p {
  margin: 0;
  color: #52677e;
  font-size: 13px;
  line-height: 1.65;
}

.worship-notice-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
  margin-left: auto;
  color: var(--blue);
  font-size: 12px;
  font-weight: 700;
  text-decoration: none;
}

.worship-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 24px;
}

.worship-card {
  --card-heading: #0a377f;
  --card-text: #23384f;
  --card-muted: #64748b;
  --card-rule: #e7edf4;
  min-width: 0;
  padding: 28px;
  border: 1px solid #dfe7f0;
  border-top: 3px solid #d8aa3c;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 8px 28px rgba(22, 48, 88, 0.05);
}

.worship-card-header {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  margin-bottom: 22px;
}

.worship-card-icon {
  display: grid;
  place-items: center;
  flex-shrink: 0;
  width: 46px;
  height: 46px;
  border-radius: 13px;
  background: #edf4fb;
  color: var(--card-heading);
}

.worship-card-icon svg {
  width: 25px;
  height: 25px;
}

.worship-card-tag {
  display: block;
  margin: 0 0 6px;
  color: var(--card-muted);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.09em;
  line-height: 1.5;
  text-transform: uppercase;
}

.worship-card h2 {
  margin: 0;
  color: var(--card-heading);
  font-family: var(--font-heading);
  font-size: 21px;
  font-weight: 700;
  line-height: 1.3;
  letter-spacing: -0.02em;
}

.worship-rows {
  display: grid;
  gap: 0;
  margin: 0;
  padding: 0;
  list-style: none;
}

.worship-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  align-items: center;
  gap: 16px;
  min-width: 0;
  padding: 18px 0;
  border-top: 1px solid var(--card-rule);
}

.worship-row-description,
.worship-row-time {
  min-width: 0;
}

.worship-day {
  display: block;
  color: var(--card-text);
  font-size: 14px;
  font-weight: 600;
  line-height: 1.5;
}

.worship-service {
  display: block;
  margin-top: 4px;
  color: var(--card-muted);
  font-size: 12px;
  line-height: 1.5;
}

.worship-row-time {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 7px;
  text-align: right;
}

.worship-time {
  color: var(--card-heading);
  font-size: 23px;
  font-weight: 800;
  line-height: 1.2;
  font-variant-numeric: tabular-nums;
  overflow-wrap: anywhere;
}

.worship-row--highlighted {
  margin: 4px -12px 0;
  padding: 16px 12px;
  border: 1px solid #e5d4a6;
  border-radius: 10px;
  background: #fffaf0;
}

.worship-row--note {
  grid-template-columns: minmax(0, 1fr);
  gap: 6px;
}

.worship-row--note .worship-row-time {
  align-items: flex-start;
  text-align: left;
}

.worship-time-note {
  color: var(--card-muted);
  font-size: 13px;
  font-weight: 500;
  line-height: 1.6;
  overflow-wrap: anywhere;
}

.worship-live-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 7px;
  border-radius: 5px;
  background: #e8f1ff;
  color: #164b8b;
  font-size: 10px;
  font-weight: 700;
  line-height: 1.4;
}

.worship-card--sunday {
  --card-heading: #fff;
  --card-text: #f2f6fd;
  --card-muted: #c8d8ed;
  --card-rule: rgba(255, 255, 255, 0.18);
  background: linear-gradient(140deg, #082e63, #0a3c80);
  border-color: #082e63;
  border-top-color: #d8aa3c;
}

.worship-card--sunday .worship-card-icon {
  background: rgba(255, 255, 255, 0.12);
  color: #f3d68b;
}

.worship-card--sunday .worship-row--highlighted {
  background: rgba(255, 255, 255, 0.08);
  border-color: rgba(243, 214, 139, 0.5);
}

.worship-stream-link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 8px;
  padding-top: 18px;
  border-top: 1px solid var(--card-rule);
  color: var(--card-heading);
  font-size: 12px;
  font-weight: 600;
  line-height: 1.5;
  text-decoration: none;
}

.worship-stream-link:hover,
.worship-notice-link:hover {
  text-decoration: underline;
}

.worship-stream-link:focus-visible,
.worship-notice-link:focus-visible {
  outline: 2px solid var(--gold);
  outline-offset: 4px;
}

.worship-card--confession .worship-card-icon {
  background: #eef6f1;
  color: #347054;
}

.worship-card--devotion .worship-card-icon {
  background: #fff6df;
  color: #947016;
}

.worship-card--special {
  grid-column: 1 / -1;
}

.worship-card--special .worship-rows {
  grid-template-columns: repeat(2, minmax(0, 1fr));
  column-gap: 32px;
}

@media (max-width: 800px) {
  .worship-content {
    padding: 28px 0 48px;
  }

  .worship-notice {
    flex-wrap: wrap;
    align-items: flex-start;
    padding: 18px;
  }

  .worship-notice > div {
    flex: 1;
    min-width: 0;
  }

  .worship-notice-link {
    flex-basis: calc(100% - 40px);
    margin-left: 40px;
  }

  .worship-grid {
    grid-template-columns: minmax(0, 1fr);
    gap: 18px;
  }

  .worship-card {
    padding: 22px;
  }
}

@media (max-width: 540px) {
  .worship-card {
    padding: 20px;
  }

  .worship-card-header {
    gap: 12px;
    margin-bottom: 18px;
  }

  .worship-card h2 {
    font-size: 19px;
  }

  .worship-card-icon {
    width: 40px;
    height: 40px;
  }

  .worship-row {
    gap: 12px;
  }

  .worship-day {
    font-size: 13px;
  }

  .worship-time {
    font-size: 20px;
  }

  .worship-card--special .worship-rows {
    grid-template-columns: minmax(0, 1fr);
  }
}
</style>
