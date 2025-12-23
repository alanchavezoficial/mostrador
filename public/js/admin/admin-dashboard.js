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

  // API Key para endpoints protegidos (puedes setearla dinámicamente)
  const API_KEY = window.ADMIN_API_KEY || '';
  if (!API_KEY) {
    console.warn('ADMIN_API_KEY está vacío o no definido. No se podrá autenticar contra los endpoints protegidos.');
  } else {
    console.log('ADMIN_API_KEY:', API_KEY);
  }
  const buildUrl = (path, params = {}) => {
    const url = new URL(BASE_URL + path, window.location.origin);
    Object.keys(params).forEach(k => { if (params[k] !== undefined && params[k] !== '') url.searchParams.set(k, params[k]); });
    if (API_KEY) url.searchParams.set('key', API_KEY);
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
      group: document.getElementById('filter-group')?.value || 'day'
    };
  };

  let pageviewsChart = null, avgTimeChart = null, clicksChart = null, referrersChart = null, routesChart = null, deviceChart = null, countryChart = null;
  const destroyCharts = () => {
    [pageviewsChart, avgTimeChart, clicksChart, referrersChart, routesChart, deviceChart, countryChart].forEach(c => { if (c) { c.destroy && c.destroy(); } });
    pageviewsChart = avgTimeChart = clicksChart = referrersChart = routesChart = deviceChart = countryChart = null;
  };

  const load = async (filters = {}) => {
    try {
      // load recent events
      if ($tbody) {
        const res = await fetch(buildUrl('admin/dashboard/events', Object.assign({ limit: 200 }, filters)));
        const list = await res.json();
        $tbody.innerHTML = '';
        list.forEach(ev => {
          const tr = document.createElement('tr');
          const pathText = ev.path || '';
          const metadataText = JSON.stringify(ev.metadata || {});
          const pathEsc = escapeAttr(pathText);
          const metadataEsc = escapeAttr(metadataText);
          tr.innerHTML = `<td class="country">${ev.country || (ev.metadata && ev.metadata.country_name) || (ev.metadata && ev.metadata.country) || ''}</td>
            <td class="id">${ev.id}</td>
            <td class="type">${ev.event_type}</td>
            <td class="session">${ev.session_id||''}</td>
            <td class="path ellipsis" title="${pathEsc}">${pathText}</td>
            <td class="element ellipsis" title="${ev.element||''}">${ev.element||''}</td>
            <td class="referrer ellipsis" title="${ev.referrer||''}">${ev.referrer||''}</td>
            <td class="ip">${ev.ip||''}</td>
            <td class="metadata ellipsis" title="${metadataEsc}">${metadataText}</td>
            <td class="fecha">${ev.created_at}</td>`;
            // attach event data to DOM row for quick modal opening without refetch
            tr._eventData = ev;
            tr.dataset.id = ev.id;
            $tbody.appendChild(tr);
        });
      }

      // Fetch aggregated stats and render charts
      try {
        const statsUrl = buildUrl('admin/dashboard/data', filters);
        console.log('Petición de stats:', statsUrl);
        const data = await (await fetch(statsUrl)).json();
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
    const filters = getFilters();
    load(filters);
  });
  document.getElementById('export-csv')?.addEventListener('click', async () => {
    // Cargar jsPDF y autoTable dinámicamente si no están presentes
    if (typeof window.jspdf === 'undefined') {
      await new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = '/public/js/admin/jspdf.umd.min.js';
        script.onload = () => {
          if (typeof window.jspdf === 'undefined') {
            alert('No se pudo cargar jsPDF (archivo local no encontrado o corrupto).');
            reject();
          } else {
            resolve();
          }
        };
        script.onerror = () => {
          alert('No se pudo cargar jsPDF (error de carga de archivo local).');
          reject();
        };
        document.body.appendChild(script);
      });
    }
    if (typeof window.jspdf?.jsPDF === 'undefined') {
      // UMD build exposes window.jspdf.jsPDF
      alert('No se pudo cargar jsPDF');
      return;
    }
    if (typeof window.jspdf?.autoTable === 'undefined') {
      await new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = '/public/js/admin/jspdf.plugin.autotable.min.js';
        script.onload = resolve;
        script.onerror = reject;
        document.body.appendChild(script);
      });
    }
    // Obtener datos de eventos
    const url = buildUrl('admin/dashboard/events', { format: 'json', limit: 10000 });
    let events = [];
    try {
      const res = await fetch(url);
      events = await res.json();
    } catch (e) {
      alert('No se pudieron obtener los datos de eventos');
      return;
    }
    // Crear PDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'portrait', unit: 'pt', format: 'a4' });
    // --- Hoja 1: Tablas resumen ---
    let y = 40;
    doc.setFontSize(18);
    doc.text('Resumen de eventos', 40, y);
    y += 30;

    // --- Páginas más visitadas ---
    const topPages = {};
    events.forEach(ev => {
      if (ev.path) topPages[ev.path] = (topPages[ev.path] || 0) + 1;
    });
    const topPagesArr = Object.entries(topPages).sort((a, b) => b[1] - a[1]).slice(0, 10);
    if (topPagesArr.length) {
      doc.setFontSize(14);
      doc.text('Páginas más visitadas', 40, y);
      y += 10;
      // Buscar información extra para cada ruta: primer y último acceso
      const pageInfo = {};
      events.forEach(ev => {
        if (ev.path) {
          if (!pageInfo[ev.path]) pageInfo[ev.path] = { count: 0, first: ev.created_at, last: ev.created_at };
          pageInfo[ev.path].count++;
          if (ev.created_at < pageInfo[ev.path].first) pageInfo[ev.path].first = ev.created_at;
          if (ev.created_at > pageInfo[ev.path].last) pageInfo[ev.path].last = ev.created_at;
        }
      });
      const topPagesFullArr = Object.entries(pageInfo)
        .sort((a, b) => b[1].count - a[1].count)
        .slice(0, 10)
        .map(([ruta, info]) => [ruta, info.count, info.first, info.last]);
      const tableOptions = {
        head: [['Ruta', 'Visitas', 'Primer acceso', 'Último acceso']],
        body: topPagesFullArr,
        startY: y + 10,
        margin: { left: 20, right: 20 },
        styles: { fontSize: 10, cellWidth: 'auto' },
        headStyles: { fillColor: [54, 162, 235] },
        tableWidth: 'wrap',
        theme: 'grid',
        didDrawPage: (data) => { y = doc.lastAutoTable.finalY + 20; }
      };
      if (typeof window.jspdf.autoTable === 'function') {
        window.jspdf.autoTable(doc, tableOptions);
        y = doc.lastAutoTable.finalY + 20;
      } else if (typeof doc.autoTable === 'function') {
        doc.autoTable(tableOptions);
        y = doc.lastAutoTable.finalY + 20;
      }
    }

    // --- Elementos más clickeados ---
    const topElements = {};
    events.forEach(ev => {
      if (ev.event_type === 'click' && ev.element) topElements[ev.element] = (topElements[ev.element] || 0) + 1;
    });
    const topElementsArr = Object.entries(topElements).sort((a, b) => b[1] - a[1]).slice(0, 10);
    if (topElementsArr.length) {
      doc.setFontSize(14);
      doc.text('Elementos más clickeados', 40, y);
      y += 10;
      // Buscar información extra para cada elemento: primer y último click
      const elementInfo = {};
      events.forEach(ev => {
        if (ev.event_type === 'click' && ev.element) {
          if (!elementInfo[ev.element]) elementInfo[ev.element] = { count: 0, first: ev.created_at, last: ev.created_at };
          elementInfo[ev.element].count++;
          if (ev.created_at < elementInfo[ev.element].first) elementInfo[ev.element].first = ev.created_at;
          if (ev.created_at > elementInfo[ev.element].last) elementInfo[ev.element].last = ev.created_at;
        }
      });
      const topElementsFullArr = Object.entries(elementInfo)
        .sort((a, b) => b[1].count - a[1].count)
        .slice(0, 10)
        .map(([el, info]) => [el, info.count, info.first, info.last]);
      const tableOptions = {
        head: [['Elemento', 'Clicks', 'Primer click', 'Último click']],
        body: topElementsFullArr,
        startY: y + 10,
        margin: { left: 20, right: 20 },
        styles: { fontSize: 10, cellWidth: 'auto' },
        headStyles: { fillColor: [255, 99, 132] },
        tableWidth: 'wrap',
        theme: 'grid',
        didDrawPage: (data) => { y = doc.lastAutoTable.finalY + 20; }
      };
      if (typeof window.jspdf.autoTable === 'function') {
        window.jspdf.autoTable(doc, tableOptions);
        y = doc.lastAutoTable.finalY + 20;
      } else if (typeof doc.autoTable === 'function') {
        doc.autoTable(tableOptions);
        y = doc.lastAutoTable.finalY + 20;
      }
    }

    // --- Resoluciones más usadas ---
    const topRes = {};
    events.forEach(ev => {
      if (ev.metadata && ev.metadata.screen && ev.metadata.screen.w && ev.metadata.screen.h) {
        const res = `${ev.metadata.screen.w}x${ev.metadata.screen.h}`;
        topRes[res] = (topRes[res] || 0) + 1;
      }
    });
    const topResArr = Object.entries(topRes).sort((a, b) => b[1] - a[1]).slice(0, 10);
    if (topResArr.length) {
      doc.setFontSize(14);
      doc.text('Resoluciones más usadas', 40, y);
      y += 10;
      // Buscar información extra para cada resolución: cantidad de usuarios únicos
      const resInfo = {};
      events.forEach(ev => {
        if (ev.metadata && ev.metadata.screen && ev.metadata.screen.w && ev.metadata.screen.h) {
          const res = `${ev.metadata.screen.w}x${ev.metadata.screen.h}`;
          if (!resInfo[res]) resInfo[res] = { count: 0, users: new Set() };
          resInfo[res].count++;
          if (ev.session_id) resInfo[res].users.add(ev.session_id);
        }
      });
      const topResFullArr = Object.entries(resInfo)
        .sort((a, b) => b[1].count - a[1].count)
        .slice(0, 10)
        .map(([res, info]) => [res, info.count, info.users.size]);
      const tableOptions = {
        head: [['Resolución', 'Veces', 'Usuarios únicos']],
        body: topResFullArr,
        startY: y + 10,
        margin: { left: 20, right: 20 },
        styles: { fontSize: 10, cellWidth: 'auto' },
        headStyles: { fillColor: [75, 192, 192] },
        tableWidth: 'wrap',
        theme: 'grid',
        didDrawPage: (data) => { y = doc.lastAutoTable.finalY + 20; }
      };
      if (typeof window.jspdf.autoTable === 'function') {
        window.jspdf.autoTable(doc, tableOptions);
        y = doc.lastAutoTable.finalY + 20;
      } else if (typeof doc.autoTable === 'function') {
        doc.autoTable(tableOptions);
        y = doc.lastAutoTable.finalY + 20;
      }
    }
    // Nueva hoja para tabla
    doc.addPage();
    doc.setFontSize(16);
    doc.text('Datos completos de eventos', 40, 40);
    // Preparar columnas y filas para autoTable
    const columns = [
      { header: 'ID', dataKey: 'id' },
      { header: 'Tipo', dataKey: 'event_type' },
      { header: 'Ruta', dataKey: 'path' },
      { header: 'Elemento', dataKey: 'element' },
      { header: 'País', dataKey: 'country' },
      { header: 'Referrer', dataKey: 'referrer' },
      { header: 'IP', dataKey: 'ip' },
      { header: 'Fecha', dataKey: 'created_at' }
    ];
    const rows = events.map(ev => ({
      id: ev.id,
      event_type: ev.event_type,
      path: ev.path,
      element: ev.element,
      country: ev.country || (ev.metadata && (ev.metadata.country_name || ev.metadata.country)) || '',
      referrer: ev.referrer,
      ip: ev.ip,
      created_at: (ev.created_at && ev.created_at.length > 19) ? ev.created_at.substring(0, 19) : ev.created_at
    }));
    // Usar autoTable
    const autoTableOptions = {
      head: [columns.map(col => col.header)],
      body: rows.map(row => columns.map(col => row[col.dataKey] || '')),
      startY: 60,
      margin: { left: 20, right: 20 },
      styles: { fontSize: 8, cellWidth: 'auto' },
      headStyles: { fillColor: [124, 58, 237] },
      tableWidth: 'wrap',
      theme: 'grid'
    };
    if (typeof window.jspdf.autoTable === 'function') {
      window.jspdf.autoTable(doc, autoTableOptions);
    } else if (typeof doc.autoTable === 'function') {
      doc.autoTable(autoTableOptions);
    } else {
      alert('No se pudo cargar autoTable para jsPDF');
      return;
    }
    // Descargar PDF
    doc.save('eventos_completos.pdf');
  });
  document.getElementById('export-stats-csv')?.addEventListener('click', () => {
    const filters = getFilters();
    const url = buildUrl('admin/dashboard/data', Object.assign({ format: 'csv' }, filters));
    window.location.href = url;
  });

  // initial load
  load(getFilters());

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
    setText('ev-ip', ev.ip || '');
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