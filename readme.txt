=== Levinger IG Reviews ===
Contributors: insightmarketing
Tags: reviews, testimonials, instagram, video, rtl
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later

Instagram-style video reviews feed for Dr. Levinger.

== Description ==

Renders a filterable, RTL grid of video testimonials from the existing `_reviews`
custom post type, with an immersive (Reels-style) lightbox. The plugin is read-only
over existing data: it queries reviews that have a video plus a doctor and procedure
relation, and does not modify the existing /reviews/ page or any existing field.

Shortcode: `[levinger_ig_reviews]`

Attributes:

* `columns`   — grid columns on desktop (default 4; clamped 2–6)
* `limit`     — max reviews (default -1 = all)
* `procedure` — pre-filter by procedure slug
* `doctor`    — pre-filter by doctor slug
* `accent`    — teal | navy | dark | purple | #hex (default teal)
* `cta_url`   — consultation link shown on the lightbox CTA
* `cta_text`  — CTA label (default: קבעו תור ייעוץ)

The feed shows a review when its `reviewsvideo` field is set and it has a `doctor` and
`procedure` relation. Optional fields used when present: `review_transcript` (lightbox
live-captions + SEO), `review_duration` (card duration pill), `ig_permalink` (share).

== Changelog ==

= 0.1.0 =
* Initial build: light feed, circular procedure filters, doctor dropdown, immersive lightbox.
