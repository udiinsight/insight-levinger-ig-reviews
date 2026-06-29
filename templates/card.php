<?php
/**
 * Single card. Included within the feed loop.
 *
 * @package levinger-ig-reviews
 * @var array $review Current review.
 * @var int   $i      Index.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lir_proc_slugs = array();
$lir_proc_first = '';
foreach ( $review['procedures'] as $lir_p ) {
	$lir_proc_slugs[] = $lir_p['slug'];
	if ( '' === $lir_proc_first ) {
		$lir_proc_first = $lir_p['name'];
	}
}
?>
<button type="button" class="lir__card" role="listitem"
	data-lir-index="<?php echo (int) $i; ?>"
	data-lir-procedure="<?php echo esc_attr( implode( ' ', $lir_proc_slugs ) ); ?>"
	data-lir-doctor="<?php echo esc_attr( $review['doctorSlug'] ); ?>"
	style="--lir-i: <?php echo (int) $i; ?>;"
	aria-label="<?php echo esc_attr( trim( $review['name'] . ( $lir_proc_first ? ' — ' . $lir_proc_first : '' ) ) ); ?>">
	<span class="lir__media">
		<?php if ( $review['poster'] ) : ?>
			<img class="lir__poster" src="<?php echo esc_url( $review['poster'] ); ?>" alt="" loading="lazy" decoding="async">
		<?php else : ?>
			<span class="lir__poster lir__poster--ph" aria-hidden="true"></span>
		<?php endif; ?>

		<span class="lir__ig" aria-hidden="true"><?php echo lir_icon( 'instagram' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php if ( $review['duration'] ) : ?>
			<span class="lir__dur"><?php echo esc_html( $review['duration'] ); ?></span>
		<?php endif; ?>

		<span class="lir__play" aria-hidden="true"><?php echo lir_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<span class="lir__scrim" aria-hidden="true"></span>

		<span class="lir__cap">
			<span class="lir__name"><?php echo esc_html( $review['name'] ); ?></span>
			<?php if ( $lir_proc_first ) : ?>
				<span class="lir__pill"><?php echo esc_html( $lir_proc_first ); ?></span>
			<?php endif; ?>
			<?php if ( $review['doctor'] ) : ?>
				<span class="lir__doc"><?php echo esc_html( $review['doctor'] ); ?></span>
			<?php endif; ?>
		</span>
	</span>
</button>
