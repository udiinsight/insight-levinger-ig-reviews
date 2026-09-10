<?php
/**
 * Read-only query layer over the existing `_reviews` CPT.
 *
 * @package levinger-ig-reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LIR_Query {

	/** Returned by resolve_related() when a scope was asked for but matches nothing. */
	const NOT_FOUND = -1;

	/**
	 * Resolve a `procedure`/`doctor` shortcode value to a post ID.
	 *
	 * Accepts "current" (the queried object), a numeric ID, a slug, or — because
	 * Hebrew slugs are percent-encoded and unusable by hand — the post's `ig_tag`.
	 *
	 * @param string $value     Attribute value.
	 * @param string $post_type 'procedure' or 'doctor'.
	 * @return int Post ID, 0 when not scoped, or self::NOT_FOUND.
	 */
	public static function resolve_related( $value, $post_type ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return 0;
		}

		if ( 'current' === $value ) {
			$obj = get_queried_object();
			return ( $obj instanceof WP_Post && $post_type === $obj->post_type ) ? (int) $obj->ID : self::NOT_FOUND;
		}

		if ( ctype_digit( $value ) ) {
			return (int) $value;
		}

		foreach ( array( $value, sanitize_title( $value ) ) as $name ) {
			$found = get_posts(
				array(
					'post_type'   => $post_type,
					'post_status' => 'publish',
					'name'        => $name,
					'fields'      => 'ids',
					'numberposts' => -1,
				)
			);
			if ( $found ) {
				return self::prefer_hebrew( $found );
			}
		}

		$found = get_posts(
			array(
				'post_type'   => $post_type,
				'post_status' => 'publish',
				'meta_key'    => 'ig_tag',
				'meta_value'  => ltrim( $value, '#' ),
				'fields'      => 'ids',
				'numberposts' => -1,
			)
		);
		return $found ? self::prefer_hebrew( $found ) : self::NOT_FOUND;
	}

	/**
	 * Polylang translations share a slug — `laser-glasses-removal` exists as he, en and
	 * ar posts — so a bare slug lookup is ambiguous. Reviews are Hebrew, so prefer that.
	 *
	 * @param array $ids Candidate post ids.
	 * @return int
	 */
	protected static function prefer_hebrew( $ids ) {
		if ( function_exists( 'pll_get_post_language' ) ) {
			foreach ( $ids as $id ) {
				if ( 'he' === pll_get_post_language( $id ) ) {
					return (int) $id;
				}
			}
		}
		return (int) $ids[0];
	}

	/**
	 * Match an ACF relation, which stores either a serialized array of ids or a bare id.
	 *
	 * @param string $key Meta key.
	 * @param int    $id  Related post id.
	 * @return array
	 */
	protected static function relation_clause( $key, $id ) {
		return array(
			'relation' => 'OR',
			array( 'key' => $key, 'value' => '"' . (int) $id . '"', 'compare' => 'LIKE' ),
			array( 'key' => $key, 'value' => (string) (int) $id, 'compare' => '=' ),
		);
	}

	/**
	 * Fetch video reviews (have a video + a doctor + a procedure) as plain arrays.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return array<int,array>
	 */
	public static function get_reviews( $atts ) {
		$procedure = isset( $atts['_procedure_id'] )
			? (int) $atts['_procedure_id']
			: self::resolve_related( isset( $atts['procedure'] ) ? $atts['procedure'] : '', 'procedure' );
		$doctor    = isset( $atts['_doctor_id'] )
			? (int) $atts['_doctor_id']
			: self::resolve_related( isset( $atts['doctor'] ) ? $atts['doctor'] : '', 'doctor' );

		// Scoped to something that does not exist — show nothing, never everything.
		if ( self::NOT_FOUND === $procedure || self::NOT_FOUND === $doctor ) {
			return array();
		}

		$meta = array(
			array( 'key' => 'reviewsvideo', 'value' => '', 'compare' => '!=' ),
			array( 'key' => 'doctor', 'value' => '', 'compare' => '!=' ),
			array( 'key' => 'procedure', 'value' => '', 'compare' => '!=' ),
		);
		if ( $procedure > 0 ) {
			$meta[] = self::relation_clause( 'procedure', $procedure );
		}
		if ( $doctor > 0 ) {
			$meta[] = self::relation_clause( 'doctor', $doctor );
		}

		$args = array(
			'post_type'              => '_reviews',
			'post_status'            => 'publish',
			'posts_per_page'         => isset( $atts['limit'] ) ? (int) $atts['limit'] : -1,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_query'             => $meta,
		);

		$query = new WP_Query( $args );
		$out   = array();

		foreach ( $query->posts as $post ) {
			$review = self::build_review( $post );
			if ( $review ) {
				$out[] = $review;
			}
		}

		wp_reset_postdata();
		return $out;
	}

	/**
	 * Build the data array for one review post.
	 *
	 * @param WP_Post $post Review post.
	 * @return array|null
	 */
	protected static function build_review( $post ) {
		$id    = $post->ID;
		$video = self::field( 'reviewsvideo', $id );
		if ( is_array( $video ) && isset( $video['url'] ) ) {
			$video = $video['url'];
		}
		if ( empty( $video ) ) {
			return null;
		}

		$doctors    = self::normalize_posts( self::field( 'doctor', $id ) );
		$procedures = self::normalize_posts( self::field( 'procedure', $id ) );
		$doctor     = ! empty( $doctors ) ? $doctors[0] : null;

		$proc_list = array();
		foreach ( $procedures as $proc ) {
			$proc_list[] = array(
				'name' => html_entity_decode( get_the_title( $proc ), ENT_QUOTES, 'UTF-8' ),
				'slug' => $proc->post_name,
				'icon' => (string) self::field( 'feed_icon', $proc->ID ),
			);
		}

		$poster = get_the_post_thumbnail_url( $id, 'large' );

		$quote = html_entity_decode(
			$post->post_excerpt
				? wp_strip_all_tags( $post->post_excerpt )
				: wp_trim_words( wp_strip_all_tags( $post->post_content ), 26, '…' ),
			ENT_QUOTES,
			'UTF-8'
		);

		return array(
			'id'           => $id,
			'name'         => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'video'        => esc_url_raw( $video ),
			'poster'       => $poster ? esc_url_raw( $poster ) : '',
			'duration'     => trim( (string) self::field( 'review_duration', $id ) ),
			'doctor'       => $doctor ? html_entity_decode( get_the_title( $doctor ), ENT_QUOTES, 'UTF-8' ) : '',
			'doctorSlug'   => $doctor ? $doctor->post_name : '',
			'doctorAvatar' => $doctor ? (string) get_the_post_thumbnail_url( $doctor->ID, 'thumbnail' ) : '',
			'doctorUrl'    => $doctor ? get_permalink( $doctor ) : '',
			'procedures'   => $proc_list,
			'quote'        => $quote,
			'transcript'   => trim( html_entity_decode( wp_strip_all_tags( (string) self::field( 'review_transcript', $id ) ), ENT_QUOTES, 'UTF-8' ) ),
			'igUrl'        => esc_url_raw( (string) self::field( 'ig_permalink', $id ) ),
		);
	}

	/**
	 * Collect the distinct procedures & doctors present in the result set (for the filter UI).
	 *
	 * @param array $reviews Reviews from get_reviews().
	 * @return array{procedures:array<string,string>,doctors:array<string,string>}
	 */
	public static function get_filters( $reviews ) {
		$procedures = array();
		$doctors    = array();

		foreach ( $reviews as $review ) {
			foreach ( $review['procedures'] as $proc ) {
				if ( $proc['slug'] && ! isset( $procedures[ $proc['slug'] ] ) ) {
					$procedures[ $proc['slug'] ] = array(
						'name' => $proc['name'],
						'icon' => isset( $proc['icon'] ) ? $proc['icon'] : '',
					);
				}
			}
			if ( $review['doctorSlug'] && ! isset( $doctors[ $review['doctorSlug'] ] ) ) {
				$doctors[ $review['doctorSlug'] ] = $review['doctor'];
			}
		}

		return array(
			'procedures' => $procedures,
			'doctors'    => $doctors,
		);
	}

	/**
	 * Read an ACF field, falling back to raw post meta if ACF is unavailable.
	 *
	 * @param string $name Field name.
	 * @param int    $id   Post ID.
	 * @return mixed
	 */
	protected static function field( $name, $id ) {
		if ( function_exists( 'get_field' ) ) {
			return get_field( $name, $id );
		}
		return get_post_meta( $id, $name, true );
	}

	/**
	 * Normalise an ACF post_object/relationship value to an array of WP_Post.
	 *
	 * @param mixed $value ID|WP_Post|array of either.
	 * @return WP_Post[]
	 */
	protected static function normalize_posts( $value ) {
		$out = array();
		if ( empty( $value ) ) {
			return $out;
		}
		$items = is_array( $value ) ? $value : array( $value );
		foreach ( $items as $item ) {
			if ( $item instanceof WP_Post ) {
				$out[] = $item;
			} elseif ( is_numeric( $item ) ) {
				$post = get_post( (int) $item );
				if ( $post && 'publish' === $post->post_status ) {
					$out[] = $post;
				}
			}
		}
		return $out;
	}
}
