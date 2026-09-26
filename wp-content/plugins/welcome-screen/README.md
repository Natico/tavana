# Welcome Screen

Standalone WordPress plugin for a fullscreen Welcome overlay.

## Purpose

Welcome Screen lets administrators create flexible Gutenberg-managed welcome content and show it as an overlay before the visitor continues to the requested frontend experience.

## Architecture

- Owns the `ws_screen` internal custom post type.
- Owns settings, frontend eligibility, overlay rendering, CSS, and JavaScript.
- Does not depend on ART CMS or ART Theme.
- Does not create public URLs, archives, or rewrite rules for Welcome Screens.

## Settings

- Enabled: Yes or No.
- Welcome Content: a published `ws_screen` record.
- Display On: `home` or `all`.
- Frequency: `once` or `session`.

## Content Model

Welcome content is edited with native Gutenberg. The public overlay renders the resolved post content only. The post title is used for admin identification and is not rendered automatically.

## Polylang

The plugin exposes `ws_screen` through Polylang Free's custom post type integration. When Polylang is available, the configured base screen is resolved to the current language with guarded Polylang APIs and falls back to the base screen when no translation exists.

## Frequency

`once` uses `localStorage`. `session` uses `sessionStorage`. The storage key includes the configured base Welcome Screen ID so selecting different content does not reuse an unrelated dismissed state.

## Site Mode

For normal theme-rendered requests, the plugin uses standard frontend hooks. For Site Mode's standalone Coming Soon document, the plugin uses generic Site Mode extension hooks:

- `site_mode_standalone_head`
- `site_mode_standalone_footer`
