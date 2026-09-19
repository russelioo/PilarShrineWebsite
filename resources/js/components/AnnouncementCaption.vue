<script setup>
import { computed, ref, useId, watch } from 'vue'

const props = defineProps({
  text: { type: String, default: '' },
  initiallyExpanded: { type: Boolean, default: false },
})

const expanded = ref(props.initiallyExpanded)
const contentId = useId()

// Facebook's copied toggle label is not part of the announcement itself.
const caption = computed(() => (props.text || '').replace(/(?:^|\s+)See (?:more|less)\s*$/i, '').trim())
const preview = computed(() => {
  const firstLines = caption.value.split('\n').slice(0, 3).join('\n')
  const characters = Array.from(firstLines)

  if (characters.length <= 280) return firstLines.trimEnd()

  const excerpt = characters.slice(0, 280).join('')
  const wordBreak = excerpt.search(/\s+\S*$/u)
  return (wordBreak > 0 ? excerpt.slice(0, wordBreak) : excerpt).trimEnd()
})
const canToggle = computed(() => preview.value.length < caption.value.length)
const visibleText = computed(() => expanded.value ? caption.value : preview.value)

watch(() => [props.text, props.initiallyExpanded], () => {
  expanded.value = props.initiallyExpanded
})
</script>

<template>
  <p class="announcement-caption"><span :id="contentId">{{ visibleText }}</span><template v-if="canToggle">{{ expanded ? ' ' : '… ' }}<button
    class="caption-toggle"
    type="button"
    :aria-expanded="expanded"
    :aria-controls="contentId"
    @click.stop="expanded = !expanded"
    @keydown.stop
    @keyup.stop
  >{{ expanded ? 'See less' : 'See more' }}</button></template></p>
</template>

<style scoped>
.caption-toggle {
  display: inline;
  margin: 0;
  padding: 0;
  border: 0;
  background: none;
  color: inherit;
  font: inherit;
  font-weight: 600;
  letter-spacing: normal;
  text-transform: none;
  white-space: nowrap;
  cursor: pointer;
}

.caption-toggle:hover {
  text-decoration: underline;
}

.caption-toggle:focus-visible {
  outline: 2px solid var(--blue, #0b3b82);
  outline-offset: 3px;
  border-radius: 2px;
}
</style>
