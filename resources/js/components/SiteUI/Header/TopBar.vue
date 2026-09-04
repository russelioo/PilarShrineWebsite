<template>
  <div class="top-strip">
    <a href="tel:+639468691254">☎ <span>0946-869-1254</span></a>
    <a href="mailto:olppspilarsorsogon@gmail.com">✉ <span>olppspilarsorsogon@gmail.com</span></a>
    <span>⌖ Binanuahan, Pilar, Sorsogon</span>
    <div class="socials" aria-label="Official social media channels">
      <a
        href="https://www.facebook.com/PilarShrineSorsogon"
        target="_blank"
        rel="noopener noreferrer"
        class="social-link"
        aria-label="Official Facebook Page"
        title="Facebook: @PilarShrineSorsogon"
      >
        <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor" aria-hidden="true">
          <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
        </svg>
      </a>
      <a
        href="https://www.youtube.com/@PilarShrineSorsogon"
        target="_blank"
        rel="noopener noreferrer"
        class="social-link"
        aria-label="Official YouTube Channel"
        title="YouTube: @PilarShrineSorsogon"
      >
        <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor" aria-hidden="true">
          <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
        </svg>
      </a>
      <a
        href="https://www.tiktok.com/@PilarShrineSorsogon"
        target="_blank"
        rel="noopener noreferrer"
        class="social-link"
        aria-label="Official TikTok Account"
        title="TikTok: @PilarShrineSorsogon"
      >
        <svg viewBox="0 0 24 24" width="12" height="12" fill="currentColor" aria-hidden="true">
          <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 3 15.68a6.34 6.34 0 0 0 10.86 4.49 6.27 6.27 0 0 0 1.84-4.49V8.71a8.21 8.21 0 0 0 4.89 1.6V6.86a4.8 4.8 0 0 1-1-.17z"/>
        </svg>
      </a>
    </div>
  </div>
</template>

<style scoped>
.top-strip {
  height: 30px;
  padding: 0 max(28px, calc((100% - var(--site-container)) / 2));
  display: flex;
  align-items: center;
  gap: 24px;
  overflow: hidden;
  background: linear-gradient(90deg, var(--color-primary-dark), #0b438d);
  color: rgba(255, 255, 255, .86);
  font-size: 9px;
  letter-spacing: .015em;
  transition: height .25s ease, opacity .2s ease;
}

.top-strip a,
.top-strip > span {
  display: inline-flex;
  align-items: center;
  color: inherit;
  text-decoration: none;
  transition: color .2s ease;
}

.top-strip a:hover {
  color: #f2cd72;
}

.socials {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  gap: 12px;
}

.social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: rgba(255, 255, 255, 0.88);
  padding: 4px;
  border-radius: 4px;
  line-height: 1;
  transition: color 0.2s ease, transform 0.2s ease;
}

.social-link:hover {
  color: #f2cd72;
  transform: translateY(-1px);
}

.social-link:focus-visible {
  outline: 2px solid #f2cd72;
  outline-offset: 2px;
}

@media (max-width: 720px) {
  .top-strip {
    display: none;
  }
}
</style>
