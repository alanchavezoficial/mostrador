<!-- src/views/admin/dashboard.php -->

<?php

require_once __DIR__ . '/../../core/auth.php'; 

?>

<h1>Panel principal</h1>

<p>Bienvenido al sistema de administración. Aquí tienes un resumen rápido:</p>

<div class="dashboard-cards">
  <div class="card">
    <h2>Usuarios</h2>
    <p><?= $stats['users'] ?? '—' ?></p>
  </div>

  <div class="card">
    <h2>Productos</h2>
    <p><?= $stats['products'] ?? '—' ?></p>
  </div>

  <div class="card">
    <h2>Eventos Analytics</h2>
    <p><?= $stats['analytics_total'] ?? 0 ?></p>
  </div>

  <div class="card">
    <h2>Visitantes únicos</h2>
    <p><?= $stats['analytics_unique'] ?? 0 ?></p>
  </div>

  <div class="card">
    <h2>Tiempo medio (s)</h2>
    <p><?= $stats['analytics_avg_time'] ?? 0 ?></p>
  </div>
</div>

<div class="card" style="margin-top: 1rem; height: 220px;">
  <h3>Pageviews (7d)</h3>
  <canvas id="dashboardPageviews" style="width: 100%; height: 160px;"></canvas>
</div>

<!-- Analytics Top Stats -->
<div class="analytics-top-stats">
  <div class="stat-card stat-card-clicks">
    <div class="stat-header">
      <svg class="stat-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 3v18m0 0l-6-6m6 6l6-6"></path>
      </svg>
      <h3>Top Clicks</h3>
    </div>
    <div class="stat-list">
      <?php $count = 0; foreach ($top_clicks ?? [] as $c): if($count++ >= 5) break; ?>
        <div class="stat-item">
          <span class="stat-label"><?= htmlspecialchars(substr($c['element'] ?: '—', 0, 30)) ?></span>
          <span class="stat-badge"><?= $c['cnt'] ?></span>
        </div>
      <?php endforeach; ?>
      <?php if(empty($top_clicks)): ?>
        <div class="stat-empty">Sin datos</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="stat-card stat-card-referrers">
    <div class="stat-header">
      <svg class="stat-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
      </svg>
      <h3>Top Referrers</h3>
    </div>
    <div class="stat-list">
      <?php $count = 0; foreach ($top_referrers ?? [] as $ref): if($count++ >= 5) break; ?>
        <div class="stat-item">
          <span class="stat-label"><?= htmlspecialchars(substr($ref['referrer'] ?: 'Directo', 0, 30)) ?></span>
          <span class="stat-badge"><?= $ref['cnt'] ?></span>
        </div>
      <?php endforeach; ?>
      <?php if(empty($top_referrers)): ?>
        <div class="stat-empty">Sin datos</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="stat-card stat-card-countries">
    <div class="stat-header">
      <svg class="stat-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="2" y1="12" x2="22" y2="12"></line>
        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
      </svg>
      <h3>Top Países</h3>
    </div>
    <div class="stat-list">
      <?php $count = 0; foreach ($top_countries ?? [] as $c): if($count++ >= 5) break; ?>
        <div class="stat-item">
          <span class="stat-label"><?= htmlspecialchars($c['country'] ?: '—') ?></span>
          <span class="stat-badge"><?= $c['cnt'] ?></span>
        </div>
      <?php endforeach; ?>
      <?php if(empty($top_countries)): ?>
        <div class="stat-empty">Sin datos</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="stat-card stat-card-wishlist">
    <div class="stat-header">
      <svg class="stat-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
      </svg>
      <h3>Más Guardados</h3>
    </div>
    <div class="stat-list">
      <?php $count = 0; foreach (($top_wishlisted ?? []) as $w): if($count++ >= 5) break; ?>
        <div class="stat-item">
          <span class="stat-label"><?= htmlspecialchars(substr($w['name'] ?? ('ID ' . ($w['product_id'] ?? '')), 0, 30)) ?></span>
          <span class="stat-badge"><?= (int)$w['cnt'] ?></span>
        </div>
      <?php endforeach; ?>
      <?php if(empty($top_wishlisted)): ?>
        <div class="stat-empty">Sin datos</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="stat-card stat-card-buyers">
    <div class="stat-header">
      <svg class="stat-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="9" cy="21" r="1"></circle>
        <circle cx="20" cy="21" r="1"></circle>
        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
      </svg>
      <h3>Top Compradores</h3>
    </div>
    <div class="stat-list">
      <?php $count = 0; foreach (($top_product_buyers ?? []) as $b): if($count++ >= 5) break; ?>
        <div class="stat-item">
          <span class="stat-label"><?= htmlspecialchars(substr($b['name'] ?? ('ID ' . ($b['product_id'] ?? '')), 0, 30)) ?></span>
          <span class="stat-badge"><?= (int)$b['cnt'] ?></span>
        </div>
      <?php endforeach; ?>
      <?php if(empty($top_product_buyers)): ?>
        <div class="stat-empty">Sin datos</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Analytics details merged here: controls, charts, and recent events -->
<h2>Analytics</h2>
<p>Estadísticas y visualizaciones. Usa el filtro para seleccionar fechas o agrupar por hora/día.</p>

<div class="analytics-controls">
  <div class="form-field">
    <label for="filter-from">Desde</label>
    <input type="date" id="filter-from" class="input input-sm" placeholder="mm/dd/aaaa">
  </div>
  <div class="form-field">
    <label for="filter-to">Hasta</label>
    <input type="date" id="filter-to" class="input input-sm" placeholder="mm/dd/aaaa">
  </div>
  <div class="form-field">
    <label for="filter-group">Agrupar</label>
    <select id="filter-group" class="select input-sm">
      <option value="day">Día</option>
      <option value="hour">Hora</option>
    </select>
  </div>
  <div class="form-actions">
    <button id="filter-apply" class="btn btn-primary btn-sm">Aplicar</button>
    <button id="export-csv" class="btn btn-ghost btn-sm">Export CSV (events)</button>
    <button id="export-stats-csv" class="btn btn-ghost btn-sm">Export CSV (stats)</button>
  </div>
</div>

<div class="dashboard-charts">
  <div class="chart-card">
    <h3>Pageviews (últimos 30 días)</h3>
    <canvas id="pageviewsChart" width="600" height="250"></canvas>
  </div>

  <div class="chart-card">
    <h3>Tiempo medio en página (s)</h3>
    <canvas id="avgTimeChart" width="600" height="250"></canvas>
  </div>

  <div class="chart-card">
    <h3>Top Clicks</h3>
    <canvas id="clicksChart" width="400" height="200"></canvas>
  </div>

  <div class="chart-card">
    <h3>Top Referrers</h3>
    <canvas id="referrersChart" width="400" height="200"></canvas>
  </div>

  <div class="chart-card">
    <h3>Device Breakdown</h3>
    <canvas id="deviceChart" width="300" height="200"></canvas>
  </div>

  <div class="chart-card">
    <h3>Country Breakdown</h3>
    <canvas id="countryChart" width="400" height="200"></canvas>
  </div>

  <div class="chart-card">
    <h3>Top Routes</h3>
    <canvas id="routesChart" width="400" height="200"></canvas>
  </div>

  <div class="chart-card">
    <h3>Compradores por producto (pagados)</h3>
    <canvas id="buyersChart" width="500" height="240"></canvas>
  </div>

  <div class="chart-card">
    <h3>Más guardados (Wishlist)</h3>
    <canvas id="wishlistChart" width="500" height="240"></canvas>
  </div>
</div>

<hr/>

<h2>Últimos eventos</h2>
<div class="table-block">
  <div class="table-title">Eventos recientes</div>
  <table class="admin-table events-table">
    <thead>
      <tr>
        <th class="country-col">País</th>
        <th>
          Tipo
          <div>
            <select id="filter-type" class="filter-select">
              <option value="">Todos</option>
              <option value="pageview">pageview</option>
              <option value="click">click</option>
              <option value="time_on_page">time_on_page</option>
              <option value="heartbeat">heartbeat</option>
              <option value="consent_accepted">consent_accepted</option>
              <option value="consent_declined">consent_declined</option>
            </select>
          </div>
        </th>
        <th class="element-col">Element</th>
        <th class="referrer-col">Referrer</th>
        <th class="metadata-col">Metadata</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody id="analytics-tbody"></tbody>
  </table>
</div>

<div id="events-pagination" class="events-pagination"></div>

<script>
  window.TOP_WISHLISTED = <?= json_encode(($top_wishlisted ?? []), JSON_UNESCAPED_UNICODE) ?>;
  window.TOP_PRODUCT_BUYERS = <?= json_encode(($top_product_buyers ?? []), JSON_UNESCAPED_UNICODE) ?>;
</script>

<!-- Modal for viewing full event details -->
<div id="event-modal" class="modal hidden" aria-hidden="true">
  <div class="modal-backdrop"></div>
  <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <header class="modal-header">
      <h3 id="modal-title">Detalle del evento</h3>
      <button id="modal-close" class="modal-close" aria-label="Cerrar">×</button>
    </header>
    <div class="modal-body">
      <div class="modal-grid">
        <div><strong>ID:</strong> <span id="ev-id"></span></div>
        <div><strong>Tipo:</strong> <span id="ev-type"></span></div>
        <div><strong>País:</strong> <span id="ev-country"></span></div>
        <div><strong>Session:</strong> <span id="ev-session"></span></div>
        <div><strong>Path:</strong> <span id="ev-path"></span></div>
        <div><strong>Elemento:</strong> <span id="ev-element"></span></div>
        <div><strong>Referrer:</strong> <span id="ev-referrer"></span></div>
        <div><strong>Fecha:</strong> <span id="ev-date"></span></div>
      </div>
      <h4>Metadata</h4>
      <pre id="ev-metadata" class="modal-metadata"></pre>
    </div>
    <footer class="modal-footer">
      <button id="modal-copy" class="btn">Copiar metadata</button>
      <button id="modal-close-2" class="btn btn-secondary">Cerrar</button>
    </footer>
  </div>
</div>
