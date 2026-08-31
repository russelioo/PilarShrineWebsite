<script setup>
const props = defineProps({
  href: { type: String, default: undefined },
  variant: { type: String, default: 'primary' },
  active: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})
const emit = defineEmits(['click'])
const handleClick = event => {
  if (props.disabled) event.preventDefault()
  else emit('click', event)
}
</script>

<template>
  <a v-if="href" class="site-button" :class="[`site-button--${variant}`, { active, disabled }]" :href="disabled ? undefined : href" :aria-current="active ? 'page' : undefined" :aria-disabled="disabled || undefined" @click="handleClick"><slot /></a>
  <button v-else class="site-button" :class="`site-button--${variant}`" type="button" :disabled="disabled" @click="handleClick"><slot /></button>
</template>
