# Prompt 2: Art CMS Plugin Foundation

```text
You are working inside an existing WordPress project.

Project root:
$HOME/Projects/art-site

Goal:
Create the first version of a custom WordPress plugin named `art-cms`.

Important architectural rules:

- Do not modify WordPress core.
- Do not modify third-party plugins.
- Do not couple content data to presentation templates.
- Plugin owns content models and business rules.
- Theme will own presentation later.
- Keep the implementation modular and object-oriented.
- Do not build frontend templates yet.
- Do not add external PHP dependencies.
- Keep WordPress coding standards in mind.
- Preserve compatibility with PHP 8.3 and current WordPress.

Create:

wp-content/plugins/art-cms/

Suggested structure:

art-cms/
├── art-cms.php
├── src/
│   ├── Plugin.php
│   ├── PostTypes/
│   │   ├── ProductPostType.php
│   │   ├── GalleryItemPostType.php
│   │   └── ContactSubmissionPostType.php
│   └── Taxonomies/
│       ├── ProductCategoryTaxonomy.php
│       └── GalleryCategoryTaxonomy.php
└── README.md

Requirements:

1. Bootstrap
   Create a proper plugin bootstrap file:
   `art-cms.php`

It must:

- define plugin constants if useful
- prevent direct file access
- load plugin classes
- initialize the plugin on the appropriate WordPress hook
- stay small and clean

2. Product Custom Post Type

Register a CPT with internal key:

art_product

Purpose:
Showcase products, not ecommerce.

Support standard WordPress features where appropriate:

- title
- editor
- revisions
- author
- thumbnail
- custom-fields

Use:

- public = true
- show_ui = true
- show_in_rest = true
- has_archive = false for now

Do not implement final frontend rewrite rules yet.

Use sensible admin labels:
Products
Product
Add New Product
Edit Product
etc.

3. Gallery Item Custom Post Type

Register:

art_gallery_item

Supports:

- title
- editor
- revisions
- author
- thumbnail
- custom-fields

Use:

- public = true
- show_ui = true
- show_in_rest = true
- has_archive = false for now

Use clear admin labels.

4. Contact Submission Custom Post Type

Register:

art_contact_submission

This is admin-only stored contact data.

Requirements:

- public = false
- publicly_queryable = false
- show_ui = true
- show_in_menu = true
- show_in_rest = false
- exclude_from_search = true
- supports title only if technically useful, otherwise minimal support
- no public archive
- no frontend rewrite

Do not implement the contact form yet.

5. Product Category Taxonomy

Register:

art_product_category

Attach it to:
art_product

Requirements:

- hierarchical = true
- unlimited hierarchy depth
- public = true
- show_ui = true
- show_in_rest = true
- show_admin_column = true

Do not implement custom URL hierarchy yet.

6. Gallery Category Taxonomy

Register:

art_gallery_category

Attach it to:
art_gallery_item

Requirements:

- hierarchical = true
- public = true
- show_ui = true
- show_in_rest = true
- show_admin_column = true

7. Plugin class

Create a central Plugin class responsible for initializing:

- ProductPostType
- GalleryItemPostType
- ContactSubmissionPostType
- ProductCategoryTaxonomy
- GalleryCategoryTaxonomy

Avoid global procedural code except the minimal bootstrap.

8. Activation / deactivation

Add safe activation and deactivation hooks.

On activation:

- register content types before flushing rewrite rules
- flush rewrite rules once

On deactivation:

- flush rewrite rules

Do not delete user content during deactivate/uninstall.

9. README

Create README.md documenting:

- plugin purpose
- registered CPTs
- registered taxonomies
- current scope
- explicitly list features intentionally NOT implemented yet:
  multilingual
  SEO
  template system
  validation
  ordering
  duplicate
  routing
  writer role
  media relationships
  contact form

10. Scope control

Do NOT implement anything beyond this task.

Do not:

- install plugins
- add Polylang
- add SEO plugins
- create database tables
- implement custom meta fields
- implement template registry
- implement frontend pages
- modify theme
- add JavaScript frameworks
- implement multilingual logic
- implement custom rewrite routing
- implement role/capability logic

11. Verification

After implementation:

- run PHP syntax checks on all created PHP files
- report the created files
- summarize the architecture
- tell me whether the plugin is ready to activate in WordPress

Do not activate the plugin automatically.
```
