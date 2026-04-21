<?php
/**
 * @var array       $config
 * @var string|null $pkgName
 */
?>

<div class="text-center py-20">
    <div class="text-6xl font-bold text-gray-800 mb-4">404</div>
    <h1 class="text-xl text-gray-300 mb-2">
        <?php if ($pkgName !== null): ?>
            Package <span class="font-mono text-teal-400"><?= htmlspecialchars($pkgName) ?></span> not found
        <?php else: ?>
            Page not found
        <?php endif; ?>
    </h1>
    <p class="text-gray-500 mb-6">The page you&rsquo;re looking for doesn&rsquo;t exist.</p>
    <a href="/" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 text-gray-300 hover:text-white hover:bg-gray-700 border border-gray-700 transition-colors no-underline text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Back to packages
    </a>
</div>
