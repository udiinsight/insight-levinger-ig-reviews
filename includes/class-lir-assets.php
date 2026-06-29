<?php
/**
 * Asset registration / conditional enqueue.
 *
 * @package levinger-ig-reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LIR_Assets {

	const HANDLE = 'lir';

	/**
	 * Register (not enqueue) styles & scripts so they exist before a shortcode renders.
	 */
	public static function register() {
		wp_register_style(
			'lir-fonts',
			'https://fonts.googleapis.com/css2?family=Assistant:wght@300;400;500;600;700;800&family=Frank+Ruhl+Libre:wght@500;700&display=swap',
			array(),
			LIR_VERSION
		);

		wp_register_style( self::HANDLE, LIR_URL . 'assets/css/lir.css', array( 'lir-fonts' ), LIR_VERSION );
		wp_register_script( self::HANDLE, LIR_URL . 'assets/js/lir.js', array(), LIR_VERSION, true );
	}

	/**
	 * Enqueue on demand (called from the shortcode). Self-registers if needed.
	 */
	public static function enqueue() {
		if ( ! wp_style_is( self::HANDLE, 'registered' ) ) {
			self::register();
		}
		wp_enqueue_style( 'lir-fonts' );
		wp_enqueue_style( self::HANDLE );
		wp_enqueue_script( self::HANDLE );
	}
}
