<script setup>
const parishAerial = '/images/pilar-shrine-aerial.png'
</script>

<template>
  <div class="home-page">
    <section class="intro-grid page-width">
      <article>
        <h3>About our parish</h3>
        <p>
          Established in 1862, the Diocesan Shrine and Parish of Our Lady of the Pillar serves as the spiritual home and cultural anchor for the Catholic community in Pilar, Sorsogon. Under the patronage of Nuestra Señora del Pilar, holding the distinct privilege of enshrined distinction as the only crowned image in both the province and the Diocese of Sorsogon. Our parish stands as a testament to generations of faith, community life, and religious tradition in the municipality.
        </p>
        <a class="button secondary" href="#/about">Read more</a>
      </article>

      <img :src="parishAerial" alt="Aerial view of Our Lady of the Pillar Shrine">

      <aside>
        <h3>Mass Schedule</h3>
        <h4 class="schedule-group">Daily Mass</h4>
        <p><b>Monday &amp; Wednesday</b> — 5:00 PM</p>
        <p><b>Tuesday, Thursday &amp; Friday</b> — 6:00 AM</p>
        <p><b>Saturday</b> — 6:00 AM</p>
        <p><b>Anticipated Mass (Saturday)</b> — 5:00 PM</p>
        <h4 class="schedule-group">Sunday Mass</h4>
        <p><b>5:00 AM</b> — Holy Mass</p>
        <p><b>7:30 AM</b> — Holy Mass</p>
        <p><b>5:00 PM</b> — Holy Mass</p>
      </aside>
    </section>
  </div>
</template>

