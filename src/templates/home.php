<?php
/**
 * @var array  $config
 * @var array  $packages
 * @var array  $distributions
 * @var string|null $currentDist
 * @var string $searchQuery
 */

// Build the setup commands from the first distribution.
$distNames = array_keys($distributions);
$firstDist = $distNames[0] ?? 'stable';
$firstRelease = $distributions[$firstDist] ?? [];
$components = $firstRelease['Components'] ?? 'main';

$keyUrl      = $config['signing_key_url'];
$keyringPath = $config['keyring_path'];
$sourcesList = $config['sources_list'];
$siteName    = $config['site_name'];

$setupCmd = "curl -fsSL {$keyUrl} | sudo gpg --dearmor -o {$keyringPath}\n"
          . "echo \"deb [signed-by={$keyringPath}] https://{$siteName} {$firstDist} {$components}\" | sudo tee /etc/apt/sources.list.d/{$sourcesList}";
?>

<!-- Hero / setup commands -->
<div class="mb-8">
    <h1 class="text-2xl sm:text-3xl font-bold text-gb-fg0 mb-2"><?= htmlspecialchars($config['site_desc']) ?></h1>
    <p class="text-gb-fg4 mb-4">Browse and download packages from this repository.</p>

    <div class="relative">
        <pre class="font-mono text-sm text-gb-green+ bg-gb-bg0h border border-gb-bg2 p-4 overflow-x-auto select-all" id="setup-cmd"><?= htmlspecialchars($setupCmd) ?></pre>
        <button
            onclick="copySetup()"
            class="absolute top-2 right-2 px-2.5 py-1 text-xs font-medium bg-gb-bg2 text-gb-fg3 hover:text-gb-fg0 hover:bg-gb-bg3 transition-colors cursor-pointer"
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
            class="flex-1 bg-gb-bg0h border border-gb-bg2 px-4 py-2.5 text-sm text-gb-fg placeholder-gb-bg4 focus:outline-none focus:border-gb-aqua transition-colors"
        >
        <button
            type="submit"
            class="px-4 py-2.5 text-sm font-medium bg-gb-aqua text-gb-bg font-bold hover:bg-gb-aqua+ transition-colors cursor-pointer"
        >Search</button>
    </form>

    <?php if (count($distributions) > 1): ?>
    <div class="flex items-center gap-2 flex-wrap">
        <a
            href="/"
            class="px-3 py-1.5 text-xs font-medium border transition-colors no-underline <?= $currentDist === null ? 'bg-gb-aqua text-gb-bg border-gb-aqua' : 'border-gb-bg2 text-gb-fg4 hover:text-gb-fg0 hover:border-gb-bg3' ?>"
        >All</a>
        <?php foreach ($distNames as $d): ?>
        <a
            href="/?dist=<?= urlencode($d) ?>"
            class="px-3 py-1.5 text-xs font-medium border transition-colors no-underline <?= $currentDist === $d ? 'bg-gb-aqua text-gb-bg border-gb-aqua' : 'border-gb-bg2 text-gb-fg4 hover:text-gb-fg0 hover:border-gb-bg3' ?>"
        ><?= htmlspecialchars($d) ?></a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php if ($searchQuery !== ''): ?>
<p class="text-sm text-gb-fg4 mb-4">
    Showing results for <span class="text-gb-yellow+ font-medium">&ldquo;<?= htmlspecialchars($searchQuery) ?>&rdquo;</span>
    &mdash; <?= count($packages) ?> package<?= count($packages) !== 1 ? 's' : '' ?> found.
    <a href="/" class="ml-2">Clear</a>
</p>
<?php endif; ?>

<!-- Package Table -->
<?php if ($packages === []): ?>
<div class="text-center py-16">
    <p class="text-gb-gray text-lg">No packages found.</p>
    <?php if ($searchQuery !== ''): ?>
    <a href="/" class="mt-2 inline-block text-sm">Back to all packages</a>
    <?php endif; ?>
</div>
<?php else: ?>

<div class="overflow-x-auto">
    <table class="w-full text-sm" id="package-table">
        <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-gb-gray border-b border-gb-bg2">
                <th class="py-3 pr-4 font-medium">Package</th>
                <th class="py-3 pr-4 font-medium hidden sm:table-cell">Version</th>
                <th class="py-3 pr-4 font-medium hidden md:table-cell">Arch</th>
                <th class="py-3 pr-4 font-medium">Description</th>
                <th class="py-3 font-medium text-right hidden sm:table-cell">Size</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gb-bg1">
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
            <tr class="group hover:bg-gb-bg0s transition-colors package-row"
                data-name="<?= htmlspecialchars(mb_strtolower($name)) ?>"
                data-desc="<?= htmlspecialchars(mb_strtolower($shortDesc)) ?>">
                <td class="py-3 pr-4">
                    <a href="/package/<?= urlencode($name) ?>" class="font-mono font-semibold no-underline">
                        <?= htmlspecialchars($name) ?>
                    </a>
                    <span class="sm:hidden text-xs text-gb-gray ml-2 font-mono"><?= htmlspecialchars($version) ?></span>
                </td>
                <td class="py-3 pr-4 font-mono text-gb-fg3 hidden sm:table-cell"><?= htmlspecialchars($version) ?></td>
                <td class="py-3 pr-4 hidden md:table-cell">
                    <span class="font-mono text-xs text-gb-fg4"><?= htmlspecialchars($arch) ?></span>
                </td>
                <td class="py-3 pr-4 text-gb-fg3 max-w-md truncate"><?= htmlspecialchars($shortDesc) ?></td>
                <td class="py-3 text-right text-gb-gray font-mono text-xs hidden sm:table-cell whitespace-nowrap"><?= $sizeFormatted ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p class="mt-4 text-xs text-gb-bg4">
    <?= count($packages) ?> package<?= count($packages) !== 1 ? 's' : '' ?> available
</p>

<?php endif; ?>

<script>
// Copy setup commands to clipboard.
function copySetup() {
    const text = document.getElementById('setup-cmd').textContent;
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
