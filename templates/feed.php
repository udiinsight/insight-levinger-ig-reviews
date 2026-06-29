<?php
/**
 * Feed template.
 *
 * @package levinger-ig-reviews
 * @var array  $lir_reviews
 * @var array  $lir_filters
 * @var array  $lir_atts
 * @var string $lir_uid
 * @var string $lir_accent
 * @var int    $lir_columns
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="lir" id="<?php echo esc_attr( $lir_uid ); ?>" dir="rtl" lang="he"
	style="--lir-accent: <?php echo esc_attr( $lir_accent ); ?>; --lir-cols: <?php echo (int) $lir_columns; ?>;"
	data-lir-cta-url="<?php echo esc_url( $lir_atts['cta_url'] ); ?>"
	data-lir-cta-text="<?php echo esc_attr( $lir_atts['cta_text'] ); ?>"
	data-lir-init-procedure="<?php echo esc_attr( $lir_atts['procedure'] ); ?>"
	data-lir-init-doctor="<?php echo esc_attr( $lir_atts['doctor'] ); ?>">

	<?php if ( empty( $lir_reviews ) ) : ?>

		<div class="lir__empty lir__empty--initial">
			<?php echo lir_icon( 'eye' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p>בקרוב — סרטוני המלצה</p>
		</div>

	<?php else : ?>

		<div class="lir__filters">
			<?php if ( count( $lir_filters['doctors'] ) > 1 ) : ?>
				<div class="lir__doctor" data-lir-dropdown>
					<button type="button" class="lir__doctor-btn" aria-haspopup="listbox" aria-expanded="false">
						<?php echo lir_icon( 'doctor' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="lir__doctor-label" data-lir-doctor-label>כל הרופאים</span>
						<span class="lir__doctor-caret"><?php echo lir_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</button>
					<ul class="lir__doctor-menu" role="listbox" tabindex="-1" aria-label="בחירת רופא" hidden>
						<li class="lir__doctor-opt is-selected" role="option" data-lir-doctor="all" aria-selected="true">כל הרופאים</li>
						<?php foreach ( $lir_filters['doctors'] as $slug => $name ) : ?>
							<li class="lir__doctor-opt" role="option" data-lir-doctor="<?php echo esc_attr( $slug ); ?>" aria-selected="false"><?php echo esc_html( $name ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="lir__procs" role="group" aria-label="סינון לפי פרוצדורה">
				<button type="button" class="lir__proc is-active" data-lir-procedure="all" aria-pressed="true">
					<span class="lir__proc-ic"><?php echo lir_icon( 'all' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="lir__proc-label">הכל</span>
				</button>
				<?php foreach ( $lir_filters['procedures'] as $slug => $proc ) : ?>
					<button type="button" class="lir__proc" data-lir-procedure="<?php echo esc_attr( $slug ); ?>" aria-pressed="false">
						<span class="lir__proc-ic"><?php echo $proc['icon'] ? lir_icon( $proc['icon'] ) : lir_procedure_icon( $proc['name'], $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="lir__proc-label"><?php echo esc_html( $proc['name'] ); ?></span>
					</button>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="lir__grid" role="list">
			<?php
			foreach ( $lir_reviews as $i => $review ) {
				include LIR_PATH . 'templates/card.php';
			}
			?>
		</div>

		<div class="lir__empty" hidden>
			<?php echo lir_icon( 'eye' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p>לא נמצאו סרטונים בקטגוריה זו</p>
		</div>

		<?php /* Immersive Stories lightbox — built once, populated by JS. */ ?>
		<div class="lir__lb" hidden>
			<div class="lir__lb-backdrop" data-lir-close></div>

			<button class="lir__lb-close" type="button" data-lir-close aria-label="סגירה">
				<?php echo lir_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<button class="lir__lb-arrow lir__lb-arrow--prev" type="button" data-lir-prev aria-label="הסרטון הקודם">
				<?php echo lir_icon( 'arrow-prev' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
			<button class="lir__lb-arrow lir__lb-arrow--next" type="button" data-lir-next aria-label="הסרטון הבא">
				<?php echo lir_icon( 'arrow-next' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>

			<div class="lir__reel" role="dialog" aria-modal="true" aria-label="סרטון המלצה" tabindex="-1">
				<video class="lir__video" playsinline preload="metadata"></video>
				<span class="lir__reel-scrim" aria-hidden="true"></span>

				<div class="lir__reel-top">
					<span class="lir__reel-pill" data-lir-lb-proc></span>
					<div class="lir__reel-id">
						<span class="lir__reel-avatar" data-lir-lb-avatar aria-hidden="true"></span>
						<span class="lir__reel-idtext">
							<span class="lir__reel-doc" data-lir-lb-doc></span>
							<span class="lir__reel-role">הרופא/ה המטפל/ת</span>
						</span>
					</div>
				</div>

				<div class="lir__reel-actions">
					<button class="lir__act" type="button" data-lir-like aria-pressed="false" aria-label="אהבתי"><?php echo lir_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
					<button class="lir__act" type="button" data-lir-share aria-label="שיתוף"><?php echo lir_icon( 'share' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
					<button class="lir__act" type="button" data-lir-save aria-pressed="false" aria-label="שמירה"><?php echo lir_icon( 'bookmark' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></button>
				</div>

				<button class="lir__reel-play" type="button" data-lir-toggle aria-label="נגן או השהה">
					<?php echo lir_icon( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</button>

				<div class="lir__reel-bottom">
					<span class="lir__reel-patient" data-lir-lb-patient></span>
					<span class="lir__reel-capnote" data-lir-lb-capnote hidden><?php echo lir_icon( 'caption' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> כתוביות חיות</span>
					<p class="lir__reel-caption" data-lir-lb-caption></p>
					<p class="lir__reel-quote" data-lir-lb-quote></p>
					<a class="lir__reel-cta" data-lir-lb-cta href="#" hidden>
						<span data-lir-lb-cta-text></span>
						<?php echo lir_icon( 'arrow-prev' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>

				<div class="lir__reel-bar"><span class="lir__reel-bar-fill" data-lir-lb-progress></span></div>
			</div>
		</div>

		<script type="application/json" class="lir__data"><?php echo wp_json_encode( $lir_reviews ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></script>

	<?php endif; ?>
</div>
