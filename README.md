# packages.efeyzee.dev

Web frontend for a reprepro-managed APT package repository.

## Stack

- PHP (vanilla, stateless) — reads reprepro's `Packages.gz` index files
- Tailwind CSS via Play CDN
- Targets Nginx + php-fpm

## Local Development

Generate a sample repository and start the PHP dev server:

```bash
./dev/generate-test-repo.sh
cd public && php -S localhost:8080
```

Then open http://localhost:8080.

## Configuration

Edit `src/config.php`:

| Key         | Description                                                    | Default          |
|-------------|----------------------------------------------------------------|------------------|
| `repo_path` | Filesystem path to the reprepro root (`dists/`, `pool/`)       | `../repo`        |
| `base_url`  | URL prefix for `.deb` download links                           | `''` (root)      |
| `site_name` | Displayed site name                                            | `packages.efeyzee.dev` |
| `site_desc` | Displayed site description                                     | `APT Package Repository` |

## Nginx Configuration (Example)

```nginx
server {
    listen 443 ssl http2;
    server_name packages.efeyzee.dev;

    root /srv/packages.efeyzee.dev/public;
    index index.php;

    # Serve apt repository files directly.
    location ~ ^/(dists|pool)/ {
        root /path/to/reprepro/;
        autoindex off;
    }

    # PHP front controller for everything else.
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Adjust `root`, `alias`, and `fastcgi_pass` to match your deployment.

## Project Structure

```
public/index.php         Front controller (routing)
src/config.php           Configuration
src/RepoReader.php       Parses reprepro Packages.gz files
src/templates/layout.php HTML shell (Tailwind CDN, nav, footer)
src/templates/home.php   Package listing + search
src/templates/package.php Package detail page
src/templates/404.php    Not found page
dev/generate-test-repo.sh Creates sample repo data for development
```
