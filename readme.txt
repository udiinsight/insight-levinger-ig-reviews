=== Levinger IG Reviews ===
Contributors: insightmarketing
Tags: reviews, testimonials, instagram, video, rtl
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 0.3.1
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
* `procedure` — scope the feed to one procedure (see below)
* `doctor`    — scope the feed to one doctor (see below)
* `accent`    — teal | navy | dark | purple | #hex (default teal)
* `cta_url`   — consultation link shown on the lightbox CTA
* `cta_text`  — CTA label (default: קבעו תור ייעוץ)

The feed shows a review when its `reviewsvideo` field is set and it has a `doctor` and
`procedure` relation. Optional fields used when present: `review_transcript` (lightbox
live-captions + SEO), `review_duration` (card duration pill), `ig_permalink` (share).

== Scoping the feed to a procedure or doctor ==

`procedure` and `doctor` filter the query itself — only matching reviews are fetched —
and the matching filter control is hidden, so a scoped feed cannot be filtered away from
the page it sits on. Each accepts, in order:

* `current`  — the post being viewed. Drop `[levinger_ig_reviews procedure="current"]`
               into a single-procedure template and it needs no other configuration.
* a post ID  — e.g. `procedure="8099"`
* a slug     — e.g. `procedure="laser-glasses-removal"`
* an `ig_tag` — e.g. `procedure="ניתוח_קטרקט_מסורתי"`, useful because Hebrew slugs are
               percent-encoded and impractical to type

Polylang translations share a slug, so a slug lookup prefers the Hebrew post. Scoping to
something that matches nothing shows the empty state rather than the whole feed.

== Changelog ==

= 0.3.0 =
* `procedure`/`doctor` now filter the query server-side instead of hiding cards in JS,
  accept `current`/ID/slug/ig_tag, and hide the filter control they scope by.

= 0.2.0 =
* Auto-fill `ig_tag` on newly published doctors and procedures, with a uniqueness guard.

= 0.1.0 =
* Initial build: light feed, circular procedure filters, doctor dropdown, immersive lightbox.
