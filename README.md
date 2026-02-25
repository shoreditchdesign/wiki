# Kirby + Svelte Wiki Scaffold

Minimal scaffold for a Kirby 5 + Svelte 5 documentation site.

## Local setup

1. Install PHP dependencies:

```bash
composer install
```

2. Install Svelte dependencies and create build artifacts:

```bash
cd svelte
npm install
npm run build
cd ..
```

3. Run local Kirby server from repo root:

```bash
php -S localhost:8000 kirby/router.php
```

Open:
- `http://localhost:8000`
- `http://localhost:8000/panel`

## Notes

- Svelte components are intentionally unimplemented for now.
- `assets/js/components.js` and `assets/css/svelte.css` are placeholders until first Svelte build.
