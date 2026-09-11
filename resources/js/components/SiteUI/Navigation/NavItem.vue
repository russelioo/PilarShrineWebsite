<script setup>
defineProps({
  item: { type: Object, required: true },
  active: { type: Boolean, default: false },
  menuitem: { type: Boolean, default: false },
})
defineEmits(['navigate'])
</script>

<template>
  <span
    v-if="item.soon"
    class="nav-item-soon"
    :role="menuitem ? 'menuitem' : undefined"
    aria-disabled="true"
    :title="`${item.label} — Coming Soon`"
  >
    {{ item.label }}
    <span class="nav-soon-badge">Soon</span>
  </span>
  <a
    v-else
    :href="item.href"
    :class="{ active }"
    :aria-current="active ? 'page' : undefined"
    :role="menuitem ? 'menuitem' : undefined"
    @click="$emit('navigate')"
  >{{ item.label }}</a>
</template>

<style scoped>
.nav-item-soon {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  width: 100%;
  height: 38px;
  padding: 0 14px;
  box-sizing: border-box;
  border: 1px solid transparent;
  border-radius: 8px;
  color: var(--color-primary);
  font-family: inherit;
  font-size: 11.5px;
  font-weight: 600;
  letter-spacing: 0.01em;
  white-space: nowrap;
  line-height: 1;
  opacity: 0.5;
  cursor: not-allowed;
  user-select: none;
}
.nav-soon-badge {
  font-size: 8px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  background: #fff3cd;
  color: #92400e;
  border: 1px solid #fde68a;
  padding: 1px 5px;
  border-radius: 8px;
  line-height: 1.4;
  flex-shrink: 0;
}
</style>
