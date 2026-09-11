<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { SearchableSelect } from './SiteUI'
import {
  getRegions,
  getProvinces,
  getCitiesMunicipalities,
  getBarangays,
} from '../services/philippineLocations'

const props = defineProps({
  mode: {
    type: String,
    default: 'login',
  },
})

// Sign In state
const email = ref('')
const password = ref('')
const remember = ref(false)
const error = ref('')
const loading = ref(false)
const showPassword = ref(false)

// Registration state
const regFirstName = ref('')
const regLastName = ref('')
const regDob = ref('')
const regEmail = ref('')
const regMobile = ref('')
const regCountry = ref('Philippines')

// PSGC Cascading Location state (defaulted to Bicol Region & Sorsogon)
const regRegion = ref('050000000')
const regRegionName = ref('Bicol Region')
const regProvince = ref('056200000')
const regProvinceName = ref('Sorsogon')
const regMunicipality = ref('')
const regMunicipalityName = ref('')
const regBarangay = ref('')
const regBarangayName = ref('')

// Cascading Options
const regionOptions = ref([])
const provinceOptions = ref([])
const municipalityOptions = ref([])
const barangayOptions = ref([])

// Loading states for cascading locations
const loadingRegions = ref(false)
const loadingProvinces = ref(false)
const loadingMunicipalities = ref(false)
const loadingBarangays = ref(false)

// Country options
const countryOptions = [
  { value: 'Philippines', label: 'Philippines' },
  { value: 'United States', label: 'United States' },
  { value: 'Canada', label: 'Canada' },
  { value: 'United Kingdom', label: 'United Kingdom' },
  { value: 'Australia', label: 'Australia' },
  { value: 'Italy', label: 'Italy' },
  { value: 'Saudi Arabia', label: 'Saudi Arabia' },
  { value: 'United Arab Emirates', label: 'United Arab Emirates' },
  { value: 'Singapore', label: 'Singapore' },
  { value: 'Japan', label: 'Japan' },
  { value: 'Other', label: 'Other Country' },
]

// Account Security & Consent
const regPassword = ref('')
const regConfirmPassword = ref('')
const regConsent = ref(false)
const regShowPassword = ref(false)
const regShowConfirmPassword = ref(false)
const regLoading = ref(false)
const regSuccess = ref(false)
const regError = ref('')
const regErrors = ref({})

// Google OAuth & Profile Completion state
const googleUnregistered = ref(false)
const googleEmail = ref('')
const googleName = ref('')
const googleAvatar = ref('')
const isNewGoogleUser = ref(false)
const profileLoading = ref(false)
const profileSaving = ref(false)
const profileError = ref('')
const profileSuccess = ref(false)

const csrfToken = computed(() => {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
})

// Date constraints
const todayDate = computed(() => {
  const d = new Date()
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
})

const formattedDobPreview = computed(() => {
  if (!regDob.value) return ''
  try {
    const [y, m, d] = regDob.value.split('-').map(Number)
    if (!y || !m || !d) return ''
    const dateObj = new Date(y, m - 1, d)
    return dateObj.toLocaleDateString('en-PH', {
      month: 'long',
      day: 'numeric',
      year: 'numeric',
    })
  } catch {
    return ''
  }
})

const dobInputRef = ref(null)
const openDatePicker = () => {
  if (dobInputRef.value) {
    if (typeof dobInputRef.value.showPicker === 'function') {
      dobInputRef.value.showPicker()
    } else {
      dobInputRef.value.focus()
    }
  }
}

// Modal state for Terms, Privacy, and Forgot Password
const activeModal = ref(null)

const openModal = (type) => {
  activeModal.value = type
  document.body.style.overflow = 'hidden'
}

const closeModal = () => {
  activeModal.value = null
  document.body.style.overflow = ''
}

const handleKeydown = (e) => {
  if (e.key === 'Escape' && activeModal.value) {
    closeModal()
  }
}

const getParamFromUrl = (key) => {
  const hashParts = window.location.hash.split('?')
  if (hashParts.length > 1) {
    const params = new URLSearchParams(hashParts[1])
    if (params.has(key)) return params.get(key)
  }
  const searchParams = new URLSearchParams(window.location.search)
  if (searchParams.has(key)) return searchParams.get(key)
  return null
}

const parseUrlParams = () => {
  const errParam = getParamFromUrl('error')
  if (errParam) {
    const decoded = decodeURIComponent(errParam)
    if (props.mode === 'register') {
      regError.value = decoded
    } else {
      error.value = decoded
    }
  }

  if (getParamFromUrl('google_unregistered') === '1') {
    googleUnregistered.value = true
    googleEmail.value = decodeURIComponent(getParamFromUrl('email') || '')
    googleName.value = decodeURIComponent(getParamFromUrl('name') || '')
  }

  if (getParamFromUrl('new') === '1') {
    isNewGoogleUser.value = true
  }
}

const dismissGoogleUnregistered = () => {
  googleUnregistered.value = false
  googleEmail.value = ''
  googleName.value = ''
  window.location.hash = '#/login'
}

const loadProfileStatus = async () => {
  profileLoading.value = true
  profileError.value = ''
  try {
    const res = await fetch('/api/user/profile-status', {
      headers: {
        Accept: 'application/json',
      },
    })
    if (!res.ok) {
      if (res.status === 401) {
        window.location.hash = '#/login'
        return
      }
      throw new Error('Failed to fetch profile status.')
    }
    const data = await res.json()
    if (data.user) {
      const u = data.user
      if (u.first_name) regFirstName.value = u.first_name
      if (u.last_name) regLastName.value = u.last_name
      if (u.email) regEmail.value = u.email
      if (u.avatar) googleAvatar.value = u.avatar
      if (u.name) googleName.value = u.name
      if (u.date_of_birth) regDob.value = u.date_of_birth
      if (u.phone) regMobile.value = u.phone
      if (u.country) regCountry.value = u.country
    }
  } catch (e) {
    console.error('Failed to load profile status:', e)
    profileError.value = 'Unable to load profile data. Please refresh the page.'
  } finally {
    profileLoading.value = false
  }
}

const saveCompleteProfile = async () => {
  profileError.value = ''

  if (!regDob.value) {
    profileError.value = 'Please provide your Date of Birth.'
    return
  }
  if (regDob.value > todayDate.value) {
    profileError.value = 'Date of birth cannot be a future date.'
    return
  }
  if (!regMobile.value) {
    profileError.value = 'Please provide your active mobile phone number.'
    return
  }
  if (!regRegionName.value || !regMunicipalityName.value || !regBarangayName.value) {
    profileError.value = 'Please complete your residence location details (Region, Municipality/City, and Barangay).'
    return
  }

  profileSaving.value = true

  const isNcr = regProvince.value === 'NCR_NO_PROVINCE' || regProvince.value === 'NO_PROVINCE'

  try {
    const response = await fetch('/api/parishioner/complete-profile', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken.value,
      },
      body: JSON.stringify({
        first_name: regFirstName.value,
        last_name: regLastName.value,
        date_of_birth: regDob.value,
        phone: regMobile.value,
        country: regCountry.value,
        region: regRegionName.value,
        province: isNcr ? null : regProvinceName.value,
        municipality_city: regMunicipalityName.value,
        barangay: regBarangayName.value,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      profileError.value = data.message || 'Unable to update profile. Please check the fields and try again.'
      return
    }

    profileSuccess.value = true
    setTimeout(() => {
      window.location.assign(data.redirect || '/parishioner/dashboard')
    }, 1200)
  } catch (err) {
    console.error('Profile completion failed:', err)
    profileError.value = 'Network error occurred. Please try again.'
  } finally {
    profileSaving.value = false
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
  loadRegions()
  parseUrlParams()
  if (props.mode === 'complete-profile') {
    loadProfileStatus()
  }
})

watch(() => props.mode, (newMode) => {
  parseUrlParams()
  if (newMode === 'complete-profile') {
    loadProfileStatus()
  }
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})

// Mobile phone formatting: 09XX XXX XXXX
const onMobileInput = (e) => {
  let val = e.target.value.replace(/\D/g, '')
  if (val.startsWith('63')) {
    val = '0' + val.slice(2)
  }
  if (val.length > 11) {
    val = val.slice(0, 11)
  }
  let formatted = val
  if (val.length > 4 && val.length <= 7) {
    formatted = `${val.slice(0, 4)} ${val.slice(4)}`
  } else if (val.length > 7) {
    formatted = `${val.slice(0, 4)} ${val.slice(4, 7)} ${val.slice(7)}`
  }
  regMobile.value = formatted
}

// Cascading Location Loaders
const loadRegions = async () => {
  loadingRegions.value = true
  try {
    const list = await getRegions()
    regionOptions.value = list.map((r) => ({
      value: r.code,
      label: r.name,
      rawName: r.rawName || r.name,
    }))

    // Setup initial defaults: Bicol Region (050000000) & Sorsogon (056200000)
    const bicol = regionOptions.value.find(
      (r) => r.value === '050000000' || r.label.includes('Bicol') || r.rawName?.includes('Bicol')
    )
    if (bicol) {
      regRegion.value = bicol.value
      regRegionName.value = bicol.rawName || bicol.label

      loadingProvinces.value = true
      const provinces = await getProvinces(bicol.value)
      provinceOptions.value = provinces.map((p) => ({
        value: p.code,
        label: p.name,
      }))
      loadingProvinces.value = false

      const sorsogon = provinceOptions.value.find(
        (p) => p.value === '056200000' || p.label.toLowerCase().includes('sorsogon')
      )
      if (sorsogon) {
        regProvince.value = sorsogon.value
        regProvinceName.value = sorsogon.label

        loadingMunicipalities.value = true
        const cities = await getCitiesMunicipalities(bicol.value, sorsogon.value)
        municipalityOptions.value = cities.map((c) => ({
          value: c.code,
          label: c.name,
        }))
        loadingMunicipalities.value = false
      }
    }
  } catch (err) {
    console.error('Failed to load regions:', err)
  } finally {
    loadingRegions.value = false
  }
}

const onCountryChange = (selected) => {
  if (selected && (selected.value === 'Philippines' || selected.name === 'Philippines')) {
    if (!regRegion.value) {
      loadRegions()
    }
  }
}

const onRegionChange = async (selected) => {
  regProvince.value = ''
  regProvinceName.value = ''
  regMunicipality.value = ''
  regMunicipalityName.value = ''
  regBarangay.value = ''
  regBarangayName.value = ''
  provinceOptions.value = []
  municipalityOptions.value = []
  barangayOptions.value = []

  if (!selected) {
    regRegion.value = ''
    regRegionName.value = ''
    return
  }

  regRegion.value = selected.value || selected.code
  regRegionName.value = selected.rawName || selected.label || selected.name

  // NCR handling
  const isNcr = regRegion.value === '130000000' || (regRegionName.value && regRegionName.value.includes('NCR'))
  if (isNcr) {
    regProvince.value = 'NCR_NO_PROVINCE'
    regProvinceName.value = 'N/A (National Capital Region / Independent)'
    provinceOptions.value = [
      { value: 'NCR_NO_PROVINCE', label: 'N/A (National Capital Region / Independent)' }
    ]
    loadingMunicipalities.value = true
    try {
      const cities = await getCitiesMunicipalities(regRegion.value, null)
      municipalityOptions.value = cities.map((c) => ({
        value: c.code,
        label: c.name,
      }))
    } finally {
      loadingMunicipalities.value = false
    }
  } else {
    loadingProvinces.value = true
    try {
      const provinces = await getProvinces(regRegion.value)
      if (provinces.length === 0) {
        regProvince.value = 'NO_PROVINCE'
        regProvinceName.value = 'N/A (Independent Cities)'
        provinceOptions.value = [
          { value: 'NO_PROVINCE', label: 'N/A (Independent Cities)' }
        ]
        loadingMunicipalities.value = true
        const cities = await getCitiesMunicipalities(regRegion.value, null)
        municipalityOptions.value = cities.map((c) => ({
          value: c.code,
          label: c.name,
        }))
        loadingMunicipalities.value = false
      } else {
        provinceOptions.value = provinces.map((p) => ({
          value: p.code,
          label: p.name,
        }))
      }
    } finally {
      loadingProvinces.value = false
    }
  }
}

const onProvinceChange = async (selected) => {
  regMunicipality.value = ''
  regMunicipalityName.value = ''
  regBarangay.value = ''
  regBarangayName.value = ''
  municipalityOptions.value = []
  barangayOptions.value = []

  if (!selected || selected.value === 'NCR_NO_PROVINCE') {
    return
  }

  regProvince.value = selected.value || selected.code
  regProvinceName.value = selected.label || selected.name

  loadingMunicipalities.value = true
  try {
    const cities = await getCitiesMunicipalities(regRegion.value, regProvince.value)
    municipalityOptions.value = cities.map((c) => ({
      value: c.code,
      label: c.name,
    }))
  } finally {
    loadingMunicipalities.value = false
  }
}

const onMunicipalityChange = async (selected) => {
  regBarangay.value = ''
  regBarangayName.value = ''
  barangayOptions.value = []

  if (!selected) {
    regMunicipality.value = ''
    regMunicipalityName.value = ''
    return
  }

  regMunicipality.value = selected.value || selected.code
  regMunicipalityName.value = selected.label || selected.name

  loadingBarangays.value = true
  try {
    const barangays = await getBarangays(regMunicipality.value)
    barangayOptions.value = barangays.map((b) => ({
      value: b.code || b.name,
      label: b.name,
    }))
  } finally {
    loadingBarangays.value = false
  }
}

const onBarangayChange = (selected) => {
  if (!selected) {
    regBarangay.value = ''
    regBarangayName.value = ''
    return
  }
  regBarangay.value = selected.value || selected.code
  regBarangayName.value = selected.label || selected.name
}

// Password strength calculation
const passwordStrength = computed(() => {
  const pwd = regPassword.value
  if (!pwd) return { score: 0, label: '', color: '' }

  let score = 0
  if (pwd.length >= 8) score++
  if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++
  if (/\d/.test(pwd)) score++
  if (/[^a-zA-Z0-9]/.test(pwd)) score++

  if (pwd.length < 8 && score > 1) score = 1

  switch (score) {
    case 1:
      return { score: 1, label: 'Weak', color: '#dc2626' }
    case 2:
      return { score: 2, label: 'Fair', color: '#ea580c' }
    case 3:
      return { score: 3, label: 'Good', color: '#0284c7' }
    case 4:
      return { score: 4, label: 'Strong', color: '#16a34a' }
    default:
      return { score: 0, label: '', color: '' }
  }
})

// Password match indicator
const passwordsMatch = computed(() => {
  if (!regConfirmPassword.value) return null
  return regPassword.value === regConfirmPassword.value
})

// Sign in handler
const login = async () => {
  error.value = ''
  loading.value = true

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? ''
    const response = await fetch('/login', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify({
        email: email.value.trim(),
        password: password.value,
        remember: remember.value,
      }),
    })

    let data = {}
    try {
      data = await response.json()
    } catch {
      // Response was not JSON (e.g. HTML 419 session expired or 500 error)
    }

    if (!response.ok) {
      if (response.status === 419) {
        error.value = 'Your security session has expired. Please refresh the page (press F5 or Ctrl+F5) and try signing in again.'
        return
      }
      if (response.status === 500) {
        error.value = 'A parish server error occurred. Please refresh and try again shortly.'
        return
      }
      error.value = data.errors?.email?.[0] ?? data.message ?? 'The provided credentials do not match our parish records.'
      return
    }

    window.location.assign(data.redirect || '/admin/dashboard')
  } catch (err) {
    console.error('Sign-in request failed:', err)
    error.value = 'Unable to connect to the parish server. Please refresh the page (press F5 or Ctrl+F5) and try again.'
  } finally {
    loading.value = false
  }
}

// Registration handler
const register = async () => {
  regError.value = ''
  regErrors.value = {}

  if (regPassword.value !== regConfirmPassword.value) {
    regError.value = 'Passwords do not match. Please verify your entries.'
    return
  }

  if (!regConsent.value) {
    regError.value = 'Please agree to the Terms of Use and Data Privacy Policy to proceed.'
    return
  }

  if (!regDob.value) {
    regError.value = 'Please provide your Date of Birth.'
    return
  }

  if (regDob.value > todayDate.value) {
    regError.value = 'Date of birth cannot be a future date.'
    return
  }

  if (!regRegionName.value || !regMunicipalityName.value || !regBarangayName.value) {
    regError.value = 'Please complete your residence location details (Region, Municipality/City, and Barangay).'
    return
  }

  regLoading.value = true

  const isNcr = regProvince.value === 'NCR_NO_PROVINCE' || regProvince.value === 'NO_PROVINCE'

  try {
    const response = await fetch('/register', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify({
        first_name: regFirstName.value,
        last_name: regLastName.value,
        date_of_birth: regDob.value,
        email: regEmail.value,
        phone: regMobile.value,
        country: regCountry.value || 'Philippines',
        region: regRegionName.value,
        province: isNcr ? null : (regProvinceName.value || null),
        municipality_city: regMunicipalityName.value,
        barangay: regBarangayName.value,
        password: regPassword.value,
        password_confirmation: regConfirmPassword.value,
      }),
    })

    let data = {}
    try {
      data = await response.json()
    } catch {
      // Response was not JSON
    }

    if (!response.ok) {
      if (response.status === 419) {
        regError.value = 'Your security session has expired. Please refresh the page (press F5 or Ctrl+F5) and try again.'
        return
      }
      if (data.errors) {
        regErrors.value = data.errors
        const firstErrorKey = Object.keys(data.errors)[0]
        regError.value = data.errors[firstErrorKey][0]
      } else {
        regError.value = data.message || 'Unable to create account. Please check the details and try again.'
      }
      return
    }

    regSuccess.value = true
    setTimeout(() => {
      window.location.hash = '/login'
    }, 1500)
  } catch (err) {
    console.error('Registration failed:', err)
    regError.value = 'Unable to connect to the parish server. Please refresh the page (press F5 or Ctrl+F5) and try again.'
  } finally {
    regLoading.value = false
  }
}
</script>

<template>
  <section class="auth-page">
    <div class="auth-shell page-width" :class="{ 'auth-shell-wide': mode === 'register' || mode === 'complete-profile' }">
      <!-- Split Screen: Left Branding Section -->
      <aside class="auth-welcome" aria-label="Parish welcome and devotion">
        <div class="auth-welcome-inner">
          <div class="auth-badge-header">
            <img
              class="auth-seal"
              :src="'/images/pilar-shrine-logo.png'"
              alt="Seal of the Diocesan Shrine of Our Lady of the Pillar"
              width="78"
              height="98"
            />
            <div class="auth-seal-meta">
              <span class="auth-kicker">
                {{ mode === 'login' ? 'Welcome to our parish portal' : (mode === 'complete-profile' ? 'Parishioner onboarding' : 'Join our parish community') }}
              </span>
              <span class="auth-diocese-text">Diocese of Sorsogon &bull; Est. 1861</span>
            </div>
          </div>

          <h1 class="auth-welcome-title">
            {{ mode === 'login' ? 'Faith brings us closer together.' : (mode === 'complete-profile' ? 'Complete your parish profile.' : 'Your faith journey continues here.') }}
          </h1>

          <div class="auth-ornament" aria-hidden="true">
            <span class="ornament-line"></span>
            <span class="ornament-cross">✣</span>
            <span class="ornament-line"></span>
          </div>

          <p class="auth-welcome-desc">
            {{ mode === 'login'
              ? 'Access sacramental services, submit Mass intentions, and stay connected with the vibrant Catholic community of Our Lady of the Pillar.'
              : (mode === 'complete-profile'
                ? 'Your Google account is now verified and connected. Please complete your personal details, contact number, and residence location to activate all parish services.'
                : 'Create your parishioner account to request sacraments, manage Mass intentions, receive bulletins, and keep your family pastoral records secure.')
            }}
          </p>

          <!-- Login Marian Scripture -->
          <blockquote v-if="mode === 'login'" class="auth-quote">
            <p>“For where two or three gather in my name, there am I with them.”</p>
            <cite>— Matthew 18:20</cite>
          </blockquote>

          <!-- Register Highlights List -->
          <div v-else class="auth-feature-box">
            <span class="feature-box-title">Parishioner Portal Benefits</span>
            <ul class="auth-feature-list">
              <li>
                <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span>Submit &amp; monitor sacramental records online</span>
              </li>
              <li>
                <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span>Request Sunday &amp; weekday Mass intentions</span>
              </li>
              <li>
                <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span>Receive verified liturgical notices &amp; advisories</span>
              </li>
              <li>
                <svg class="check-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                <span>Strictly confidential under the Data Privacy Act</span>
              </li>
            </ul>
          </div>

          <div class="auth-patroness-stamp">
            <span>Nuestra Señora del Pilar &bull; Rogad Por Nosotros</span>
          </div>
        </div>
      </aside>

      <!-- Split Screen: Right Form Card -->
      <div class="auth-card-wrap">
        <!-- 1. SIGN IN FORM -->
        <form
          v-if="mode === 'login'"
          class="auth-form-card"
          @submit.prevent="login"
          novalidate
        >
          <div class="form-header-nav">
            <a class="auth-back" href="#/home">
              <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
              </svg>
              <span>Back to home</span>
            </a>
            <span class="auth-card-kicker">ACCOUNT ACCESS</span>
          </div>

          <h2 class="auth-card-title">Sign in to your account</h2>
          <p class="auth-card-desc">Welcome back. Please enter your credentials to continue.</p>

          <!-- Error Alert -->
          <div v-if="error" class="auth-alert auth-alert-error" role="alert">
            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>{{ error }}</span>
          </div>

          <!-- Google Unregistered Prompt Banner -->
          <div v-if="googleUnregistered" class="google-unregistered-banner" role="alert">
            <div class="google-banner-header">
              <span class="google-banner-badge" aria-hidden="true">
                <svg class="google-icon" viewBox="0 0 24 24" width="22" height="22">
                  <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                  <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
                  <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.97 0 12s.45 3.84 1.25 5.42l4.03-3.15z"/>
                  <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                </svg>
              </span>
              <div>
                <h3 class="google-banner-title">No Parish Account Found</h3>
                <p class="google-banner-desc">
                  No parish account was found for <strong>{{ googleEmail || 'this Google account' }}</strong>. Would you like to create one now?
                </p>
              </div>
            </div>

            <div class="google-banner-actions">
              <form action="/auth/google/confirm-register" method="POST">
                <input type="hidden" name="_token" :value="csrfToken" />
                <button type="submit" class="btn btn-gold btn-sm">
                  Create Parish Account
                </button>
              </form>
              <button type="button" class="btn-cancel-link" @click="dismissGoogleUnregistered">
                Cancel &amp; Sign In with another email
              </button>
            </div>
          </div>

          <!-- Google Sign In Button -->
          <a
            href="/auth/google?intent=login"
            class="btn-google-auth"
            aria-label="Continue with Google"
          >
            <svg class="google-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
              <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
              <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
              <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.97 0 12s.45 3.84 1.25 5.42l4.03-3.15z"/>
              <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
            </svg>
            <span>Continue with Google</span>
          </a>

          <!-- Divider -->
          <div class="auth-divider" role="separator" aria-label="or sign in with email">
            <span class="divider-line"></span>
            <span class="divider-text">OR SIGN IN WITH EMAIL</span>
            <span class="divider-line"></span>
          </div>

          <!-- Email Field -->
          <div class="form-group">
            <label for="login-email" class="form-label">Email address</label>
            <div class="input-control">
              <span class="input-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                </svg>
              </span>
              <input
                id="login-email"
                v-model="email"
                type="email"
                autocomplete="username"
                placeholder="you@example.com"
                required
                :disabled="loading"
              />
            </div>
          </div>

          <!-- Password Field -->
          <div class="form-group">
            <label for="login-password" class="form-label">Password</label>
            <div class="input-control">
              <span class="input-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
              </span>
              <input
                id="login-password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                placeholder="Enter your password"
                required
                :disabled="loading"
              />
              <button
                type="button"
                class="password-toggle-btn"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
                @click="showPassword = !showPassword"
              >
                <svg v-if="!showPassword" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Options: Remember Me & Forgot Password -->
          <div class="form-options-row">
            <label class="checkbox-label">
              <input v-model="remember" type="checkbox" :disabled="loading" />
              <span>Remember me</span>
            </label>
            <button
              type="button"
              class="forgot-link-btn"
              @click="openModal('forgot')"
            >
              Forgot password?
            </button>
          </div>

          <!-- Submit Button with Loading State -->
          <button
            type="submit"
            class="auth-submit-btn"
            :disabled="loading"
          >
            <span v-if="loading" class="btn-spinner" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="3">
                <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.25)"/>
                <path d="M12 2a10 10 0 0 1 10 10" stroke="#ffffff" stroke-linecap="round"/>
              </svg>
            </span>
            <span>{{ loading ? 'Signing in...' : 'Sign in' }}</span>
            <svg v-if="!loading" class="btn-arrow" viewBox="0 0 20 20" width="16" height="16" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>

          <!-- Alternative Registration Link -->
          <p class="auth-alt-prompt">
            New to the parish portal?
            <a href="#/register" class="auth-alt-link">Create an account</a>
          </p>

          <!-- Confidentiality Footer -->
          <div class="auth-privacy-stamp">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
            </svg>
            <span>Your information is protected and kept confidential under the Data Privacy Act.</span>
          </div>
        </form>

        <!-- 2. REGISTRATION FORM -->
        <form
          v-else-if="mode === 'register'"
          class="auth-form-card auth-form-card-wide"
          @submit.prevent="register"
          novalidate
        >
          <div class="form-header-nav">
            <a class="auth-back" href="#/home">
              <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
              </svg>
              <span>Back to home</span>
            </a>
            <span class="auth-card-kicker">PARISHIONER REGISTRATION</span>
          </div>

          <h2 class="auth-card-title">Create your account</h2>
          <p class="auth-card-desc">Complete the details below to enroll in online parish services.</p>

          <!-- Success Alert -->
          <div v-if="regSuccess" class="auth-alert auth-alert-success" role="status">
            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Account created successfully! Redirecting you to sign in...</span>
          </div>

          <!-- Error Alert -->
          <div v-if="regError" class="auth-alert auth-alert-error" role="alert">
            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>{{ regError }}</span>
          </div>

          <!-- Google Register Button -->
          <a
            href="/auth/google?intent=register"
            class="btn-google-auth btn-google-auth-wide"
            aria-label="Continue with Google"
          >
            <svg class="google-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
              <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
              <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
              <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.97 0 12s.45 3.84 1.25 5.42l4.03-3.15z"/>
              <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
            </svg>
            <span>Continue with Google</span>
          </a>

          <!-- Divider -->
          <div class="auth-divider" role="separator" aria-label="or register with personal details">
            <span class="divider-line"></span>
            <span class="divider-text">OR REGISTER WITH EMAIL</span>
            <span class="divider-line"></span>
          </div>

          <!-- SECTION 1: Personal Details -->
          <div class="form-section">
            <div class="section-indicator">
              <span class="section-num">1</span>
              <span class="section-heading">Personal Information</span>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="reg-first-name" class="form-label">
                  First name
                  <span class="required-star" aria-hidden="true">*</span>
                </label>
                <div class="input-control">
                  <span class="input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                  </span>
                  <input
                    id="reg-first-name"
                    v-model="regFirstName"
                    type="text"
                    autocomplete="given-name"
                    placeholder="Juan"
                    required
                  />
                </div>
              </div>

              <div class="form-group">
                <label for="reg-last-name" class="form-label">
                  Last name
                  <span class="required-star" aria-hidden="true">*</span>
                </label>
                <div class="input-control">
                  <span class="input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                  </span>
                  <input
                    id="reg-last-name"
                    v-model="regLastName"
                    type="text"
                    autocomplete="family-name"
                    placeholder="Dela Cruz"
                    required
                  />
                </div>
              </div>
            </div>

            <!-- Date of Birth with Modern Date Picker -->
            <div class="form-group" style="margin-top: 14px;">
              <label for="reg-dob" class="form-label">
                Date of birth
                <span class="required-star" aria-hidden="true">*</span>
              </label>
              <div class="input-control date-input-control">
                <span class="input-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                  </svg>
                </span>
                <input
                  id="reg-dob"
                  ref="dobInputRef"
                  v-model="regDob"
                  type="date"
                  autocomplete="bday"
                  :max="todayDate"
                  required
                  class="native-date-input"
                />
                <button
                  type="button"
                  class="date-picker-trigger-btn"
                  aria-label="Open date picker calendar"
                  title="Choose date from calendar"
                  @click="openDatePicker"
                >
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><circle cx="12" cy="15" r="1.5"/>
                  </svg>
                </button>
              </div>
              <div v-if="formattedDobPreview" class="field-hint-badge">
                Selected: {{ formattedDobPreview }}
              </div>
              <span v-else class="field-helper-hint">Format: MM/DD/YYYY &bull; Must not be a future date</span>
            </div>
          </div>

          <!-- SECTION 2: Contact & Residence -->
          <div class="form-section">
            <div class="section-indicator">
              <span class="section-num">2</span>
              <span class="section-heading">Contact &amp; Residence</span>
            </div>

            <!-- Email (Full Width) -->
            <div class="form-group full-width">
              <label for="reg-email" class="form-label">
                Email address
                <span class="required-star" aria-hidden="true">*</span>
              </label>
              <div class="input-control">
                <span class="input-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                  </svg>
                </span>
                <input
                  id="reg-email"
                  v-model="regEmail"
                  type="email"
                  autocomplete="email"
                  placeholder="juan.delacruz@example.com"
                  required
                />
              </div>
            </div>

            <!-- Mobile Number & Country Grid -->
            <div class="form-grid-2">
              <div class="form-group">
                <label for="reg-mobile" class="form-label">
                  Mobile number
                  <span class="required-star" aria-hidden="true">*</span>
                </label>
                <div class="input-control">
                  <span class="input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                  </span>
                  <input
                    id="reg-mobile"
                    :value="regMobile"
                    type="tel"
                    autocomplete="tel"
                    inputmode="tel"
                    placeholder="09XX XXX XXXX"
                    required
                    @input="onMobileInput"
                  />
                </div>
                <span class="field-helper-hint">Format: 09XX XXX XXXX (11 digits)</span>
              </div>

              <!-- Country Selector -->
              <SearchableSelect
                id="reg-country"
                v-model="regCountry"
                :options="countryOptions"
                label="Country"
                placeholder="Select country..."
                search-placeholder="Search country..."
                :clearable="false"
                :required="true"
                @change="onCountryChange"
              />
            </div>

            <!-- Region & Province Cascading Grid -->
            <div class="form-grid-2" style="margin-top: 14px;">
              <!-- Region -->
              <SearchableSelect
                id="reg-region"
                v-model="regRegion"
                :options="regionOptions"
                label="Region"
                placeholder="Select region..."
                search-placeholder="Search Philippine region..."
                :loading="loadingRegions"
                :required="true"
                @change="onRegionChange"
              />

              <!-- Province -->
              <SearchableSelect
                id="reg-province"
                v-model="regProvince"
                :options="provinceOptions"
                label="Province"
                :placeholder="regRegion ? (regProvince === 'NCR_NO_PROVINCE' ? 'N/A (NCR / Independent)' : 'Select province...') : 'Select region first'"
                search-placeholder="Search province..."
                :disabled="!regRegion || regProvince === 'NCR_NO_PROVINCE' || regProvince === 'NO_PROVINCE'"
                :loading="loadingProvinces"
                :required="regProvince !== 'NCR_NO_PROVINCE' && regProvince !== 'NO_PROVINCE'"
                @change="onProvinceChange"
              />
            </div>

            <!-- Municipality/City & Barangay Cascading Grid -->
            <div class="form-grid-2" style="margin-top: 14px;">
              <!-- Municipality / City -->
              <SearchableSelect
                id="reg-municipality"
                v-model="regMunicipality"
                :options="municipalityOptions"
                label="Municipality / City"
                :placeholder="regRegion ? 'Select municipality or city...' : 'Select region & province first'"
                search-placeholder="Search municipality or city..."
                :disabled="!regRegion || (!regProvince && regRegion !== '130000000')"
                :loading="loadingMunicipalities"
                :required="true"
                @change="onMunicipalityChange"
              />

              <!-- Barangay -->
              <SearchableSelect
                id="reg-barangay"
                v-model="regBarangay"
                :options="barangayOptions"
                label="Barangay"
                :placeholder="regMunicipality ? 'Select barangay...' : 'Select municipality / city first'"
                search-placeholder="Search barangay..."
                :disabled="!regMunicipality"
                :loading="loadingBarangays"
                :required="true"
                @change="onBarangayChange"
              />
            </div>
          </div>

          <!-- SECTION 3: Account Security -->
          <div class="form-section">
            <div class="section-indicator">
              <span class="section-num">3</span>
              <span class="section-heading">Account Security</span>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="reg-password" class="form-label">
                  Password
                  <span class="required-star" aria-hidden="true">*</span>
                </label>
                <div class="input-control">
                  <span class="input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                  </span>
                  <input
                    id="reg-password"
                    v-model="regPassword"
                    :type="regShowPassword ? 'text' : 'password'"
                    autocomplete="new-password"
                    minlength="8"
                    placeholder="At least 8 characters"
                    required
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    :aria-label="regShowPassword ? 'Hide password' : 'Show password'"
                    @click="regShowPassword = !regShowPassword"
                  >
                    <svg v-if="!regShowPassword" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>
                    </svg>
                  </button>
                </div>

                <!-- Password Strength Meter -->
                <div v-if="regPassword" class="strength-meter-wrap">
                  <div class="strength-bars">
                    <span
                      v-for="bar in 4"
                      :key="bar"
                      class="strength-bar"
                      :style="{
                        background: bar <= passwordStrength.score ? passwordStrength.color : '#e2e8f0'
                      }"
                    ></span>
                  </div>
                  <span class="strength-text" :style="{ color: passwordStrength.color }">
                    Password strength: {{ passwordStrength.label }}
                  </span>
                </div>
              </div>

              <div class="form-group">
                <label for="reg-confirm-password" class="form-label">
                  Confirm password
                  <span class="required-star" aria-hidden="true">*</span>
                </label>
                <div class="input-control">
                  <span class="input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                  </span>
                  <input
                    id="reg-confirm-password"
                    v-model="regConfirmPassword"
                    :type="regShowConfirmPassword ? 'text' : 'password'"
                    autocomplete="new-password"
                    minlength="8"
                    placeholder="Repeat your password"
                    required
                  />
                  <button
                    type="button"
                    class="password-toggle-btn"
                    :aria-label="regShowConfirmPassword ? 'Hide password' : 'Show password'"
                    @click="regShowConfirmPassword = !regShowConfirmPassword"
                  >
                    <svg v-if="!regShowConfirmPassword" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                    <svg v-else viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>
                    </svg>
                  </button>
                </div>

                <!-- Password Match Feedback -->
                <div v-if="regConfirmPassword" class="match-indicator">
                  <span v-if="passwordsMatch" class="match-text match-good">
                    <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Passwords match
                  </span>
                  <span v-else class="match-text match-bad">
                    <svg viewBox="0 0 20 20" width="13" height="13" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    Passwords do not match yet
                  </span>
                </div>
              </div>
            </div>

            <!-- Password Security Helper Note -->
            <div class="security-note">
              <span class="security-note-icon" aria-hidden="true">
                <svg viewBox="0 0 20 20" width="16" height="16" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
              </span>
              <span>Must be at least 8 characters long. Combine uppercase, lowercase, numbers, and symbols to ensure optimal account security.</span>
            </div>
          </div>

          <!-- SECTION 4: Data Privacy & Terms Consent -->
          <div class="consent-card">
            <label class="consent-checkbox-label">
              <input v-model="regConsent" type="checkbox" required />
              <span class="consent-text">
                I agree to the
                <button type="button" class="legal-inline-btn" @click="openModal('terms')">Terms of Use</button>
                and
                <button type="button" class="legal-inline-btn" @click="openModal('privacy')">Data Privacy Policy</button>,
                and consent to the secure collection and processing of my personal data for parish pastoral services and sacramental records.
              </span>
            </label>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            class="auth-submit-btn"
            :disabled="regLoading || (regPassword && !passwordsMatch) || !regConsent"
          >
            <span v-if="regLoading" class="btn-spinner" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="3">
                <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.25)"/>
                <path d="M12 2a10 10 0 0 1 10 10" stroke="#ffffff" stroke-linecap="round"/>
              </svg>
            </span>
            <span>{{ regLoading ? 'Creating account...' : 'Create parishioner account' }}</span>
            <svg v-if="!regLoading" class="btn-arrow" viewBox="0 0 20 20" width="16" height="16" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>

          <!-- Alternative Link -->
          <p class="auth-alt-prompt">
            Already have an account?
            <a href="#/login" class="auth-alt-link">Sign in</a>
          </p>

          <!-- Staff Notice -->
          <div class="auth-privacy-stamp">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <span>Staff, ministry leaders, and administrator accounts are issued directly by the parish office.</span>
          </div>
        </form>

        <!-- 3. COMPLETE PROFILE FORM (GOOGLE ONBOARDING) -->
        <form
          v-else-if="mode === 'complete-profile'"
          class="auth-form-card auth-form-card-wide"
          @submit.prevent="saveCompleteProfile"
          novalidate
        >
          <div class="form-header-nav">
            <a class="auth-back" href="#/home">
              <svg viewBox="0 0 20 20" width="14" height="14" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
              </svg>
              <span>Back to home</span>
            </a>
            <span class="auth-card-kicker">PARISHIONER ONBOARDING</span>
          </div>

          <!-- User Google Identity Card -->
          <div class="google-user-chip-card">
            <img
              v-if="googleAvatar"
              :src="googleAvatar"
              class="google-user-avatar"
              alt="Google Avatar"
            />
            <div v-else class="google-user-avatar-fallback">
              {{ (regFirstName || 'P').charAt(0) }}
            </div>
            <div class="google-user-chip-meta">
              <h2 class="auth-card-title" style="margin-bottom: 2px;">Complete your parish profile</h2>
              <p class="auth-card-desc" style="margin-bottom: 0;">
                Connected with Google as <strong>{{ regEmail }}</strong>. Provide your birth date, phone, and residence to activate parish online services.
              </p>
            </div>
          </div>

          <!-- Success Alert -->
          <div v-if="profileSuccess" class="auth-alert auth-alert-success" role="status">
            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Profile completed successfully! Redirecting to parishioner portal...</span>
          </div>

          <!-- Error Alert -->
          <div v-if="profileError" class="auth-alert auth-alert-error" role="alert">
            <svg class="alert-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>{{ profileError }}</span>
          </div>

          <!-- Profile Loading Indicator -->
          <div v-if="profileLoading" class="auth-loading-banner">
            <span class="auth-spinner"></span>
            <span>Loading profile records...</span>
          </div>

          <div v-else>
            <!-- SECTION 1: Personal Details -->
            <div class="form-section">
              <div class="section-indicator">
                <span class="section-num">1</span>
                <span class="section-heading">Personal Information</span>
              </div>

              <div class="form-grid-2">
                <div class="form-group">
                  <label for="complete-first-name" class="form-label">
                    First name
                    <span class="required-star" aria-hidden="true">*</span>
                  </label>
                  <div class="input-control">
                    <span class="input-icon" aria-hidden="true">
                      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                      </svg>
                    </span>
                    <input
                      id="complete-first-name"
                      v-model="regFirstName"
                      type="text"
                      placeholder="Juan"
                      required
                      :disabled="profileSaving"
                    />
                  </div>
                </div>

                <div class="form-group">
                  <label for="complete-last-name" class="form-label">
                    Last name
                    <span class="required-star" aria-hidden="true">*</span>
                  </label>
                  <div class="input-control">
                    <span class="input-icon" aria-hidden="true">
                      <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                      </svg>
                    </span>
                    <input
                      id="complete-last-name"
                      v-model="regLastName"
                      type="text"
                      placeholder="Dela Cruz"
                      required
                      :disabled="profileSaving"
                    />
                  </div>
                </div>
              </div>

              <!-- Date of Birth with Modern Date Picker -->
              <div class="form-group" style="margin-top: 14px;">
                <label for="complete-dob" class="form-label">
                  Date of birth
                  <span class="required-star" aria-hidden="true">*</span>
                </label>
                <div class="input-control date-input-control">
                  <span class="input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>
                    </svg>
                  </span>
                  <input
                    id="complete-dob"
                    ref="dobInputRef"
                    v-model="regDob"
                    type="date"
                    :max="todayDate"
                    required
                    class="native-date-input"
                    :disabled="profileSaving"
                  />
                  <button
                    type="button"
                    class="date-picker-trigger-btn"
                    aria-label="Open date picker calendar"
                    title="Choose date from calendar"
                    @click="openDatePicker"
                  >
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><circle cx="12" cy="15" r="1.5"/>
                    </svg>
                  </button>
                </div>
                <div v-if="formattedDobPreview" class="field-hint-badge">
                  Selected: {{ formattedDobPreview }}
                </div>
                <span v-else class="field-helper-hint">Format: MM/DD/YYYY &bull; Must not be a future date</span>
              </div>
            </div>

            <!-- SECTION 2: Contact & Residence -->
            <div class="form-section">
              <div class="section-indicator">
                <span class="section-num">2</span>
                <span class="section-heading">Contact &amp; Residence</span>
              </div>

              <!-- Email (Disabled / Readonly Google badge) -->
              <div class="form-group full-width">
                <label for="complete-email" class="form-label">
                  Email address
                  <span class="field-verified-pill">
                    <svg viewBox="0 0 20 20" width="12" height="12" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Verified with Google
                  </span>
                </label>
                <div class="input-control">
                  <span class="input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                    </svg>
                  </span>
                  <input
                    id="complete-email"
                    :value="regEmail"
                    type="email"
                    disabled
                    class="input-disabled-verified"
                  />
                </div>
              </div>

              <!-- Mobile Phone Number -->
              <div class="form-group full-width" style="margin-top: 14px;">
                <label for="complete-mobile" class="form-label">
                  Mobile phone number
                  <span class="required-star" aria-hidden="true">*</span>
                </label>
                <div class="input-control">
                  <span class="input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                  </span>
                  <input
                    id="complete-mobile"
                    v-model="regMobile"
                    type="tel"
                    placeholder="09XX XXX XXXX"
                    maxlength="13"
                    required
                    :disabled="profileSaving"
                    @input="onMobileInput"
                  />
                </div>
                <span class="field-helper-hint">Format: 09XX XXX XXXX (e.g. 0917 123 4567)</span>
              </div>

              <!-- Country Selector -->
              <div class="form-group full-width" style="margin-top: 14px;">
                <label class="form-label">
                  Country
                  <span class="required-star" aria-hidden="true">*</span>
                </label>
                <SearchableSelect
                  v-model="regCountry"
                  :options="countryOptions"
                  placeholder="Select Country"
                  :disabled="profileSaving"
                />
              </div>

              <!-- Philippine PSGC Cascading Location Selectors -->
              <div v-if="regCountry === 'Philippines'" class="location-group-box">
                <div class="location-box-header">
                  <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                  </svg>
                  <span>Residence Address (PSGC Standard)</span>
                </div>

                <div class="form-grid-2">
                  <div class="form-group">
                    <label class="form-label">
                      Region
                      <span class="required-star" aria-hidden="true">*</span>
                    </label>
                    <SearchableSelect
                      v-model="regRegion"
                      :options="regionOptions"
                      placeholder="Select Region"
                      :loading="loadingRegions"
                      :disabled="profileSaving"
                      @change="onRegionChange"
                    />
                  </div>

                  <div class="form-group">
                    <label class="form-label">
                      Province
                      <span v-if="regProvince === 'NCR_NO_PROVINCE' || regProvince === 'NO_PROVINCE'" class="sub-label">(Not Applicable)</span>
                      <span v-else class="required-star" aria-hidden="true">*</span>
                    </label>
                    <SearchableSelect
                      v-model="regProvince"
                      :options="provinceOptions"
                      placeholder="Select Province"
                      :loading="loadingProvinces"
                      :disabled="profileSaving || loadingProvinces || provinceOptions.length === 0"
                      @change="onProvinceChange"
                    />
                  </div>
                </div>

                <div class="form-grid-2" style="margin-top: 14px;">
                  <div class="form-group">
                    <label class="form-label">
                      Municipality / City
                      <span class="required-star" aria-hidden="true">*</span>
                    </label>
                    <SearchableSelect
                      v-model="regMunicipality"
                      :options="municipalityOptions"
                      placeholder="Select Municipality / City"
                      :loading="loadingMunicipalities"
                      :disabled="profileSaving || loadingMunicipalities || municipalityOptions.length === 0"
                      @change="onMunicipalityChange"
                    />
                  </div>

                  <div class="form-group">
                    <label class="form-label">
                      Barangay
                      <span class="required-star" aria-hidden="true">*</span>
                    </label>
                    <SearchableSelect
                      v-model="regBarangay"
                      :options="barangayOptions"
                      placeholder="Select Barangay"
                      :loading="loadingBarangays"
                      :disabled="profileSaving || loadingBarangays || barangayOptions.length === 0"
                      @change="onBarangayChange"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              class="auth-submit-btn"
              :disabled="profileSaving"
              style="margin-top: 24px;"
            >
              <span v-if="profileSaving" class="btn-spinner" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="3">
                  <circle cx="12" cy="12" r="10" stroke="rgba(255,255,255,0.25)"/>
                  <path d="M12 2a10 10 0 0 1 10 10" stroke="#ffffff" stroke-linecap="round"/>
                </svg>
              </span>
              <span>{{ profileSaving ? 'Saving profile...' : 'Save & Proceed to Parishioner Portal' }}</span>
              <svg v-if="!profileSaving" class="btn-arrow" viewBox="0 0 20 20" width="16" height="16" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
              </svg>
            </button>
          </div>

          <!-- Confidentiality Footer -->
          <div class="auth-privacy-stamp" style="margin-top: 20px;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
            </svg>
            <span>Your information is protected and kept confidential under the Data Privacy Act.</span>
          </div>
        </form>
      </div>
    </div>

    <!-- ACCESSIBLE MODALS FOR TERMS, PRIVACY, AND FORGOT PASSWORD -->
    <Teleport to="body">
      <div
        v-if="activeModal"
        class="auth-modal-backdrop"
        role="dialog"
        aria-modal="true"
        :aria-label="
          activeModal === 'terms' ? 'Terms of Use' :
          activeModal === 'privacy' ? 'Data Privacy Policy' : 'Forgot Password Assistance'
        "
        @click.self="closeModal"
      >
        <div class="auth-modal-card">
          <div class="auth-modal-header">
            <div class="auth-modal-title-wrap">
              <span class="modal-kicker">OUR LADY OF THE PILLAR PARISH</span>
              <h3 class="modal-title">
                {{
                  activeModal === 'terms' ? 'Terms of Use' :
                  activeModal === 'privacy' ? 'Data Privacy Policy' : 'Account Access Assistance'
                }}
              </h3>
            </div>
            <button
              type="button"
              class="modal-close-btn"
              aria-label="Close dialog"
              @click="closeModal"
            >
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <div class="auth-modal-body">
            <!-- TERMS OF USE CONTENT -->
            <div v-if="activeModal === 'terms'" class="legal-content">
              <h4>1. Acceptance of Terms</h4>
              <p>By accessing and using the Diocesan Shrine and Parish of Our Lady of the Pillar online portal, you agree to comply with and be bound by these Terms of Use and all applicable Philippine ecclesiastical and civil laws.</p>

              <h4>2. Authorized Parish Services</h4>
              <p>This portal provides registered parishioners with facilities to schedule Mass intentions, request sacramental documentation (Baptismal, Confirmation, and Marriage records), and receive official shrine communications. All submitted information must be accurate, true, and verifiable.</p>

              <h4>3. User Responsibility</h4>
              <p>Parishioners are responsible for maintaining the confidentiality of their account login credentials. Any unauthorized activity conducted under your account must be reported immediately to the Parish Administration Office.</p>

              <h4>4. Pastoral Revisions</h4>
              <p>Mass schedules, liturgical calendars, and sacrament availability are subject to the canonical direction of the Parish Priest and Diocese of Sorsogon.</p>
            </div>

            <!-- PRIVACY POLICY CONTENT -->
            <div v-else-if="activeModal === 'privacy'" class="legal-content">
              <h4>1. Compliance with Republic Act No. 10173</h4>
              <p>The Diocesan Shrine of Our Lady of the Pillar is committed to safeguarding personal data in accordance with the Philippine Data Privacy Act of 2012 (RA 10173). Personal information collected is treated with sacred trust and strict confidentiality.</p>

              <h4>2. Information Collected</h4>
              <p>We collect personal information including full name, email address, contact telephone number, residence/barangay, and sacramental history solely for church pastoral administration, parish registry documentation, and certificate verification.</p>

              <h4>3. Security and Non-Disclosure</h4>
              <p>Your personal data is encrypted, stored securely in our diocesan records system, and never sold, leased, or disclosed to unauthorized commercial third parties.</p>

              <h4>4. Data Subject Rights</h4>
              <p>Parishioners have the right to review, rectify, or request updates to their parish registry data by contacting the Parish Office in person or through official email correspondence.</p>
            </div>

            <!-- FORGOT PASSWORD CONTENT -->
            <div v-else-if="activeModal === 'forgot'" class="forgot-help-content">
              <div class="forgot-icon-wrap">
                <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="#062f78" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
              </div>
              <p class="forgot-lead">Password recovery is securely handled by our parish records office to protect your sacramental and personal information.</p>

              <div class="office-contact-card">
                <div class="contact-row">
                  <strong>Parish Office:</strong>
                  <span>Binanuahan, Pilar, Sorsogon</span>
                </div>
                <div class="contact-row">
                  <strong>Direct Telephone:</strong>
                  <a href="tel:+639468691254">0946-869-1254</a>
                </div>
                <div class="contact-row">
                  <strong>Official Email:</strong>
                  <a href="https://mail.google.com/mail/?view=cm&fs=1&to=olppspilarsorsogon@gmail.com&su=Parish%20Inquiry%20-%20Pilar%20Shrine" target="_blank" rel="noopener noreferrer">olppspilarsorsogon@gmail.com</a>
                </div>
                <div class="contact-row">
                  <strong>Office Hours:</strong>
                  <span>Tuesday &ndash; Sunday: 8:00 AM &ndash; 5:00 PM (Closed Mondays)</span>
                </div>
              </div>

              <p class="forgot-subtext">You may present a valid ID at the parish secretariat or request assistance using the registered email address above.</p>
            </div>
          </div>

          <div class="auth-modal-footer">
            <button type="button" class="modal-primary-btn" @click="closeModal">
              Understood
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </section>
</template>

<style src="../auth.css"></style>

