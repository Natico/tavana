# Art CMS

Art CMS owns the content models and business rules for the Art WordPress site. It intentionally avoids presentation concerns so the active theme can own templates and frontend rendering later.

## Registered Custom Post Types

- `art_product`: showcase products, not ecommerce.
- `art_gallery_item`: gallery content items.
- `art_contact_msg`: admin-only stored contact submission records.

## Registered Taxonomies

- `art_product_category`: hierarchical product categories attached to `art_product`.
- `art_gallery_category`: hierarchical gallery categories attached to `art_gallery_item`.

## Current Scope

This first version registers the core content types and taxonomies, provides a small plugin bootstrap, and flushes rewrite rules safely on activation and deactivation.

The content post types are grouped under the `ART` admin menu for easier editorial navigation.

No WordPress core files, third-party plugins, theme templates, database tables, or external PHP dependencies are modified or introduced.

## Intentionally Not Implemented Yet

- Multilingual support.
- SEO integrations or metadata.
- Template system.
- Validation rules.
- Ordering features.
- Duplicate handling.
- Custom routing or rewrite hierarchy.
- Writer role or custom capability logic.
- Media relationships.
- Contact form.
