<?php

declare(strict_types=1);

// ── Bootstrap ────────────────────────────────────────────────────────

$config = require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/RepoReader.php';

$reader = new RepoReader($config['repo_path']);

// ── Routing ──────────────────────────────────────────────────────────

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri  = rtrim($uri, '/') ?: '/';

// Helper: render a template inside the layout.
function render(string $template, array $vars = []): void
{
    global $config;
    extract($vars);
    ob_start();
    require __DIR__ . '/../src/templates/' . $template . '.php';
    $content = ob_get_clean();
    require __DIR__ . '/../src/templates/layout.php';
}

// ── Routes ───────────────────────────────────────────────────────────

// Home / package listing
if ($uri === '/') {
    $dist     = $_GET['dist'] ?? null;
    $packages = $dist
        ? $reader->getPackages($dist)
        : $reader->getAllPackages();

    $distributions = $reader->getDistributions();

    render('home', [
        'pageTitle'     => $config['site_name'],
        'packages'      => $packages,
        'distributions' => $distributions,
        'currentDist'   => $dist,
        'searchQuery'   => '',
    ]);
    exit;
}

// Search
if ($uri === '/search') {
    $query    = trim($_GET['q'] ?? '');
    $packages = $reader->searchPackages($query);
    $distributions = $reader->getDistributions();

    render('home', [
        'pageTitle'     => 'Search: ' . $query . ' — ' . $config['site_name'],
        'packages'      => $packages,
        'distributions' => $distributions,
        'currentDist'   => null,
        'searchQuery'   => $query,
    ]);
    exit;
}

// Package detail: /package/{name}
if (preg_match('#^/package/([A-Za-z0-9][A-Za-z0-9.+\-]+)$#', $uri, $m)) {
    $pkgName       = $m[1];
    $distributions = $reader->getDistributions();
    $allEntries    = [];

    foreach (array_keys($distributions) as $dist) {
        $found = $reader->getPackage($dist, $pkgName);
        if ($found !== null) {
            $allEntries = array_merge($allEntries, $found);
        }
    }

    if ($allEntries === []) {
        http_response_code(404);
        render('404', [
            'pageTitle' => 'Not Found — ' . $config['site_name'],
            'pkgName'   => $pkgName,
        ]);
        exit;
    }

    // Group by version+arch, keep the richest entry per combination.
    $grouped = [];
    foreach ($allEntries as $entry) {
        $key = ($entry['Version'] ?? '?') . ':' . ($entry['Architecture'] ?? 'all');
        if (!isset($grouped[$key])) {
            $grouped[$key] = $entry;
        }
    }

    // Sort by version descending.
    usort($grouped, function (array $a, array $b) {
        return version_compare($b['Version'] ?? '0', $a['Version'] ?? '0');
    });

    $primary = $grouped[0]; // Latest version for the heading.

    // Collect all package names in the repo for dependency linking.
    $allPackageNames = array_unique(array_column($reader->getAllPackages(), 'Package'));

    render('package', [
        'pageTitle'       => $pkgName . ' — ' . $config['site_name'],
        'primary'         => $primary,
        'entries'         => array_values($grouped),
        'allPackageNames' => $allPackageNames,
    ]);
    exit;
}

// ── 404 fallback ─────────────────────────────────────────────────────
http_response_code(404);
render('404', [
    'pageTitle' => 'Not Found — ' . $config['site_name'],
    'pkgName'   => null,
]);
