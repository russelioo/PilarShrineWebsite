<script setup>
import { computed, ref } from 'vue'

const props = defineProps({ active: { type: String, default: 'home' } })
const open = ref(false)
const moreOpen = ref(false)

const primaryLinks = [
  ['home', 'Home'], ['about', 'About'], ['schedule', 'Mass Schedule'], ['sacraments', 'Sacraments'],
]
const moreLinks = [
  ['events', 'Events & News'], ['ministries', 'Ministries'],
  ['novenas', 'Novenas'], ['store', 'Store'], ['contact', 'Contact'],
]
const moreIsActive = computed(() => moreLinks.some(([key]) => key === props.active) || props.active === 'novena-details')

const closeNavigation = () => {
  open.value = false
  moreOpen.value = false
}
</script>

<template>
  <header class="site-header">
    <div class="top-strip">
      <span>☎ 0946-869-1254</span><span>✉ olppspilarsorsogon@gmail.com</span>
      <span>⌖ Binanuahan, Pilar, Sorsogon</span>
      <span class="socials">f&nbsp;&nbsp;▶&nbsp;&nbsp;◎</span>
    </div>
    <nav class="navbar page-width" aria-label="Main navigation">
      <a class="brand" href="#/home">
        <img class="brand-logo" :src="'/images/pilar-shrine-logo.png'" alt="Our Lady of the Pillar Parish logo">
        <div><strong>PILAR SHRINE<br></strong>SORSOGON</div>
      </a>
      <button class="menu-button" type="button" aria-label="Toggle menu" :aria-expanded="open" @click="open = !open; moreOpen = false">☰</button>
      <div class="nav-links" :class="{ open }">
        <a v-for="[key, label] in primaryLinks" :key="key" :href="'#/' + key" :class="{ active: props.active === key }" :aria-current="props.active === key ? 'page' : undefined" @click="closeNavigation">{{ label }}</a>
        <div class="more-nav">
          <button class="nav-more" type="button" :class="{ active: moreIsActive }" aria-haspopup="menu" :aria-expanded="moreOpen" @click="moreOpen = !moreOpen" @keydown.esc="moreOpen = false">
            More <span aria-hidden="true">▾</span>
          </button>
          <div v-show="moreOpen" class="more-menu" role="menu" aria-label="More pages" @keydown.esc="moreOpen = false">
            <a v-for="[key, label] in moreLinks" :key="key" :href="'#/' + key" role="menuitem" :aria-current="props.active === key ? 'page' : undefined" @click="closeNavigation">{{ label }}</a>
          </div>
        </div>
        <div class="auth-actions" aria-label="Parish account">
          <a class="sign-in" href="#/login" :aria-current="props.active === 'login' ? 'page' : undefined" @click="closeNavigation">Sign in</a>
          <a class="sign-up" href="#/register" :aria-current="props.active === 'register' ? 'page' : undefined" @click="closeNavigation">Sign up</a>
        </div>
      </div>
    </nav>
  </header>
</template>

<style scoped>
.navbar {
  height: 86px;
}

.brand-logo {
  width: 48px;
  height: 62px;
}

.brand strong {
  font-size: 16px;
}

.nav-links {
  gap: 28px;
}

.nav-links > a,
.nav-more {
  color: var(--blue);
  font: inherit;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  white-space: nowrap;
}

.more-nav {
  position: relative;
}

.auth-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: 4px;
}

.auth-actions a {
  display: inline-flex;
  min-height: 38px;
  align-items: center;
  justify-content: center;
  padding: 10px 15px;
  border: 1px solid var(--blue);
  border-radius: 6px;
  color: var(--blue);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  white-space: nowrap;
  transition: background .2s, color .2s, box-shadow .2s, transform .2s;
}

.auth-actions .sign-up {
  background: var(--blue);
  color: #fff;
  box-shadow: 0 5px 14px rgba(6, 47, 120, .18);
}

.auth-actions a:hover,
.auth-actions a:focus-visible,
.auth-actions a[aria-current='page'] {
  background: #edf4fb;
  color: var(--blue);
  transform: translateY(-1px);
}

.auth-actions .sign-up:hover,
.auth-actions .sign-up:focus-visible,
.auth-actions .sign-up[aria-current='page'] {
  background: #0758b8;
  color: #fff;
}

.auth-actions a:focus-visible {
  outline: 2px solid var(--gold);
  outline-offset: 2px;
}

.nav-more {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  min-height: 38px;
  padding: 12px 0 10px;
  border: 0;
  border-bottom: 2px solid transparent;
  background: transparent;
  cursor: pointer;
}

.nav-more:hover,
.nav-more:focus-visible,
.nav-more.active {
  border-bottom-color: var(--blue);
}

.nav-more:focus-visible,
.more-menu a:focus-visible {
  outline: 2px solid var(--gold);
  outline-offset: 3px;
}

.more-menu {
  position: absolute;
  z-index: 30;
  top: calc(100% + 7px);
  right: 0;
  display: grid;
  width: 184px;
  padding: 7px;
  border: 1px solid #dce4ec;
  border-radius: 6px;
  background: #fff;
  box-shadow: 0 12px 26px rgba(14, 50, 95, .16);
}

.more-menu a {
  padding: 10px 12px;
  color: var(--blue);
  font-size: 11px;
  font-weight: 600;
}

.more-menu a:hover,
.more-menu a:focus-visible,
.more-menu a[aria-current='page'] {
  background: #edf4fb;
  color: var(--blue);
}

@media (max-width: 1050px) {
  .nav-links {
    top: 86px;
    gap: 12px 20px;
  }
}

@media (max-width: 720px) {
  .navbar {
    height: 74px;
  }

  .brand-logo {
    width: 42px;
    height: 56px;
  }

  .nav-links {
    top: 74px;
  }

  .more-nav {
    width: 100%;
  }

  .nav-more {
    justify-content: space-between;
    width: 100%;
  }

  .more-menu {
    position: static;
    width: 100%;
    margin: 4px 0 8px;
    box-shadow: none;
  }

  .auth-actions {
    width: 100%;
    margin: 4px 0 0;
  }

  .auth-actions a {
    flex: 1;
  }
}
</style>
