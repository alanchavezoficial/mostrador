<?php
// Vista para mostrar logs de uso de API Key
require_once __DIR__ . '/../../core/auth.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'owner') {
    http_response_code(403);
    echo 'No autorizado.';
    exit;
}
require_once __DIR__ . '/../../core/db.php';
$conn = db();
$res = $conn->query("SELECT aku.*, u.username FROM api_key_usage aku JOIN users u ON aku.user_id = u.id ORDER BY aku.used_at DESC LIMIT 100");
?>
<h2>Logs de uso de API Key</h2>
<table border="1" cellpadding="4" cellspacing="0">
<tr><th>Usuario</th><th>Endpoint</th><th>IP</th><th>Fecha/Hora</th></tr>
<?php while($row = $res->fetch_assoc()): ?>
<tr>
  <td><?= htmlspecialchars($row['username']) ?></td>
  <td><?= htmlspecialchars($row['endpoint']) ?></td>
  <td><?= htmlspecialchars($row['ip']) ?></td>
  <td><?= htmlspecialchars($row['used_at']) ?></td>
</tr>
<?php endwhile; ?>
</table>
