# Media Relationships Planning

This document plans future media relationship work for `art_product` and `art_gallery_item`.

It does not implement media fields, gallery ordering, upload handling, or frontend rendering.

## Current Media Model

The current project foundation supports:

- WordPress featured image for products and gallery items.
- Editor-managed media inside post content.
- Custom admin meta fields for product and gallery details.

This is enough for the current foundation but not enough for curated multi-image product or gallery presentations.

## Goals

Future media relationship work should support:

- Multiple curated images per product or gallery item.
- Manual image ordering.
- Optional captions or credit text per related image.
- Reuse of existing WordPress Media Library attachments.
- Admin UI that is understandable for editors.
- Theme-owned presentation.

## Non-Goals

Do not include these in the first media relationship implementation:

- Custom database tables.
- Frontend carousel or slideshow UI.
- Image processing pipelines.
- Asset download management.
- External DAM integrations.
- Per-image commerce data.
- Replacing the WordPress Media Library.

## Candidate Storage Options

### Attachment ID List In Post Meta

Store an ordered list of attachment IDs in post meta.

Pros:

- Simple.
- Native WordPress attachment IDs.
- Easy to query with post meta.
- Good fit for ordered image sets.

Cons:

- Per-image captions or custom credit overrides need an additional data structure.
- Requires careful sanitization and validation.

### Structured JSON In Post Meta

Store ordered objects in post meta, for example attachment ID plus caption override.

Pros:

- Supports richer per-image metadata.
- Still avoids custom tables.

Cons:

- More validation complexity.
- Harder to inspect manually.
- Needs disciplined schema versioning if it grows.

### Child Posts

Represent media relationships as child posts.

Pros:

- Better for complex relationship records.
- Native post querying.

Cons:

- More UI complexity.
- Likely too heavy for the first implementation.

## Recommended First Approach

Start with an ordered attachment ID list in post meta.

Suggested meta keys:

- `_art_product_media_ids`
- `_art_gallery_media_ids`

Store values as an array of positive integer attachment IDs.

Defer per-image custom captions and credit overrides until real content proves the need. Use WordPress attachment captions and descriptions first.

## Admin UI Requirements

A future admin UI should allow editors to:

- Add images from the Media Library.
- Remove images from the relationship list.
- Reorder images manually.
- See image thumbnails.
- Keep the featured image separate from the curated media set.

The UI should not require a JavaScript framework. Use WordPress admin scripts and Media Library APIs where possible.

## Validation Rules

Future implementation should:

- Accept only positive integer attachment IDs.
- Confirm each ID belongs to an attachment.
- Remove duplicates while preserving order.
- Delete empty meta.
- Save only for users who can edit the parent post.
- Use nonce checks and autosave guards.

## Suggested Feature Sequence

1. `feature/product-media-relationships`
   Add ordered product attachment IDs and admin UI.

2. `feature/gallery-media-relationships`
   Add ordered gallery attachment IDs and admin UI.

3. `feature/media-admin-preview-polish`
   Improve editor preview, empty states, and ordering controls.

4. `feature/media-template-integration`
   Let the theme render curated media sets after the data model is stable.

## Open Questions

- Should products and gallery items share one reusable media field service?
- Should the first UI support drag-and-drop ordering or explicit move buttons?
- Should the featured image automatically appear first in the rendered media set, or stay separate?
- Should attachment captions be trusted as the first source of display captions?
- Should REST exposure be required for future editor integrations?
