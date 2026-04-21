<?php
/**
 * @var array  $config
 * @var array  $packages
 * @var array  $distributions
 * @var string|null $currentDist
 * @var string $searchQuery
 */

// Build the apt source line from the first distribution.
$distNames = array_keys($distributions);
$firstDist = $distNames[0] ?? 'stable';
$firstRelease = $distributions[$firstDist] ?? [];
$components = $firstRelease['Components'] ?? 'main';
$aptLine = "deb https://{$config['site_name']} {$firstDist} {$components}";
?>

<!-- Hero / apt source line -->
<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-white mb-2"><?= htmlspecialchars($config['site_desc']) ?></h1>
    <p class="text-gray-400 mb-4">Browse and download packages from this repository.</p>

    <div class="bg-gray-900 border border-gray-800 rounded-lg p-4 flex items-center justify-between gap-4">
        <code class="font-mono text-sm text-teal-300 break-all select-all" id="apt-line"><?= htmlspecialchars($aptLine) ?></code>
        <button
            onclick="copyAptLine()"
            class="shrink-0 px-3 py-1.5 text-xs font-medium rounded-md bg-gray-800 text-gray-300 hover:bg-gray-700 hover:text-white border border-gray-700 transition-all cursor-pointer"
            id="copy-btn"
            title="Copy to clipboard"
        >
            <span id="copy-label">Copy</span>
        </button>
    </div>
</div>

<!-- Search + Filters -->
<div class="flex flex-col sm:flex-row gap-4 mb-6">
    <form action="/search" method="get" class="flex-1 flex gap-2">
        <input
            type="text"
            name="q"
            id="search-input"
            value="<?= htmlspecialchars($searchQuery) ?>"
            placeholder="Search packages&hellip;"
            class="flex-1 bg-gray-900 border border-gray-700 rounded-lg px-4 py-2.5 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500/30 transition-colors"
        >
        <button
            type="submit"
            class="px-4 py-2.5 text-sm font-medium rounded-lg bg-teal-600 text-white hover:bg-teal-500 transition-colors cursor-pointer"
        >Search</button>
    </form>

    <?php if (count($distributions) > 1): ?>
    <div class="flex items-center gap-2 flex-wrap">
        <a
            href="/"
            class="px-3 py-1.5 text-xs font-medium rounded-full border transition-colors no-underline <?= $currentDist === null ? 'bg-teal-600 text-white border-teal-600' : 'border-gray-700 text-gray-400 hover:text-white hover:border-gray-500' ?>"
        >All</a>
        <?php foreach ($distNames as $d): ?>
        <a
            href="/?dist=<?= urlencode($d) ?>"
            class="px-3 py-1.5 text-xs font-medium rounded-full border transition-colors no-underline <?= $currentDist === $d ? 'bg-teal-600 text-white border-teal-600' : 'border-gray-700 text-gray-400 hover:text-white hover:border-gray-500' ?>"
        ><?= htmlspecialchars($d) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($searchQuery !== ''): ?>
<p class="text-sm text-gray-400 mb-4">
    Showing results for <span class="text-teal-400 font-medium">&ldquo;<?= htmlspecialchars($searchQuery) ?>&rdquo;</span>
    &mdash; <?= count($packages) ?> package<?= count($packages) !== 1 ? 's' : '' ?> found.
    <a href="/" class="ml-2">Clear</a>
</p>
<?php endif; ?>

<!-- Package Table -->
<?php if ($packages === []): ?>
<div class="text-center py-16">
    <p class="text-gray-500 text-lg">No packages found.</p>
    <?php if ($searchQuery !== ''): ?>
    <a href="/" class="mt-2 inline-block text-sm">Back to all packages</a>
    <?php endif; ?>
</div>
<?php else: ?>

<!-- Mobile: card layout / Desktop: table -->
<div class="overflow-x-auto">
    <table class="w-full text-sm" id="package-table">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-gray-500 border-b border-gray-800">
                <th class="py-3 pr-4 font-medium">Package</th>
                <th class="py-3 pr-4 font-medium hidden sm:table-cell">Version</th>
                <th class="py-3 pr-4 font-medium hidden md:table-cell">Arch</th>
                <th class="py-3 pr-4 font-medium">Description</th>
                <th class="py-3 font-medium text-right hidden sm:table-cell">Size</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
            <?php foreach ($packages as $pkg):
                $name = $pkg['Package'] ?? '';
                $version = $pkg['Version'] ?? '';
                $arch = $pkg['Architecture'] ?? '';
                // Description: first line only.
                $desc = $pkg['Description'] ?? '';
                $shortDesc = explode("\n", $desc)[0];
                $size = $pkg['Size'] ?? '';
                $sizeFormatted = $size !== '' ? formatBytes((int)$size) : '';
            ?>
            <tr class="group hover:bg-gray-900/50 transition-colors package-row"
                data-name="<?= htmlspecialchars(mb_strtolower($name)) ?>"
                data-desc="<?= htmlspecialchars(mb_strtolower($shortDesc)) ?>">
                <td class="py-3 pr-4">
                    <a href="/package/<?= urlencode($name) ?>" class="font-mono font-semibold text-teal-400 hover:text-teal-300 no-underline">
                        <?= htmlspecialchars($name) ?>
                    </a>
                    <span class="sm:hidden text-xs text-gray-500 ml-2 font-mono"><?= htmlspecialchars($version) ?></span>
                </td>
                <td class="py-3 pr-4 font-mono text-gray-400 hidden sm:table-cell"><?= htmlspecialchars($version) ?></td>
                <td class="py-3 pr-4 hidden md:table-cell">
                    <span class="inline-block px-2 py-0.5 text-xs rounded bg-gray-800 text-gray-300 font-mono"><?= htmlspecialchars($arch) ?></span>
                </td>
                <td class="py-3 pr-4 text-gray-400 max-w-md truncate"><?= htmlspecialchars($shortDesc) ?></td>
                <td class="py-3 text-right text-gray-500 font-mono text-xs hidden sm:table-cell whitespace-nowrap"><?= $sizeFormatted ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p class="mt-4 text-xs text-gray-600">
    <?= count($packages) ?> package<?= count($packages) !== 1 ? 's' : '' ?> available
</p>

<?php endif; ?>

<script>
// Copy apt line to clipboard.
function copyAptLine() {
    const text = document.getElementById('apt-line').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const label = document.getElementById('copy-label');
        label.textContent = 'Copied!';
        setTimeout(() => { label.textContent = 'Copy'; }, 2000);
    });
}

// Live client-side filtering.
(function() {
    const input = document.getElementById('search-input');
    const rows = document.querySelectorAll('.package-row');
    if (!input || rows.length === 0) return;

    input.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        rows.forEach(row => {
            const name = row.dataset.name || '';
            const desc = row.dataset.desc || '';
            const match = q === '' || name.includes(q) || desc.includes(q);
            row.style.display = match ? '' : 'none';
        });
    });
})();
</script>

<?php
function formatBytes(int $bytes): string
{
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}
?>
