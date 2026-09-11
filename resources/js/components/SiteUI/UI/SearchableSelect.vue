<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  options: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    default: '',
  },
  id: {
    type: String,
    required: true,
  },
  placeholder: {
    type: String,
    default: 'Select an option...',
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...',
  },
  emptyText: {
    type: String,
    default: 'No options found',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
  clearable: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const isOpen = ref(false)
const searchQuery = ref('')
const highlightedIndex = ref(-1)
const rootRef = ref(null)
const searchInputRef = ref(null)
const listboxRef = ref(null)

// Normalize options to { value, label, raw }
const normalizedOptions = computed(() => {
  return props.options.map((opt) => {
    if (typeof opt === 'string' || typeof opt === 'number') {
      return { value: String(opt), label: String(opt), raw: opt }
    }
    const val = opt.code !== undefined ? String(opt.code) : (opt.value !== undefined ? String(opt.value) : String(opt.name))
    const lbl = opt.label || opt.name || String(opt.code || opt.value || '')
    return { value: val, label: lbl, raw: opt }
  })
})

// Current selected item
const selectedOption = computed(() => {
  if (props.modelValue === '' || props.modelValue === null || props.modelValue === undefined) return null
  return normalizedOptions.value.find((opt) => opt.value === String(props.modelValue) || opt.label === String(props.modelValue)) || null
})

// Filtered options based on search query
const filteredOptions = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return normalizedOptions.value
  return normalizedOptions.value.filter((opt) =>
    opt.label.toLowerCase().includes(q)
  )
})

const toggleDropdown = () => {
  if (props.disabled || props.loading) return
  if (isOpen.value) {
    closeDropdown()
  } else {
    openDropdown()
  }
}

const openDropdown = () => {
  if (props.disabled || props.loading) return
  isOpen.value = true
  searchQuery.value = ''
  highlightedIndex.value = filteredOptions.value.findIndex((opt) => opt.value === String(props.modelValue))
  if (highlightedIndex.value === -1 && filteredOptions.value.length > 0) {
    highlightedIndex.value = 0
  }
  nextTick(() => {
    searchInputRef.value?.focus()
  })
}

const closeDropdown = () => {
  isOpen.value = false
  searchQuery.value = ''
  highlightedIndex.value = -1
}

const selectOption = (opt) => {
  emit('update:modelValue', opt.value)
  emit('change', opt.raw)
  closeDropdown()
}

const clearSelection = (e) => {
  e.stopPropagation()
  if (props.disabled) return
  emit('update:modelValue', '')
  emit('change', null)
  searchQuery.value = ''
}

const onKeyDown = (e) => {
  if (!isOpen.value) {
    if (e.key === 'ArrowDown' || e.key === 'Enter' || e.key === ' ') {
      e.preventDefault()
      openDropdown()
    }
    return
  }

  const list = filteredOptions.value
  const len = list.length

  if (e.key === 'ArrowDown') {
    e.preventDefault()
    if (len === 0) return
    highlightedIndex.value = (highlightedIndex.value + 1) % len
    scrollHighlightedIntoView()
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    if (len === 0) return
    highlightedIndex.value = (highlightedIndex.value - 1 + len) % len
    scrollHighlightedIntoView()
  } else if (e.key === 'Enter') {
    e.preventDefault()
    if (highlightedIndex.value >= 0 && highlightedIndex.value < len) {
      selectOption(list[highlightedIndex.value])
    }
  } else if (e.key === 'Escape') {
    e.preventDefault()
    closeDropdown()
  } else if (e.key === 'Tab') {
    closeDropdown()
  }
}

const scrollHighlightedIntoView = () => {
  nextTick(() => {
    if (!listboxRef.value) return
    const activeEl = listboxRef.value.querySelector('.combobox-option.is-highlighted')
    if (activeEl) {
      activeEl.scrollIntoView({ block: 'nearest' })
    }
  })
}

// Click outside handling
const handleClickOutside = (e) => {
  if (rootRef.value && !rootRef.value.contains(e.target)) {
    closeDropdown()
  }
}

watch(() => props.disabled, (newVal) => {
  if (newVal && isOpen.value) {
    closeDropdown()
  }
})

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div
    ref="rootRef"
    class="combobox-field"
    :class="{
      'is-disabled': disabled,
      'is-loading': loading,
      'is-open': isOpen,
      'has-error': !!error,
      'has-value': !!selectedOption,
    }"
    @keydown="onKeyDown"
  >
    <label v-if="label" :for="id" class="form-label">
      {{ label }}
      <span v-if="required" class="required-star" aria-hidden="true">*</span>
    </label>

    <div
      :id="id"
      class="combobox-control"
      role="combobox"
      :aria-expanded="isOpen"
      aria-haspopup="listbox"
      :aria-controls="id + '-listbox'"
      :aria-disabled="disabled"
      tabindex="0"
      @click="toggleDropdown"
    >
      <div class="combobox-display">
        <span v-if="selectedOption" class="combobox-selected-text">
          {{ selectedOption.label }}
        </span>
        <span v-else class="combobox-placeholder">
          {{ loading ? 'Loading options...' : placeholder }}
        </span>
      </div>

      <div class="combobox-actions">
        <span v-if="loading" class="combobox-spinner" aria-label="Loading options">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10" stroke="rgba(6, 47, 120, 0.15)"/>
            <path d="M12 2a10 10 0 0 1 10 10" stroke="#062f78" stroke-linecap="round"/>
          </svg>
        </span>

        <button
          v-else-if="clearable && selectedOption && !disabled"
          type="button"
          class="combobox-clear-btn"
          aria-label="Clear selection"
          @click="clearSelection"
        >
          <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </button>

        <span class="combobox-arrow" aria-hidden="true">
          <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
          </svg>
        </span>
      </div>
    </div>

    <!-- Dropdown Menu -->
    <div
      v-if="isOpen"
      :id="id + '-listbox'"
      class="combobox-dropdown"
      role="listbox"
    >
      <div class="combobox-search-bar" @click.stop>
        <span class="search-bar-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
        </span>
        <input
          ref="searchInputRef"
          v-model="searchQuery"
          type="text"
          class="combobox-search-input"
          :placeholder="searchPlaceholder"
          autocomplete="off"
          spellcheck="false"
          @keydown="onKeyDown"
        />
        <button
          v-if="searchQuery"
          type="button"
          class="search-clear-btn"
          aria-label="Clear search text"
          @click="searchQuery = ''"
        >
          &times;
        </button>
      </div>

      <ul ref="listboxRef" class="combobox-options-list">
        <li
          v-for="(opt, idx) in filteredOptions"
          :key="opt.value"
          class="combobox-option"
          :class="{
            'is-selected': selectedOption && selectedOption.value === opt.value,
            'is-highlighted': idx === highlightedIndex,
          }"
          role="option"
          :aria-selected="selectedOption && selectedOption.value === opt.value"
          @mousedown.prevent="selectOption(opt)"
          @mouseenter="highlightedIndex = idx"
        >
          <span class="option-text">{{ opt.label }}</span>
          <span v-if="selectedOption && selectedOption.value === opt.value" class="option-check" aria-hidden="true">
            <svg viewBox="0 0 20 20" width="15" height="15" fill="currentColor">
              <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
          </span>
        </li>
        <li v-if="filteredOptions.length === 0" class="combobox-empty">
          {{ emptyText }}
        </li>
      </ul>
    </div>

    <span v-if="error" class="form-error-msg" role="alert">{{ error }}</span>
  </div>
</template>
