# Art CMS

Art CMS owns the content models and business rules for the Art WordPress site. It intentionally avoids presentation concerns so the active theme can own templates and frontend rendering later.

## Registered Custom Post Types

- `art_product`: showcase products, not ecommerce.
- `art_gallery_item`: gallery collections.

## Registered Taxonomies

- `art_product_category`: hierarchical product categories attached to `art_product`.
- `art_gallery_category`: hierarchical gallery categories attached to `art_gallery_item`.

## Current Scope

This first version registers the core content types and taxonomies, provides a small plugin bootstrap, and flushes rewrite rules safely on activation and deactivation.

The content post types are grouped under the `ART` admin menu for easier editorial navigation. Contact management is owned by the standalone Contact Workflow plugin.

Products use a classic, template-driven editing screen instead of the block editor. Product title is stored in core `post_title`, the plain-text product description is stored in core `post_content`, and optional product data fields are provided for short description, product code, and design year. Product cover / hero images use the native WordPress Featured Image field.

Product Templates are developer-defined in code. The selected Product Template is stored on each Product as `_art_product_template`, and supported field states are `required`, `optional`, and `hidden`. Template visibility affects the Product admin form only; stored Product data remains independent of visibility. Frontend Product template rendering is not implemented yet.

Product Categories remain hierarchical WordPress taxonomy terms. WordPress owns their name, slug, parent, and description fields. Category cover / hero images are stored as `_art_product_category_cover_id` term meta referencing a WordPress Media Library image attachment. Product Category frontend pages are rendered by the active theme.

Gallery Collections use WordPress core post data for title, description, and status. Gallery Categories remain native taxonomy relationships. The selected Gallery Template is stored as `_art_gallery_template`, ordered Gallery Media Items use the `_art_gallery_media_items` data contract, and Gallery Cover references one Image Media Item stable ID using `_art_gallery_cover_media_item_id`. The Gallery Media Manager supports images, uploaded videos, external video URLs, local item metadata, drag-and-drop ordering, and cover selection from image items only. Gallery Collections can reference multiple Products through Gallery-owned `_art_gallery_related_products` data. Related Products are currently admin/data only; Gallery frontend rendering is not implemented yet.

Gallery Categories remain hierarchical WordPress taxonomy terms. WordPress owns their name, slug, parent, and description fields. Gallery Category cover / hero images are stored as `_art_gallery_category_cover_id` term meta referencing a reusable WordPress Media Library image attachment. Gallery Category frontend rendering is not implemented yet.

No WordPress core files, third-party plugins, theme templates, database tables, or external PHP dependencies are modified or introduced.

## Intentionally Not Implemented Yet

- Multilingual support.
- SEO integrations or metadata.
- Template system.
- Validation rules.
- Ordering features.
- Duplicate handling.
- Custom routing or rewrite hierarchy.
- Writer role creation or CMS-wide role management.
- Media relationships.
- Contact management; use the standalone Contact Workflow plugin.
- Custom meta fields beyond the approved Product and Gallery data fields.
- Admin columns.
