<script setup>
const ministries = [
  {
    name: 'Liturgical Ministry',
    tag: 'Worship & Liturgy',
    icon: '✝',
    description: 'Serving at the altar of the Lord as Extraordinary Ministers of Holy Communion, Lectors & Commentators, Altar Servers, and Ushers.',
    coordinator: 'Parish Commission on Liturgy',
  },
  {
    name: 'Music & Choir Ministry',
    tag: 'Sacred Music',
    icon: '♪',
    description: 'Enriching liturgical celebrations and shrine devotions through sacred hymns, choral music, and musical accompaniment.',
    coordinator: 'Parish Music Ministry',
  },
  {
    name: 'Youth Apostolate (PYM)',
    tag: 'Youth Formation',
    icon: '✦',
    description: 'Forming young disciples through faith formation, retreats, campus outreach, fellowship, and active parish service.',
    coordinator: 'Parish Youth Ministry Council',
  },
  {
    name: 'Catechetical Ministry',
    tag: 'Faith Formation',
    icon: '📖',
    description: 'Providing religious instruction and sacramental preparation for children, youth, and catechumens in the parish and local schools.',
    coordinator: 'Commission on Catechesis',
  },
  {
    name: 'Caritas & Social Action',
    tag: 'Community Service',
    icon: '♡',
    description: 'Expressing Christ’s charity through feeding programs, calamity response, medical missions, and outreach to marginalized families.',
    coordinator: 'Social Action Commission',
  },
  {
    name: 'Mandated Organizations',
    tag: 'Lay Apostolates',
    icon: '♛',
    description: 'Organizations including the Knights of Columbus, Catholic Women’s League, Legion of Mary, and Mother Butler Guild.',
    coordinator: 'Council of Lay Apostolates',
  },
]
</script>

<template>
  <div class="ministries-page">
    <div class="page-width">
      <div class="ministries-intro-block">
        <span class="section-eyebrow">Parish Apostolates</span>
        <h2>Active Faith in Service of God &amp; Community</h2>
        <div class="gold-rule" aria-hidden="true">✣</div>
        <p class="section-subtitle">
          Discover the diverse ministries and organizations serving our shrine. We invite all parishioners to share their time, talent, and treasure in the vineyard of the Lord.
        </p>
      </div>

      <div class="ministries-grid">
        <article v-for="m in ministries" :key="m.name" class="ministry-card">
          <div class="ministry-card-header">
            <span class="ministry-icon" aria-hidden="true">{{ m.icon }}</span>
            <span class="ministry-tag">{{ m.tag }}</span>
          </div>
          <h3>{{ m.name }}</h3>
          <p>{{ m.description }}</p>
          <div class="ministry-footer">
            <small>Lead: {{ m.coordinator }}</small>
          </div>
        </article>
      </div>

      <div class="ministry-join-banner">
        <div class="join-content">
          <span>Join a Parish Ministry</span>
          <h3>Discerning where the Lord is calling you?</h3>
          <p>
            Whether you are called to liturgical service, music, youth formation, or social action, our parish community welcomes you with open arms.
          </p>
        </div>
        <a class="button" href="#/contact">Inquire at Parish Office</a>
      </div>
    </div>
  </div>
</template>

