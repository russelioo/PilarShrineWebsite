<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'

const commissions = ref([])
const ministries = ref([])
const isLoading = ref(true)
const fetchError = ref(null)

const searchQuery = ref('')
const selectedCommissionSlug = ref('all')
const activeModalMinistry = ref(null)

// Fetch live database-driven commissions and ministries
const fetchMinistries = async () => {
  isLoading.value = true
  fetchError.value = null
  try {
    const res = await fetch('/api/ministries', {
      headers: { Accept: 'application/json' },
    })
    if (!res.ok) {
      throw new Error(`Failed to load directory (Status ${res.status})`)
    }
    const data = await res.json()
    commissions.value = Array.isArray(data.commissions) ? data.commissions : []
    ministries.value = Array.isArray(data.ministries) ? data.ministries : []
  } catch (err) {
    console.error('Error fetching public ministries:', err)
    fetchError.value = 'Unable to load parish ministries at this moment. Please check your internet connection and try again.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchMinistries()
  window.addEventListener('keydown', handleGlobalKeyDown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeyDown)
  document.body.style.overflow = ''
})

const handleGlobalKeyDown = (e) => {
  if (e.key === 'Escape' && activeModalMinistry.value) {
    closeModal()
  }
}

const openModal = (ministry) => {
  activeModalMinistry.value = ministry
  document.body.style.overflow = 'hidden'
}

const closeModal = () => {
  activeModalMinistry.value = null
  document.body.style.overflow = ''
}

// Filtered ministries based on selected commission and search query
const filteredMinistries = computed(() => {
  let list = ministries.value

  // Commission filter
  if (selectedCommissionSlug.value !== 'all') {
    list = list.filter((m) => {
      const commSlug = m.commission?.slug?.toLowerCase()
      const commCode = m.commission?.code?.toLowerCase()
      const filter = selectedCommissionSlug.value.toLowerCase()
      return commSlug === filter || commCode === filter || m.category?.toLowerCase() === filter
    })
  }

  // Search query filter (matches ministry name, description, about, coordinator, or commission)
  const q = searchQuery.value.trim().toLowerCase()
  if (q) {
    list = list.filter((m) => {
      const nameMatch = m.name?.toLowerCase().includes(q)
      const descMatch = m.description?.toLowerCase().includes(q)
      const aboutMatch = m.about?.toLowerCase().includes(q)
      const coordMatch = m.coordinator_name?.toLowerCase().includes(q)
      const locationMatch = m.meeting_location?.toLowerCase().includes(q)
      const commMatch = m.commission?.name?.toLowerCase().includes(q)
      return nameMatch || descMatch || aboutMatch || coordMatch || locationMatch || commMatch
    })
  }

  return list
})

const totalActiveCount = computed(() => ministries.value.length)
const filteredCount = computed(() => filteredMinistries.value.length)

// Map commission icon tokens to unicode symbols
const resolveIcon = (icon) => {
  const map = {
    cross: '✝',
    'book-open': '📖',
    'heart-handshake': '♡',
    building: '🏛',
    users: '👥',
    home: '👨‍👩‍👧',
    spark: '✦',
    shield: '🛡',
    radio: '📻',
  }
  return map[icon] || icon || '✝'
}

// Commission directory holders when 0 individual ministries exist in database
const displayedHolders = computed(() => {
  let list = commissions.value

  if (selectedCommissionSlug.value !== 'all') {
    list = list.filter((c) => {
      const slugMatch = c.slug?.toLowerCase() === selectedCommissionSlug.value.toLowerCase()
      const codeMatch = c.code?.toLowerCase() === selectedCommissionSlug.value.toLowerCase()
      return slugMatch || codeMatch
    })
  }

  const q = searchQuery.value.trim().toLowerCase()
  if (q) {
    list = list.filter((c) => {
      const nameMatch = c.name?.toLowerCase().includes(q)
      const descMatch = c.description?.toLowerCase().includes(q)
      const codeMatch = c.code?.toLowerCase().includes(q)
      return nameMatch || descMatch || codeMatch
    })
  }

  return list
})

const openHolderModal = (c) => {
  activeModalMinistry.value = {
    id: `holder-${c.id || c.slug}`,
    name: c.name,
    category: c.name.replace(/^Commission on\s+/i, ''),
    commission: c,
    icon: resolveIcon(c.icon),
    description: c.description || 'Oversees parish ministries, apostolates, and mandated lay organizations under this pastoral jurisdiction.',
    about: 'Individual parish apostolates, liturgical guilds, and mandated lay associations under this commission are currently undergoing official registration in the parish database. Once registered by the Parish Pastoral Council and Commission leadership, their complete schedules, meeting venues, and coordinators will be published here.',
    meeting_schedule: 'To be announced upon completion of parish registration',
    meeting_location: 'Diocesan Shrine and Parish of Our Lady of the Pillar',
    coordinator_name: 'Parish Office / Commission Secretariat',
    coordinator_email: null,
    coordinator_phone: null,
    is_accepting_members: false,
    is_holder: true,
  }
  document.body.style.overflow = 'hidden'
}

const resetFilters = () => {
  searchQuery.value = ''
  selectedCommissionSlug.value = 'all'
}
</script>

<template>
  <div class="ministries-directory-page">
    <div class="page-width ministries-content-wrap">
      <!-- 1. Search & Dynamic Commission Filtering Toolbar -->
      <section class="directory-controls-section" aria-label="Ministry Search and Commission Filters">
        <!-- Search Input Bar -->
        <div class="search-input-wrap">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
          </svg>
          <input
            v-model="searchQuery"
            type="search"
            class="search-input"
            placeholder="Search ministries by name, commission, keywords, coordinator, or venue..."
            aria-label="Search parish ministries"
          >
          <button
            v-if="searchQuery"
            type="button"
            class="clear-search-btn"
            title="Clear search"
            aria-label="Clear search input"
            @click="searchQuery = ''"
          >
            ✕
          </button>
        </div>

        <!-- Dynamic Commission Filter Tabs / Pills -->
        <div class="commission-filter-nav">
          <span class="filter-nav-label">Filter by Commission:</span>
          <div class="commission-pills-row" role="tablist" aria-label="Commission Filter Tabs">
            <button
              type="button"
              role="tab"
              class="commission-tab-pill"
              :class="{ active: selectedCommissionSlug === 'all' }"
              :aria-selected="selectedCommissionSlug === 'all'"
              @click="selectedCommissionSlug = 'all'"
            >
              <span>All Ministries</span>
              <span class="tab-count-badge">{{ totalActiveCount }}</span>
            </button>

            <button
              v-for="c in commissions"
              :key="c.slug || c.id"
              type="button"
              role="tab"
              class="commission-tab-pill"
              :class="{ active: selectedCommissionSlug === c.slug }"
              :aria-selected="selectedCommissionSlug === c.slug"
              @click="selectedCommissionSlug = c.slug"
            >
              <span>{{ c.name.replace(/^Commission on\s+/i, '') }}</span>
              <span class="tab-count-badge">{{ c.ministries_count }}</span>
            </button>
          </div>
        </div>

        <!-- Results Counter & Reset Action Bar -->
        <div class="results-meta-bar">
          <div class="counter-text">
            <span v-if="isLoading">Loading parish ministries...</span>
            <span v-else-if="totalActiveCount === 0">
              <span v-if="selectedCommissionSlug !== 'all' || searchQuery">
                Showing <strong>{{ displayedHolders.length }}</strong> of <strong>{{ commissions.length }}</strong> pastoral commission directories
              </span>
              <span v-else>
                Showing all <strong>{{ displayedHolders.length }}</strong> pastoral commission directories (awaiting individual ministry registration)
              </span>
            </span>
            <span v-else-if="searchQuery || selectedCommissionSlug !== 'all'">
              Showing <strong>{{ filteredCount }}</strong> of <strong>{{ totalActiveCount }}</strong> active ministries
            </span>
            <span v-else>
              Showing all <strong>{{ totalActiveCount }}</strong> active parish ministries
            </span>
          </div>

          <button
            v-if="searchQuery || selectedCommissionSlug !== 'all'"
            type="button"
            class="reset-filters-btn"
            @click="resetFilters"
          >
            Reset filters ✕
          </button>
        </div>
      </section>

      <!-- 2. Directory Main Content Area -->
      <main class="directory-main-area" aria-label="Ministries Directory List">
        <!-- Error Notice -->
        <div v-if="fetchError" class="fetch-error-box" role="alert">
          <span class="error-icon" aria-hidden="true">⚠️</span>
          <div class="error-msg">
            <strong>Could not load directory</strong>
            <p>{{ fetchError }}</p>
          </div>
          <button type="button" class="button secondary button-sm" @click="fetchMinistries">Try Again</button>
        </div>

        <!-- Loading Skeleton Grid -->
        <div v-else-if="isLoading" class="ministries-cards-grid" aria-busy="true">
          <article v-for="i in 6" :key="i" class="ministry-resource-card skeleton-card">
            <div class="skeleton-header">
              <div class="skeleton-pill"></div>
              <div class="skeleton-icon"></div>
            </div>
            <div class="skeleton-title"></div>
            <div class="skeleton-line"></div>
            <div class="skeleton-line short"></div>
            <div class="skeleton-footer"></div>
          </article>
        </div>

        <!-- When 0 registered ministries exist in DB: Display Commission Directory Card Holders -->
        <div v-else-if="totalActiveCount === 0">
          <!-- Empty State: Search/Filter matched 0 commission holders -->
          <div v-if="displayedHolders.length === 0" class="canonical-empty-card" role="status">
            <div class="empty-search-icon" aria-hidden="true">🔍</div>
            <h3 class="empty-title">No Commissions Found</h3>
            <p class="empty-subtitle">
              We could not find any pastoral commissions matching <strong v-if="searchQuery">"{{ searchQuery }}"</strong>.
            </p>
            <button type="button" class="button secondary button-sm" @click="resetFilters">
              Reset Search &amp; Filters
            </button>
          </div>

          <!-- Live Commission Card Holders Grid -->
          <div v-else class="ministries-cards-grid">
            <article
              v-for="c in displayedHolders"
              :key="c.id || c.slug"
              class="ministry-resource-card holder-card"
              tabindex="0"
              role="button"
              :aria-label="`View details for ${c.name}`"
              @click="openHolderModal(c)"
              @keydown.enter="openHolderModal(c)"
              @keydown.space.prevent="openHolderModal(c)"
            >
              <!-- Top Row: Commission Tag & Icon -->
              <div class="card-eyebrow-row">
                <span class="ministry-commission-tag">
                  {{ c.name.replace(/^Commission on\s+/i, '') }}
                </span>
                <span class="ministry-icon-badge" aria-hidden="true">{{ resolveIcon(c.icon) }}</span>
              </div>

              <!-- Title & Description from DB -->
              <h3 class="ministry-title">{{ c.name }}</h3>
              <p class="ministry-desc">
                {{ c.description || 'Oversees parish ministries, apostolates, and mandated lay organizations under this pastoral area.' }}
              </p>

              <!-- Key Logistics Placeholders (Without fabricated data) -->
              <div class="ministry-logistics-list">
                <div class="logistics-item">
                  <svg class="logistics-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                  </svg>
                  <span>Schedule: To be announced</span>
                </div>
                <div class="logistics-item">
                  <svg class="logistics-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                    <circle cx="12" cy="10" r="3" />
                  </svg>
                  <span>Venue: Parish Shrine / Pastoral Center</span>
                </div>
              </div>

              <!-- Bottom Footer Row: Status Pill & View Details Action -->
              <div class="ministry-card-footer">
                <span class="status-pill status-holder">
                  ◌ Awaiting Registration
                </span>
                <span class="view-details-action">
                  Inquire Details <span aria-hidden="true">&rarr;</span>
                </span>
              </div>
            </article>
          </div>
        </div>

        <!-- Empty State: Search/Filter yielded 0 matches -->
        <div v-else-if="filteredMinistries.length === 0" class="canonical-empty-card" role="status">
          <div class="empty-search-icon" aria-hidden="true">🔍</div>
          <h3 class="empty-title">No Ministries Found</h3>
          <p class="empty-subtitle">
            We could not find any active ministries matching <strong v-if="searchQuery">"{{ searchQuery }}"</strong>
            <span v-if="selectedCommissionSlug !== 'all'"> under the selected commission</span>.
          </p>
          <button type="button" class="button secondary button-sm" @click="resetFilters">
            View All Ministries
          </button>
        </div>

        <!-- Live Database-Driven Ministries Grid -->
        <div v-else class="ministries-cards-grid">
          <article
            v-for="m in filteredMinistries"
            :key="m.id || m.slug"
            class="ministry-resource-card"
            tabindex="0"
            role="button"
            :aria-label="`View details for ${m.name}`"
            @click="openModal(m)"
            @keydown.enter="openModal(m)"
            @keydown.space.prevent="openModal(m)"
          >
            <!-- Top Row: Commission Tag & Icon -->
            <div class="card-eyebrow-row">
              <span class="ministry-commission-tag">
                {{ m.commission ? m.commission.name.replace(/^Commission on\s+/i, '') : m.category }}
              </span>
              <span v-if="m.icon" class="ministry-icon-badge" aria-hidden="true">{{ m.icon }}</span>
            </div>

            <!-- Ministry Title & Description -->
            <h3 class="ministry-title">{{ m.name }}</h3>
            <p class="ministry-desc">{{ m.description }}</p>

            <!-- Key Logistics Badges (only rendered if actual data exists) -->
            <div v-if="m.meeting_schedule || m.meeting_location" class="ministry-logistics-list">
              <div v-if="m.meeting_schedule" class="logistics-item">
                <svg class="logistics-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <circle cx="12" cy="12" r="10" />
                  <polyline points="12 6 12 12 16 14" />
                </svg>
                <span>{{ m.meeting_schedule }}</span>
              </div>
              <div v-if="m.meeting_location" class="logistics-item">
                <svg class="logistics-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                  <circle cx="12" cy="10" r="3" />
                </svg>
                <span>{{ m.meeting_location }}</span>
              </div>
            </div>

            <!-- Bottom Footer Row: Status Pill & View Details Action -->
            <div class="ministry-card-footer">
              <span
                class="status-pill"
                :class="m.is_accepting_members ? 'status-open' : 'status-closed'"
              >
                {{ m.is_accepting_members ? '● Accepting Members' : 'Applications Closed' }}
              </span>

              <span class="view-details-action">
                View Details <span aria-hidden="true">&rarr;</span>
              </span>
            </div>
          </article>
        </div>
      </main>

      <!-- 3. Existing Parish CTA Banner -->
      <section class="home-cta-section" aria-label="Serve with the Parish">
        <div class="home-cta-banner">
          <div class="cta-copy-wrap">
            <span class="cta-eyebrow">Apostolates &amp; Pastoral Engagement</span>
            <h2 class="cta-heading">Serve with the Shrine of Our Lady of the Pillar</h2>
            <p class="cta-subtext">
              Looking to discern your pastoral vocation, participate in liturgical celebrations, or connect with our shrine parish community? All parishioners and pilgrims are warmly welcomed.
            </p>
          </div>
          <div class="cta-actions-wrap">
            <a class="button" href="#/schedule">Mass Schedule</a>
            <a class="button secondary" href="#/sacraments">Sacraments</a>
            <a class="button secondary" href="#/contact">Contact Us</a>
          </div>
        </div>
      </section>
    </div>

    <!-- 4. Accessible Ministry Detail Modal -->
    <transition name="modal-fade">
      <div
        v-if="activeModalMinistry"
        class="ministry-modal-backdrop"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="`modal-title-${activeModalMinistry.id}`"
        @click.self="closeModal"
      >
        <div class="ministry-modal-card">
          <!-- Modal Header -->
          <header class="modal-card-header">
            <div class="modal-header-meta">
              <span class="modal-commission-tag">
                {{ activeModalMinistry.commission ? activeModalMinistry.commission.name : activeModalMinistry.category }}
              </span>
              <h2 :id="`modal-title-${activeModalMinistry.id}`" class="modal-title">
                {{ activeModalMinistry.name }}
              </h2>
              <div class="gold-rule left small" aria-hidden="true">✣</div>
            </div>
            <button
              type="button"
              class="modal-close-btn"
              title="Close modal (Esc)"
              aria-label="Close details modal"
              @click="closeModal"
            >
              ✕
            </button>
          </header>

          <!-- Modal Scrollable Body -->
          <div class="modal-scroll-surface">
            <!-- Status Pill & Summary -->
            <div class="modal-status-banner">
              <span
                class="status-pill-lg"
                :class="activeModalMinistry.is_holder ? 'status-holder' : (activeModalMinistry.is_accepting_members ? 'status-open' : 'status-closed')"
              >
                {{ activeModalMinistry.is_holder ? '◌ Registration in Progress' : (activeModalMinistry.is_accepting_members ? '● Currently Accepting New Members' : 'Applications Currently Closed') }}
              </span>
              <p class="modal-lead-desc">{{ activeModalMinistry.description }}</p>
            </div>

            <!-- About / Apostolate Vision -->
            <div v-if="activeModalMinistry.about" class="modal-sub-section">
              <h3 class="modal-section-title">Vision &amp; Apostolate Mission</h3>
              <p class="modal-about-text">{{ activeModalMinistry.about }}</p>
            </div>

            <!-- Logistics & Leadership Grid -->
            <div
              v-if="activeModalMinistry.meeting_schedule || activeModalMinistry.meeting_location || activeModalMinistry.coordinator_name || activeModalMinistry.coordinator_email || activeModalMinistry.coordinator_phone"
              class="modal-logistics-grid"
            >
              <!-- Gathering Logistics -->
              <div v-if="activeModalMinistry.meeting_schedule || activeModalMinistry.meeting_location" class="modal-info-block">
                <h4 class="info-block-title">
                  <svg class="info-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                  </svg>
                  Gathering Logistics
                </h4>
                <div v-if="activeModalMinistry.meeting_schedule" class="info-data-row">
                  <span class="info-data-label">Schedule</span>
                  <strong class="info-data-value">{{ activeModalMinistry.meeting_schedule }}</strong>
                </div>
                <div v-if="activeModalMinistry.meeting_location" class="info-data-row">
                  <span class="info-data-label">Venue</span>
                  <strong class="info-data-value">{{ activeModalMinistry.meeting_location }}</strong>
                </div>
              </div>

              <!-- Leadership & Contact -->
              <div
                v-if="activeModalMinistry.coordinator_name || activeModalMinistry.coordinator_email || activeModalMinistry.coordinator_phone"
                class="modal-info-block"
              >
                <h4 class="info-block-title">
                  <svg class="info-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                  Leadership &amp; Contact
                </h4>
                <div v-if="activeModalMinistry.coordinator_name" class="info-data-row">
                  <span class="info-data-label">Coordinator</span>
                  <strong class="info-data-value">{{ activeModalMinistry.coordinator_name }}</strong>
                </div>
                <div v-if="activeModalMinistry.coordinator_email" class="info-data-row">
                  <span class="info-data-label">Email</span>
                  <span class="info-data-value">
                    <a :href="`mailto:${activeModalMinistry.coordinator_email}`" class="contact-anchor">
                      {{ activeModalMinistry.coordinator_email }}
                    </a>
                  </span>
                </div>
                <div v-if="activeModalMinistry.coordinator_phone" class="info-data-row">
                  <span class="info-data-label">Phone</span>
                  <span class="info-data-value">
                    <a :href="`tel:${activeModalMinistry.coordinator_phone}`" class="contact-anchor">
                      {{ activeModalMinistry.coordinator_phone }}
                    </a>
                  </span>
                </div>
              </div>
            </div>

            <!-- Core Activities Checklist -->
            <div v-if="activeModalMinistry.activities && activeModalMinistry.activities.length > 0" class="modal-sub-section">
              <h3 class="modal-section-title">Core Activities &amp; Responsibilities</h3>
              <ul class="modal-checklist">
                <li v-for="(act, idx) in activeModalMinistry.activities" :key="idx">
                  <span class="check-mark" aria-hidden="true">✓</span>
                  <span>{{ act }}</span>
                </li>
              </ul>
            </div>

            <!-- Requirements Checklist -->
            <div v-if="activeModalMinistry.requirements && activeModalMinistry.requirements.length > 0" class="modal-sub-section">
              <h3 class="modal-section-title">Requirements for Joining</h3>
              <ul class="modal-checklist">
                <li v-for="(req, idx) in activeModalMinistry.requirements" :key="idx">
                  <span class="check-mark gold" aria-hidden="true">✣</span>
                  <span>{{ req }}</span>
                </li>
              </ul>
            </div>

            <!-- How to Join Callout Box -->
            <div class="how-to-join-callout">
              <div class="callout-copy">
                <strong>{{ activeModalMinistry.is_holder ? 'Parish Pastoral Directory Registration' : 'How to Join this Ministry' }}</strong>
                <p>
                  <span v-if="activeModalMinistry.is_holder">
                    Parish ministries, liturgical guilds, and lay associations under this pastoral commission are currently undergoing official registration in the parish registry. To enroll an apostolate or inquire about volunteering, please contact the Parish Office.
                  </span>
                  <span v-else-if="activeModalMinistry.is_accepting_members">
                    Interested in becoming a member? You may visit the Parish Office during official office hours (Tuesday–Sunday) or talk to any coordinator after Sunday Holy Masses.
                  </span>
                  <span v-else>
                    Formal membership intake for this ministry is temporarily closed. You may inquire at the Parish Office for upcoming formation dates.
                  </span>
                </p>
              </div>
              <a
                v-if="activeModalMinistry.is_accepting_members || activeModalMinistry.is_holder"
                class="button button-sm"
                href="#/contact"
                @click="closeModal"
              >
                Inquire at Parish Office
              </a>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
/* ==========================================================================
   PARISH WEBPAGE CONSISTENCY: Shrine Ministries Directory
   Strictly adheres to existing Parish design system, tokens, and typography.
   ========================================================================== */

.ministries-directory-page {
  background: #ffffff;
  min-height: 520px;
}

.ministries-content-wrap {
  padding: 40px 0 70px;
  box-sizing: border-box;
}

/* 1. Directory Controls Section (Part of main content - DOES NOT FLOAT) */
.directory-controls-section {
  margin-bottom: 36px;
}

/* Search Bar */
.search-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid rgba(14, 50, 95, 0.16);
  box-shadow: 0 2px 8px rgba(14, 50, 95, 0.04);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  box-sizing: border-box;
}

.search-input-wrap:focus-within {
  border-color: var(--blue, #0a377f);
  box-shadow: 0 0 0 3px rgba(10, 55, 127, 0.12), 0 4px 12px rgba(14, 50, 95, 0.06);
}

.search-icon {
  position: absolute;
  left: 16px;
  width: 18px;
  height: 18px;
  color: var(--muted, #607086);
  pointer-events: none;
}

.search-input {
  width: 100%;
  height: 48px;
  padding: 0 44px 0 46px;
  border: none;
  background: transparent;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 14px;
  color: var(--ink, #17263a);
  outline: none;
  box-sizing: border-box;
}

.search-input::placeholder {
  color: #8392a5;
  font-size: 13.5px;
}

.clear-search-btn {
  position: absolute;
  right: 12px;
  width: 26px;
  height: 26px;
  border: none;
  background: rgba(14, 50, 95, 0.08);
  border-radius: 50%;
  color: var(--muted, #607086);
  font-size: 11px;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: all 0.18s ease;
}

.clear-search-btn:hover {
  background: rgba(14, 50, 95, 0.18);
  color: var(--ink, #17263a);
}

/* Commission Filter Navigation */
.commission-filter-nav {
  margin-top: 22px;
}

.filter-nav-label {
  display: block;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--muted, #607086);
  margin-bottom: 12px;
}

.commission-pills-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}

/* Filter Tab Pill - Exactly matches .devotional-filter-tab in modern.css */
.commission-tab-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 18px;
  border-radius: 9999px;
  background: #ffffff;
  border: 1px solid rgba(14, 50, 95, 0.16);
  color: #072a5a;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 6px rgba(14, 50, 95, 0.03);
  box-sizing: border-box;
  white-space: nowrap;
}

.commission-tab-pill:hover {
  background: #f4f8fc;
  border-color: rgba(10, 55, 127, 0.35);
  color: var(--blue, #0a377f);
}

.commission-tab-pill.active {
  background: var(--blue, #0a377f);
  color: #ffffff;
  border-color: var(--blue, #0a377f);
  box-shadow: 0 4px 14px rgba(10, 55, 127, 0.22);
}

.tab-count-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 10px;
  background: rgba(10, 55, 127, 0.09);
  color: var(--blue, #0a377f);
  line-height: 1;
}

.commission-tab-pill.active .tab-count-badge {
  background: rgba(255, 255, 255, 0.24);
  color: #ffffff;
}

/* Results Counter & Reset Action Bar */
.results-meta-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 18px;
  padding-top: 16px;
  border-top: 1px solid rgba(220, 228, 236, 0.6);
  font-family: Montserrat, Arial, sans-serif;
  font-size: 13.5px;
  color: var(--muted, #607086);
}

.counter-text strong {
  color: var(--blue, #0a377f);
  font-weight: 700;
}

.reset-filters-btn {
  border: none;
  background: none;
  color: var(--blue, #0a377f);
  font-family: Montserrat, Arial, sans-serif;
  font-size: 12.5px;
  font-weight: 700;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 4px;
  transition: background 0.15s ease, color 0.15s ease;
}

.reset-filters-btn:hover {
  background: #f0f6fc;
  text-decoration: underline;
}

/* 2. Directory Main Area & Empty State */
.directory-main-area {
  margin-bottom: 56px;
}

/* Canonical Parish Empty State Card */
.canonical-empty-card {
  text-align: center;
  background: #ffffff;
  border: 1px solid rgba(14, 50, 95, 0.1);
  border-radius: 16px;
  box-shadow: 0 8px 24px rgba(14, 50, 95, 0.05);
  padding: 60px 32px 50px;
  margin: 24px 0 40px;
  box-sizing: border-box;
}

.empty-star-symbol {
  font-size: 36px;
  color: var(--gold, #d8aa3c);
  margin-bottom: 12px;
  user-select: none;
}

.empty-search-icon {
  font-size: 34px;
  margin-bottom: 12px;
}

.empty-title {
  font-family: 'Libre Baskerville', Georgia, serif;
  font-size: clamp(22px, 2.4vw, 28px);
  font-weight: 700;
  color: var(--blue, #0a377f);
  margin: 0 0 10px;
  line-height: 1.3;
}

.empty-subtitle {
  font-family: Montserrat, Arial, sans-serif;
  font-size: 14.5px;
  color: var(--muted, #607086);
  max-width: 520px;
  margin: 0 auto;
  line-height: 1.65;
}

.empty-notice-banner {
  margin-top: 36px !important;
  margin-bottom: 0 !important;
  text-align: left;
}

/* Cards Grid */
.ministries-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 24px;
  margin-top: 24px;
}

/* Ministry Card - Matches .devotional-resource-card in modern.css */
.ministry-resource-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid rgba(14, 50, 95, 0.1);
  box-shadow: 0 8px 24px rgba(14, 50, 95, 0.05);
  padding: 24px 22px 20px;
  display: flex;
  flex-direction: column;
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
  box-sizing: border-box;
  cursor: pointer;
  outline: none;
}

.ministry-resource-card:hover,
.ministry-resource-card:focus-visible {
  transform: translateY(-4px);
  box-shadow: 0 16px 36px rgba(14, 50, 95, 0.12);
  border-color: rgba(216, 170, 60, 0.55);
}

.card-eyebrow-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.ministry-commission-tag {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 6px;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  background: rgba(10, 55, 127, 0.08);
  color: var(--blue, #0a377f);
  border: 1px solid rgba(10, 55, 127, 0.15);
}

.ministry-icon-badge {
  font-size: 16px;
  color: var(--gold, #d8aa3c);
}

.ministry-title {
  font-family: 'Libre Baskerville', Georgia, serif;
  font-size: 19px;
  font-weight: 700;
  color: #072a5a;
  line-height: 1.35;
  margin: 0 0 8px;
}

.ministry-desc {
  font-family: Montserrat, Arial, sans-serif;
  font-size: 13px;
  color: #475569;
  line-height: 1.6;
  margin: 0 0 16px;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Logistics List */
.ministry-logistics-list {
  display: flex;
  flex-direction: column;
  gap: 7px;
  margin-bottom: 18px;
  padding: 10px 12px;
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #edf2f7;
}

.logistics-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 12px;
  color: #55687d;
  line-height: 1.4;
}

.logistics-svg {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
  color: var(--blue, #0a377f);
}

/* Card Footer */
.ministry-card-footer {
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 11px;
  font-weight: 600;
  padding: 3px 9px;
  border-radius: 20px;
}

.status-pill.status-open {
  background: #e6f7ef;
  color: #0d6832;
  border: 1px solid #c2eed5;
}

.status-pill.status-closed {
  background: #f1f5f9;
  color: #64748b;
  border: 1px solid #e2e8f0;
}

.status-pill.status-holder {
  background: #f8fafc;
  color: #475569;
  border: 1px solid #cbd5e1;
}

.holder-card {
  border-style: dashed;
  border-color: rgba(10, 55, 127, 0.22);
}

.holder-card:hover {
  border-style: solid;
  border-color: var(--gold, #d8aa3c);
  transform: translateY(-4px);
  box-shadow: 0 12px 28px rgba(10, 55, 127, 0.08);
}

.view-details-action {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 12px;
  font-weight: 700;
  color: var(--blue, #0a377f);
  transition: gap 0.15s ease;
}

.ministry-resource-card:hover .view-details-action {
  gap: 8px;
}

/* Skeletons */
.skeleton-card {
  pointer-events: none;
  min-height: 240px;
}

.skeleton-header,
.skeleton-title,
.skeleton-line,
.skeleton-footer {
  background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
  background-size: 200% 100%;
  animation: skeleton-shimmer 1.5s infinite;
  border-radius: 6px;
}

.skeleton-header {
  height: 20px;
  width: 40%;
  margin-bottom: 16px;
}

.skeleton-title {
  height: 24px;
  width: 80%;
  margin-bottom: 12px;
}

.skeleton-line {
  height: 14px;
  width: 100%;
  margin-bottom: 8px;
}

.skeleton-line.short {
  width: 60%;
}

.skeleton-footer {
  height: 20px;
  width: 50%;
  margin-top: auto;
}

@keyframes skeleton-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Error Box */
.fetch-error-box {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: #fff5f5;
  border: 1px solid #fed7d7;
  border-left: 4px solid #e53e3e;
  border-radius: 12px;
  margin: 20px 0;
}

.error-msg strong {
  display: block;
  color: #9b2c2c;
  font-size: 14px;
  margin-bottom: 4px;
}

.error-msg p {
  margin: 0;
  color: #742a2a;
  font-size: 13px;
}

.button-sm {
  min-height: 38px !important;
  padding: 8px 18px !important;
  font-size: 11px !important;
}

/* 4. Accessible Ministry Detail Modal */
.ministry-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(5, 23, 44, 0.72);
  backdrop-filter: blur(4px);
  z-index: 1000;
  display: grid;
  place-items: center;
  padding: 24px;
  overflow-y: auto;
}

.ministry-modal-card {
  position: relative;
  background: #ffffff;
  width: min(680px, 100%);
  max-height: 88vh;
  border-radius: 20px;
  box-shadow: 0 24px 60px rgba(5, 23, 44, 0.35);
  border: 1px solid rgba(14, 50, 95, 0.12);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-card-header {
  padding: 28px 28px 18px;
  border-bottom: 1px solid #edf2f7;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  background: #fafcff;
}

.modal-commission-tag {
  display: inline-block;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--blue, #0a377f);
  margin-bottom: 6px;
}

.modal-title {
  font-family: 'Libre Baskerville', Georgia, serif;
  font-size: 22px;
  font-weight: 700;
  color: #072a5a;
  margin: 0 0 10px;
  line-height: 1.3;
}

.gold-rule.small {
  margin: 8px 0 0 !important;
}

.gold-rule.small:before,
.gold-rule.small:after {
  width: 45px !important;
}

.modal-close-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #64748b;
  font-size: 14px;
  display: grid;
  place-items: center;
  cursor: pointer;
  flex-shrink: 0;
  transition: all 0.18s ease;
}

.modal-close-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #cbd5e1;
}

.modal-scroll-surface {
  padding: 24px 28px 32px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 22px;
}

.modal-status-banner {
  padding-bottom: 18px;
  border-bottom: 1px solid #edf2f7;
}

.status-pill-lg {
  display: inline-flex;
  align-items: center;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 11.5px;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 20px;
  margin-bottom: 12px;
}

.status-pill-lg.status-open {
  background: #e6f7ef;
  color: #0d6832;
  border: 1px solid #c2eed5;
}

.status-pill-lg.status-closed {
  background: #f1f5f9;
  color: #64748b;
  border: 1px solid #e2e8f0;
}

.status-pill-lg.status-holder {
  background: #f8fafc;
  color: #334155;
  border: 1px solid #cbd5e1;
}

.modal-lead-desc {
  font-family: Montserrat, Arial, sans-serif;
  font-size: 14.5px;
  line-height: 1.7;
  color: #334155;
  margin: 0;
}

.modal-section-title {
  font-family: 'Libre Baskerville', Georgia, serif;
  font-size: 16px;
  font-weight: 700;
  color: var(--blue, #0a377f);
  margin: 0 0 10px;
}

.modal-about-text {
  font-family: Montserrat, Arial, sans-serif;
  font-size: 13.5px;
  line-height: 1.7;
  color: #475569;
  margin: 0;
}

/* Logistics & Leadership 2-Column Grid inside Modal */
.modal-logistics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
}

.modal-info-block {
  padding: 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}

.info-block-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 12.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--blue, #0a377f);
  margin: 0 0 12px;
}

.info-svg {
  width: 15px;
  height: 15px;
  color: var(--gold, #d8aa3c);
}

.info-data-row {
  display: flex;
  flex-direction: column;
  margin-bottom: 8px;
}

.info-data-row:last-child {
  margin-bottom: 0;
}

.info-data-label {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #64748b;
  margin-bottom: 2px;
}

.info-data-value {
  font-size: 13px;
  color: #1e293b;
  line-height: 1.45;
}

.contact-anchor {
  color: var(--blue, #0a377f);
  font-weight: 600;
  text-decoration: underline;
}

/* Checklists */
.modal-checklist {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.modal-checklist li {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 13px;
  color: #334155;
  line-height: 1.55;
}

.check-mark {
  color: #0d6832;
  font-weight: 700;
  flex-shrink: 0;
}

.check-mark.gold {
  color: var(--gold, #d8aa3c);
}

/* How to Join Callout */
.how-to-join-callout {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  padding: 18px 20px;
  border-radius: 12px;
  background: #f0f6fc;
  border: 1px solid #c9dcf0;
  border-left: 4px solid var(--blue, #0a377f);
}

.callout-copy strong {
  display: block;
  font-family: Montserrat, Arial, sans-serif;
  font-size: 13px;
  font-weight: 700;
  color: var(--blue, #0a377f);
  margin-bottom: 4px;
}

.callout-copy p {
  font-family: Montserrat, Arial, sans-serif;
  font-size: 12.5px;
  color: #475569;
  line-height: 1.55;
  margin: 0;
}

/* Transitions */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.22s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

@media (max-width: 768px) {
  .how-to-join-callout {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
