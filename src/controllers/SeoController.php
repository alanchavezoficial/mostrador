<?php
// src/controllers/SeoController.php

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../core/db.php';

class SeoController
{
    private mysqli $conn;
    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function sitemap(): void
    {
        header('Content-Type: application/xml; charset=UTF-8');
        $urls = [];
        $base = rtrim(BASE_URL, '/');
        $now  = date('c');
        $urls[] = [ 'loc' => $base, 'lastmod' => $now ];
        $urls[] = [ 'loc' => $base . '/productos', 'lastmod' => $now ];

        $prodRes = $this->conn->query("SELECT id, creado_en FROM products ORDER BY creado_en DESC LIMIT 200");
        if ($prodRes) {
            while ($p = $prodRes->fetch_assoc()) {
                $last = $p['creado_en'] ?? date('Y-m-d');
                $urls[] = [ 'loc' => $base . '/product?id=' . $p['id'], 'lastmod' => date('c', strtotime($last)) ];
            }
        }

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        foreach ($urls as $u) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
            if (!empty($u['lastmod'])) {
                echo "    <lastmod>" . $u['lastmod'] . "</lastmod>\n";
            }
            echo "    <changefreq>weekly</changefreq>\n";
            echo "  </url>\n";
        }
        echo "</urlset>";
    }
}
