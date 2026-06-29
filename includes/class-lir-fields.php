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
						'instructions' => 'התגית המזהה בפוסט באינסטגרם — בלי #. לדוגמה: dr_levinger או smile. משמשת למיפוי אוטומטי של ההמלצה לרופא/לטיפול.',
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
}
