#!/usr/bin/env bash
#
# Generate a minimal reprepro-like repository structure for local development.
# This creates sample Packages/Packages.gz files with fictional packages
# so the web UI can be tested without a real reprepro installation.
#

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_DIR="$SCRIPT_DIR/../repo"

echo "Creating test repository at $REPO_DIR ..."

# ── Directory structure ──────────────────────────────────────────────

mkdir -p "$REPO_DIR/dists/stable/main/binary-amd64"
mkdir -p "$REPO_DIR/dists/stable/main/binary-arm64"
mkdir -p "$REPO_DIR/pool/main/h/hello-world"
mkdir -p "$REPO_DIR/pool/main/n/nginx-custom"
mkdir -p "$REPO_DIR/pool/main/p/postfix-utils"
mkdir -p "$REPO_DIR/pool/main/m/monitoring-agent"
mkdir -p "$REPO_DIR/pool/main/l/libfoo"

# ── Dummy .deb files ─────────────────────────────────────────────────
# (just empty files — the web UI only needs the metadata)

touch "$REPO_DIR/pool/main/h/hello-world/hello-world_2.1.0-1_amd64.deb"
touch "$REPO_DIR/pool/main/n/nginx-custom/nginx-custom_1.24.0-3_amd64.deb"
touch "$REPO_DIR/pool/main/p/postfix-utils/postfix-utils_0.9.4-1_amd64.deb"
touch "$REPO_DIR/pool/main/m/monitoring-agent/monitoring-agent_3.5.2-1_amd64.deb"
touch "$REPO_DIR/pool/main/m/monitoring-agent/monitoring-agent_3.5.2-1_arm64.deb"
touch "$REPO_DIR/pool/main/l/libfoo/libfoo_1.0.0-1_amd64.deb"

# ── Release file ─────────────────────────────────────────────────────

cat > "$REPO_DIR/dists/stable/Release" <<'RELEASE'
Origin: efeyzee
Label: efeyzee
Suite: stable
Codename: stable
Architectures: amd64 arm64
Components: main
Description: efeyzee APT repository
RELEASE

# ── Packages file (amd64) ───────────────────────────────────────────

cat > "$REPO_DIR/dists/stable/main/binary-amd64/Packages" <<'PACKAGES'
Package: hello-world
Version: 2.1.0-1
Architecture: amd64
Maintainer: Ege F. <ege@physsec.org>
Installed-Size: 128
Depends: libc6 (>= 2.34), coreutils
Filename: pool/main/h/hello-world/hello-world_2.1.0-1_amd64.deb
Size: 52480
SHA256: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855
Section: utils
Priority: optional
Homepage: https://github.com/efeyzee/hello-world
Description: A friendly hello world program
 This package provides an example hello world binary
 demonstrating proper Debian packaging conventions.
 .
 Features include:
  - Greeting customisation via environment variables
  - Internationalisation support (i18n)
  - Minimal runtime dependencies

Package: nginx-custom
Version: 1.24.0-3
Architecture: amd64
Maintainer: Ege F. <ege@physsec.org>
Installed-Size: 4096
Depends: libc6 (>= 2.34), libssl3 (>= 3.0.0), libpcre2-8-0 (>= 10.40), zlib1g (>= 1:1.2.11)
Filename: pool/main/n/nginx-custom/nginx-custom_1.24.0-3_amd64.deb
Size: 1048576
SHA256: a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2
Section: web
Priority: optional
Homepage: https://nginx.org
Description: Custom-built Nginx with extra modules
 Nginx built from source with additional modules:
 .
  - headers-more-nginx-module
  - ngx_brotli
  - ngx_cache_purge
 .
 Configured for high-performance reverse proxy use cases.

Package: postfix-utils
Version: 0.9.4-1
Architecture: amd64
Maintainer: Ege F. <ege@physsec.org>
Installed-Size: 256
Depends: postfix, python3 (>= 3.10)
Filename: pool/main/p/postfix-utils/postfix-utils_0.9.4-1_amd64.deb
Size: 28672
SHA256: b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3
Section: mail
Priority: optional
Description: Helper scripts for Postfix mail server
 A collection of utility scripts for managing Postfix:
 .
  - Queue inspection and cleanup
  - Log analysis and reporting
  - Automated certificate renewal hooks

Package: monitoring-agent
Version: 3.5.2-1
Architecture: amd64
Maintainer: Ege F. <ege@physsec.org>
Installed-Size: 8192
Depends: libc6 (>= 2.34), libssl3 (>= 3.0.0), ca-certificates
Filename: pool/main/m/monitoring-agent/monitoring-agent_3.5.2-1_amd64.deb
Size: 2097152
SHA256: c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4
Section: admin
Priority: optional
Homepage: https://github.com/efeyzee/monitoring-agent
Description: Lightweight system monitoring agent
 Collects and exports system metrics in Prometheus format.
 .
 Monitors CPU, memory, disk, network, and systemd service health.
 Configurable alerting thresholds with webhook notifications.

Package: libfoo
Version: 1.0.0-1
Architecture: amd64
Maintainer: Ege F. <ege@physsec.org>
Installed-Size: 512
Depends: libc6 (>= 2.34)
Filename: pool/main/l/libfoo/libfoo_1.0.0-1_amd64.deb
Size: 65536
SHA256: d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5
Section: libs
Priority: optional
Description: Shared library for foo applications
 Provides libfoo.so used by the foo application suite.
PACKAGES

# ── Packages file (arm64) ───────────────────────────────────────────

cat > "$REPO_DIR/dists/stable/main/binary-arm64/Packages" <<'PACKAGES'
Package: monitoring-agent
Version: 3.5.2-1
Architecture: arm64
Maintainer: Ege F. <ege@physsec.org>
Installed-Size: 8192
Depends: libc6 (>= 2.34), libssl3 (>= 3.0.0), ca-certificates
Filename: pool/main/m/monitoring-agent/monitoring-agent_3.5.2-1_arm64.deb
Size: 1998848
SHA256: e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6
Section: admin
Priority: optional
Homepage: https://github.com/efeyzee/monitoring-agent
Description: Lightweight system monitoring agent
 Collects and exports system metrics in Prometheus format.
 .
 Monitors CPU, memory, disk, network, and systemd service health.
 Configurable alerting thresholds with webhook notifications.
PACKAGES

# ── Compress Packages files ──────────────────────────────────────────

gzip -kf "$REPO_DIR/dists/stable/main/binary-amd64/Packages"
gzip -kf "$REPO_DIR/dists/stable/main/binary-arm64/Packages"

echo "Done. Test repository created at $REPO_DIR"
echo ""
echo "Structure:"
find "$REPO_DIR" -type f | sort | sed "s|$REPO_DIR/|  |"
