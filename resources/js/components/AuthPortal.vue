<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'

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
const regEmail = ref('')
const regMobile = ref('')
const regBarangay = ref('')
const regPassword = ref('')
const regConfirmPassword = ref('')
const regConsent = ref(false)
const regShowPassword = ref(false)
const regShowConfirmPassword = ref(false)
const regLoading = ref(false)
const regSuccess = ref(false)
const regError = ref('')

// Modal state for Terms, Privacy, and Forgot Password
const activeModal = ref(null) // 'terms' | 'privacy' | 'forgot' | null

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

onMounted(() => window.addEventListener('keydown', handleKeydown))
onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  document.body.style.overflow = ''
})

// Password strength calculation for registration
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
    const response = await fetch('/login', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
      },
      body: JSON.stringify({
        email: email.value,
        password: password.value,
        remember: remember.value,
      }),
    })

    const data = await response.json()

    if (!response.ok) {
      error.value = data.errors?.email?.[0] ?? data.message ?? 'The provided credentials do not match our parish records.'
      return
    }

    window.location.assign(data.redirect)
  } catch {
    error.value = 'Unable to connect to the parish server. Please check your network and try again.'
  } finally {
    loading.value = false
  }
}

// Registration handler
const register = () => {
  if (regPassword.value !== regConfirmPassword.value) {
    regError.value = 'Passwords do not match. Please verify your entries.'
    return
  }

  if (!regConsent.value) {
    regError.value = 'Please agree to the Terms of Use and Data Privacy Policy to proceed.'
    return
  }

  regError.value = ''
  regLoading.value = true

  setTimeout(() => {
    regLoading.value = false
    regSuccess.value = true
    setTimeout(() => {
      location.hash = '/login'
    }, 1400)
  }, 700)
}
</script>

<template>
  <section class="auth-page">
    <div class="auth-shell page-width" :class="{ 'auth-shell-wide': mode === 'register' }">
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
                {{ mode === 'login' ? 'Welcome to our parish portal' : 'Join our parish community' }}
              </span>
              <span class="auth-diocese-text">Diocese of Sorsogon &bull; Est. 1861</span>
            </div>
          </div>

          <h1 class="auth-welcome-title">
            {{ mode === 'login' ? 'Faith brings us closer together.' : 'Your faith journey continues here.' }}
          </h1>

          <div class="auth-ornament" aria-hidden="true">
            <span class="ornament-line"></span>
            <span class="ornament-cross">✣</span>
            <span class="ornament-line"></span>
          </div>

          <p class="auth-welcome-desc">
            {{ mode === 'login'
              ? 'Access sacramental services, submit Mass intentions, and stay connected with the vibrant Catholic community of Our Lady of the Pillar.'
              : 'Create your parishioner account to request sacraments, manage Mass intentions, receive bulletins, and keep your family pastoral records secure.'
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
          v-else
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

          <!-- SECTION 1: Personal Details -->
          <div class="form-section">
            <div class="section-indicator">
              <span class="section-num">1</span>
              <span class="section-heading">Personal Information</span>
            </div>

            <div class="form-grid-2">
              <div class="form-group">
                <label for="reg-first-name" class="form-label">First name</label>
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
                <label for="reg-last-name" class="form-label">Last name</label>
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
          </div>

          <!-- SECTION 2: Contact & Residence -->
          <div class="form-section">
            <div class="section-indicator">
              <span class="section-num">2</span>
              <span class="section-heading">Contact &amp; Residence</span>
            </div>

            <div class="form-group full-width">
              <label for="reg-email" class="form-label">Email address</label>
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

            <div class="form-grid-2">
              <div class="form-group">
                <label for="reg-mobile" class="form-label">Mobile number</label>
                <div class="input-control">
                  <span class="input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                  </span>
                  <input
                    id="reg-mobile"
                    v-model="regMobile"
                    type="tel"
                    autocomplete="tel"
                    placeholder="0917 123 4567"
                    required
                  />
                </div>
              </div>

              <div class="form-group">
                <label for="reg-barangay" class="form-label">Barangay</label>
                <div class="input-control">
                  <span class="input-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                  </span>
                  <input
                    id="reg-barangay"
                    v-model="regBarangay"
                    type="text"
                    placeholder="e.g. Poblacion, Binanuahan, Danlog"
                    required
                  />
                </div>
              </div>
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
                <label for="reg-password" class="form-label">Password</label>
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
                <label for="reg-confirm-password" class="form-label">Confirm password</label>
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
                  <a href="mailto:olppspilarsorsogon@gmail.com">olppspilarsorsogon@gmail.com</a>
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

