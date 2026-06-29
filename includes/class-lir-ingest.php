<?php
/**
 * Phase 2 — Instagram ingestion receiver.
 *
 * REST endpoint that Make.com (or any authorized caller) POSTs new Instagram
 * testimonial videos to. Each call creates a DRAFT `_reviews` post, mapped to a
 * doctor + procedure via the post's #ig_tag hashtags, for staff to review/publish.
 *
 * @package levinger-ig-reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LIR_Ingest {

	const REST_NS       = 'levinger-reviews/v1';
	const ROUTE         = '/ingest';
	const SECRET_OPTION = 'lir_ingest_secret';
	const EMAIL_OPTION  = 'lir_ingest_email';

	public static function register_routes() {
		register_rest_route(
			self::REST_NS,
			self::ROUTE,
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle' ),
				'permission_callback' => array( __CLASS__, 'authorize' ),
			)
		);
	}

	/** Get (or lazily create) the shared secret token. */
	public static function get_secret() {
		$secret = get_option( self::SECRET_OPTION );
		if ( ! $secret ) {
			$secret = wp_generate_password( 40, false, false );
			update_option( self::SECRET_OPTION, $secret, false );
		}
		return $secret;
	}

	/** Authorize via the X-LIR-Token header (or ?token=) matched to the secret. */
	public static function authorize( $request ) {
		$provided = (string) $request->get_header( 'x-lir-token' );
		if ( '' === $provided ) {
			$provided = (string) $request->get_param( 'token' );
		}
		$secret = self::get_secret();
		return ( '' !== $provided && hash_equals( $secret, $provided ) );
	}

	public static function handle( WP_REST_Request $request ) {
		$p = $request->get_json_params();
		if ( ! is_array( $p ) ) {
			$p = $request->get_params();
		}

		$ig_media_id = isset( $p['ig_media_id'] ) ? sanitize_text_field( $p['ig_media_id'] ) : '';
		$caption     = isset( $p['caption'] ) ? wp_kses_post( $p['caption'] ) : '';
		$video_url   = isset( $p['video_url'] ) ? esc_url_raw( $p['video_url'] ) : '';
		$thumb_url   = isset( $p['thumbnail_url'] ) ? esc_url_raw( $p['thumbnail_url'] ) : '';
		$transcript  = isset( $p['transcript'] ) ? sanitize_textarea_field( $p['transcript'] ) : '';
		$duration    = isset( $p['duration'] ) ? sanitize_text_field( $p['duration'] ) : '';
		$permalink   = isset( $p['permalink'] ) ? esc_url_raw( $p['permalink'] ) : '';
		$patient     = isset( $p['patient_name'] ) ? sanitize_text_field( $p['patient_name'] ) : '';

		if ( ! $video_url ) {
			return new WP_REST_Response( array( 'status' => 'error', 'message' => 'missing video_url' ), 400 );
		}

		// Dedupe by Instagram media id.
		if ( $ig_media_id ) {
			$dupe = get_posts( array(
				'post_type'   => '_reviews',
				'post_status' => 'any',
				'meta_key'    => 'ig_media_id',
				'meta_value'  => $ig_media_id,
				'fields'      => 'ids',
				'numberposts' => 1,
			) );
			if ( $dupe ) {
				return new WP_REST_Response( array( 'status' => 'duplicate', 'id' => (int) $dupe[0] ), 200 );
			}
		}

		// Map hashtags → doctor + procedure(s).
		$tags = ( isset( $p['hashtags'] ) && is_array( $p['hashtags'] ) ) ? $p['hashtags'] : self::extract_hashtags( $caption );
		list( $doctor_id, $procedure_ids ) = self::resolve_tags( $tags );

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$post_id = wp_insert_post(
			array(
				'post_type'    => '_reviews',
				'post_status'  => 'draft',
				'post_title'   => $patient ? $patient : ( $caption ? wp_trim_words( wp_strip_all_tags( $caption ), 8, '' ) : 'המלצת וידאו' ),
				'post_content' => $caption,
				'post_excerpt' => $caption ? wp_trim_words( wp_strip_all_tags( $caption ), 26, '…' ) : '',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new WP_REST_Response( array( 'status' => 'error', 'message' => $post_id->get_error_message() ), 500 );
		}

		$video_id = self::sideload( $video_url, 'ig-review-' . $post_id . '.mp4', $post_id );
		if ( $video_id ) {
			self::set_field( 'reviewsvideo', $video_id, $post_id );
		}
		if ( $thumb_url ) {
			$thumb_id = self::sideload( $thumb_url, 'ig-review-' . $post_id . '.jpg', $post_id );
			if ( $thumb_id ) {
				set_post_thumbnail( $post_id, $thumb_id );
			}
		}
		if ( $doctor_id ) {
			self::set_field( 'doctor', $doctor_id, $post_id );
		}
		if ( ! empty( $procedure_ids ) ) {
			self::set_field( 'procedure', $procedure_ids, $post_id );
		}
		if ( $transcript ) {
			self::set_field( 'review_transcript', $transcript, $post_id );
		}
		if ( $duration ) {
			self::set_field( 'review_duration', $duration, $post_id );
		}
		if ( $permalink ) {
			self::set_field( 'ig_permalink', $permalink, $post_id );
		}
		if ( $ig_media_id ) {
			update_post_meta( $post_id, 'ig_media_id', $ig_media_id );
		}

		if ( function_exists( 'pll_set_post_language' ) ) {
			pll_set_post_language( $post_id, 'he' );
		}

		self::notify( $post_id, $doctor_id, $procedure_ids );

		return new WP_REST_Response(
			array(
				'status'     => 'created',
				'id'         => $post_id,
				'edit'       => admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
				'doctor'     => (int) $doctor_id,
				'procedures' => array_values( $procedure_ids ),
				'matched'    => (bool) ( $doctor_id || $procedure_ids ),
			),
			201
		);
	}

	protected static function extract_hashtags( $text ) {
		$out = array();
		if ( preg_match_all( '/#([\p{L}\p{N}_]+)/u', (string) $text, $m ) ) {
			$out = $m[1];
		}
		return $out;
	}

	protected static function resolve_tags( $tags ) {
		$doctor_id     = 0;
		$procedure_ids = array();
		foreach ( (array) $tags as $tag ) {
			$tag = ltrim( sanitize_text_field( $tag ), '#' );
			if ( '' === $tag ) {
				continue;
			}
			if ( ! $doctor_id ) {
				$d = self::find_by_tag( 'doctor', $tag );
				if ( $d ) {
					$doctor_id = $d;
					continue;
				}
			}
			$pr = self::find_by_tag( 'procedure', $tag );
			if ( $pr ) {
				$procedure_ids[] = $pr;
			}
		}
		return array( $doctor_id, array_values( array_unique( $procedure_ids ) ) );
	}

	protected static function find_by_tag( $post_type, $tag ) {
		$found = get_posts(
			array(
				'post_type'   => $post_type,
				'post_status' => 'publish',
				'meta_key'    => 'ig_tag',
				'meta_value'  => $tag,
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);
		return $found ? (int) $found[0] : 0;
	}

	protected static function sideload( $url, $name, $parent ) {
		$tmp = download_url( $url );
		if ( is_wp_error( $tmp ) ) {
			return 0;
		}
		$file = array( 'name' => $name, 'tmp_name' => $tmp );
		$id   = media_handle_sideload( $file, $parent );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			return 0;
		}
		return (int) $id;
	}

	protected static function set_field( $name, $value, $post_id ) {
		if ( function_exists( 'update_field' ) ) {
			update_field( $name, $value, $post_id );
		} else {
			update_post_meta( $post_id, $name, $value );
		}
	}

	protected static function notify( $post_id, $doctor_id, $procedure_ids ) {
		$to = get_option( self::EMAIL_OPTION );
		if ( ! $to ) {
			$to = get_option( 'admin_email' );
		}
		if ( ! $to ) {
			return;
		}
		$edit    = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
		$matched = ( $doctor_id || $procedure_ids ) ? 'מופתה אוטומטית לרופא/טיפול' : 'ללא מיפוי — נדרשת התאמה ידנית';
		$subject = 'המלצת וידאו חדשה מאינסטגרם — ממתינה לאישור';
		$body    = "התקבלה המלצת וידאו חדשה מאינסטגרם ונשמרה כטיוטה.\n\n";
		$body   .= "סטטוס מיפוי: {$matched}\n";
		$body   .= "לעריכה ולאישור (פרסום): {$edit}\n";
		wp_mail( $to, $subject, $body );
	}
}
