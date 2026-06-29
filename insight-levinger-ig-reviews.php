<?php
/**
 * Plugin Name:       Insight - Levinger - IG Reviews
 * Plugin URI:        https://github.com/udiinsight/insight-levinger-ig-reviews
 * Description:       Instagram-style video reviews feed for Dr. Levinger — a filterable, RTL grid of video testimonials with an immersive (Reels-style) lightbox. Reads the existing _reviews CPT. Shortcode: [levinger_ig_reviews].
 * Version:           0.1.0
 * Author:            Insight Marketing
 * Author URI:        https://insight-marketing.co.il
 * Text Domain:       insight-levinger-ig-reviews
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 *
 * Read-only over existing data: queries the `_reviews` CPT and its doctor/procedure
 * relations. Adds nothing to and changes nothing about the existing /reviews/ display.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LIR_VERSION', '0.1.0' );
define( 'LIR_FILE', __FILE__ );
define( 'LIR_PATH', plugin_dir_path( __FILE__ ) );
define( 'LIR_URL', plugin_dir_url( __FILE__ ) );

require_once LIR_PATH . 'includes/icons.php';
require_once LIR_PATH . 'includes/class-lir-query.php';
require_once LIR_PATH . 'includes/class-lir-assets.php';
require_once LIR_PATH . 'includes/class-lir-shortcode.php';
require_once LIR_PATH . 'includes/class-lir-fields.php';

add_action( 'init', function () {
	add_shortcode( 'levinger_ig_reviews', array( 'LIR_Shortcode', 'render' ) );
} );

add_action( 'wp_enqueue_scripts', array( 'LIR_Assets', 'register' ) );
add_action( 'acf/init', array( 'LIR_Fields', 'register' ) );
