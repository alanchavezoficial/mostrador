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

<div class="analytics-details">
  <div class="card">
    <h3>Top Referrers</h3>
    <ul>
      <?php foreach ($top_referrers ?? [] as $ref): ?>
        <li><?= htmlspecialchars($ref['referrer'] ?: 'Directo') ?> (<?= $ref['cnt'] ?>)</li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="card">
    <h3>Top Clicks</h3>
    <ul>
      <?php foreach ($top_clicks ?? [] as $c): ?>
        <li><?= htmlspecialchars($c['element'] ?: '—') ?> (<?= $c['cnt'] ?>)</li>
      <?php endforeach; ?>
    </ul>
  </div>

  <div class="card">
    <h3>Top Countries</h3>
    <ul>
      <?php foreach ($top_countries ?? [] as $c): ?>
        <li><?= htmlspecialchars($c['country'] ?: '—') ?> (<?= $c['cnt'] ?>)</li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>

<!-- Analytics details merged here: controls, charts, and recent events -->
<h2>Analytics</h2>
<p>Estadísticas y visualizaciones. Usa el filtro para seleccionar fechas o agrupar por hora/día.</p>

<div class="analytics-controls">
  <label>Desde: <input type="date" id="filter-from"></label>
  <label>Hasta: <input type="date" id="filter-to"></label>
  <label>Agrupar: 
    <select id="filter-group">
      <option value="day">Día</option>
      <option value="hour">Hora</option>
    </select>
  </label>
  <button id="filter-apply">Aplicar</button>
  <button id="export-csv">Export CSV (events)</button>
  <button id="export-stats-csv">Export CSV (stats)</button>
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
</div>

<hr/>

<h2>Últimos eventos</h2>
<div class="table-responsive">
  <table class="table">
    <thead>
      <tr>
          <th>País</th>
        <th>ID</th>
        <th>Tipo</th>
        <th>Session</th>
        <th>Path</th>
        <th>Element</th>
        <th>Referrer</th>
        <th>IP</th>
        <th>Metadata</th>
        <th>Fecha</th>
      </tr>
    </thead>
    <tbody id="analytics-tbody"></tbody>
  </table>
</div>

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
        <div><strong>IP:</strong> <span id="ev-ip"></span></div>
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
