// admin-dashboard.js - small charts and analytics on the admin dashboard
(function(){
  const smallEl = document.getElementById('dashboardPageviews');
  const $tbody = document.getElementById('analytics-tbody');
  const pageviewsCanvas = document.getElementById('pageviewsChart');
  const avgTimeCanvas = document.getElementById('avgTimeChart');
  const clicksCanvas = document.getElementById('clicksChart');
  const referrersCanvas = document.getElementById('referrersChart');
  const deviceCanvas = document.getElementById('deviceChart');
  const countryCanvas = document.getElementById('countryChart');
  const routesCanvas = document.getElementById('routesChart');

  const buildUrl = (path, params = {}) => {
    const url = new URL(BASE_URL + path, window.location.origin);
    Object.keys(params).forEach(k => { if (params[k] !== undefined && params[k] !== '') url.searchParams.set(k, params[k]); });
    return url.toString();
  };

  const escapeAttr = (str) => {
    if (str === null || str === undefined) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  };

  const codeToFlag = (cc) => {
    if (!cc || typeof cc !== 'string') return '';
    if (cc.length === 2) {
      const up = cc.toUpperCase();
      return up.split('').map(c => String.fromCodePoint(127397 + c.charCodeAt(0))).join('');
    }
    return '';
  };

  const getFilters = () => {
    return {
      from: document.getElementById('filter-from')?.value || '',
      to: document.getElementById('filter-to')?.value || '',
      group: document.getElementById('filter-group')?.value || 'day',
      event_type: document.getElementById('filter-type')?.value || ''
    };
  };

  // Pagination state and helpers
  let currentPage = 1;
  const perPage = 25;
  const $pager = document.getElementById('events-pagination');
  const renderPager = (total, page, limit) => {
    if (!$pager) return;
    const pages = Math.max(1, Math.ceil((Number(total) || 0) / (Number(limit) || perPage)));
    const p = Math.min(Math.max(1, page || 1), pages);
    $pager.innerHTML = '';
    const btn = (label, disabled, goTo) => {
      const b = document.createElement('button');
      b.textContent = label;
      b.disabled = !!disabled;
      b.style.padding = '6px 10px';
      b.style.border = '1px solid #ddd';
      b.style.background = disabled ? '#f5f5f5' : '#fff';
      b.style.borderRadius = '6px';
      b.addEventListener('click', () => { if (!disabled && typeof goTo === 'function') goTo(); });
      return b;
    };
    const info = document.createElement('span');
    info.textContent = `Página ${p} de ${pages}`;
    info.style.margin = '0 8px';
    $pager.appendChild(btn('« Anterior', p <= 1, () => { currentPage = p - 1; load(getFilters()); }));
    $pager.appendChild(info);
    $pager.appendChild(btn('Siguiente »', p >= pages, () => { currentPage = p + 1; load(getFilters()); }));
  };

  // Helpers for table rendering
  const typeChip = (t) => {
    const txt = (t || '').toString();
    return `<span class="chip chip-${txt}">${txt}</span>`;
  };
  const metaSummary = (ev) => {
    try {
      const md = ev?.metadata || {};
      switch (ev?.event_type) {
        case 'click': {
          if (md.text) return `"${String(md.text).slice(0, 40)}"`;
          if (typeof md.x === 'number' && typeof md.y === 'number') return `x:${md.x}, y:${md.y}`;
          return 'click';
        }
        case 'time_on_page':
        case 'heartbeat': {
          const s = (md.seconds !== undefined) ? Number(md.seconds) : 0;
          return `${s}s`;
        }
        default: {
          const keys = Object.keys(md);
          return keys.length ? keys.slice(0, 3).join(', ') : '';
        }
      }
    } catch (_) { return ''; }
  };

  let pageviewsChart = null, avgTimeChart = null, clicksChart = null, referrersChart = null, routesChart = null, deviceChart = null, countryChart = null, buyersChart = null, wishlistChart = null;
  const destroyCharts = () => {
    [pageviewsChart, avgTimeChart, clicksChart, referrersChart, routesChart, deviceChart, countryChart, buyersChart, wishlistChart].forEach(c => { if (c) { c.destroy && c.destroy(); } });
    pageviewsChart = avgTimeChart = clicksChart = referrersChart = routesChart = deviceChart = countryChart = buyersChart = wishlistChart = null;
  };

  const load = async (filters = {}) => {
    try {
      // load recent events
      if ($tbody) {
        const res = await fetch(buildUrl('admin/dashboard/events', Object.assign({ limit: perPage, page: currentPage }, filters)));
        let payload = await res.json();
        // Support both legacy array and new object format { items, total, page, limit }
        const list = Array.isArray(payload) ? payload : (payload.items || []);
        const total = Array.isArray(payload) ? list.length : (payload.total || list.length || 0);
        const serverPage = Array.isArray(payload) ? 1 : (payload.page || 1);
        const serverLimit = Array.isArray(payload) ? list.length : (payload.limit || perPage);
        $tbody.innerHTML = '';
        list.forEach(ev => {
          const tr = document.createElement('tr');
          const pathText = ev.path || '';
          const metadataText = JSON.stringify(ev.metadata || {});
          const pathEsc = escapeAttr(pathText);
          const metadataEsc = escapeAttr(metadataText);
            const countryText = ev.country || (ev.metadata && (ev.metadata.country || ev.metadata.country_name)) || '';
            const flag = (typeof countryText === 'string' && countryText.length === 2) ? codeToFlag(countryText) : '';
            tr.innerHTML = `<td class="country country-col">${flag ? (flag + ' ') : ''}${countryText}</td>
              <td class="type">${typeChip(ev.event_type)}</td>
            <td class="element ellipsis" title="${ev.element||''}">${ev.element||''}</td>
            <td class="referrer ellipsis" title="${ev.referrer||''}">${ev.referrer||''}</td>
              <td class="metadata ellipsis" title="${metadataEsc}">${escapeAttr(metaSummary(ev))}</td>
            <td class="fecha">${ev.created_at}</td>`;
            // attach event data to DOM row for quick modal opening without refetch
            tr._eventData = ev;
            tr.dataset.id = ev.id;
            $tbody.appendChild(tr);
        });
        renderPager(total, serverPage, serverLimit);
      }

      // Fetch aggregated stats and render charts
      try {
        const data = await (await fetch(buildUrl('admin/dashboard/data', filters))).json();
        destroyCharts();
        if (pageviewsCanvas && data.pageviews) {
          const labels = data.pageviews.map(p => p.date);
          pageviewsChart = new Chart(pageviewsCanvas, {
            type: 'line',
            data: { labels, datasets: [{ label: 'Pageviews', data: data.pageviews.map(p=>p.cnt), borderColor: 'rgba(54,162,235,0.8)', backgroundColor: 'rgba(54,162,235,0.2)', tension: 0.3 }]}
          });
        }
        if (avgTimeCanvas && data.avg_time) {
          const labels = data.avg_time.map(p => p.date);
          avgTimeChart = new Chart(avgTimeCanvas, { type: 'line', data: { labels, datasets: [{ label: 'Avg sec', data: data.avg_time.map(p=>parseFloat(p.avg_sec||0)), borderColor: 'rgba(255,99,132,0.8)', backgroundColor: 'rgba(255,99,132,0.2)', tension: 0.3 }]}});
        }
        if (clicksCanvas && data.top_clicks) {
          clicksChart = new Chart(clicksCanvas, { type: 'bar', data: { labels: data.top_clicks.map(c => c.element || '(sin)'), datasets: [{ label: 'Clicks', data: data.top_clicks.map(c=>c.cnt), backgroundColor: 'rgba(75,192,192,0.8)'}]}, options:{indexAxis:'y'}});
        }
        if (referrersCanvas && data.top_referrers) {
          referrersChart = new Chart(referrersCanvas, { type: 'bar', data: { labels: data.top_referrers.map(r=>r.referrer || '(directo)'), datasets:[{label:'Referrers', data: data.top_referrers.map(r=>r.cnt), backgroundColor:'rgba(153,102,255,0.8)'}]}, options:{indexAxis:'y'}});
        }
        if (deviceCanvas && data.device_breakdown) {
          deviceCanvas.parentElement.classList.remove('hidden');
          deviceChart = new Chart(deviceCanvas, { type: 'pie', data: { labels: data.device_breakdown.map(d => d.device), datasets: [{ data: data.device_breakdown.map(d=>d.cnt), backgroundColor: ['#36a2eb','#ffcd56','#4bc0c0'] }]}});
        }
        if (countryCanvas && data.country_breakdown) {
          countryCanvas.parentElement.classList.remove('hidden');
          countryChart = new Chart(countryCanvas, { type: 'bar', data: { labels: data.country_breakdown.map(c => (codeToFlag(c.country) ? (codeToFlag(c.country) + ' ') : '') + (c.country || '(Desconocido)')), datasets: [{ label: 'Visits', data: data.country_breakdown.map(c => c.cnt), backgroundColor: 'rgba(54,162,235,0.8)' }] }, options: { indexAxis: 'y' } });
        }
        if (routesCanvas && data.top_routes) {
          routesChart = new Chart(routesCanvas, { type: 'bar', data: { labels: data.top_routes.map(r=>r.path || '(sin ruta)'), datasets: [{ label: 'Visits', data: data.top_routes.map(r=>r.cnt), backgroundColor: 'rgba(100,149,237,0.8)'}]}, options:{indexAxis:'y'}});
        }
      } catch(e) { console.error('Failed to load stats', e); }

      // Render buyers and wishlist charts if global data available
      try {
        const buyersData = (window.TOP_PRODUCT_BUYERS || []).slice(0, 10);
        const wishlistData = (window.TOP_WISHLISTED || []).slice(0, 10);
        const buyersEl = document.getElementById('buyersChart');
        const wishlistEl = document.getElementById('wishlistChart');
        if (buyersEl && buyersData.length) {
          buyersChart = new Chart(buyersEl, {
            type: 'bar',
            data: {
              labels: buyersData.map(b => b.name || ('ID ' + (b.product_id || ''))),
              datasets: [{ label: 'Compradores únicos (pagados)', data: buyersData.map(b => Number(b.cnt) || 0), backgroundColor: 'rgba(46, 204, 113, 0.85)' }]
            },
            options: { indexAxis: 'y' }
          });
        }
        if (wishlistEl && wishlistData.length) {
          wishlistChart = new Chart(wishlistEl, {
            type: 'bar',
            data: {
              labels: wishlistData.map(w => w.name || ('ID ' + (w.product_id || ''))),
              datasets: [{ label: 'Guardados (usuarios únicos)', data: wishlistData.map(w => Number(w.cnt) || 0), backgroundColor: 'rgba(255, 159, 64, 0.85)' }]
            },
            options: { indexAxis: 'y' }
          });
        }
      } catch (err) { console.warn('Failed to render buyers/wishlist charts', err); }
    } catch (e) {
      console.error('Failed to load analytics events', e);
    }
  };

  // Small dashboard chart
  (async ()=>{
    try {
      if (!smallEl) return;
      const res = await fetch(buildUrl('admin/dashboard/data', { days: 7 }));
      const data = await res.json();
      
      if (!data.pageviews || !Array.isArray(data.pageviews) || data.pageviews.length === 0) {
        console.warn('No pageviews data available');
        return;
      }
      
      const labels = data.pageviews.map(p=>p.date);
      const values = data.pageviews.map(p=>p.cnt);
      new Chart(smallEl, { type: 'line', data: { labels, datasets: [{ label: 'Pageviews (7d)', data: values, borderColor: 'rgba(54,162,235,0.8)', backgroundColor: 'rgba(54,162,235,0.2)', tension: 0.3 }]}, options: { responsive: true, maintainAspectRatio: false }});
    } catch (e) { console.error('admin dashboard chart failed', e); }
  })();

  // Hook up filters
  document.getElementById('filter-apply')?.addEventListener('click', () => {
    currentPage = 1;
    const filters = getFilters();
    load(filters);
  });
  document.getElementById('export-csv')?.addEventListener('click', () => {
    const filters = getFilters();
    const url = buildUrl('admin/dashboard/events', Object.assign({ format: 'csv', limit: 2000 }, filters));
    window.location.href = url;
  });
  document.getElementById('export-stats-csv')?.addEventListener('click', () => {
    const filters = getFilters();
    const url = buildUrl('admin/dashboard/data', Object.assign({ format: 'csv' }, filters));
    window.location.href = url;
  });

  // initial load
  load(getFilters());

  // Event type filter change triggers reload
  document.getElementById('filter-type')?.addEventListener('change', () => {
    currentPage = 1;
    load(getFilters());
  });

  // Modal handlers
  const modal = document.getElementById('event-modal');
  const modalClose = document.getElementById('modal-close');
  const modalClose2 = document.getElementById('modal-close-2');
  const modalCopy = document.getElementById('modal-copy');
  const setText = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text ?? ''; };
  const setPre = (id, text) => { const el = document.getElementById(id); if (el) el.textContent = text ?? ''; };

  const openModalWithEvent = (ev) => {
    if (!modal) return;
    setText('ev-id', ev.id);
    setText('ev-type', ev.event_type);
    setText('ev-country', ev.country || (ev.metadata && ev.metadata.country_name) || (ev.metadata && ev.metadata.country) || '');
    setText('ev-session', ev.session_id || '');
    setText('ev-path', ev.path || '');
    setText('ev-element', ev.element || '');
    setText('ev-referrer', ev.referrer || '');
      // IP intentionally omitted from modal
    setText('ev-date', ev.created_at || '');
    setPre('ev-metadata', JSON.stringify(ev.metadata || {}, null, 2));
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  };

  const closeModal = () => {
    if (!modal) return;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  };

  modalClose?.addEventListener('click', closeModal);
  modalClose2?.addEventListener('click', closeModal);
  modalCopy?.addEventListener('click', () => {
    const data = document.getElementById('ev-metadata')?.textContent || '';
    navigator.clipboard?.writeText(data).then(() => {
      alert('Metadata copiada al portapapeles');
    }).catch(() => { alert('Error copiando metadata'); });
  });

  // Close on backdrop click
  modal?.addEventListener('click', (e) => {
    if (e.target === modal || e.target.classList.contains('modal-backdrop')) closeModal();
  });
  // Close on ESC
  window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });

  // Row click delegation
  document.getElementById('analytics-tbody')?.addEventListener('click', function (e) {
    let tr = e.target.closest('tr');
    if (!tr) return;
    const id = tr.dataset.id;
    if (!id) return;
    // find event data: if we stored it on row, use it; else fetch recent single event endpoint
    const ev = tr._eventData;
    if (ev) { openModalWithEvent(ev); return; }
    // fallback: fetch events by id using recent with limit and filtering (small trick)
    (async () => {
      try {
        const res = await fetch(buildUrl('admin/dashboard/events', { limit: 1, // use id filter by id param if supported
          id: id }));
        const list = await res.json();
        if (list && list.length) openModalWithEvent(list[0]);
      } catch (err) { console.error('Error fetching event for modal', err); }
    })();
  });
})();
