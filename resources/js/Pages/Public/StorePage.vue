<script setup>
import { ref } from 'vue'

const prayer = 'https://images.unsplash.com/photo-1507692049790-de58290a4334?auto=format&fit=crop&w=1400&q=85'

const categories = [
  'All Items',
  'Statues & Images',
  'Rosaries',
  'Prayer Books',
  'Novena Booklets',
  'Medals & Keychains',
  'Apparel',
  'Candles',
  'Souvenirs',
]

const selectedCategory = ref('All Items')

const products = [
  { name: 'Pillar Statue', price: 950, image: prayer },
  { name: 'Pillar Rosary', price: 250, image: 'https://images.unsplash.com/photo-1602173574767-37ac01994b2a?auto=format&fit=crop&w=600&q=80' },
  { name: 'Marian Prayer Book', price: 120, image: 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&w=600&q=80' },
  { name: 'Novena Booklet', price: 25, image: 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?auto=format&fit=crop&w=600&q=80' },
  { name: 'Pillar Medal', price: 80, image: 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=600&q=80' },
  { name: 'Parish Shirt', price: 350, image: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=600&q=80' },
  { name: 'Devotional Candle', price: 120, image: 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=600&q=80' },
  { name: 'Souvenir Keychain', price: 70, image: 'https://images.unsplash.com/photo-1603561596112-db1d3d140b8c?auto=format&fit=crop&w=600&q=80' },
]
</script>

<template>
  <div class="store-page">
    <section class="shop page-width">
      <aside>
        <h3>Categories</h3>
        <a
          v-for="c in categories"
          :key="c"
          :class="{ active: selectedCategory === c }"
          href="javascript:void(0)"
          @click="selectedCategory = c"
        >
          ◇ {{ c }}
        </a>
      </aside>

      <div>
        <h2>All items</h2>
        <div class="product-grid">
          <article v-for="p in products" :key="p.name">
            <img :src="p.image" :alt="p.name">
            <h3>{{ p.name }}</h3>
            <b>₱{{ p.price }}.00</b>
            <button type="button">View item &nbsp; 🛒</button>
          </article>
        </div>
      </div>
    </section>
  </div>
</template>

