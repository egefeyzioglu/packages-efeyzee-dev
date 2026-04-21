<?php
/**
 * @var array       $config
 * @var string|null $pkgName
 */
?>

<div class="text-center py-20">
    <div class="text-6xl font-bold text-gb-bg3 mb-4">404</div>
    <h1 class="text-xl text-gb-fg2 mb-2">
        <?php if ($pkgName !== null): ?>
            Package <span class="font-mono text-gb-yellow+"><?= htmlspecialchars($pkgName) ?></span> not found
        <?php else: ?>
            Page not found
        <?php endif; ?>
    </h1>
    <p class="text-gb-gray mb-6">The page you&rsquo;re looking for doesn&rsquo;t exist.</p>
    <a href="/" class="inline-flex items-center gap-2 px-4 py-2 bg-gb-bg1 text-gb-fg3 hover:text-gb-fg0 hover:bg-gb-bg2 transition-colors no-underline text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Back to packages
    </a>
</div>
