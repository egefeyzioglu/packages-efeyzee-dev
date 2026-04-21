<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $config['site_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gb: {
                            bg:    '#282828',
                            bg0h:  '#1d2021',
                            bg0s:  '#32302f',
                            bg1:   '#3c3836',
                            bg2:   '#504945',
                            bg3:   '#665c54',
                            bg4:   '#7c6f64',
                            fg:    '#ebdbb2',
                            fg0:   '#fbf1c7',
                            fg1:   '#ebdbb2',
                            fg2:   '#d5c4a1',
                            fg3:   '#bdae93',
                            fg4:   '#a89984',
                            gray:  '#928374',
                            red:   '#cc241d',
                            'red+':    '#fb4934',
                            green: '#98971a',
                            'green+':  '#b8bb26',
                            yellow:    '#d79921',
                            'yellow+': '#fabd2f',
                            blue:  '#458588',
                            'blue+':   '#83a598',
                            purple:    '#b16286',
                            'purple+': '#d3869b',
                            aqua:  '#689d6a',
                            'aqua+':   '#8ec07c',
                            orange:    '#d65d0e',
                            'orange+': '#fe8019',
                        },
                    },
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
                @apply bg-gb-bg text-gb-fg antialiased;
            }
            a {
                @apply text-gb-aqua+ hover:text-gb-green+ transition-colors;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Navigation -->
    <nav class="border-b border-gb-bg1 sticky top-0 z-50 bg-gb-bg">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-3 text-gb-fg0 hover:text-gb-aqua+ no-underline">
                <svg class="w-7 h-7 text-gb-aqua+" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <span class="font-semibold text-lg"><?= htmlspecialchars($config['site_name']) ?></span>
            </a>
            <a href="/" class="text-sm text-gb-fg4 hover:text-gb-aqua+">All Packages</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl mx-auto w-full px-4 sm:px-6 py-8">
        <?= $content ?>
    </main>

</body>
</html>
