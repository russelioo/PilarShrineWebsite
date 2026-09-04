<script setup>
import NovenaSlider from '../../components/NovenaSlider.vue'
</script>

<template>
  <div class="novena-details-page">
    <div class="page-width">
      <div class="novena-calendar">
        <div><span>Novena begins</span><strong>October 3</strong></div>
        <div><span>Nine days of prayer</span><strong>October 3–11</strong></div>
        <div class="feast-date"><span>Solemnity &amp; feast day</span><strong>October 12</strong></div>
      </div>
      
      <NovenaSlider />

      <aside class="feast-callout">
        <span>After the nine-day novena</span>
        <h2>Feast of Our Lady of the Pillar</h2>
        <time>October 12</time>
        <p>Join the parish community as we honor Nuestra Señora del Pilar, our patroness, in prayer and celebration.</p>
      </aside>
    </div>
  </div>
</template>

