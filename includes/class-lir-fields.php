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
	}
}
