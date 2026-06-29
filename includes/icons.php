<?php
/**
 * Inline SVG line-icons (24x24, currentColor).
 *
 * @package levinger-ig-reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pick a procedure icon by matching keywords in its name/slug.
 *
 * @param string $name Procedure title.
 * @param string $slug Procedure slug.
 * @return string SVG markup.
 */
function lir_procedure_icon( $name, $slug = '' ) {
	$key = function_exists( 'mb_strtolower' ) ? mb_strtolower( $name . ' ' . $slug ) : strtolower( $name . ' ' . $slug );

	$match = 'eye';
	if ( false !== strpos( $key, 'smile' ) ) {
		$match = 'smile';
	} elseif ( false !== strpos( $key, 'prk' ) ) {
		$match = 'prk';
	} elseif ( false !== strpos( $key, 'lasik' ) || false !== strpos( $key, 'אינטרא' ) || false !== strpos( $key, 'לאסיק' ) ) {
		$match = 'lasik';
	} elseif ( false !== strpos( $key, 'wavefront' ) || false !== strpos( $key, 'גלי' ) ) {
		$match = 'wavefront';
	} elseif ( false !== strpos( $key, 'mono' ) || false !== strpos( $key, 'מונו' ) ) {
		$match = 'monovision';
	} elseif ( false !== strpos( $key, 'עדשה' ) || false !== strpos( $key, 'iol' ) || false !== strpos( $key, 'implant' ) || false !== strpos( $key, 'lens' ) ) {
		$match = 'lens';
	}

	return lir_icon( $match );
}

/**
 * Return a named inline SVG icon.
 *
 * @param string $name Icon key.
 * @return string SVG markup (empty string if unknown).
 */
function lir_icon( $name ) {
	$o = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
	$c = '</svg>';

	switch ( $name ) {
		case 'all':
			return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><circle cx="8" cy="8" r="2.1"/><circle cx="16" cy="8" r="2.1"/><circle cx="8" cy="16" r="2.1"/><circle cx="16" cy="16" r="2.1"/></svg>';
		case 'eye':
			return $o . '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>' . $c;
		case 'smile':
			return $o . '<circle cx="12" cy="12" r="9"/><path d="M8 14s1.4 2 4 2 4-2 4-2"/><path d="M9 9h.01M15 9h.01"/>' . $c;
		case 'prk':
			return $o . '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/>' . $c;
		case 'lasik':
			return $o . '<path d="M3 12s3-6 9-6 9 6 9 6"/><circle cx="12" cy="11" r="2.5"/><path d="M12 2.5V5"/>' . $c;
		case 'wavefront':
			return $o . '<path d="M2 9c2.5-3 5.5-3 8 0s5.5 3 8 0"/><path d="M2 15c2.5-3 5.5-3 8 0s5.5 3 8 0"/>' . $c;
		case 'monovision':
			return $o . '<path d="M8 8a4 4 0 1 0 0 8c2.2 0 3.2-2 4-4s1.8-4 4-4a4 4 0 1 1 0 8c-2.2 0-3.2-2-4-4S10.2 8 8 8z"/>' . $c;
		case 'lens':
			return $o . '<ellipse cx="12" cy="12" rx="8.5" ry="5.4"/><path d="M12 6.6v10.8"/>' . $c;
		case 'doctor':
			return $o . '<circle cx="12" cy="8" r="3.4"/><path d="M5 20c0-3.6 3-6 7-6s7 2.4 7 6"/>' . $c;
		case 'play':
			return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M8 5.5v13a1 1 0 0 0 1.54.84l10-6.5a1 1 0 0 0 0-1.68l-10-6.5A1 1 0 0 0 8 5.5z"/></svg>';
		case 'close':
			return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18"/></svg>';
		case 'heart':
			return $o . '<path d="M12 20.5S3.5 15.5 3.5 9.6C3.5 6.9 5.6 5 8 5c1.7 0 3.1 1 4 2.3C12.9 6 14.3 5 16 5c2.4 0 4.5 1.9 4.5 4.6 0 5.9-8.5 10.9-8.5 10.9z"/>' . $c;
		case 'share':
			return $o . '<path d="M21 3L10.5 13.5"/><path d="M21 3l-6.7 18-3.8-8.5L2 8.9 21 3z"/>' . $c;
		case 'bookmark':
			return $o . '<path d="M6.5 3.5h11a1 1 0 0 1 1 1v16l-6.5-3.8L5.5 20.5v-16a1 1 0 0 1 1-1z"/>' . $c;
		case 'chevron':
			return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M6 9l6 6 6-6"/></svg>';
		case 'arrow-prev':
			return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M15 6l-6 6 6 6"/></svg>';
		case 'arrow-next':
			return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M9 6l6 6-6 6"/></svg>';
		case 'instagram':
			return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="3.8"/><circle cx="17.2" cy="6.8" r="1.1" fill="currentColor" stroke="none"/></svg>';
		case 'quote':
			return '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false"><path d="M10 6.5C6.4 6.5 4 9.3 4 13v4.5h6V11H7.2c.2-1.6 1.4-2.6 2.8-2.6V6.5zm10 0c-3.6 0-6 2.8-6 6.5v4.5h6V11h-2.8c.2-1.6 1.4-2.6 2.8-2.6V6.5z"/></svg>';
		case 'caption':
			return $o . '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M9.5 10.4c-1.5-1.1-3.2-.1-3.2 1.6s1.7 2.7 3.2 1.6M16.5 10.4c-1.5-1.1-3.2-.1-3.2 1.6s1.7 2.7 3.2 1.6"/>' . $c;
		default:
			return '';
	}
}
