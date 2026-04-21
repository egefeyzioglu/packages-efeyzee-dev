<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $config['site_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        mono: ['JetBrains Mono', 'Fira Code', 'ui-monospace', 'SFMono-Regular', 'monospace'],
                    },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        @layer base {
            body {
                @apply bg-gray-950 text-gray-200 antialiased;
            }
            a {
                @apply text-teal-400 hover:text-teal-300 transition-colors;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Navigation -->
    <nav class="border-b border-gray-800 bg-gray-950/80 backdrop-blur-sm sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 text-white hover:text-teal-400 no-underline">
                <svg class="w-7 h-7 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <span class="font-semibold text-lg"><?= htmlspecialchars($config['site_name']) ?></span>
            </a>
            <a href="/" class="text-sm text-gray-400 hover:text-teal-400">All Packages</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 py-8">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="border-t border-gray-800 mt-auto">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 text-center text-sm text-gray-500">
            <?= htmlspecialchars($config['site_name']) ?> &mdash; APT package repository
        </div>
    </footer>

</body>
</html>
