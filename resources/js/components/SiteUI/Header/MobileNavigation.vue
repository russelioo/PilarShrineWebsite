<script setup>
defineProps({ open: { type: Boolean, default: false } })
defineEmits(['toggle'])
</script>
<template><button class="menu-button" type="button" aria-label="Toggle navigation" :aria-expanded="open" aria-controls="site-navigation" @click="$emit('toggle')"><span></span><span></span><span></span></button></template>
<style scoped>
.menu-button{display:none;width:44px;height:44px;padding:10px;border:0;border-radius:9px;background:#f1f5f9}.menu-button span{display:block;width:22px;height:2px;margin:4px auto;border-radius:2px;background:var(--color-primary);transition:transform .2s,opacity .2s}.menu-button[aria-expanded=true] span:first-child{transform:translateY(6px) rotate(45deg)}.menu-button[aria-expanded=true] span:nth-child(2){opacity:0}.menu-button[aria-expanded=true] span:last-child{transform:translateY(-6px) rotate(-45deg)}@media(max-width:1050px){.menu-button{display:block}}
</style>
