<?php
/**
 * Plugin Name: Cookie Consent by Osano
 * Description: Reusable cookie consent plugin powered by Osano CookieConsent.
 * Version: 0.2.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Akilez
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: cookie-consent-by-osano
 * Update URI: https://github.com/Akilez/osano-wp-plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CCBO_COOKIE_CONSENT_VERSION', '0.2.0' );
define( 'CCBO_COOKIE_CONSENT_FILE', __FILE__ );
define( 'CCBO_COOKIE_CONSENT_PATH', plugin_dir_path( __FILE__ ) );
define( 'CCBO_COOKIE_CONSENT_URL', plugin_dir_url( __FILE__ ) );

require_once CCBO_COOKIE_CONSENT_PATH . 'includes/class-plugin.php';
require_once CCBO_COOKIE_CONSENT_PATH . 'includes/class-settings.php';

register_activation_hook( CCBO_COOKIE_CONSENT_FILE, 'ccbo_cookie_consent_activate' );
register_deactivation_hook( CCBO_COOKIE_CONSENT_FILE, 'ccbo_cookie_consent_deactivate' );
register_uninstall_hook( CCBO_COOKIE_CONSENT_FILE, 'ccbo_cookie_consent_uninstall' );

/**
 * Set up plugin defaults on activation.
 *
 * @return void
 */
function ccbo_cookie_consent_activate() {
	$settings = new CCBO_Cookie_Consent_Settings();
	$options  = get_option( CCBO_Cookie_Consent_Settings::OPTION_KEY, null );

	if ( null === $options ) {
		add_option( CCBO_Cookie_Consent_Settings::OPTION_KEY, $settings->get_default_options() );
		return;
	}

	if ( is_array( $options ) ) {
		update_option(
			CCBO_Cookie_Consent_Settings::OPTION_KEY,
			array_merge( $settings->get_default_options(), $options )
		);
	}
}

/**
 * Handle plugin deactivation.
 *
 * @return void
 */
function ccbo_cookie_consent_deactivate() {
	// Reserved for future cleanup of transient or scheduled data.
}

/**
 * Remove plugin data on uninstall.
 *
 * @return void
 */
function ccbo_cookie_consent_uninstall() {
	delete_option( CCBO_Cookie_Consent_Settings::OPTION_KEY );
}

/**
 * Bootstrap the plugin singleton.
 *
 * @return CCBO_Cookie_Consent_Plugin
 */
function ccbo_cookie_consent() {
	return CCBO_Cookie_Consent_Plugin::instance();
}

ccbo_cookie_consent();
