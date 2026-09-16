import './bootstrap';
import { createApp } from 'vue';
import MessagesApp from './components/messaging/MessagesApp.vue';

const el = document.getElementById('messages-app');
if (el) {
    let initialData = {};
    if (el.dataset.initial) {
        try {
            initialData = JSON.parse(el.dataset.initial);
        } catch (e) {
            console.error('Failed to parse initial messaging data:', e);
        }
    }
    createApp(MessagesApp, { initialData }).mount(el);
}

