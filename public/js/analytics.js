// analytics.js - seguimiento básico con consentimiento de cookies
(function () {
  const CONSENT_COOKIE = 'analytics_consent';
  const SESSION_COOKIE = 'tracking_session';
  const COOKIE_EXP_DAYS = 365;
  const HEARTBEAT_INTERVAL = 30000; // 30s

  function setCookie(name, value, days) {
    const d = new Date();
    d.setTime(d.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value};path=/;expires=${d.toUTCString()};SameSite=Lax`;
  }

  function getCookie(name) {
    return document.cookie.split('; ').reduce((r, v) => {
      const parts = v.split('=');
      return parts[0] === name ? decodeURIComponent(parts[1]) : r;
    }, '');
  }

  function createSession() {
    let sid = getCookie(SESSION_COOKIE);
    if (!sid) {
      sid = 's_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
      setCookie(SESSION_COOKIE, sid, COOKIE_EXP_DAYS);
    }
    return sid;
  }

  function hasConsent() {
    return getCookie(CONSENT_COOKIE) === '1';
  }

  function buildPayload(eventType, extra = {}) {
    return {
      event_type: eventType,
      session_id: getCookie(SESSION_COOKIE) || null,
      path: window.location.pathname + window.location.search,
      referrer: document.referrer || null,
      metadata: Object.assign({
        screen: { w: screen.width, h: screen.height },
        viewport: { w: window.innerWidth, h: window.innerHeight },
      }, extra)
    };
  }

  async function getVisitorCountry() {
    // Try session storage cache
    try {
      const cached = sessionStorage.getItem('visitorCountry');
      if (cached) {
        return JSON.parse(cached);
      }
    } catch (e) {}
    // Fetch from public geoip service
    try {
      const res = await fetch('https://ipapi.co/json/');
      if (res.ok) {
        const j = await res.json();
        const country = { country: j.country || null, country_name: j.country_name || null };
        try { sessionStorage.setItem('visitorCountry', JSON.stringify(country)); } catch (e) {}
        return country;
      }
    } catch (e) {
      // ignore
    }
    return { country: null, country_name: null };
  }

  function sendEvent(eventType, extra = {}) {
    if (!hasConsent()) return;
    const payload = buildPayload(eventType, extra);
    // Inject cached country info if available (small optimization)
    try {
      const c = sessionStorage.getItem('visitorCountry');
      if (c) {
        const cc = JSON.parse(c);
        payload.metadata = Object.assign({}, payload.metadata, { country: cc.country, country_name: cc.country_name });
      }
    } catch (e) {}
    const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'analytics/collect';
    try {
      const body = JSON.stringify(payload);
      if (navigator.sendBeacon) {
        const blob = new Blob([body], { type: 'application/json' });
        navigator.sendBeacon(url, blob);
      } else {
        fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body });
      }
    } catch (e) {
      console.error('Analytics send failed', e);
    }
  }

  function initCookieBanner() {
    if (getCookie(CONSENT_COOKIE)) return; // already decided

    const banner = document.createElement('div');
    banner.id = 'cookie-banner-analytics';
    banner.innerHTML = `
      <div class="cookie-inner">
        <p>Usamos cookies para mejorar la experiencia y obtener estadísticas. ¿Aceptas que recolectemos datos anónimos?</p>
        <div class="cookie-actions">
          <button id="cookie-accept">Aceptar</button>
          <button id="cookie-decline">Rechazar</button>
        </div>
      </div>
    `;
    document.body.appendChild(banner);

    document.getElementById('cookie-accept').addEventListener('click', function () {
      setCookie(CONSENT_COOKIE, '1', COOKIE_EXP_DAYS);
      createSession();
      banner.remove();
      sendEvent('consent_accepted');
      sendEvent('pageview');
    });
    document.getElementById('cookie-decline').addEventListener('click', function () {
      setCookie(CONSENT_COOKIE, '0', COOKIE_EXP_DAYS);
      banner.remove();
    });
  }

  function initSettingsButton() {
    const btn = document.createElement('button');
    btn.id = 'cookie-settings-btn';
    btn.style.position = 'fixed';
    btn.style.right = '1rem';
    btn.style.bottom = '1rem';
    btn.style.zIndex = 9998;
    btn.style.padding = '6px 10px';
    btn.style.borderRadius = '6px';
    btn.style.border = 'none';
    btn.style.background = 'rgba(0,0,0,0.7)';
    btn.style.color = 'white';
    btn.textContent = 'Cookie settings';
    btn.addEventListener('click', function () {
      // Reopen banner
      if (!document.getElementById('cookie-banner-analytics')) {
        initCookieBanner();
      }
    });
    document.body.appendChild(btn);
  }

  function initClicks() {
    let lastSent = 0;
    document.addEventListener('click', function (e) {
      if (!hasConsent()) return;
      const now = Date.now();
      if (now - lastSent < 250) return; // simple throttle 250ms
      lastSent = now;

      const el = e.target;
      const elDesc = (el.id ? `#${el.id}` : '') + (el.className ? `.${el.className.toString().split(' ').join('.')}` : '') || el.tagName;
      const payload = {
        element: elDesc,
        text: el.innerText?.substring(0, 100) || null,
        x: e.clientX,
        y: e.clientY
      };
      sendEvent('click', payload);
    }, true);
  }

  function initTimeOnPage() {
    const loadedAt = Date.now();
    let lastPing = loadedAt;
    const ping = () => {
      const diff = Math.round((Date.now() - loadedAt) / 1000);
      sendEvent('heartbeat', { seconds: diff });
      lastPing = Date.now();
    };
    const interval = setInterval(ping, HEARTBEAT_INTERVAL);

    window.addEventListener('beforeunload', function (e) {
      const totalSec = Math.round((Date.now() - loadedAt) / 1000);
      const payload = { seconds: totalSec };
      const url = (typeof BASE_URL !== 'undefined' ? BASE_URL : '/') + 'analytics/collect';
      try {
        const body = JSON.stringify(buildPayload('time_on_page', payload));
        if (navigator.sendBeacon) {
          const blob = new Blob([body], { type: 'application/json' });
          navigator.sendBeacon(url, blob);
        } else {
          // best effort synchronous fetch blocked in many browsers, but try
          var xhr = new XMLHttpRequest();
          xhr.open('POST', url, false);
          xhr.setRequestHeader('Content-Type', 'application/json');
          xhr.send(body);
        }
      } catch (err) {
        console.warn('Failed to send time_on_page', err);
      }
      clearInterval(interval);
    });
  }

  // Inicialización
  (function init() {
    // Crear entorno de tracking solo si no es admin
    if (window.location.pathname.indexOf('/admin') !== -1) return;
    // Crear variables y banner
    if (!hasConsent()) {
      initCookieBanner();
    } else {
      createSession();
      // preload country and then send a pageview enriched with country
      getVisitorCountry().finally(() => sendEvent('pageview'));
    }
    // Add settings button always (to change preference)
    initSettingsButton();
    initClicks();
    initTimeOnPage();
    // Preload visitor country for metadata enrichment
    // Preloading already handled above when consent exists
  })();
})();