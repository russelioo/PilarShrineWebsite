import { createApp } from 'vue';
import ParishSite from './ParishSite.vue';
import { applyTypographyConfig } from './config/typography';

applyTypographyConfig();

createApp(ParishSite).mount('#app');
