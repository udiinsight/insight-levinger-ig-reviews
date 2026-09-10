<?php
/**
 * [levinger_ig_reviews] shortcode.
 *
 * @package levinger-ig-reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LIR_Shortcode {

	/** @var int Instance counter for unique ids. */
	protected static $uid = 0;

	/**
	 * Render the feed.
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string
	 */
	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'columns'   => 4,
				'limit'     => -1,
				'procedure' => '',           // optional pre-filter (procedure slug)
				'doctor'    => '',           // optional pre-filter (doctor slug)
				'accent'    => 'navy',       // teal | navy | dark | purple | #hex
				'cta_url'   => '',           // optional override link (defaults to each review's doctor page)
				'cta_text'  => 'לדף הרופא',
			),
			$atts,
			'levinger_ig_reviews'
		);

		// Resolve the scope once — the query filters on it, the template hides the
		// matching control so a scoped feed cannot be filtered away from its own page.
		$atts['_procedure_id'] = LIR_Query::resolve_related( $atts['procedure'], 'procedure' );
		$atts['_doctor_id']    = LIR_Query::resolve_related( $atts['doctor'], 'doctor' );

		$lir_reviews = LIR_Query::get_reviews( $atts );
		$lir_filters = LIR_Query::get_filters( $lir_reviews );
		$lir_atts    = $atts;
		$lir_uid     = 'lir-' . ( ++self::$uid );
		$lir_accent  = self::accent_color( $atts['accent'] );
		$lir_columns = max( 2, min( 6, (int) $atts['columns'] ) );

		LIR_Assets::enqueue();

		ob_start();
		include LIR_PATH . 'templates/feed.php';
		return ob_get_clean();
	}

	/**
	 * Resolve an accent keyword or hex to a color.
	 *
	 * @param string $accent Keyword or hex.
	 * @return string
	 */
	protected static function accent_color( $accent ) {
		$map = array(
			'teal'   => '#1DA69A',
			'navy'   => '#153b8b',
			'dark'   => '#072027',
			'purple' => '#7c5cff',
		);
		$accent = trim( (string) $accent );
		if ( isset( $map[ $accent ] ) ) {
			return $map[ $accent ];
		}
		if ( preg_match( '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $accent ) ) {
			return $accent;
		}
		return $map['teal'];
	}
}
