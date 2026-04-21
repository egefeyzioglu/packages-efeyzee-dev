<?php
/**
 * @var array  $config
 * @var array  $primary        Latest version entry.
 * @var array  $entries        All version/arch entries for this package.
 * @var array  $allPackageNames  All package names in the repo (for dependency linking).
 */

$name        = $primary['Package'] ?? '';
$version     = $primary['Version'] ?? '';
$arch        = $primary['Architecture'] ?? '';
$maintainer  = $primary['Maintainer'] ?? '';
$section     = $primary['Section'] ?? '';
$priority    = $primary['Priority'] ?? '';
$homepage    = $primary['Homepage'] ?? '';
$instSize    = $primary['Installed-Size'] ?? '';
$depends     = $primary['Depends'] ?? '';
$sha256      = $primary['SHA256'] ?? '';
$description = $primary['Description'] ?? '';

// Split description into short (first line) and extended (rest).
$descLines = explode("\n", $description);
$shortDesc = array_shift($descLines);
$longDesc  = implode("\n", $descLines);

/**
 * Render a dependency string with links to packages in the repo.
 */
function renderDeps(string $raw, array $knownPackages, string $baseUrl): string
{
    if ($raw === '') return '<span class="text-gray-500">None</span>';

    $parts = preg_split('/\s*,\s*/', $raw);
    $html  = [];

    foreach ($parts as $part) {
        // Handle alternatives (|)
        $alts = preg_split('/\s*\|\s*/', $part);
        $rendered = [];
        foreach ($alts as $alt) {
            // Extract package name (possibly with version constraint).
            if (preg_match('/^([a-z0-9][a-z0-9.+\-]+)(.*)$/i', trim($alt), $m)) {
                $depName    = $m[1];
                $constraint = htmlspecialchars($m[2]);
                if (in_array($depName, $knownPackages, true)) {
                    $rendered[] = '<a href="' . $baseUrl . '/package/' . urlencode($depName)
                        . '" class="text-teal-400 hover:text-teal-300">'
                        . htmlspecialchars($depName) . '</a>'
                        . '<span class="text-gray-500">' . $constraint . '</span>';
                } else {
                    $rendered[] = '<span class="text-gray-300">' . htmlspecialchars($depName) . '</span>'
                        . '<span class="text-gray-500">' . $constraint . '</span>';
                }
            } else {
                $rendered[] = htmlspecialchars(trim($alt));
            }
        }
        $html[] = implode(' <span class="text-gray-600">|</span> ', $rendered);
    }

    return implode('<span class="text-gray-600">,</span> ', $html);
}

function formatBytesDetail(int $bytes): string
{
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}
?>

<!-- Breadcrumb -->
<nav class="text-sm text-gray-500 mb-6">
    <a href="/" class="hover:text-teal-400">Packages</a>
    <span class="mx-2">/</span>
    <span class="text-gray-300"><?= htmlspecialchars($name) ?></span>
</nav>

<!-- Package Header -->
<div class="mb-8">
    <div class="flex flex-wrap items-center gap-3 mb-2">
        <h1 class="text-2xl sm:text-3xl font-bold text-white font-mono"><?= htmlspecialchars($name) ?></h1>
        <span class="px-2.5 py-1 text-xs rounded-full bg-teal-900/50 text-teal-300 border border-teal-800 font-mono">
            <?= htmlspecialchars($version) ?>
        </span>
        <span class="px-2.5 py-1 text-xs rounded-full bg-gray-800 text-gray-300 border border-gray-700 font-mono">
            <?= htmlspecialchars($arch) ?>
        </span>
    </div>
    <p class="text-gray-400 text-lg"><?= htmlspecialchars($shortDesc) ?></p>
</div>

<!-- Download Button -->
<?php
    $filename = $primary['Filename'] ?? '';
    $fileSize = $primary['Size'] ?? '';
    $downloadUrl = $filename !== '' ? rtrim($config['base_url'], '/') . '/' . ltrim($filename, '/') : '';
?>
<?php if ($downloadUrl !== ''): ?>
<div class="mb-8">
    <a
        href="<?= htmlspecialchars($downloadUrl) ?>"
        class="inline-flex items-center gap-2 px-5 py-3 rounded-lg bg-teal-600 text-white font-medium hover:bg-teal-500 transition-colors no-underline"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        Download .deb
        <?php if ($fileSize !== ''): ?>
        <span class="text-teal-200 text-sm">(<?= formatBytesDetail((int)$fileSize) ?>)</span>
        <?php endif; ?>
    </a>
</div>
<?php endif; ?>

<!-- Metadata Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

    <div class="bg-gray-900 border border-gray-800 rounded-lg p-5 space-y-4">
        <h2 class="text-sm uppercase tracking-wider text-gray-500 font-medium">Package Information</h2>

        <dl class="space-y-3 text-sm">
            <?php if ($section !== ''): ?>
            <div class="flex justify-between">
                <dt class="text-gray-500">Section</dt>
                <dd class="text-gray-300"><?= htmlspecialchars($section) ?></dd>
            </div>
            <?php endif; ?>

            <?php if ($priority !== ''): ?>
            <div class="flex justify-between">
                <dt class="text-gray-500">Priority</dt>
                <dd class="text-gray-300"><?= htmlspecialchars($priority) ?></dd>
            </div>
            <?php endif; ?>

            <?php if ($instSize !== ''): ?>
            <div class="flex justify-between">
                <dt class="text-gray-500">Installed Size</dt>
                <dd class="text-gray-300 font-mono"><?= formatBytesDetail((int)$instSize * 1024) ?></dd>
            </div>
            <?php endif; ?>

            <?php if ($maintainer !== ''): ?>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500 shrink-0">Maintainer</dt>
                <dd class="text-gray-300 text-right truncate"><?= htmlspecialchars($maintainer) ?></dd>
            </div>
            <?php endif; ?>

            <?php if ($homepage !== ''): ?>
            <div class="flex justify-between gap-4">
                <dt class="text-gray-500 shrink-0">Homepage</dt>
                <dd class="truncate">
                    <a href="<?= htmlspecialchars($homepage) ?>" target="_blank" rel="noopener" class="text-teal-400 hover:text-teal-300"><?= htmlspecialchars($homepage) ?></a>
                </dd>
            </div>
            <?php endif; ?>
        </dl>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-lg p-5 space-y-4">
        <h2 class="text-sm uppercase tracking-wider text-gray-500 font-medium">Dependencies</h2>
        <div class="text-sm font-mono leading-relaxed flex flex-wrap gap-x-1 gap-y-0.5">
            <?= renderDeps($depends, $allPackageNames, '') ?>
        </div>
    </div>

</div>

<!-- Checksum -->
<?php if ($sha256 !== ''): ?>
<div class="mb-8 bg-gray-900 border border-gray-800 rounded-lg p-5">
    <h2 class="text-sm uppercase tracking-wider text-gray-500 font-medium mb-3">Verification</h2>
    <div class="flex items-center gap-3">
        <span class="text-xs text-gray-500 uppercase shrink-0">SHA-256</span>
        <code class="font-mono text-xs text-gray-400 break-all select-all"><?= htmlspecialchars($sha256) ?></code>
    </div>
</div>
<?php endif; ?>

<!-- Extended Description -->
<?php if (trim($longDesc) !== ''): ?>
<div class="mb-8 bg-gray-900 border border-gray-800 rounded-lg p-5">
    <h2 class="text-sm uppercase tracking-wider text-gray-500 font-medium mb-3">Description</h2>
    <div class="text-sm text-gray-300 whitespace-pre-line leading-relaxed"><?= htmlspecialchars(trim($longDesc)) ?></div>
</div>
<?php endif; ?>

<!-- All Versions / Architectures -->
<?php if (count($entries) > 1): ?>
<div class="mb-8 bg-gray-900 border border-gray-800 rounded-lg p-5">
    <h2 class="text-sm uppercase tracking-wider text-gray-500 font-medium mb-4">All Available Versions</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wider text-gray-500 border-b border-gray-800">
                    <th class="py-2 pr-4 font-medium">Version</th>
                    <th class="py-2 pr-4 font-medium">Architecture</th>
                    <th class="py-2 pr-4 font-medium">Distribution</th>
                    <th class="py-2 pr-4 font-medium">Size</th>
                    <th class="py-2 font-medium text-right">Download</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/50">
                <?php foreach ($entries as $entry):
                    $eVer   = $entry['Version'] ?? '';
                    $eArch  = $entry['Architecture'] ?? '';
                    $eDist  = $entry['_dist'] ?? '';
                    $eSize  = $entry['Size'] ?? '';
                    $eFile  = $entry['Filename'] ?? '';
                    $eUrl   = $eFile !== '' ? rtrim($config['base_url'], '/') . '/' . ltrim($eFile, '/') : '';
                ?>
                <tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="py-2.5 pr-4 font-mono text-gray-300"><?= htmlspecialchars($eVer) ?></td>
                    <td class="py-2.5 pr-4">
                        <span class="inline-block px-2 py-0.5 text-xs rounded bg-gray-800 text-gray-300 font-mono"><?= htmlspecialchars($eArch) ?></span>
                    </td>
                    <td class="py-2.5 pr-4 text-gray-400"><?= htmlspecialchars($eDist) ?></td>
                    <td class="py-2.5 pr-4 text-gray-500 font-mono text-xs"><?= $eSize !== '' ? formatBytesDetail((int)$eSize) : '' ?></td>
                    <td class="py-2.5 text-right">
                        <?php if ($eUrl !== ''): ?>
                        <a href="<?= htmlspecialchars($eUrl) ?>" class="text-teal-400 hover:text-teal-300 text-xs font-medium no-underline">.deb</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Install instruction -->
<div class="bg-gray-900 border border-gray-800 rounded-lg p-5">
    <h2 class="text-sm uppercase tracking-wider text-gray-500 font-medium mb-3">Install</h2>
    <code class="font-mono text-sm text-teal-300 select-all">sudo apt install <?= htmlspecialchars($name) ?></code>
</div>
