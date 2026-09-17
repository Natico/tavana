# Product And Gallery Meta Planning

This document plans the next content-model layer for `art_product` and `art_gallery_item`.

It does not implement custom fields. Each approved field group should become a separate feature branch before code is added.

## Current Content Model

### Product

Post type:

- `art_product`

Current supports:

- title
- editor
- revisions
- author
- thumbnail
- custom fields

Taxonomy:

- `art_product_category`

### Gallery Item

Post type:

- `art_gallery_item`

Current supports:

- title
- editor
- revisions
- author
- thumbnail
- custom fields

Taxonomy:

- `art_gallery_category`

## Modeling Rules

Use taxonomy when the value is shared, filterable, and reused across many entries.

Use post meta when the value belongs to one entry and is not primarily a grouping or navigation concept.

Keep frontend presentation out of the plugin. The plugin should define and store content data; the theme should decide how to render it.

Do not add custom database tables for these fields unless WordPress post meta becomes clearly insufficient.

## Proposed Product Fields

These are candidate post meta fields for future implementation:

- subtitle: short secondary title for editorial display.
- material: concise material or medium description.
- dimensions: human-readable size or dimensions.
- production_year: year or period of creation.
- availability_note: non-ecommerce availability text such as available, archived, or by request.
- inquiry_label: optional text for future inquiry actions.

Recommended first implementation set:

- subtitle
- material
- dimensions
- production_year
- availability_note

Deferred:

- pricing
- inventory
- cart/ecommerce behavior
- product variants
- payment or order data

## Proposed Gallery Item Fields

These are candidate post meta fields for future implementation:

- subtitle: short secondary title for editorial display.
- artwork_date: date, year, or period associated with the item.
- medium: material or medium description.
- dimensions: human-readable size or dimensions.
- location_note: optional note such as studio, exhibition, collection, or archive.
- credit_line: attribution or credit text.

Recommended first implementation set:

- subtitle
- artwork_date
- medium
- dimensions
- credit_line

Deferred:

- complex media relationships
- slideshow ordering
- external collection IDs
- exhibition routing
- downloadable asset management

## Taxonomy Candidates

Potential future taxonomies should be considered only if real content needs them:

- product series
- product material group
- gallery collection
- gallery technique
- exhibition or project

Do not add these until content examples prove they are reused grouping concepts.

## Media Relationship Notes

The current foundation relies on WordPress featured images and editor media.

Future media relationship work should be separate from basic meta fields. It may include:

- multiple curated images per product or gallery item
- image captions and credits
- ordered media sets
- admin UI for selecting related media

This should not be mixed into the first meta-field implementation.

## Suggested Feature Sequence

1. `feature/product-basic-fields`
   Add the first approved product meta fields and admin UI.

2. `feature/gallery-basic-fields`
   Add the first approved gallery item meta fields and admin UI.

3. `feature/product-gallery-admin-columns`
   Surface selected meta values in admin list tables.

4. `feature/media-relationships-planning`
   Plan richer media relationships after real content examples are available.

## Open Questions

- Should `production_year` and `artwork_date` be strict numeric fields or flexible text?
- Should availability be free text first, or a controlled option set?
- Are product and gallery fields similar enough to share helper classes?
- Which fields should be exposed through REST for future editor or frontend use?
