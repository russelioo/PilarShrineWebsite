<script setup>
const altar = '/images/pilar-shrine-sanctuary.jpg'
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
          <!-- Daily Mass -->
          <article class="schedule-card">
            <div class="card-header-row">
              <span class="card-icon" aria-hidden="true">◷</span>
              <div>
                <h2>Daily Mass</h2>
                <span class="card-tag">Monday to Saturday</span>
              </div>
            </div>
            <div class="schedule-details-list">
              <div class="schedule-item">
                <span class="item-day">Monday &amp; Wednesday</span>
                <span class="item-time">5:00 PM — Holy Mass</span>
              </div>
              <div class="schedule-item">
                <span class="item-day">Tuesday, Thursday &amp; Friday</span>
                <span class="item-time">6:00 AM — Holy Mass</span>
              </div>
              <div class="schedule-item">
                <span class="item-day">Saturday</span>
                <span class="item-time">6:00 AM — Holy Mass</span>
              </div>
              <div class="schedule-item highlighted">
                <span class="item-day">Anticipated Mass (Saturday)</span>
                <span class="item-time">5:00 PM — Anticipated Sunday Mass</span>
              </div>
            </div>
          </article>

          <!-- Sunday Mass -->
          <article class="schedule-card">
            <div class="card-header-row">
              <span class="card-icon" aria-hidden="true">✝</span>
              <div>
                <h2>Sunday Mass</h2>
                <span class="card-tag">Lord's Day Celebrations</span>
              </div>
            </div>
            <div class="schedule-details-list">
              <div class="schedule-item">
                <span class="item-day">Early Morning</span>
                <span class="item-time">5:00 AM — Holy Mass</span>
              </div>
              <div class="schedule-item">
                <span class="item-day">Morning (Live)</span>
                <span class="item-time">7:30 AM — Holy Mass <span class="live-badge">FB Live</span></span>
              </div>
              <div class="schedule-item">
                <span class="item-day">Afternoon (Live)</span>
                <span class="item-time">5:00 PM — Holy Mass <span class="live-badge">FB Live</span></span>
              </div>
            </div>
          </article>

          <!-- Sacrament of Reconciliation (Confession) -->
          <article class="schedule-card">
            <div class="card-header-row">
              <span class="card-icon" aria-hidden="true">✦</span>
              <div>
                <h2>Sacrament of Reconciliation</h2>
                <span class="card-tag">Confession &amp; Spiritual Healing</span>
              </div>
            </div>
            <div class="schedule-details-list">
              <div class="schedule-item">
                <span class="item-day">Every First Thursday of the Month</span>
                <span class="item-time">5:00 PM — Confession</span>
              </div>
            </div>
          </article>

          <!-- Monthly Devotion to Our Lady of the Pillar -->
          <article class="schedule-card">
            <div class="card-header-row">
              <span class="card-icon" aria-hidden="true">♛</span>
              <div>
                <h2>Monthly Devotion to Our Lady of the Pillar</h2>
                <span class="card-tag">Patronal Devotional Day</span>
              </div>
            </div>
            <div class="schedule-details-list">
              <div class="schedule-item">
                <span class="item-day">Every 12th of the Month</span>
                <span class="item-time">5:00 PM — Holy Mass</span>
              </div>
              <div class="schedule-item">
                <span class="item-day">Procession</span>
                <span class="item-time">6:00 PM — Marian Procession</span>
              </div>
            </div>
          </article>

          <!-- Special Liturgical Activities -->
          <article class="schedule-card">
            <div class="card-header-row">
              <span class="card-icon" aria-hidden="true">▦</span>
              <div>
                <h2>Special Liturgical Activities</h2>
                <span class="card-tag">Monthly Observances &amp; Chapels</span>
              </div>
            </div>
            <div class="schedule-details-list">
              <div class="schedule-item">
                <span class="item-day">Every First Tuesday</span>
                <span class="item-time">6:00 AM — Healing Mass</span>
              </div>
              <div class="schedule-item">
                <span class="item-day">Every First Monday</span>
                <span class="item-time">6:00 AM — Misa sa Campo Santo</span>
              </div>
              <div class="schedule-item">
                <span class="item-day">First Saturday</span>
                <span class="item-time">6:00 AM — Mass at Our Lady of Fatima Chapel (Banuyo)</span>
              </div>
              <div class="schedule-item">
                <span class="item-day">Every First Friday</span>
                <span class="item-time">Holy Hour after Holy Mass</span>
              </div>
            </div>
          </article>
        </div>

        <!-- Right Column: Supporting Shrine Visual & Parish Office Panel -->
        <aside class="schedule-sidebar">
          <!-- Shrine Visual Card -->
          <div class="sidebar-visual-card">
            <img :src="altar" alt="Sanctuary and Altar of Our Lady of the Pillar Shrine">
            <div class="sidebar-visual-caption">
              <span>Our Place of Worship</span>
              <strong>Our Lady of the Pillar Shrine</strong>
              <small>Diocesan Shrine &amp; Parish · Pilar, Sorsogon</small>
            </div>
          </div>

          <!-- Parish Office Supporting Panel -->
          <div class="parish-office-card">
            <div class="office-header">
              <span class="section-eyebrow">Parish Information</span>
              <h3>Parish Office &amp; Services</h3>
            </div>
            <p class="office-desc">For Mass intentions, certificate requests, or sacrament inquiries, please visit or contact our parish office.</p>
            
            <div class="office-meta-list">
              <div class="office-meta-item">
                <span class="meta-icon" aria-hidden="true">⌖</span>
                <div>
                  <b>Location</b>
                  <span>Binanuahan, Pilar, Sorsogon</span>
                </div>
              </div>
              <div class="office-meta-item">
                <span class="meta-icon" aria-hidden="true">☏</span>
                <div>
                  <b>Contact Number</b>
                  <span>0946-869-1254</span>
                </div>
              </div>
              <div class="office-meta-item">
                <span class="meta-icon" aria-hidden="true">✉</span>
                <div>
                  <b>Email Address</b>
                  <span>olppspilarsorsogon@gmail.com</span>
                </div>
              </div>
            </div>

            <div class="office-actions">
              <a class="button" href="#/forms">Request Mass Intention</a>
              <a class="button secondary" href="#/sacraments">Sacrament Requirements</a>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

