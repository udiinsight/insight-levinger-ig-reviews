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
	data-lir-cta-text="<?php echo esc_attr( $lir_atts['cta_text'] ); ?>">

	<?php if ( empty( $lir_reviews ) ) : ?>

		<div class="lir__empty lir__empty--initial">
			<?php echo lir_icon( 'eye' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p>בקרוב — סרטוני המלצה</p>
		</div>

	<?php else : ?>

		<?php
		// A feed already scoped to one procedure/doctor hides that control, so the
		// visitor cannot filter away from the page they are on.
		$lir_show_docs  = empty( $lir_atts['_doctor_id'] ) && count( $lir_filters['doctors'] ) > 1;
		$lir_show_procs = empty( $lir_atts['_procedure_id'] ) && count( $lir_filters['procedures'] ) > 1;

		// Per-filter counts, so each pill says how many videos sit behind it.
		$lir_total  = count( $lir_reviews );
		$lir_pcount = array();
		$lir_dcount = array();
		foreach ( $lir_reviews as $lir_r ) {
			foreach ( $lir_r['procedures'] as $lir_p ) {
				$lir_pcount[ $lir_p['slug'] ] = ( isset( $lir_pcount[ $lir_p['slug'] ] ) ? $lir_pcount[ $lir_p['slug'] ] : 0 ) + 1;
			}
			if ( ! empty( $lir_r['doctorSlug'] ) ) {
				$lir_dcount[ $lir_r['doctorSlug'] ] = ( isset( $lir_dcount[ $lir_r['doctorSlug'] ] ) ? $lir_dcount[ $lir_r['doctorSlug'] ] : 0 ) + 1;
			}
		}
		$lir_noun = ( 1 === $lir_total ) ? 'סרטון המלצה אחד' : $lir_total . ' סרטוני המלצה';
		?>

		<?php if ( $lir_show_docs || $lir_show_procs ) : ?>
		<div class="lir__filters">
			<?php if ( $lir_show_procs ) : ?>
			<div class="lir__procs" role="group" aria-label="סינון לפי טיפול">
				<button type="button" class="lir__proc is-active" data-lir-procedure="all" aria-pressed="true">
					<span class="lir__proc-label">הכל</span>
					<span class="lir__proc-count"><?php echo (int) $lir_total; ?></span>
				</button>
				<?php foreach ( $lir_filters['procedures'] as $slug => $proc ) : ?>
					<button type="button" class="lir__proc" data-lir-procedure="<?php echo esc_attr( $slug ); ?>" aria-pressed="false">
						<span class="lir__proc-ic"><?php echo $proc['icon'] ? lir_icon( $proc['icon'] ) : lir_procedure_icon( $proc['name'], $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="lir__proc-label"><?php echo esc_html( $proc['name'] ); ?></span>
						<span class="lir__proc-count"><?php echo isset( $lir_pcount[ $slug ] ) ? (int) $lir_pcount[ $slug ] : 0; ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php if ( $lir_show_docs ) : ?>
			<div class="lir__doctor" data-lir-dropdown>
				<button type="button" class="lir__doctor-btn" aria-haspopup="listbox" aria-expanded="false">
					<?php echo lir_icon( 'doctor' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="lir__doctor-label" data-lir-doctor-label>כל הרופאים</span>
					<span class="lir__doctor-caret"><?php echo lir_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				</button>
				<ul class="lir__doctor-menu" role="listbox" tabindex="-1" aria-label="בחירת רופא" hidden>
					<li class="lir__doctor-opt is-selected" role="option" data-lir-doctor="all" data-lir-label="כל הרופאים" aria-selected="true">
						<span>כל הרופאים</span>
						<span class="lir__opt-count"><?php echo (int) $lir_total; ?></span>
					</li>
					<?php foreach ( $lir_filters['doctors'] as $slug => $name ) : ?>
						<li class="lir__doctor-opt" role="option" data-lir-doctor="<?php echo esc_attr( $slug ); ?>" data-lir-label="<?php echo esc_attr( $name ); ?>" aria-selected="false">
							<span><?php echo esc_html( $name ); ?></span>
							<span class="lir__opt-count"><?php echo isset( $lir_dcount[ $slug ] ) ? (int) $lir_dcount[ $slug ] : 0; ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<p class="lir__count" data-lir-count data-lir-total="<?php echo (int) $lir_total; ?>" data-lir-all="<?php echo esc_attr( $lir_noun ); ?>" aria-live="polite"><?php echo esc_html( $lir_noun ); ?></p>

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
