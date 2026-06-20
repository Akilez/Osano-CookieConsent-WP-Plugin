<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend asset registration and enqueueing.
 */
class CCBO_Cookie_Consent_Assets {

	/**
	 * Settings manager.
	 *
	 * @var CCBO_Cookie_Consent_Settings
	 */
	private $settings;

	/**
	 * Construct the asset loader.
	 *
	 * @param CCBO_Cookie_Consent_Settings $settings Settings manager instance.
	 */
	public function __construct( CCBO_Cookie_Consent_Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * Enqueue plugin frontend assets.
	 *
	 * @return void
	 */
	public function enqueue_frontend_assets() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		do_action( 'ccbo_cookie_consent_before_banner_init' );

		wp_register_style(
			'ccbo-cookie-consent-vendor',
			CCBO_COOKIE_CONSENT_URL . 'assets/css/cookieconsent.min.css',
			array(),
			CCBO_COOKIE_CONSENT_VERSION
		);

		wp_register_style(
			'ccbo-cookie-consent',
			CCBO_COOKIE_CONSENT_URL . 'assets/css/ccbo-cookie-consent.css',
			array( 'ccbo-cookie-consent-vendor' ),
			CCBO_COOKIE_CONSENT_VERSION
		);

		wp_register_script(
			'ccbo-cookie-consent-vendor',
			CCBO_COOKIE_CONSENT_URL . 'assets/js/cookieconsent.min.js',
			array(),
			CCBO_COOKIE_CONSENT_VERSION,
			true
		);

		wp_register_script(
			'ccbo-cookie-consent',
			CCBO_COOKIE_CONSENT_URL . 'assets/js/ccbo-cookie-consent.js',
			array( 'ccbo-cookie-consent-vendor' ),
			CCBO_COOKIE_CONSENT_VERSION,
			true
		);

		wp_localize_script(
			'ccbo-cookie-consent',
			'ccboCookieConsentConfig',
			$this->get_frontend_config()
		);

		wp_enqueue_style( 'ccbo-cookie-consent-vendor' );
		wp_enqueue_style( 'ccbo-cookie-consent' );
		wp_enqueue_script( 'ccbo-cookie-consent-vendor' );
		wp_enqueue_script( 'ccbo-cookie-consent' );

		$custom_css = $this->get_custom_css();

		if ( '' !== $custom_css ) {
			wp_add_inline_style( 'ccbo-cookie-consent', $custom_css );
		}

		do_action( 'ccbo_cookie_consent_after_banner_init' );
	}

	/**
	 * Determine whether the frontend banner should load.
	 *
	 * @return bool
	 */
	private function is_enabled() {
		$options = $this->settings->get_options();
		$enabled = ! empty( $options['enabled'] );

		return (bool) apply_filters( 'ccbo_cookie_consent_enabled', $enabled );
	}

	/**
	 * Build the frontend CookieConsent config object.
	 *
	 * @return array
	 */
	private function get_frontend_config() {
		$options = $this->settings->get_options();
		$config  = array(
			'type'      => $options['consent_mode'],
			'position'  => $options['position'],
			'theme'     => $options['theme'],
			'revokable' => ! empty( $options['revokable'] ),
			'palette'   => array(
				'popup'     => array(
					'background' => $options['palette_popup_background'],
					'text'       => $options['palette_popup_text'],
				),
				'button'    => array(
					'background' => $options['palette_button_background'],
					'text'       => $options['palette_button_text'],
					'border'     => $options['palette_button_border'],
				),
				'highlight' => array(
					'background' => 'transparent',
					'text'       => $options['palette_highlight_text'],
					'border'     => 'transparent',
				),
			),
			'content'   => array(
				'message' => $options['message'],
				'allow'   => $options['allow_text'],
				'deny'    => $options['deny_text'],
				'link'    => $options['link_text'],
				'href'    => apply_filters( 'ccbo_cookie_consent_policy_url', $options['policy_url'] ),
			),
			'cookie'    => array(
				'name'       => $options['cookie_name'],
				'path'       => $options['cookie_path'],
				'expiryDays' => (int) $options['expiry_days'],
				'secure'     => is_ssl(),
			),
			'ulcLocation' => array(
				'enabled'    => ! empty( $options['enable_location_services'] ),
				'provider'   => $options['location_service_provider'],
				'endpoint'   => $options['location_service_url'],
				'timeout'    => (int) $options['location_service_timeout'],
				'cacheHours' => (int) $options['location_service_cache_hours'],
			),
			'meta'      => array(
				'pluginVersion' => CCBO_COOKIE_CONSENT_VERSION,
			),
		);

		if ( '' !== $options['cookie_domain'] ) {
			$config['cookie']['domain'] = $options['cookie_domain'];
		}

		return apply_filters( 'ccbo_cookie_consent_config', $config );
	}

	/**
	 * Return sanitized custom CSS overrides.
	 *
	 * @return string
	 */
	private function get_custom_css() {
		$options = $this->settings->get_options();

		if ( empty( $options['custom_css'] ) ) {
			return '';
		}

		return trim( (string) $options['custom_css'] );
	}
}
