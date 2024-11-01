<?php
/**
 * Plugin Name: Particle WordPress Backgrounds
 * Plugin URI: http://wordpress.org/plugins/swap-snow-fall/
 * Description: Particle WordPress Backgrounds is fast, fully customizable WordPress plugin for adding particle JS to the background of  websites. It is very lightweight (less than 25KB on frontend) and offers unparalleled speed even with Particle JS.
 * Author: Swapnil Dhanrale
 * Version: 2.0.0
 * Author URI: https://profiles.wordpress.org/swapnild
 *
 * @package SSF
 */

define( 'SSF_FILE', __FILE__ );
define( 'SSF_ROOT', dirname( plugin_basename( SSF_FILE ) ) );

if ( ! version_compare( PHP_VERSION, '5.3', '>=' ) ) {
	add_action( 'admin_notices', 'ssf_fail_php_version' );
} else {
	require_once 'classes/class-ssf-loader.php';
}

/**
 * Swap Snow Fall admin notice for minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @since 1.1.0
 *
 * @return void
 */
function ssf_fail_php_version() {
	/* translators: %s: PHP version */
	$message      = sprintf( esc_html__( 'Swap Snow Fall requires PHP version %s+, plugin is currently NOT RUNNING.', 'swap-snow-fall' ), '5.3' );
	$html_message = sprintf( '<div class="error">%s</div>', wpautop( $message ) );
	echo wp_kses_post( $html_message );
}
