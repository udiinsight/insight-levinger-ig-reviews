<?php
/**
 * Admin settings page for the Instagram ingestion endpoint (Phase 2).
 * Shows the endpoint URL + secret token (regenerable) and a notification email.
 *
 * @package levinger-ig-reviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LIR_Admin {

	public static function menu() {
		add_options_page(
			'Levinger IG Reviews',
			'IG Reviews — סנכרון',
			'manage_options',
			'lir-ingest',
			array( __CLASS__, 'render' )
		);
	}

	public static function handle_actions() {
		if ( empty( $_POST['lir_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		check_admin_referer( 'lir_ingest_settings' );

		$action = sanitize_key( wp_unslash( $_POST['lir_action'] ) );
		if ( 'regenerate' === $action ) {
			update_option( LIR_Ingest::SECRET_OPTION, wp_generate_password( 40, false, false ), false );
			add_settings_error( 'lir', 'lir_regenerated', 'הטוקן חודש — יש לעדכן אותו ב-Make.', 'updated' );
		} elseif ( 'save_email' === $action ) {
			update_option( LIR_Ingest::EMAIL_OPTION, sanitize_email( wp_unslash( $_POST['lir_email'] ) ) );
			add_settings_error( 'lir', 'lir_saved', 'נשמר.', 'updated' );
		}
	}

	public static function render() {
		$endpoint = rest_url( LIR_Ingest::REST_NS . LIR_Ingest::ROUTE );
		$secret   = LIR_Ingest::get_secret();
		$email    = get_option( LIR_Ingest::EMAIL_OPTION, get_option( 'admin_email' ) );
		?>
		<div class="wrap" dir="rtl" style="max-width:820px">
			<h1>Levinger IG Reviews — סנכרון אינסטגרם</h1>
			<?php settings_errors( 'lir' ); ?>
			<p>Make.com שולח לכאן סרטוני המלצה חדשים מאינסטגרם. כל פנייה יוצרת המלצה כ<strong>טיוטה</strong> הממתינה לאישור (פרסום) של צוות.</p>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">Endpoint URL</th>
					<td><code style="user-select:all"><?php echo esc_html( $endpoint ); ?></code></td>
				</tr>
				<tr>
					<th scope="row">Secret token</th>
					<td>
						<code style="user-select:all"><?php echo esc_html( $secret ); ?></code>
						<p class="description">יש לשלוח אותו בכותרת <code>X-LIR-Token</code> (או כפרמטר <code>token</code>) בכל בקשה מ-Make.</p>
						<form method="post" style="margin-top:8px">
							<?php wp_nonce_field( 'lir_ingest_settings' ); ?>
							<input type="hidden" name="lir_action" value="regenerate">
							<?php submit_button( 'חידוש טוקן', 'secondary', 'submit', false, array( 'onclick' => "return confirm('לחדש את הטוקן? Make יפסיק לעבוד עד שתעדכן אותו שם.')" ) ); ?>
						</form>
					</td>
				</tr>
				<tr>
					<th scope="row">אימייל להתראות</th>
					<td>
						<form method="post">
							<?php wp_nonce_field( 'lir_ingest_settings' ); ?>
							<input type="hidden" name="lir_action" value="save_email">
							<input type="email" name="lir_email" value="<?php echo esc_attr( $email ); ?>" class="regular-text" placeholder="name@example.com">
							<?php submit_button( 'שמירה', 'primary', 'submit', false ); ?>
							<p class="description">כתובת שתקבל התראה על כל המלצה חדשה שנוצרה כטיוטה.</p>
						</form>
					</td>
				</tr>
			</table>

			<h2>שדות מיפוי (hashtags)</h2>
			<p>כדי שהמיפוי האוטומטי יעבוד, יש למלא <strong>תגית אינסטגרם</strong> (<code>ig_tag</code>) בכל רופא ובכל טיפול רלוונטי (בלי <code>#</code>). לדוגמה: ברופא ד"ר לוינגר → <code>dr_levinger</code>, בטיפול SMILE → <code>smile</code>. בפוסט באינסטגרם משתמשים ב-<code>#dr_levinger #smile</code>.</p>
		</div>
		<?php
	}
}
