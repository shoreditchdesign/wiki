# Kirby + Svelte Site Scaffold & Deployment Guide

> This guide is written for an AI agent (or a human) starting a **new** Kirby CMS + Svelte 5 web component site from scratch and deploying it to Railway. It documents every layer of the architecture as it exists in the `ornithopter` repo, so you can replicate it with different content, blocks, and design.

---

## Table of Contents

1. [Architecture Overview](#1-architecture-overview)
2. [Repository Structure](#2-repository-structure)
3. [Kirby CMS Concepts](#3-kirby-cms-concepts)
4. [Blueprint System](#4-blueprint-system)
5. [Template + Snippet System](#5-template--snippet-system)
6. [Block System — End to End](#6-block-system--end-to-end)
7. [Svelte Web Component Layer](#7-svelte-web-component-layer)
8. [Props / JSON Data Bridge](#8-props--json-data-bridge)
9. [Asset Pipeline (Vite → IIFE)](#9-asset-pipeline-vite--iife)
10. [CSS & Design Token System](#10-css--design-token-system)
11. [Script Load Order](#11-script-load-order)
12. [Content Files & Folder Conventions](#12-content-files--folder-conventions)
13. [Kirby Panel Configuration](#13-kirby-panel-configuration)
14. [Deployment: Dockerfile for Railway](#14-deployment-dockerfile-for-railway)
15. [Railway-Specific Configuration](#15-railway-specific-configuration)
16. [Kirby Config for Reverse Proxies](#16-kirby-config-for-reverse-proxies)
17. [.htaccess](#17-htaccess)
18. [Step-by-Step: Scaffold a New Site](#18-step-by-step-scaffold-a-new-site)
19. [Adding a New Block Type](#19-adding-a-new-block-type)
20. [Adding a New Page Type](#20-adding-a-new-page-type)
21. [Adding a New Svelte Component](#21-adding-a-new-svelte-component)
22. [Kirby Panel First-Run Setup](#22-kirby-panel-first-run-setup)
23. [Common Pitfalls & Gotchas](#23-common-pitfalls--gotchas)

---

## 1. Architecture Overview

```
Browser
  └── Kirby (PHP / Apache) renders static HTML
        ├── Templates output custom element tags: <c-header>, <l-index>, <b-text>, …
        ├── Sibling <script type="application/json" data-for="id"> carries props as JSON
        └── scripts.php loads Svelte bundle (assets/js/components.js)
              └── register.ts: each custom element reads its JSON props and mounts
                  the Svelte 5 component via mount()
```

**Key characteristics:**
- Kirby is a flat-file CMS — no database. Content lives in `content/` as `.txt` files.
- Svelte components are compiled to a single IIFE bundle (`assets/js/components.js`) via Vite.
- No Shadow DOM. Custom elements use `shadow: "none"` (in practice, our manual wrapper pattern). Svelte scoped CSS applies at build time via hashed classes.
- CDN scripts (Lenis, GSAP, ScrollTrigger, Barba) are loaded before the Svelte bundle.
- Barba.js handles SPA-style page transitions — only `<main data-barba="container">` is swapped; `<header>` and `<footer>` persist.

---

## 2. Repository Structure

```
/
├── Dockerfile                    # Docker build for Railway
├── railway.toml                  # Railway config (minimal)
├── composer.json                 # PHP deps: kirby/cms ^5.2, php ~8.2-8.4
├── .htaccess                     # Apache rewrite rules, caching headers
├── index.php                     # Kirby bootstrap (do not modify)
│
├── site/
│   ├── config/
│   │   └── config.php            # Kirby config: URL detection, thumbs driver, panel menu
│   ├── blueprints/
│   │   ├── site.yml              # Site-wide Panel layout (globals, header, footer, preloader)
│   │   ├── pages/                # One .yml per page template
│   │   │   ├── home.yml
│   │   │   ├── project.yml       # Uses blocks fieldset
│   │   │   ├── projects.yml      # Container page
│   │   │   ├── about.yml
│   │   │   ├── thoughts.yml      # Container page
│   │   │   ├── thought.yml
│   │   │   ├── default.yml       # Generic blocks page
│   │   │   ├── text.yml
│   │   │   └── holding.yml
│   │   ├── blocks/               # One .yml per block type
│   │   │   ├── b-header.yml
│   │   │   ├── b-text.yml
│   │   │   ├── b-img.yml
│   │   │   ├── b-grid.yml
│   │   │   └── b-fullbleed.yml
│   │   ├── sections/             # Reusable panel section partials
│   │   │   ├── projects.yml
│   │   │   └── thoughts.yml
│   │   └── files/
│   │       └── image.yml         # File blueprint for images (adds alt field)
│   ├── templates/                # PHP template per page type
│   │   ├── home.php
│   │   ├── project.php
│   │   ├── about.php
│   │   ├── thoughts.php
│   │   ├── thought.php
│   │   ├── default.php
│   │   ├── text.php
│   │   ├── error.php
│   │   └── holding.php
│   └── snippets/
│       ├── head.php              # <html><head>…</head><body>
│       ├── header.php            # <c-header> custom element + JSON props
│       ├── footer.php            # <c-footer> custom element + JSON props
│       ├── preloader.php         # <c-preloader> custom element + JSON props
│       ├── scripts.php           # CDN scripts + assets/js/script.js + components.js
│       └── blocks/               # One .php per block type
│           ├── b-header.php
│           ├── b-text.php
│           ├── b-img.php
│           ├── b-grid.php
│           └── b-fullbleed.php
│
├── content/                      # Flat-file content (committed to git)
│   ├── site.txt                  # Site-wide content (title, description, favicon, etc.)
│   ├── 1_home/home.txt
│   ├── 3_projects/
│   │   ├── projects.txt
│   │   └── 0_nuro/project.txt + image files
│   ├── 4_thoughts/thoughts.txt
│   └── error/error.txt
│
├── assets/                       # Static assets (committed + Vite output)
│   ├── css/
│   │   ├── normalize.css
│   │   ├── variables.css         # CSS custom properties + font-face
│   │   ├── styles.css            # Global layout styles
│   │   ├── components.css        # Component-specific global styles
│   │   ├── keyframes.css         # CSS animations
│   │   └── svelte.css            # BUILT by Vite — Svelte scoped styles
│   ├── js/
│   │   ├── script.js             # Lenis init + Barba transitions (vanilla JS)
│   │   └── components.js         # BUILT by Vite — all Svelte components as IIFE
│   └── fonts/
│       ├── InterDisplay-Regular.ttf
│       ├── InterDisplay-Medium.ttf
│       ├── InterDisplay-Bold.ttf
│       ├── EBGaramond-Regular.ttf
│       └── EBGaramond-Italic.ttf
│
└── svelte/                       # Svelte source (not served directly)
    ├── package.json
    ├── vite.config.ts
    ├── tsconfig.json
    └── src/
        ├── main.ts               # Entry point: imports + registerSvelteElement() calls
        ├── register.ts           # Custom element wrapper (reads JSON props, calls mount())
        └── components/
            ├── global/           # Header.svelte, Footer.svelte, Preloader.svelte
            ├── layout/           # LayoutIndex.svelte, LayoutAbout.svelte, etc.
            ├── ui/               # Button.svelte, IndexCard.svelte, etc.
            └── blocks/           # BlockHeader.svelte, BlockText.svelte, etc.
```

---

## 3. Kirby CMS Concepts

### Flat-file content
Every page is a folder inside `content/`. The folder name sets the slug (numeric prefix sets sort order). Inside, a `.txt` file stores field values separated by `----`. Images and other files live in the same folder.

```
content/
  3_projects/          ← slug: "projects", sort: 3
    0_nuro/            ← slug: "nuro", sort: 0 (within projects)
      project.txt      ← template: project (matches blueprints/pages/project.yml)
      nuro-base.png
```

### Templates
A template is a PHP file at `site/templates/{template-name}.php`. Kirby picks the template based on the page's content file name (e.g. `project.txt` → `project.php`).

### Blueprints
YAML files in `site/blueprints/pages/` define the Panel UI for each template. Fields defined here are stored in the content `.txt` files.

### Snippets
Reusable PHP partials at `site/snippets/`. Called with `<?php snippet("name"); ?>` or `<?php snippet("blocks/b-header", ["block" => $block]); ?>`.

### Field Access
```php
$page->field_name()->value()       // raw string
$page->field_name()->kt()          // kirbytext (markdown → HTML)
$page->field_name()->toFile()      // file field → KirbyFile object
$page->field_name()->toFiles()     // files field → collection
$page->field_name()->toPages()     // pages field → collection
$page->field_name()->toStructure() // structure field → collection
$page->field_name()->toBlocks()    // blocks field → collection
$page->field_name()->toUrl()       // link field → URL string
```

### Reserved field names (structure items)
**Never** use `content`, `id`, `parent`, `site`, `kirby` as field names inside a `structure` fieldset. They conflict with Kirby's internal methods. Use `$item->toArray()['fieldname']` to escape this:

```php
// BAD
$item->content()->value() // returns Content object, not your field

// GOOD
$itemData = $item->toArray();
$value = $itemData['content'] ?? '';
```

---

## 4. Blueprint System

### Page blueprint (`site/blueprints/pages/example.yml`)
```yaml
title: My Page Template

# Lock certain panel actions (optional)
options:
  changeSlug: false
  delete: false

# Two-column layout
columns:
  - width: 2/3
    sections:
      content:
        type: fields
        fields:
          hero_title:
            type: writer        # rich text, inline
            label: Heading
            marks:
              - italic
              - bold
          hero_button:
            type: link
            label: CTA Link
            options:
              - page
              - url

  - width: 1/3
    sticky: true
    sections:
      seo:
        type: fields
        label: SEO
        fields:
          meta_title:
            type: text
          meta_description:
            type: textarea
            maxlength: 160
          meta_image:
            type: files
            max: 1
            uploads:
              template: image   # uses blueprints/files/image.yml
```

### Block blueprint (`site/blueprints/blocks/b-myblock.yml`)
```yaml
name: My Block
icon: box          # any Kirby panel icon name
preview: fields    # or "image" to show image preview

fields:
  heading:
    type: text
    label: Heading

  body:
    type: writer
    label: Body
    marks:
      - bold
      - italic
      - link

  image:
    type: files
    label: Image
    max: 1
    uploads:
      template: image
```

### Site blueprint (`site/blueprints/site.yml`)
Defines the **Site** panel (globals, shared settings). Structure fields here are used by `$site->fieldname()` in PHP templates. Good for: header links, footer links, favicon, og_image, preloader content.

### File blueprint (`site/blueprints/files/image.yml`)
Defines extra fields on uploaded files. Use `template: image` in any files field to apply it.

```yaml
title: Image
accept:
  extension: jpg, jpeg, png, gif, webp, avif, svg
fields:
  alt:
    type: text
    label: Alt Text
```

### Sections (`site/blueprints/sections/`)
Reusable section partials for the Panel. Referenced in blueprints as:
```yaml
sections:
  projects: sections/projects   # loads site/blueprints/sections/projects.yml
```

---

## 5. Template + Snippet System

### Page rendering flow

```
Request → index.php → Kirby router → site/templates/{name}.php
  → snippet("head")       ← <html><head>…<body>
  → snippet("header")     ← <c-header> web component + JSON
  → [page-specific HTML and web components]
  → snippet("footer")     ← <c-footer> web component + JSON
  → snippet("scripts")    ← CDN scripts + script.js + components.js
  → closes </html>
```

### `site/snippets/head.php`
- Outputs `<!DOCTYPE html>`, `<head>` and opens `<body class="body" data-barba="wrapper">`
- `data-barba="wrapper"` is required for Barba.js — the entire body is the wrapper
- Loads CSS files: `normalize.css`, `variables.css`, `styles.css`, `keyframes.css`, `svelte.css`
- Handles favicon, OG tags, Twitter card meta
- Preloads any images needed by the preloader (Three.js TextureLoader fetches them)

### `site/snippets/scripts.php`
Loaded at the **very end** of every template (just before `</html>`):
```html
<script src="https://unpkg.com/lenis@1.1.18/dist/lenis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
<script src="https://unpkg.com/@barba/core@2.10.3/dist/barba.umd.js"></script>
<script src="/assets/js/script.js"></script>
<script src="/assets/js/components.js"></script>
```

Order matters: CDN libs → `script.js` (initializes Lenis + Barba) → `components.js` (registers and mounts Svelte components).

### A minimal template

```php
<?php snippet("head"); ?>
<?php snippet("header"); ?>

<div class="page" data-barba="container" data-barba-namespace="mypage">
<main class="main">
  <?php
  $id = "mypage";
  $props = [
    "title" => $page->title()->value(),
    "subtitle" => $page->subtitle()->value(),
  ];
  ?>
  <l-mypage id="<?= $id ?>"></l-mypage>
  <script type="application/json" data-for="<?= $id ?>">
  <?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
  </script>
</main>
<?php snippet("footer"); ?>
</div>
<?php snippet("scripts"); ?>
```

**Rules:**
- Every page wraps content in `<div class="page" data-barba="container" data-barba-namespace="UNIQUE_NAME">` — Barba swaps only this element between navigations.
- `data-barba-namespace` should be unique per template type.

---

## 6. Block System — End to End

Blocks are Kirby's structured content system for rich page bodies. Each block type has three parts:

### Part 1: Block blueprint (`site/blueprints/blocks/b-myblock.yml`)
Defines the Panel form fields for this block.

### Part 2: Block snippet (`site/snippets/blocks/b-myblock.php`)
Renders the block to HTML. The convention in this project is to render a Svelte custom element and pass data as JSON.

```php
<?php
$blockNamespace = $blockNamespace ?? "blk";
$blockId = $blockNamespace . "-" . $block->id();

// Collect props from the block's fields
$props = [
    "heading" => $block->heading()->value(),
    "body" => (string) $block->body()->kt(),    // kt() = kirbytext = markdown→HTML
];

// Render the custom element + sibling JSON
?>
<b-myblock id="<?= $blockId ?>"></b-myblock>
<script type="application/json" data-for="<?= $blockId ?>">
<?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
</script>
```

**Key points:**
- `$block->id()` gives a unique UUID per block instance
- `$blockNamespace` prevents ID collisions when the same template renders blocks in multiple contexts (e.g. `"pr"` for project, `"df"` for default)
- `(string) $block->field()->kt()` converts KirbyText (markdown + Kirby tags) to HTML string
- For structure fields with a field named `content`, use `$item->toArray()['content']` (see pitfalls)

### Part 3: Svelte block component (`svelte/src/components/blocks/BlockMyblock.svelte`)
```svelte
<script lang="ts">
  let { heading = "", body = "" } = $props();
</script>

<div class="my-block">
  <h2>{heading}</h2>
  <div class="body">{@html body}</div>
</div>

<style>
  .my-block { /* scoped automatically */ }
</style>
```

### Registering the block in `main.ts`
```ts
import BlockMyblock from "./components/blocks/BlockMyblock.svelte";
registerSvelteElement("b-myblock", BlockMyblock, ["heading", "body"]);
```

### Using blocks in a page blueprint
```yaml
fields:
  blocks:
    type: blocks
    label: Content
    fieldsets:
      - b-header
      - b-myblock
      - b-img
```

### Rendering blocks in a template
```php
<?php foreach ($page->blocks()->toBlocks() as $block): ?>
  <?php snippet("blocks/" . $block->type(), [
    "block" => $block,
    "blockNamespace" => "pr",
  ]); ?>
<?php endforeach; ?>
```

Kirby automatically looks for `site/snippets/blocks/{type}.php` where `type` matches the block blueprint filename.

---

## 7. Svelte Web Component Layer

### Philosophy
This project uses Svelte 5 compiled to custom elements via a **manual wrapper pattern** — not Svelte's built-in `customElement: true` option. This avoids Shadow DOM while still getting custom element registration.

### `svelte/src/register.ts` — the core mechanism

```ts
import { mount, unmount } from 'svelte';
import type { Component } from 'svelte';

export function registerSvelteElement(
  tag: string,
  ComponentClass: Component<any>,
  propNames: string[] = []
) {
  class SvelteElement extends HTMLElement {
    private _component: Record<string, any> | null = null;

    connectedCallback() {
      if (this._component) return; // guard against double-mounting
      this.innerHTML = '';

      // Priority 1: sibling <script type="application/json" data-for="[id]">
      let props: Record<string, any> = {};
      const id = this.getAttribute('id');
      if (id) {
        const script = document.querySelector(`script[data-for="${id}"]`);
        if (script) {
          props = JSON.parse(script.textContent || '{}');
          script.remove(); // clean up after reading
        }
      }

      // Priority 2: HTML attributes (auto JSON-parsed if valid JSON)
      if (Object.keys(props).length === 0) {
        for (const name of propNames) {
          const attr = this.getAttribute(name);
          if (attr !== null) {
            try { props[name] = JSON.parse(attr); }
            catch { props[name] = attr; }
          }
        }
      }

      // Mount Svelte 5 component into the custom element's DOM
      this._component = mount(ComponentClass, { target: this, props });
    }

    disconnectedCallback() {
      if (this._component) {
        unmount(this._component);
        this._component = null;
      }
    }
  }

  customElements.define(tag, SvelteElement);
}
```

### `svelte/src/main.ts` — registration entry point

```ts
import { registerSvelteElement } from "./register";
import MyComponent from "./components/MyComponent.svelte";

registerSvelteElement("c-my-component", MyComponent, ["prop1", "prop2"]);

// After all components registered:
window.dispatchEvent(new CustomEvent("svelte-ready"));
```

The `svelte-ready` event is caught by `script.js` to resize Lenis and refresh ScrollTrigger after components have added content to the page.

### Svelte 5 component conventions

```svelte
<script lang="ts">
  import { onMount } from 'svelte';

  // All props declared with $props() rune
  let { title = "", items = [] } = $props();

  // Reactive state with $state()
  let count = $state(0);

  // DOM refs with bind:this (used in onMount, not reactive)
  let containerEl: HTMLElement;

  onMount(() => {
    // Safe to access DOM here
    // Return cleanup function if needed
    return () => { /* cleanup */ };
  });
</script>

<div bind:this={containerEl}>
  <h1>{title}</h1>
</div>

<style>
  /* Scoped to this component via compile-time hashing */
  div { color: white; }
</style>
```

**No slots** — this project uses `shadow: "none"` (no Shadow DOM), so `<slot>` does not work. Pass all content as props.

---

## 8. Props / JSON Data Bridge

This is the core pattern for passing PHP data to Svelte components.

### Pattern A: Sibling script tag (preferred for complex data)

**PHP (template or snippet):**
```php
<?php
$id = "my-component";
$props = [
    "title" => $page->title()->value(),
    "items" => $complexArray,
];
?>
<c-my-component id="<?= $id ?>"></c-my-component>
<script type="application/json" data-for="<?= $id ?>">
<?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
</script>
```

**How it works:** `register.ts` queries `document.querySelector('script[data-for="my-component"]')`, parses its JSON content, removes the script tag from DOM, and passes the object as props to `mount()`.

### Pattern B: HTML attributes (for simple string props)

```php
<c-button href="<?= $url ?>" label="<?= $label ?>"></c-button>
```

`register.ts` reads each declared prop name as an attribute, attempts `JSON.parse()` (so arrays/objects survive as attributes), falls back to raw string.

### Building the props array in PHP

**Simple fields:**
```php
"title" => $page->title()->value(),
"slug" => $page->slug(),
```

**Rich text (KirbyText → HTML string):**
```php
"description" => (string) $page->description()->kt(),
// or for inline writer fields:
"headline" => strip_tags((string) $page->headline()->kti(), "<em><i><strong><b>"),
```

**Single file → URL:**
```php
"image" => $page->thumbnail()->toFile()?->url() ?? "",
```

**Multiple files → array:**
```php
"images" => array_map(
    fn($f) => ["url" => $f->url(), "alt" => $f->alt()->value()],
    iterator_to_array($page->gallery()->toFiles())
),
```

**Structure field → array:**
```php
$items = [];
foreach ($page->items()->toStructure() as $item) {
    $items[] = [
        "label" => $item->label()->value(),
        "value" => $item->value()->value(),
    ];
}
```

**Structure field with reserved 'content' name:**
```php
foreach ($page->items()->toStructure() as $item) {
    $data = $item->toArray();
    $items[] = [
        "heading" => $data["heading"] ?? "",
        "content" => $data["content"] ?? "",  // toArray() bypasses method conflict
    ];
}
```

**Pages field → array:**
```php
$links = [];
foreach ($page->related_pages()->toPages() as $related) {
    $links[] = [
        "url" => $related->url(),
        "title" => $related->title()->value(),
    ];
}
```

---

## 9. Asset Pipeline (Vite → IIFE)

### `svelte/vite.config.ts`

```ts
import { defineConfig } from "vite";
import { svelte } from "@sveltejs/vite-plugin-svelte";

export default defineConfig({
  plugins: [svelte()],
  build: {
    outDir: "../assets",      // outputs to /assets relative to svelte/
    emptyOutDir: false,       // don't wipe the whole assets folder
    lib: {
      entry: "src/main.ts",
      name: "components",
      fileName: () => "js/components.js",
      formats: ["iife"],      // single self-executing bundle, no module system needed
    },
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo) => {
          if (assetInfo.name?.endsWith(".css")) {
            return "css/svelte.css";   // all Svelte CSS into one file
          }
          return "js/[name][extname]";
        },
      },
    },
  },
});
```

### `svelte/package.json` scripts

```json
{
  "scripts": {
    "dev": "vite build --watch",   // rebuild on file save during development
    "build": "vite build",          // one-time production build
    "check": "svelte-check --tsconfig ./tsconfig.json"
  },
  "devDependencies": {
    "@sveltejs/vite-plugin-svelte": "^5.0.0",
    "svelte": "^5.0.0",
    "typescript": "^5.0.0",
    "vite": "^6.0.0"
  },
  "dependencies": {
    "three": "^0.182.0"   // only if using Three.js; remove if not needed
  }
}
```

### Build output
- `assets/js/components.js` — all Svelte components + Three.js (if used) as an IIFE
- `assets/css/svelte.css` — all scoped component CSS concatenated

Both files are committed to git so the Dockerfile can COPY them without running a Node build step.

### Development workflow
```bash
cd svelte
npm install
npm run dev       # watches and rebuilds on change
```

In another terminal:
```bash
php -S localhost:8000 kirby/router.php   # from repo root
```

---

## 10. CSS & Design Token System

### File load order in `head.php`
1. `normalize.css` — CSS reset
2. `variables.css` — font-face declarations + CSS custom properties (design tokens)
3. `styles.css` — global layout styles, utility classes
4. `keyframes.css` — CSS animation definitions
5. `svelte.css` — Vite-built Svelte scoped styles

### Design tokens in `variables.css`
All design values are CSS custom properties on `:root`. The naming convention from Webflow:
```css
:root {
  --_themes---site--bg--bg-primary: #020508;
  --_themes---site--text--text-primary: white;
  --_themes---site--border--border-primary: #fff3;
  --_units---abs--4: 1rem;
  --_units---abs--8: 2rem;
  --gap--md: var(--_units---abs--6);
  --padding--sm: var(--_units---abs--12);
}
```

For a new site, replace `variables.css` with your own token system. Svelte components reference tokens via `var(--token-name)` in their `<style>` blocks.

### Fonts
Fonts are self-hosted in `assets/fonts/` and declared via `@font-face` in `variables.css`. Reference them in CSS via `font-family: var(--typeface--primary)`.

---

## 11. Script Load Order

**Critical** — wrong order breaks everything:

```
1. Lenis CDN         (defines window.Lenis)
2. GSAP CDN          (defines window.gsap)
3. ScrollTrigger CDN (extends GSAP)
4. Barba CDN         (defines window.barba)
5. script.js         (initializes Lenis + Barba; listens for svelte-ready)
6. components.js     (Svelte bundle; dispatches svelte-ready when done)
```

All scripts are in `site/snippets/scripts.php`, loaded at the **end** of the body in every template via `<?php snippet("scripts"); ?>`.

`script.js` uses `DOMContentLoaded` to defer initialization until the DOM is ready. It checks for `typeof Lenis`, `typeof gsap`, `typeof barba` before using them (graceful degradation if CDN fails).

---

## 12. Barba.js Page Transitions

Barba.js provides SPA-style navigation by fetching the next page and swapping only the `[data-barba="container"]` element.

### Required HTML structure

**In `head.php` / body tag:**
```html
<body data-barba="wrapper">
```

**In every template:**
```html
<div class="page" data-barba="container" data-barba-namespace="UNIQUE_NAME">
  <main>…content…</main>
  …footer…
</div>
```

The `header` lives outside `data-barba="container"` so it persists across navigations.

### Barba + Svelte components

When Barba swaps in new HTML containing custom elements (e.g. `<c-header>`), the browser's custom element registry fires `connectedCallback` on new elements. This means Svelte components remount automatically on navigation — no special handling needed.

However, you must re-initialize Lenis and ScrollTrigger after each navigation in Barba's `after` hook:
```js
after: function() {
  if (window.lenis) { window.lenis.start(); window.lenis.resize(); }
  if (typeof ScrollTrigger !== "undefined") ScrollTrigger.refresh();
}
```

### Preventing Barba from intercepting links

Add `data-barba-prevent` attribute to any link that should do a full page load (e.g. external links, Panel links):
```html
<a href="/panel" data-barba-prevent>Panel</a>
```

### Custom events for canvas navigation

Components using `window.location.href` bypass Barba. Instead, dispatch a custom event:
```ts
// In Svelte component
this.dispatchEvent(new CustomEvent("mycomp:exit", {
  detail: { url: targetUrl },
  bubbles: true
}));
```

```js
// In script.js
document.addEventListener("mycomp:exit", function(e) {
  barba.go(e.detail.url);
});
```

---

## 13. Lenis Smooth Scroll

Lenis is initialized in `script.js` on `DOMContentLoaded`:

```js
var lenis = new Lenis({ smoothWheel: true, lerp: 0.12 });

// Integrate with GSAP ticker
gsap.ticker.add((time) => lenis.raf(time * 1000));
gsap.ticker.lagSmoothing(0);

// Integrate with ScrollTrigger
lenis.on("scroll", ScrollTrigger.update);

// Expose globally
window.lenis = lenis;
window.dispatchEvent(new CustomEvent("lenis-ready"));
```

**Components that scroll:**
- Listen for `lenis-ready` event or check `window.lenis` before using it
- Call `window.lenis.scrollTo(target)` instead of `window.scrollTo()`
- For Three.js canvas components that manage their own scroll (like IndexWheel), disable Lenis for that section by calling `lenis.stop()` and `lenis.start()` around the canvas zone, or simply skip Lenis entirely for that element and handle scroll natively

**After Barba navigation:** always call `lenis.resize()` and `ScrollTrigger.refresh()` to remeasure the new page content.

---

## 14. Content Files & Folder Conventions

### Folder naming
```
content/
  1_home/        ← number prefix = sort order, underscore, slug
  2_about/
  3_projects/
    0_nuro/      ← project children sorted numerically
  4_thoughts/
  error/         ← special: no number, matched by Kirby's error route
```

### Content `.txt` format
```
Title: My Page Title

----

Field-name: Value

----

Uuid: AutoGeneratedByKirby
```

Fields are separated by `----`. Field names are case-insensitive and hyphen-separated (matching blueprint field names). Kirby auto-generates and manages `Uuid` — do not change it.

### File metadata
Each file in a content folder can have a companion `.txt` file with the same name:
```
nuro-base.png
nuro-base.png.txt    ← stores alt text and other file fields
```

Content of `nuro-base.png.txt`:
```
Alt: A product screenshot of the Nuro app

----

Uuid: abc123...
```

### Pages vs Site content
- `site.txt` at `content/` root stores site-wide fields (accessed via `$site->fieldname()`)
- All other `.txt` files are page content (accessed via `$page->fieldname()`)

### Blocks in content files
Blocks are stored as JSON in the `.txt` file:
```
Blocks: [{"content":{"title":"My Heading"},"id":"uuid","type":"b-header"},…]
```

You don't manually edit this. The Panel generates it.

---

## 15. Kirby Panel Configuration

### Accessing the Panel
`/panel` route. First-run creates an admin user (see section 24).

### `site/config/config.php`

```php
<?php
use Kirby\Cms\App;

// Detect base URL from Railway reverse proxy
$url = null;
if (!empty($_SERVER["HTTP_X_FORWARDED_HOST"])) {
    $proto = $_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "https";
    $host = $_SERVER["HTTP_X_FORWARDED_HOST"];
    $url = $proto . "://" . $host;
}

// Auto-detect image processing driver
$thumbsDriver = extension_loaded("imagick") ? "imagick" : "gd";

return [
    "debug" => false,               // ALWAYS false in production
    "yaml.handler" => "symfony",    // Required by Kirby 5
    "url" => $url ?? null,          // Needed for Railway proxy

    // Customize Panel sidebar
    "panel.menu" => [
        "site",
        "users",
        "system",
        "-",                        // divider
        "posts" => [
            "label" => "Posts",
            "icon" => "text",
            "link" => "pages/posts",
            "current" => function() {
                $path = ltrim(App::instance()->request()->path()->toString(), "/");
                return str_starts_with($path, "panel/pages/posts");
            },
        ],
    ],

    "thumbs" => [
        "driver" => $thumbsDriver,
    ],
];
```

**Set `debug => false` in production.** Debug mode exposes stack traces to the browser.

### Panel menu items
Standard entries: `"site"`, `"users"`, `"system"`. Custom entries need `label`, `icon`, `link`, and `current` (closure returning bool). Use `"-"` for a separator.

---

## 16. Deployment: Dockerfile for Railway

The production deployment uses Docker. Railway detects the `Dockerfile` at the repo root and builds/runs it.

```dockerfile
FROM php:8.4-apache

# System deps + PHP extensions Kirby needs
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libwebp-dev libfreetype-dev \
    imagemagick libmagickwand-dev \
    libzip-dev libxml2-dev libcurl4-openssl-dev \
    libonig-dev libicu-dev libexif-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install gd zip mbstring dom xml curl intl exif opcache \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache modules: rewrite (for .htaccess), headers, expires (for caching)
RUN a2enmod rewrite headers expires

# Document root
ENV APACHE_DOCUMENT_ROOT /var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides in document root
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Copy all files
COPY . /var/www/html/

# Install PHP deps (no dev, optimized autoloader)
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader --no-interaction

# Kirby creates /media/ at runtime; pre-create so permissions are right
RUN mkdir -p /var/www/html/media

# www-data needs write access to: media/, site/sessions/, site/accounts/, content/
RUN chown -R www-data:www-data /var/www/html

# Railway injects $PORT. Apache must listen on it.
# Also fix MPM conflict (Railway's base image sometimes has mpm_event enabled
# alongside prefork, causing Apache to refuse to start).
CMD ["bash", "-lc", "set -eux; \
  a2dismod mpm_event mpm_worker || true; \
  rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.* || true; \
  a2enmod mpm_prefork; \
  sed -i \"s/Listen 80/Listen ${PORT:-80}/g\" /etc/apache2/ports.conf; \
  sed -i \"s/*:80/*:${PORT:-80}/g\" /etc/apache2/sites-available/000-default.conf; \
  apache2ctl -t; \
  exec apache2-foreground"]
```

### Why these PHP extensions?

| Extension | Required for |
|-----------|-------------|
| `gd` | Image thumbnail generation (fallback) |
| `imagick` | Better image processing (preferred) |
| `zip` | Kirby file operations |
| `mbstring` | Multi-byte string handling |
| `dom`, `xml` | XML/HTML parsing |
| `curl` | External HTTP requests |
| `intl` | Internationalization (Kirby 5 requirement) |
| `exif` | Image metadata reading |
| `opcache` | PHP bytecode caching (performance) |

### Writable directories
Kirby needs write access to:
- `media/` — generated thumbnails and resized images
- `site/sessions/` — user sessions (auto-created by Kirby)
- `site/accounts/` — panel user accounts
- `content/` — if you allow the panel to create/modify content

The `chown -R www-data:www-data /var/www/html` covers all of these.

### The `media/` directory
Kirby generates `media/` at runtime. It is gitignored. The Dockerfile `mkdir -p /var/www/html/media` pre-creates it so the permission `chown` applies.

---

## 17. Railway-Specific Configuration

### `railway.toml`
Minimal — Railway auto-detects the Dockerfile:
```toml
# Railway Configuration
# Railway will auto-detect the Dockerfile at the repo root
```

You can add more configuration here if needed (build commands, health checks, etc.) but the defaults work.

### Environment Variables on Railway
Set these in the Railway dashboard under your service's Variables tab:

| Variable | Value | Notes |
|----------|-------|-------|
| `PORT` | (auto-set by Railway) | Railway injects this; your CMD reads it |
| `KIRBY_DEBUG` | `false` | Optional; `config.php` uses PHP's `debug` setting |

Railway automatically injects:
- `PORT` — the port your service must listen on
- `RAILWAY_PUBLIC_DOMAIN` — your service's public URL (not directly needed; we use `HTTP_X_FORWARDED_HOST`)

### Custom domain
1. Add your domain in Railway dashboard → service → Settings → Domains
2. Point your DNS (CNAME or A record) to Railway's provided target
3. Railway handles SSL automatically via Let's Encrypt

### Volumes (persistent storage)
Kirby stores content, accounts, and media on the filesystem. On Railway, the container filesystem is **ephemeral** — it resets on every deploy.

**Strategies:**

**Option A (this project's approach):** Commit content to git. `content/` lives in the repo. Panel edits on production are lost on next deploy. Only practical if you manage content locally and deploy via git.

**Option B: Railway Volume.** Create a Railway volume and mount it at `/var/www/html/content`. Content changes persist. Requires mounting at runtime, not build time. Add to `railway.toml`:
```toml
[deploy]
  numReplicas = 1  # required for volumes
```
Then mount the volume to `/var/www/html/content` in the dashboard.

**Option C: External storage.** Use Kirby's S3 or remote storage plugins. More complex setup.

---

## 18. Kirby Config for Reverse Proxies

Railway (and most PaaS platforms) sit behind a reverse proxy. Kirby needs to know the public URL to generate correct absolute URLs for assets, links, and the Panel.

```php
// site/config/config.php
$url = null;
if (!empty($_SERVER["HTTP_X_FORWARDED_HOST"])) {
    $proto = $_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "https";
    $host = $_SERVER["HTTP_X_FORWARDED_HOST"];
    $url = $proto . "://" . $host;
}

return [
    "url" => $url ?? null,
    // ...
];
```

This reads the `X-Forwarded-Host` and `X-Forwarded-Proto` headers set by Railway's proxy. Without this, Kirby generates `http://localhost` URLs and the Panel may not load correctly.

---

## 19. .htaccess

Apache needs `.htaccess` overrides enabled (handled in Dockerfile). The `.htaccess` at the repo root does:

1. **Rewrite rules:** Routes all requests through `index.php` (Kirby's entry point), except actual files/directories. Blocks direct access to `content/`, `site/`, `kirby/` directories.

2. **Compression:** `mod_deflate` compresses text responses (HTML, CSS, JS, JSON).

3. **MIME types:** Ensures correct content-type headers for webp, woff2, etc.

4. **Browser caching:** Images and fonts get 1-year cache; CSS/JS get 1-week cache; HTML gets no-cache.

The HTTPS redirect is commented out — Railway handles SSL termination upstream, so redirecting to HTTPS at the PHP/Apache level is unnecessary and may cause redirect loops.

---

## 20. Step-by-Step: Scaffold a New Site

### Prerequisites
- PHP 8.2+ with extensions: gd, zip, mbstring, dom, xml, curl, intl, exif
- Composer
- Node.js 20+ and npm
- Git
- Railway account + CLI (`railway` command)

### Step 1: Clone or copy the repo

```bash
git clone <this-repo> my-new-site
cd my-new-site
```

Or start fresh:
```bash
composer create-project getkirby/plainkit my-new-site
cd my-new-site
```

If starting from scratch, you'll need to manually create:
- All files in `site/blueprints/`, `site/templates/`, `site/snippets/`
- The `svelte/` directory with `package.json`, `vite.config.ts`, `tsconfig.json`, `src/main.ts`, `src/register.ts`
- The `assets/` directory structure
- `Dockerfile`, `railway.toml`, `.htaccess`

### Step 2: Install PHP dependencies

```bash
composer install
```

### Step 3: Set up Svelte

```bash
cd svelte
npm install
```

Edit `svelte/src/main.ts` to import and register only the components you need.

### Step 4: Design your content model

Decide on your page types and block types:
- What pages does your site need? (home, about, work, contact, etc.)
- What block types will content editors use? (text, image, quote, video, etc.)
- What global settings does the site need? (header links, footer, etc.)

### Step 5: Create blueprints

For each page type: `site/blueprints/pages/{name}.yml`
For each block type: `site/blueprints/blocks/b-{name}.yml`
Update `site/blueprints/site.yml` for global settings

### Step 6: Create templates

For each page blueprint: `site/templates/{name}.php`
- Wrap content in `<div class="page" data-barba="container" data-barba-namespace="{name}">`
- Build props array from `$page->field()` calls
- Output the layout Svelte component + JSON script tag
- End with `snippet("scripts")`

### Step 7: Create block snippets

For each block blueprint: `site/snippets/blocks/b-{name}.php`
- Build props from `$block->field()` calls
- Output the block Svelte component + JSON script tag with unique `$blockId`

### Step 8: Create Svelte components

For each layout: `svelte/src/components/layout/Layout{Name}.svelte`
For each block: `svelte/src/components/blocks/Block{Name}.svelte`
For global elements: `svelte/src/components/global/{Name}.svelte`

Register everything in `svelte/src/main.ts`.

### Step 9: Build Svelte

```bash
cd svelte && npm run build
```

This writes to `assets/js/components.js` and `assets/css/svelte.css`.

### Step 10: Create initial content

Create content folders and `.txt` files for required pages. At minimum:
```
content/
  1_home/home.txt          ← Title: Home
  error/error.txt          ← Title: Error
  site.txt                 ← Title: My Site
```

### Step 11: Test locally

```bash
php -S localhost:8000 kirby/router.php
```

Visit `http://localhost:8000/panel` to set up the admin account and populate content.

### Step 12: Commit everything

```bash
git add .
git commit -m "initial scaffold"
```

**Important:** Commit `assets/js/components.js` and `assets/css/svelte.css`. The Docker build does NOT run `npm build` — it only runs `composer install`. The Svelte build artifacts must be in the repo.

### Step 13: Deploy to Railway

```bash
railway login
railway init          # create new project
railway up            # deploy from current directory
```

Or connect your GitHub repo in the Railway dashboard for automatic deploys on push.

### Step 14: First-run Panel setup

Visit `https://your-site.railway.app/panel` and create the admin user account. This creates `site/accounts/{email}/index.php`.

---

## 21. Adding a New Block Type

**Example: adding a `b-quote` block (pull quote with attribution)**

### 1. Blueprint: `site/blueprints/blocks/b-quote.yml`
```yaml
name: Pull Quote
icon: quote
preview: fields

fields:
  quote:
    type: writer
    label: Quote
    marks:
      - italic
      - bold

  attribution:
    type: text
    label: Attribution
```

### 2. Snippet: `site/snippets/blocks/b-quote.php`
```php
<?php
$blockNamespace = $blockNamespace ?? "blk";
$blockId = $blockNamespace . "-" . $block->id();
$props = [
    "quote" => (string) $block->quote()->kt(),
    "attribution" => $block->attribution()->value(),
];
?>
<b-quote id="<?= $blockId ?>"></b-quote>
<script type="application/json" data-for="<?= $blockId ?>">
<?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
</script>
```

### 3. Svelte component: `svelte/src/components/blocks/BlockQuote.svelte`
```svelte
<script lang="ts">
  let { quote = "", attribution = "" } = $props();
</script>

<blockquote class="pull-quote">
  <div class="quote-text">{@html quote}</div>
  {#if attribution}
    <cite class="attribution">{attribution}</cite>
  {/if}
</blockquote>

<style>
  .pull-quote {
    border-left: 2px solid var(--_themes---site--border--border-primary);
    padding: 1rem 2rem;
    margin: 2rem 0;
  }
  .attribution {
    font-style: normal;
    opacity: 0.6;
    font-size: 0.875rem;
  }
</style>
```

### 4. Register in `svelte/src/main.ts`
```ts
import BlockQuote from "./components/blocks/BlockQuote.svelte";
registerSvelteElement("b-quote", BlockQuote, ["quote", "attribution"]);
```

### 5. Add to page blueprints
In any page blueprint that should allow this block:
```yaml
fields:
  blocks:
    type: blocks
    fieldsets:
      - b-header
      - b-quote    # add here
      - b-text
```

### 6. Rebuild Svelte
```bash
cd svelte && npm run build
```

### 7. Commit and deploy
```bash
git add .
git commit -m "add b-quote block"
git push  # Railway auto-deploys if connected
```

---

## 22. Adding a New Page Type

**Example: a `services` page listing service offerings**

### 1. Blueprint: `site/blueprints/pages/services.yml`
```yaml
title: Services

options:
  changeSlug: false
  delete: false

columns:
  - width: 2/3
    sections:
      content:
        type: fields
        fields:
          page_heading:
            type: writer
            label: Heading
            marks:
              - italic

          services_list:
            type: structure
            label: Services
            fields:
              name:
                type: text
                label: Service Name
                required: true
              description:
                type: writer
                label: Description
                marks:
                  - bold
                  - italic
                  - link
              icon:
                type: files
                label: Icon
                max: 1
                uploads:
                  template: image

  - width: 1/3
    sticky: true
    sections:
      seo:
        type: fields
        label: SEO
        fields:
          meta_title:
            type: text
          meta_description:
            type: textarea
            maxlength: 160
          meta_image:
            type: files
            max: 1
            uploads:
              template: image
```

### 2. Template: `site/templates/services.php`
```php
<?php snippet("head"); ?>
<?php snippet("header"); ?>

<?php
$services = [];
foreach ($page->services_list()->toStructure() as $item) {
    $icon = $item->icon()->toFile();
    $services[] = [
        "name" => $item->name()->value(),
        "description" => (string) $item->description()->kt(),
        "icon" => $icon?->url() ?? "",
    ];
}

$id = "services-page";
$props = [
    "heading" => strip_tags(
        (string) $page->page_heading()->kti(),
        "<em><i><strong><b>"
    ),
    "services" => $services,
];
?>

<div class="page" data-barba="container" data-barba-namespace="services">
<main class="main">
<l-services id="<?= $id ?>"></l-services>
<script type="application/json" data-for="<?= $id ?>">
<?= json_encode($props, JSON_UNESCAPED_SLASHES) ?>
</script>
</main>
<?php snippet("footer"); ?>
</div>
<?php snippet("scripts"); ?>
```

### 3. Layout component: `svelte/src/components/layout/LayoutServices.svelte`
```svelte
<script lang="ts">
  interface Service {
    name: string;
    description: string;
    icon: string;
  }

  let { heading = "", services = [] as Service[] } = $props();
</script>

<section class="services-layout">
  <header class="services-header">
    <h1>{@html heading}</h1>
  </header>

  <div class="services-grid">
    {#each services as service}
      <div class="service-card">
        {#if service.icon}
          <img src={service.icon} alt="" class="service-icon" />
        {/if}
        <h3>{service.name}</h3>
        <div>{@html service.description}</div>
      </div>
    {/each}
  </div>
</section>

<style>
  .services-layout { padding: var(--padding--md); }
  .services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--gap--lg);
  }
</style>
```

### 4. Register in `svelte/src/main.ts`
```ts
import LayoutServices from "./components/layout/LayoutServices.svelte";
registerSvelteElement("l-services", LayoutServices, ["heading", "services"]);
```

### 5. Create content
```
content/
  5_services/
    services.txt    ← Title: Services
```

### 6. Add to site blueprint (optional)
If you want this page accessible from the Panel's main pages list:
```yaml
# site/blueprints/site.yml
sections:
  pages:
    type: pages
    templates:
      - home
      - about
      - services   # add here
```

---

## 23. Adding a New Svelte Component

For a UI component (not a full layout, not a block):

### 1. Create `svelte/src/components/ui/MyWidget.svelte`
```svelte
<script lang="ts">
  let { value = "", onClick = undefined } = $props();
</script>

<div class="widget" on:click={onClick}>
  {value}
</div>
```

### 2. Register in `main.ts`
```ts
import MyWidget from "./components/ui/MyWidget.svelte";
registerSvelteElement("c-my-widget", MyWidget, ["value"]);
```

### 3. Use in PHP templates
```php
<c-my-widget id="widget1"></c-my-widget>
<script type="application/json" data-for="widget1">
{"value": "<?= $page->some_field()->value() ?>"}
</script>
```

Or with simple string props via attributes:
```html
<c-my-widget value="hello world"></c-my-widget>
```

---

## 24. Kirby Panel First-Run Setup

The first time you visit `/panel` on a fresh installation, Kirby shows a setup screen:

1. Visit `https://your-domain.com/panel`
2. Fill in: email, name, password
3. Kirby creates `site/accounts/{uuid}/index.php`

**If deploying to Railway where the filesystem resets on deploy:**
- You can pre-commit the accounts directory. Kirby stores accounts as PHP files in `site/accounts/`.
- Create the account locally, commit `site/accounts/`, and it will persist across deploys.
- **Security note:** Account files contain hashed passwords — safe to commit, but don't use guessable passwords.

**To change password:** Use the Panel → User settings, or delete the account file and re-run first-run setup.

---

## 25. Common Pitfalls & Gotchas

### Reserved field names in structures
Never use `content`, `id`, `parent`, `site`, `kirby` as field names inside `type: structure` fields. Use `$item->toArray()['fieldname']` to work around this.

### Svelte scoped CSS doesn't apply to `{@html}` content
Kirbytext renders HTML via `{@html body}`. Svelte's scoped CSS doesn't pierce dynamically injected HTML. Use `:global(...)` selectors or a wrapping class with unscoped child selectors:
```css
.content-wrapper :global(p) { margin-bottom: 1em; }
.content-wrapper :global(a) { color: white; }
```

### Build artifacts must be committed
`assets/js/components.js` and `assets/css/svelte.css` must be in git. The Dockerfile runs `composer install` but NOT `npm install` or `npm build`. If you forget to commit after a Svelte change, production will run old JS.

### `debug: true` in production
Exposes full PHP stack traces. Always set `"debug" => false` in `config.php` before deploying.

### Barba.js and panel links
The panel at `/panel` must be excluded from Barba transitions. Add `data-barba-prevent` to any link to the Panel:
```php
<a href="<?= $kirby->panel()->url() ?>" data-barba-prevent>Panel</a>
```

### Thumbnail driver
`config.php` auto-detects `imagick` vs `gd`. The Dockerfile installs both. If you remove `imagick` from the Dockerfile, `gd` will be used (less powerful but sufficient for basic resizing).

### Apache MPM conflict
Railway's base image sometimes has `mpm_event` enabled. Kirby needs `mpm_prefork` for PHP. The CMD in the Dockerfile explicitly disables `mpm_event`/`mpm_worker` and enables `mpm_prefork` before starting Apache.

### `mod_rewrite` must be enabled
All requests go through `index.php` via `.htaccess` rewrites. `a2enmod rewrite` is in the Dockerfile. If you switch to nginx, you need equivalent rewrite rules.

### Content UUIDs after manual editing
Kirby assigns UUIDs to pages and files. If you manually create content `.txt` files without UUIDs, Kirby will generate them on first access. Don't manually change UUIDs — references in other pages (e.g. `page://uuid` in a pages field) will break.

### JSON escaping in PHP
Always use `json_encode($props, JSON_UNESCAPED_SLASHES)` for the sibling script tag. Without `JSON_UNESCAPED_SLASHES`, URLs with slashes get double-escaped. Avoid `JSON_PRETTY_PRINT` in production (slightly larger payload, parsing is the same).

### Svelte 5 prop reactivity
Props from `$props()` are reactive by default in Svelte 5. However, since our components are mounted once (when the custom element connects) and receive props from static JSON, reactivity isn't used in practice. If you need to update a component after mount, you'd need to use Svelte's `$state` + a custom event system or re-mount the component.

### No slots with custom elements
Because we use `shadow: "none"` (no Shadow DOM), `<slot>` elements in Svelte components don't project content. All content must be passed as props. For rich HTML content, pass it as a KirbyText-rendered string and use `{@html}` in the component.

### `kirby/router.php` for local dev only
Kirby's built-in PHP server router (`kirby/router.php`) is only for local development. Production uses Apache with `.htaccess`. Never reference this file in deployment config.

### File permissions on Railway
The Dockerfile sets `chown -R www-data:www-data /var/www/html`. If Railway's container runs as a different user, `media/` writes will fail. Check Railway's container user if you see file permission errors.

### `yaml.handler: symfony` required in Kirby 5
Kirby 5 requires this in `config.php`. Without it, blueprint YAML parsing fails on some fields.
