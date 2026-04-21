<?php

class RepoReader
{
    private string $repoPath;

    public function __construct(string $repoPath)
    {
        $this->repoPath = rtrim($repoPath, '/');
    }

    /**
     * Scan dists/ for available distributions and parse their Release files.
     *
     * @return array<string, array<string, string>>  Keyed by codename.
     */
    public function getDistributions(): array
    {
        $distsDir = $this->repoPath . '/dists';
        if (!is_dir($distsDir)) {
            return [];
        }

        $dists = [];
        foreach (scandir($distsDir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $distPath = $distsDir . '/' . $entry;
            if (!is_dir($distPath)) {
                continue;
            }

            $release = $this->parseReleaseFile($distPath . '/Release');
            $release['Codename'] = $release['Codename'] ?? $entry;
            $dists[$entry] = $release;
        }

        return $dists;
    }

    /**
     * Get packages for a distribution, optionally filtered by component and architecture.
     *
     * @return array<int, array<string, string>>
     */
    public function getPackages(string $dist, ?string $component = null, ?string $arch = null): array
    {
        $basePath = $this->repoPath . '/dists/' . $dist;
        if (!is_dir($basePath)) {
            return [];
        }

        $components = $component !== null
            ? [$component]
            : $this->discoverComponents($basePath);

        $packages = [];

        foreach ($components as $comp) {
            $compPath = $basePath . '/' . $comp;
            if (!is_dir($compPath)) {
                continue;
            }

            $arches = $arch !== null
                ? [$arch]
                : $this->discoverArchitectures($compPath);

            foreach ($arches as $a) {
                $packagesFile = $compPath . '/binary-' . $a;
                $parsed = $this->parsePackagesFile($packagesFile);

                // Tag each entry with its distribution and component.
                foreach ($parsed as &$pkg) {
                    $pkg['_dist']      = $dist;
                    $pkg['_component'] = $comp;
                }
                unset($pkg);

                $packages = array_merge($packages, $parsed);
            }
        }

        return $packages;
    }

    /**
     * Find a single package by name within a distribution (all components/arches).
     * Returns all matching entries (different versions/arches) or null if not found.
     *
     * @return array<int, array<string, string>>|null
     */
    public function getPackage(string $dist, string $name): ?array
    {
        $all = $this->getPackages($dist);
        $matches = array_values(array_filter($all, function (array $pkg) use ($name) {
            return ($pkg['Package'] ?? '') === $name;
        }));

        return $matches !== [] ? $matches : null;
    }

    /**
     * Merge packages across all distributions, deduplicated by name
     * (keeps the entry with the highest version per package name).
     *
     * @return array<int, array<string, string>>
     */
    public function getAllPackages(): array
    {
        $dists = $this->getDistributions();
        $byName = [];

        foreach (array_keys($dists) as $dist) {
            foreach ($this->getPackages($dist) as $pkg) {
                $name = $pkg['Package'] ?? '';
                if ($name === '') {
                    continue;
                }

                $key = $name . ':' . ($pkg['Architecture'] ?? 'all');

                if (!isset($byName[$key])
                    || version_compare($pkg['Version'] ?? '0', $byName[$key]['Version'] ?? '0', '>')) {
                    $byName[$key] = $pkg;
                }
            }
        }

        $packages = array_values($byName);
        usort($packages, function (array $a, array $b) {
            return strcasecmp($a['Package'] ?? '', $b['Package'] ?? '');
        });

        return $packages;
    }

    /**
     * Search packages by name or description substring (case-insensitive).
     *
     * @return array<int, array<string, string>>
     */
    public function searchPackages(string $query): array
    {
        $query = mb_strtolower(trim($query));
        if ($query === '') {
            return $this->getAllPackages();
        }

        return array_values(array_filter($this->getAllPackages(), function (array $pkg) use ($query) {
            $name = mb_strtolower($pkg['Package'] ?? '');
            $desc = mb_strtolower($pkg['Description'] ?? '');
            return str_contains($name, $query) || str_contains($desc, $query);
        }));
    }

    // ──────────────────────────────────────────────
    //  Internal helpers
    // ──────────────────────────────────────────────

    /**
     * Parse a Release file into a key-value array.
     */
    private function parseReleaseFile(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $data = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES) as $line) {
            // Skip hash sections (MD5Sum, SHA1, SHA256 value lists).
            if ($line !== '' && ($line[0] === ' ' || $line[0] === "\t")) {
                continue;
            }
            if (str_contains($line, ':')) {
                [$key, $value] = explode(':', $line, 2);
                $data[trim($key)] = trim($value);
            }
        }

        return $data;
    }

    /**
     * Discover component directories under a distribution path.
     *
     * @return string[]
     */
    private function discoverComponents(string $distPath): array
    {
        $components = [];
        foreach (scandir($distPath) as $entry) {
            if ($entry === '.' || $entry === '..' || !is_dir($distPath . '/' . $entry)) {
                continue;
            }
            // A component directory contains binary-* subdirs.
            $sub = $distPath . '/' . $entry;
            foreach (scandir($sub) as $child) {
                if (str_starts_with($child, 'binary-')) {
                    $components[] = $entry;
                    break;
                }
            }
        }
        return $components;
    }

    /**
     * Discover architectures available under a component path.
     *
     * @return string[]
     */
    private function discoverArchitectures(string $compPath): array
    {
        $arches = [];
        foreach (scandir($compPath) as $entry) {
            if (str_starts_with($entry, 'binary-') && is_dir($compPath . '/' . $entry)) {
                $arches[] = substr($entry, strlen('binary-'));
            }
        }
        return $arches;
    }

    /**
     * Parse a Packages or Packages.gz file into an array of package stanzas.
     *
     * @param  string  $dir  Directory that contains Packages.gz / Packages.
     * @return array<int, array<string, string>>
     */
    private function parsePackagesFile(string $dir): array
    {
        $gzPath    = $dir . '/Packages.gz';
        $plainPath = $dir . '/Packages';

        $lines = null;

        if (is_file($gzPath)) {
            $raw = file_get_contents('compress.zlib://' . $gzPath);
            if ($raw !== false) {
                $lines = explode("\n", $raw);
            }
        }

        if ($lines === null && is_file($plainPath)) {
            $lines = file($plainPath, FILE_IGNORE_NEW_LINES);
        }

        if ($lines === null) {
            return [];
        }

        return $this->parseControlStanzas($lines);
    }

    /**
     * Parse Debian control-file formatted lines into stanzas.
     *
     * @param  string[]  $lines
     * @return array<int, array<string, string>>
     */
    private function parseControlStanzas(array $lines): array
    {
        $packages  = [];
        $current   = [];
        $lastKey   = null;

        foreach ($lines as $line) {
            // Blank line = end of stanza.
            if ($line === '') {
                if ($current !== []) {
                    $packages[] = $current;
                    $current  = [];
                    $lastKey  = null;
                }
                continue;
            }

            // Continuation line (starts with space or tab).
            if ($line[0] === ' ' || $line[0] === "\t") {
                if ($lastKey !== null) {
                    $trimmed = ltrim($line);
                    // A single dot means a blank line in the extended description.
                    if ($trimmed === '.') {
                        $current[$lastKey] .= "\n";
                    } else {
                        $current[$lastKey] .= "\n" . $trimmed;
                    }
                }
                continue;
            }

            // Key: Value line.
            $colonPos = strpos($line, ':');
            if ($colonPos !== false) {
                $key   = substr($line, 0, $colonPos);
                $value = ltrim(substr($line, $colonPos + 1));
                $current[$key] = $value;
                $lastKey = $key;
            }
        }

        // Flush last stanza if file doesn't end with a blank line.
        if ($current !== []) {
            $packages[] = $current;
        }

        return $packages;
    }
}
