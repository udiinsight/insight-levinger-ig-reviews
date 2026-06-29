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

	/**
	 * Fetch video reviews (have a video + a doctor + a procedure) as plain arrays.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return array<int,array>
	 */
	public static function get_reviews( $atts ) {
		$args = array(
			'post_type'              => '_reviews',
			'post_status'            => 'publish',
			'posts_per_page'         => isset( $atts['limit'] ) ? (int) $atts['limit'] : -1,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				array( 'key' => 'reviewsvideo', 'value' => '', 'compare' => '!=' ),
				array( 'key' => 'doctor', 'value' => '', 'compare' => '!=' ),
				array( 'key' => 'procedure', 'value' => '', 'compare' => '!=' ),
			),
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
