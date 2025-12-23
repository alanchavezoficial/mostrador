
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- src/views/admin/dashboard.php -->
<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/db.php';
$conn = db();
if (isset($_SESSION['user_id'])) {
  $uid = (int)$_SESSION['user_id'];
  $api_key = null;
  $res = $conn->query("SELECT api_key FROM users WHERE id = $uid LIMIT 1");
  $row = $res ? $res->fetch_assoc() : null;
  if (!$row || !isset($row['api_key']) || !preg_match('/^[a-f0-9]{64}$/', $row['api_key'])) {
    if (!function_exists('generate_api_key')) {
      function generate_api_key() { return bin2hex(random_bytes(32)); }
    }
    $api_key = generate_api_key();
    $stmt = $conn->prepare("UPDATE users SET api_key = ? WHERE id = ?");
    $stmt->bind_param('si', $api_key, $uid);
    $stmt->execute();
  } else {
    $api_key = $row['api_key'];
  }
  if (preg_match('/^[a-f0-9]{64}$/', $api_key)) {
    echo "<script>window.ADMIN_API_KEY = '" . addslashes($api_key) . "';</script>\n";
  } else {
    echo "<script>console.error('No se pudo generar una API key válida para el usuario actual'); window.ADMIN_API_KEY = '';</script>\n";
  }
}
?>
<script>
  window.BASE_URL = <?= json_encode(defined('BASE_URL') ? BASE_URL : '/') ?>;
</script>
<!-- ...existing code... -->
<script src="/public/js/admin/admin-dashboard.js"></script>
<script>
  // Script para el botón de descarga, ahora al final
  document.addEventListener('DOMContentLoaded', function() {
    var btn = document.getElementById('export-csv');
    if (btn) {
      btn.addEventListener('click', function() {
        console.log('PRUEBA: click detectado en export-csv');
        // window.location.href = (window.BASE_URL || '/') + 'admin/dashboard/events?format=csv&limit=10000';
      });
    }
  });
</script>
</body>

<?php
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/db.php';
$conn = db();
if (isset($_SESSION['user_id'])) {
  $uid = $_SESSION['user_id'];
  $res = $conn->query("SELECT api_key FROM users WHERE id = $uid LIMIT 1");
  $row = $res ? $res->fetch_assoc() : null;
  if (!$row || !$row['api_key']) {
    if (!function_exists('generate_api_key')) {
      eval('function generate_api_key() { return bin2hex(random_bytes(32)); }');
    }
    $api_key = generate_api_key();
    $stmt = $conn->prepare("UPDATE users SET api_key = ? WHERE id = ?");
    $stmt->bind_param('si', $api_key, $uid);
    $stmt->execute();
  } else {
    $api_key = $row['api_key'];
  }
  echo "<script>window.ADMIN_API_KEY = '" . addslashes($api_key) . "';</script>";
}
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
    <!-- Controles de analytics simplificados -->
    <p>Si necesitas analizar todos los eventos con el máximo detalle (incluyendo país, IP, metadata, etc.), puedes descargar el archivo completo en CSV:</p>
    <!-- Eliminado: el botón de descarga estará solo abajo -->
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

<div class="card" style="margin:2rem 0 1rem 0; padding:1.5rem; background:rgba(124,58,237,0.07); border:1px solid #7c3aed22; border-radius:10px;">
  <h2 style="margin:0 0 1rem 0;">Descargar eventos detallados</h2>
  <p>Si necesitas analizar todos los eventos con el máximo detalle (incluyendo país, IP, metadata, etc.), puedes descargar el archivo completo en CSV:</p>
  <button id="export-csv" class="btn btn-primary">Descargar CSV completo de eventos</button>
</div>