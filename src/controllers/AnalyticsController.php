<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';

class AnalyticsController
{
    private mysqli $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    private function ensureTableExists(): void
    {
        $this->conn->query(<<<SQL
        CREATE TABLE IF NOT EXISTS analytics_events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_type VARCHAR(32) NOT NULL,
            session_id VARCHAR(128) DEFAULT NULL,
            path VARCHAR(255) DEFAULT NULL,
            element VARCHAR(255) DEFAULT NULL,
            referrer VARCHAR(255) DEFAULT NULL,
            country VARCHAR(100) DEFAULT NULL,
            ip VARCHAR(45) DEFAULT NULL,
            user_agent VARCHAR(255) DEFAULT NULL,
            metadata JSON DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL
        );
        // Ensure `country` column exists for performance
        $colRes = $this->conn->query("SHOW COLUMNS FROM analytics_events LIKE 'country'");
        if ($colRes->num_rows === 0) {
            $this->conn->query("ALTER TABLE analytics_events ADD COLUMN country VARCHAR(100) DEFAULT NULL");
        }
        // Add index on country if not exists
        $idxRes = $this->conn->query("SHOW INDEX FROM analytics_events WHERE Key_name = 'idx_analytics_country'");
        if ($idxRes->num_rows === 0) {
            try { $this->conn->query("ALTER TABLE analytics_events ADD INDEX idx_analytics_country (country)"); } catch (Throwable $e) {}
        }
    }

    public function collect(): void
    {
        header('Content-Type: application/json');
        try {
            $this->ensureTableExists();

            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (!$data || !isset($data['event_type'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Payload inválido']);
                return;
            }

            $event_type = strtolower(trim($data['event_type']));
            $allowed = ['pageview','click','heartbeat','time_on_page','consent_accepted','consent_declined'];
            if (!in_array($event_type, $allowed, true)) {
                http_response_code(400);
                echo json_encode(['error' => 'event_type no permitido']);
                return;
            }
            $session_id = isset($data['session_id']) ? substr(trim($data['session_id']), 0, 128) : null;
            $path = $data['path'] ?? null;
            $element = isset($data['element']) ? substr(trim($data['element']), 0, 255) : null;
            $referrer = $data['referrer'] ?? null;
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            $clientIp = $_SERVER['REMOTE_ADDR'] ?? null;
            $ip = $clientIp;
            $metadata = isset($data['metadata']) ? json_encode($data['metadata'], JSON_UNESCAPED_UNICODE) : null;

            // If element wasn't provided at top-level, try to extract from metadata
            if (!$element && isset($data['metadata']) && is_array($data['metadata']) && !empty($data['metadata']['element'])) {
                $element = substr(trim((string)$data['metadata']['element']), 0, 255);
            }

            // Sanitize lengths
            $path = $path ? substr($path, 0, 255) : null;
            $referrer = $referrer ? substr($referrer, 0, 255) : null;
            $user_agent = $user_agent ? substr($user_agent, 0, 255) : null;
            if ($metadata && strlen($metadata) > 2048) {
                $metadata = substr($metadata, 0, 2048);
            }

            // Determine country: try metadata from client, else do a server-side geo lookup by IP
            $country = null;
            if (isset($data['metadata']) && is_array($data['metadata']) && !empty($data['metadata']['country'])) {
                $country = substr(trim($data['metadata']['country']), 0, 100);
            } else if ($clientIp) {
                try {
                    $api = 'https://ipapi.co/' . $clientIp . '/json/';
                    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
                    $geo = @file_get_contents($api, false, $ctx);
                    if ($geo) {
                        $g = json_decode($geo, true);
                        $country = $g['country'] ?? $g['country_name'] ?? null;
                        if ($country) $country = substr($country, 0, 100);
                    }
                } catch (Throwable $e) {
                    $country = null;
                }
            }

            // Anonimizar IP - enmascarar ultima parte (IPv4) o últimos bloques (IPv6)
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $parts = explode('.', $ip);
                    $parts[3] = '0';
                    $ip = implode('.', $parts);
                } else {
                    // IPv6: mantengo 4 primeros hextets
                    $parts = explode(':', $ip);
                    $len = count($parts);
                    for ($i = max(0, $len - 4); $i < $len; $i++) { $parts[$i] = '0'; }
                    $ip = implode(':', $parts);
                }
            } else {
                $ip = null;
            }

            // convert nulls to empty string for bind_param
            $session_id = $session_id ?? '';
            $path = $path ?? '';
            $element = $element ?? '';
            $referrer = $referrer ?? '';
            $ip = $ip ?? '';
            $country = $country ?? '';
            $user_agent = $user_agent ?? '';
            $metadata = $metadata ?? '';

            $stmt = $this->conn->prepare("INSERT INTO analytics_events (event_type, session_id, path, element, referrer, country, ip, user_agent, metadata) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssssss', $event_type, $session_id, $path, $element, $referrer, $country, $ip, $user_agent, $metadata);
            $stmt->execute();

            echo json_encode(['ok' => true]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function recent(): void
    {
        // Endpoint para admin: devuelve los últimos eventos en JSON (protegido)
        require_once __DIR__ . '/../core/auth.php';
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['owner','admin'], true)) {
            http_response_code(401);
            echo json_encode([]);
            return;
        }

        header('Content-Type: application/json');
        try {
            $this->ensureTableExists();
            // Pagination params
            $limit = intval($_GET['limit'] ?? 25);
            $limit = max(1, min(1000, $limit));
            $page = intval($_GET['page'] ?? 1);
            $page = max(1, $page);
            $offset = ($page - 1) * $limit;
            $where = [];
            $params = [];
            $from = $_GET['from'] ?? null;
            $to = $_GET['to'] ?? null;
            $eventType = $_GET['event_type'] ?? '';
            if ($from) {
                $from = date('Y-m-d H:i:s', strtotime($from));
                $where[] = "created_at >= '$from'";
            }
            if ($to) {
                // include end of day if date only
                $to = date('Y-m-d H:i:s', strtotime($to));
                $where[] = "created_at <= '$to'";
            }
            if ($eventType) {
                $allowed = ['pageview','click','heartbeat','time_on_page','consent_accepted','consent_declined'];
                if (in_array($eventType, $allowed, true)) {
                    $safe = $this->conn->real_escape_string($eventType);
                    $where[] = "event_type = '$safe'";
                }
            }
            $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
            // total count for pagination
            $countSql = "SELECT COUNT(*) AS total FROM analytics_events $whereSql";
            $countRes = $this->conn->query($countSql);
            $total = 0;
            if ($countRes) { $rowT = $countRes->fetch_assoc(); $total = intval($rowT['total'] ?? 0); }

            $sql = "SELECT id, event_type, session_id, path, element, referrer, country, ip, user_agent, metadata, created_at FROM analytics_events $whereSql ORDER BY id DESC LIMIT $limit OFFSET $offset";
            $res = $this->conn->query($sql);
            $out = [];
            while ($row = $res->fetch_assoc()) {
                $row['metadata'] = $row['metadata'] ? json_decode($row['metadata'], true) : null;
                $out[] = $row;
            }
            // CSV export support
            if (($_GET['format'] ?? '') === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="analytics_events.csv"');
                $outStream = fopen('php://output', 'w');
                fputcsv($outStream, ['id','event_type','session_id','path','element','referrer','country','ip','user_agent','metadata','created_at']);
                foreach ($out as $r) {
                    fputcsv($outStream, [$r['id'],$r['event_type'],$r['session_id'],$r['path'],$r['element'],$r['referrer'],$r['country'],$r['ip'],$r['user_agent'],json_encode($r['metadata']),$r['created_at']]);
                }
                fclose($outStream);
                return;
            }
            echo json_encode(['items' => $out, 'total' => $total, 'page' => $page, 'limit' => $limit]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function stats(): void
    {
        // Limpiar cualquier output previo
        if (ob_get_level()) ob_end_clean();
        
        // Suprimir warnings/notices para asegurar JSON limpio
        error_reporting(E_ERROR | E_PARSE);
        
        require_once __DIR__ . '/../core/auth.php';
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([]);
            exit;
        }

        header('Content-Type: application/json');
        try {
            $this->ensureTableExists();
            $from = $_GET['from'] ?? null;
            $to = $_GET['to'] ?? null;
            $days = intval($_GET['days'] ?? 30);
            $days = max(1, min(365, $days));
            $group = in_array($_GET['group'] ?? 'day', ['day', 'hour'], true) ? $_GET['group'] : 'day';

            $where = [];
            if ($from) { $from = date('Y-m-d H:i:s', strtotime($from)); $where[] = "created_at >= '$from'"; }
            if ($to) { $to = date('Y-m-d H:i:s', strtotime($to)); $where[] = "created_at <= '$to'"; }
            if (!$from && !$to) { $where[] = "created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)"; }
            $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

            // Grouping expression
            $groupExpr = $group === 'hour' ? "DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00')" : 'DATE(created_at)';

            // Pageviews per period
            $filter = $whereSql ? ($whereSql . " AND event_type = 'pageview'") : "WHERE event_type = 'pageview'";
            $sql = "SELECT $groupExpr as period, COUNT(*) as cnt FROM analytics_events $filter GROUP BY $groupExpr ORDER BY period ASC";
            $res = $this->conn->query($sql);
            $pageviews = [];
            while ($r = $res->fetch_assoc()) { $pageviews[] = ['date' => $r['period'], 'cnt' => (int)$r['cnt']]; }

            // Average time on page per period
            $filter = $whereSql ? ($whereSql . " AND event_type = 'time_on_page'") : "WHERE event_type = 'time_on_page'";
            $sql = "SELECT $groupExpr as period, AVG(JSON_EXTRACT(metadata,'$.seconds') + 0) as avg_sec FROM analytics_events $filter GROUP BY $groupExpr ORDER BY period ASC";
            $res = $this->conn->query($sql);
            $avgTime = [];
            while ($r = $res->fetch_assoc()) { $avgTime[] = ['date' => $r['period'], 'avg_sec' => $r['avg_sec']]; }

            // Top clicks
            $filter = $whereSql ? ($whereSql . " AND event_type = 'click'") : "WHERE event_type = 'click'";
            $res = $this->conn->query("SELECT COALESCE(NULLIF(element,''), JSON_UNQUOTE(JSON_EXTRACT(metadata,'$.element'))) AS element, COUNT(*) AS cnt FROM analytics_events $filter GROUP BY COALESCE(NULLIF(element,''), JSON_UNQUOTE(JSON_EXTRACT(metadata,'$.element'))) ORDER BY cnt DESC LIMIT 10");
            $topClicks = [];
            while ($r = $res->fetch_assoc()) { $topClicks[] = $r; }

            // Top referrers
            $filter = $whereSql ? ($whereSql . " AND referrer IS NOT NULL AND referrer != ''") : "WHERE referrer IS NOT NULL AND referrer != ''";
            $res = $this->conn->query("SELECT referrer, COUNT(*) AS cnt FROM analytics_events $filter GROUP BY referrer ORDER BY cnt DESC LIMIT 10");
            $topReferrers = [];
            while ($r = $res->fetch_assoc()) { $topReferrers[] = $r; }

            // Device breakdown
            $res = $this->conn->query("SELECT CASE WHEN (JSON_UNQUOTE(JSON_EXTRACT(metadata,'$.screen.w'))+0) < 768 THEN 'mobile' WHEN (JSON_UNQUOTE(JSON_EXTRACT(metadata,'$.screen.w'))+0) < 1024 THEN 'tablet' ELSE 'desktop' END AS device, COUNT(*) AS cnt FROM analytics_events $whereSql GROUP BY device ORDER BY cnt DESC");
            $deviceBreakdown = [];
            while ($r = $res->fetch_assoc()) { $deviceBreakdown[] = $r; }

            // Top routes
            $res = $this->conn->query("SELECT path, COUNT(*) AS cnt FROM analytics_events $whereSql GROUP BY path ORDER BY cnt DESC LIMIT 10");
            $topRoutes = [];
            while ($r = $res->fetch_assoc()) { $topRoutes[] = $r; }

            // Country breakdown using the new `country` column
            $res = $this->conn->query("SELECT country, COUNT(*) AS cnt FROM analytics_events $whereSql GROUP BY country ORDER BY cnt DESC");
            $countryBreakdown = [];
            while ($r = $res->fetch_assoc()) { $countryBreakdown[] = $r; }

            // CSV export for stats: combined pageviews + avg time per period
            if (($_GET['format'] ?? '') === 'csv') {
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="analytics_stats.csv"');
                $outStream = fopen('php://output', 'w');
                fputcsv($outStream, ['period','pageviews','avg_sec']);
                // index pageviews by date
                $pvMap = [];
                foreach ($pageviews as $p) $pvMap[$p['date']] = $p['cnt'];
                $atMap = [];
                foreach ($avgTime as $p) $atMap[$p['date']] = $p['avg_sec'];
                $dates = array_unique(array_merge(array_keys($pvMap), array_keys($atMap)));
                sort($dates);
                foreach ($dates as $d) {
                    fputcsv($outStream, [$d, $pvMap[$d] ?? 0, $atMap[$d] ?? 0]);
                }
                fclose($outStream);
                return;
            }

            echo json_encode([
                'pageviews'   => $pageviews,
                'avg_time'    => $avgTime,
                'top_clicks'  => $topClicks,
                'top_referrers' => $topReferrers,
                'device_breakdown' => $deviceBreakdown,
                'top_routes' => $topRoutes,
                'country_breakdown' => $countryBreakdown,
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

