<?php
/**
 * Additive ACF fields on the _reviews CPT (registered locally by this plugin).
 * Purely additive — adds new fields to the review editor; touches nothing existing.
 *
 * @package levinger-ig-reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LIR_Fields {

	public static function register() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'         => 'group_lir_video',
				'title'       => 'פיד וידאו — אינסטגרם (Levinger IG Reviews)',
				'fields'      => array(
					array(
						'key'          => 'field_lir_transcript',
						'label'        => 'תמלול / כתוביות',
						'name'         => 'review_transcript',
						'type'         => 'textarea',
						'instructions' => 'תמלול הסרטון — מוצג ככתוביות בלייטבוקס וכטקסט לאינדוקס (SEO).',
						'rows'         => 4,
						'new_lines'    => 'wpautop',
					),
					array(
						'key'          => 'field_lir_duration',
						'label'        => 'משך הסרטון',
						'name'         => 'review_duration',
						'type'         => 'text',
						'instructions' => 'לדוגמה: 0:45 — מוצג על כרטיס הווידאו.',
						'placeholder'  => '0:45',
					),
					array(
						'key'          => 'field_lir_igperma',
						'label'        => 'קישור לפוסט באינסטגרם',
						'name'         => 'ig_permalink',
						'type'         => 'url',
						'instructions' => 'אופציונלי — לכפתור השיתוף בלייטבוקס.',
					),
					array(
						'key'          => 'field_lir_igmedia',
						'label'        => 'מזהה מדיה (Instagram)',
						'name'         => 'ig_media_id',
						'type'         => 'text',
						'instructions' => 'מנוהל אוטומטית ע"י הסנכרון מאינסטגרם — למניעת כפילויות.',
						'readonly'     => 1,
					),
				),
				'location'    => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => '_reviews',
						),
					),
				),
				'menu_order'  => 20,
				'position'    => 'normal',
				'description' => 'שדות נוספים עבור פיד וידאו ההמלצות בסגנון אינסטגרם.',
			)
		);

		acf_add_local_field_group(
			array(
				'key'         => 'group_lir_proc_icon',
				'title'       => 'אייקון לפיד וידאו (Levinger IG Reviews)',
				'fields'      => array(
					array(
						'key'          => 'field_lir_feed_icon',
						'label'        => 'אייקון בפילטר',
						'name'         => 'feed_icon',
						'type'         => 'select',
						'instructions' => 'האייקון שמוצג בעיגול הסינון בפיד הווידאו. ריק = אוטומטי לפי שם הטיפול.',
						'allow_null'   => 1,
						'ui'           => 1,
						'choices'      => array(
							'eye'        => 'עין',
							'smile'      => 'SMILE / חיוך',
							'prk'        => 'PRK / לייזר',
							'lasik'      => 'לאסיק',
							'lens'       => 'עדשה תוך-עינית',
							'wavefront'  => 'Wavefront / גלי',
							'monovision' => 'מונוויז\'ן',
						),
					),
				),
				'location'    => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'procedure',
						),
					),
				),
				'menu_order'  => 30,
				'position'    => 'side',
				'description' => 'בחירת אייקון לטיפול עבור פיד הווידאו בסגנון אינסטגרם.',
			)
		);

		acf_add_local_field_group(
			array(
				'key'         => 'group_lir_igtag',
				'title'       => 'תגית אינסטגרם (Levinger IG Reviews)',
				'fields'      => array(
					array(
						'key'          => 'field_lir_ig_tag',
						'label'        => 'תגית (hashtag) לסנכרון',
						'name'         => 'ig_tag',
						'type'         => 'text',
						'instructions' => 'התגית המזהה בפוסט באינסטגרם — בלי #. מזהה רופא מסוים (לא את המרפאה), ולכן יש להשתמש בשם פרטי + שם משפחה: shmuel_levinger, elia_levinger. בטיפול: smile. התגית חייבת להיות ייחודית — אין לחזור על אותה תגית ברופא אחר או בטיפול.',
					),
				),
				'location'    => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'doctor',
						),
					),
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'procedure',
						),
					),
				),
				'menu_order'  => 30,
				'position'    => 'side',
				'description' => 'תגית אינסטגרם למיפוי אוטומטי בסנכרון פיד הווידאו.',
			)
		);
	}

	/**
	 * Fill `ig_tag` on a newly published doctor/procedure, so the Instagram mapping
	 * keeps working as the directory grows. Only ever fills a blank — a value typed
	 * by hand is never overwritten.
	 */
	public static function autofill_tag( $post_id, $post ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		// Only Hebrew posts: SMILE and PRK exist as four Polylang translations each,
		// and tagging every language guarantees duplicate tags.
		if ( function_exists( 'pll_get_post_language' ) ) {
			$lang = pll_get_post_language( $post_id );
			if ( $lang && 'he' !== $lang ) {
				return;
			}
		}

		if ( '' !== (string) get_post_meta( $post_id, 'ig_tag', true ) ) {
			return;
		}

		$tag = self::title_to_tag( $post->post_title );
		if ( '' === $tag ) {
			return;
		}

		$base = $tag;
		for ( $n = 2; $n <= 50 && self::tag_taken( $tag, $post_id ); $n++ ) {
			$tag = $base . '_' . $n;
		}
		if ( self::tag_taken( $tag, $post_id ) ) {
			return;
		}

		if ( function_exists( 'update_field' ) ) {
			update_field( 'ig_tag', $tag, $post_id );
		} else {
			update_post_meta( $post_id, 'ig_tag', $tag );
		}
	}

	/** Strip the honorific and punctuation, leaving a hashtag-safe Hebrew tag. */
	public static function title_to_tag( $title ) {
		$t = trim( (string) $title );
		$t = preg_replace( '/^\s*(פרופסור|פרופ["\'\x{05F3}\x{05F4}]?|ד["\'\x{05F3}\x{05F4}]?ר|דר|מר|גב["\'\x{05F3}\x{05F4}]?)\s+/u', '', $t );
		$t = preg_replace( '/["\'\x{05F3}\x{05F4}\x{2018}\x{2019}\x{201C}\x{201D}]/u', '', $t );
		$t = preg_replace( '/[^\p{L}\p{N}]+/u', '_', $t );
		$t = preg_replace( '/_+/u', '_', $t );
		return trim( (string) $t, '_' );
	}

	/** A tag already used by any other doctor or procedure, in any status. */
	protected static function tag_taken( $tag, $post_id ) {
		$found = get_posts(
			array(
				'post_type'   => array( 'doctor', 'procedure' ),
				'post_status' => 'any',
				'meta_key'    => 'ig_tag',
				'meta_value'  => $tag,
				'exclude'     => array( (int) $post_id ),
				'fields'      => 'ids',
				'numberposts' => 1,
			)
		);
		return ! empty( $found );
	}
}
