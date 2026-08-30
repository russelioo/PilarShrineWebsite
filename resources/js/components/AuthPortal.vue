<script setup>
import { ref } from 'vue'

defineProps({ mode: String })

const audience = ref('parishioner')
const email = ref('')
const password = ref('')
const remember = ref(false)
const error = ref('')
const loading = ref(false)
const show = ref(false)
const confirmShow = ref(false)
const success = ref(false)

const login = async () => {
  error.value = ''

  if (audience.value === 'parishioner') {
    location.hash = '/home'
    return
  }

  loading.value = true

  try {
    const response = await fetch('/login/staff', {
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
      error.value = data.errors?.email?.[0] ?? data.message ?? 'Unable to sign in.'
      return
    }

    window.location.assign(data.redirect)
  } catch {
    error.value = 'Unable to connect. Please try again.'
  } finally {
    loading.value = false
  }
}

const register = () => {
  success.value = true
  setTimeout(() => { location.hash = '/login' }, 1200)
}
</script>

<template>
<section class="auth-page"><div class="auth-shell page-width">
  <aside class="auth-welcome"><div>
    <img :src="'/images/pilar-shrine-logo.png'" alt="Pilar Shrine seal">
    <span class="auth-kicker">{{ mode === 'login' ? 'Welcome to our parish portal' : 'Join our parish community' }}</span>
    <h1>{{ mode === 'login' ? 'Faith brings us closer together.' : 'Your faith journey continues here.' }}</h1>
    <div class="auth-ornament"><span></span>✣<span></span></div>
    <p>{{ mode === 'login' ? 'Access parish services, submit requests, and stay connected with the community of Our Lady of the Pillar.' : 'Create an account to request sacraments, send Mass intentions, receive updates, and manage parish services online.' }}</p>
    <blockquote v-if="mode === 'login'">“For where two or three gather in my name, there am I with them.” <b>— Matthew 18:20</b></blockquote>
    <ul v-else><li>✓ Submit and track parish requests</li><li>✓ Receive parish announcements</li><li>✓ Keep your records in one secure place</li></ul>
  </div></aside>
  <div class="auth-card-wrap">
    <form v-if="mode === 'login'" class="auth-card" @submit.prevent="login">
      <a class="auth-back" href="#/home">← &nbsp; Back to home</a><span class="auth-kicker">Account access</span>
      <h2>Sign in to your account</h2><p class="auth-intro">Welcome back. Please enter your details below.</p>
      <div class="account-switch"><button type="button" :class="{active:audience==='parishioner'}" @click="audience='parishioner'">♙ <span>Parishioner</span></button><button type="button" :class="{active:audience==='staff'}" @click="audience='staff'">♜ <span>Staff / Admin</span></button></div>
      <div v-if="audience==='staff'" class="staff-notice">Authorized parish personnel only. Staff activity may be monitored.</div>
      <label class="auth-field"><span>Email address</span><div><i>✉</i><input v-model="email" type="email" autocomplete="email" placeholder="you@example.com" required></div></label>
      <label class="auth-field"><span>Password</span><div><i>⌑</i><input v-model="password" :type="show?'text':'password'" autocomplete="current-password" placeholder="Enter your password" required><button type="button" @click="show=!show">{{ show?'◉':'◎' }}</button></div></label>
      <div class="auth-options"><label><input v-model="remember" type="checkbox"> Remember me</label><a href="#/forgot-password">Forgot password?</a></div>
      <p v-if="error" class="auth-error" role="alert">{{ error }}</p>
      <button class="auth-submit" :disabled="loading">{{ loading ? 'Signing in...' : 'Sign in' }} <span>→</span></button>
      <p v-if="audience==='parishioner'" class="auth-alternate">New to the parish portal? <a href="#/register">Create an account</a></p><p v-else class="auth-alternate">Need staff access? Contact the parish administrator.</p>
      <p class="auth-security">▣ &nbsp; Your information is protected and kept confidential.</p>
    </form>
    <form v-else class="auth-card auth-card-wide" @submit.prevent="register">
      <a class="auth-back" href="#/home">← &nbsp; Back to home</a><span class="auth-kicker">Parishioner registration</span>
      <h2>Create your account</h2><p class="auth-intro">Complete the form to access online parish services.</p><div v-if="success" class="auth-success">✓ Account created. Taking you to sign in…</div>
      <div class="auth-form-grid">
        <label class="auth-field"><span>First name</span><div><i>♙</i><input placeholder="First name" required></div></label><label class="auth-field"><span>Last name</span><div><i>♙</i><input placeholder="Last name" required></div></label>
        <label class="auth-field full"><span>Email address</span><div><i>✉</i><input type="email" placeholder="you@example.com" required></div></label><label class="auth-field"><span>Mobile number</span><div><i>☎</i><input type="tel" placeholder="09XX XXX XXXX" required></div></label><label class="auth-field"><span>Barangay</span><div><i>⌖</i><input placeholder="Your barangay" required></div></label>
        <label class="auth-field"><span>Password</span><div><i>⌑</i><input :type="show?'text':'password'" minlength="8" placeholder="At least 8 characters" required><button type="button" @click="show=!show">{{ show?'◉':'◎' }}</button></div></label><label class="auth-field"><span>Confirm password</span><div><i>⌑</i><input :type="confirmShow?'text':'password'" minlength="8" placeholder="Repeat password" required><button type="button" @click="confirmShow=!confirmShow">{{ confirmShow?'◉':'◎' }}</button></div></label>
      </div>
      <label class="auth-consent"><input type="checkbox" required><span>I agree to the <a href="#/terms">Terms of Use</a> and <a href="#/privacy">Data Privacy Policy</a>, and consent to secure processing of my information.</span></label>
      <button class="auth-submit">Create parishioner account <span>→</span></button><p class="auth-alternate">Already have an account? <a href="#/login">Sign in</a></p><p class="auth-security">▣ &nbsp; Staff and administrator accounts are issued by the parish office.</p>
    </form>
  </div>
</div></section>
</template>
<style src="../auth.css"></style>
