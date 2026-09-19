<script setup>
import { computed, ref } from 'vue'

import { sacramentOptions } from '../../data/sacraments.js'

const selectedSacramentIndex = ref(0)
const selectedSacrament = computed(() => sacramentOptions[selectedSacramentIndex.value])
</script>

<template>
  <div class="sacraments-page">
    <div class="page-width sacraments-content-wrap">
      <!-- Pastoral Guidance & Scheduling Banner -->
      <div class="sacraments-notice-banner" role="note">
        <span class="notice-icon" aria-hidden="true">ℹ</span>
        <div class="notice-text">
          <strong>Pastoral Guidance &amp; Advance Scheduling</strong>
          <p>
            Sacraments are sacred encounters with God and the Christian community. Parishioners and couples are encouraged to coordinate with the Parish Office early to fulfill document verification, canonical interviews, and sacramental seminars.
          </p>
        </div>
      </div>

      <!-- Sacrament Selector (Modern, refined interactive tabs) -->
      <div class="sacraments-selector-wrap">
        <div class="sacraments-tabs" role="tablist" aria-label="Select sacrament to view guidelines">
          <button
            v-for="(option, index) in sacramentOptions"
            :key="option.name"
            type="button"
            role="tab"
            :class="['sacrament-tab-btn', { active: selectedSacramentIndex === index }]"
            :aria-selected="selectedSacramentIndex === index"
            :aria-controls="`sacrament-panel-${index}`"
            :id="`sacrament-tab-${index}`"
            @click="selectedSacramentIndex = index"
          >
            <span class="tab-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                <path v-if="option.icon === 'baptism'" d="M12 2S6.5 8.4 6.5 13a5.5 5.5 0 0 0 11 0C17.5 8.4 12 2 12 2Z"/>
                <g v-else-if="option.icon === 'wedding'"><circle cx="9" cy="12" r="5"/><circle cx="15" cy="12" r="5"/></g>
                <path v-else-if="option.icon === 'confirmation'" d="M13 2c.7 4-3 5.2-3 9a3 3 0 0 0 6 0c2 2 3 4 3 6a7 7 0 0 1-14 0c0-3.5 2-6.6 5-9-.5 3 .5 4.6 2 5.5C9.5 8 13 6.3 13 2Z"/>
                <g v-else-if="option.icon === 'anointing'"><path d="M8 3h8M10 3v5l-3 4v8h10v-8l-3-4V3"/><path d="M9 13h6"/></g>
                <g v-else><path d="M12 3v18M7 8h10"/><path d="M5 21h14"/></g>
              </svg>
            </span>
            <span class="tab-label-group">
              <span class="tab-name">{{ option.name }}</span>
              <span class="tab-sub">{{ option.categoryShort }}</span>
            </span>
          </button>
        </div>
      </div>

      <!-- Two-Column Sacramental Detail Showcase -->
      <div
        class="sacraments-detail-layout"
        role="tabpanel"
        :id="`sacrament-panel-${selectedSacramentIndex}`"
        :aria-labelledby="`sacrament-tab-${selectedSacramentIndex}`"
        aria-live="polite"
      >
        <!-- Left Column: Sacramental Information & Guidelines -->
        <article class="sacrament-main-card">
          <header class="sacrament-card-header">
            <div class="sacrament-title-badge-row">
              <span class="sacrament-category-tag">{{ selectedSacrament.category }}</span>
              <span class="sacrament-badge-pill">Parish Guidelines</span>
            </div>
            <h2>{{ selectedSacrament.title }}</h2>
            <div class="gold-rule left small">✣</div>
            <p class="sacrament-lead-desc">{{ selectedSacrament.description }}</p>
          </header>

          <!-- Requirements Section -->
          <section class="sacrament-sub-section">
            <div class="sub-section-header">
              <span class="sub-section-icon" aria-hidden="true">✓</span>
              <div>
                <h3>Required Documents &amp; Prerequisites</h3>
                <span class="sub-section-hint">Please prepare original and clear photocopies for submission</span>
              </div>
            </div>
            <ul class="requirements-checklist">
              <li v-for="(req, rIdx) in selectedSacrament.requirements" :key="rIdx">
                <span class="check-bullet" aria-hidden="true">✔</span>
                <span>{{ req }}</span>
              </li>
            </ul>
          </section>

          <!-- Process & Procedure Section -->
          <section class="sacrament-sub-section">
            <div class="sub-section-header">
              <span class="sub-section-icon" aria-hidden="true">➔</span>
              <div>
                <h3>Step-by-Step Pastoral Procedure</h3>
                <span class="sub-section-hint">Order of preparation, registration, and celebration</span>
              </div>
            </div>
            <ol class="procedure-steps-list">
              <li v-for="(step, sIdx) in selectedSacrament.process" :key="sIdx">
                <span class="step-num" aria-hidden="true">{{ sIdx + 1 }}</span>
                <div class="step-content">
                  <strong>{{ step }}</strong>
                </div>
              </li>
            </ol>
          </section>

          <!-- Pastoral Advisory Callout inside Left Column -->
          <div class="sacrament-advisory-callout">
            <span class="callout-icon" aria-hidden="true">✦</span>
            <div class="callout-content">
              <strong>Pastoral Advisory for {{ selectedSacrament.name }}</strong>
              <p>{{ selectedSacrament.advisory }}</p>
            </div>
          </div>
        </article>
      </div>
    </div>
  </div>
</template>

