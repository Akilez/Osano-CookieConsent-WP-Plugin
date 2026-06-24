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

		$custom_css = $this->get_frontend_inline_css();

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
			'ga4' => array(
				'enabled'       => ! empty( $options['ga4_enabled'] ) && '' !== $options['ga4_measurement_id'],
				'measurementId' => $options['ga4_measurement_id'],
			),
			'deferredScripts' => $this->get_deferred_scripts(),
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

	/**
	 * Return generated shortcode styles plus custom CSS overrides.
	 *
	 * @return string
	 */
	private function get_frontend_inline_css() {
		$css_parts = array(
			$this->get_shortcode_color_css(),
			$this->get_custom_css(),
		);

		return trim( implode( "\n", array_filter( $css_parts ) ) );
	}

	/**
	 * Return CSS variables for shortcode colors.
	 *
	 * @return string
	 */
	private function get_shortcode_color_css() {
		$options = $this->settings->get_options();

		return sprintf(
			":root {\n\t--ccbo-reopen-button-background: %1\$s;\n\t--ccbo-reopen-button-text: %2\$s;\n\t--ccbo-reopen-button-border: %3\$s;\n\t--ccbo-attribution-text: %4\$s;\n\t--ccbo-attribution-link: %5\$s;\n}",
			$options['reopen_button_background'],
			$options['reopen_button_text'],
			$options['reopen_button_border'],
			$options['attribution_text'],
			$options['attribution_link']
		);
	}

	/**
	 * Return consent-gated scripts registered by site-specific code.
	 *
	 * @return array
	 */
	private function get_deferred_scripts() {
		$scripts    = array_merge(
			$this->get_built_in_deferred_scripts(),
			$this->get_admin_deferred_scripts(),
			(array) apply_filters( 'ccbo_cookie_consent_deferred_scripts', array() )
		);
		$normalized = array();

		if ( ! is_array( $scripts ) ) {
			return $normalized;
		}

		foreach ( $scripts as $index => $script ) {
			if ( ! is_array( $script ) ) {
				continue;
			}

			$src    = isset( $script['src'] ) ? esc_url_raw( trim( (string) $script['src'] ) ) : '';
			$inline = isset( $script['inline'] ) ? trim( (string) $script['inline'] ) : '';

			if ( '' === $src && '' === $inline ) {
				continue;
			}

			$script_id = isset( $script['id'] ) ? sanitize_key( $script['id'] ) : '';

			if ( '' === $script_id ) {
				$script_id = 'ccbo_deferred_script_' . absint( $index + 1 );
			}

			$normalized_script = array(
				'id'         => $script_id,
				'src'        => $src,
				'inline'     => $inline,
				'attributes' => $this->normalize_deferred_script_attributes(
					isset( $script['attributes'] ) ? $script['attributes'] : array()
				),
			);

			$normalized[] = $normalized_script;
		}

		return $normalized;
	}

	/**
	 * Return deferred scripts configured from the admin settings screen.
	 *
	 * @return array
	 */
	private function get_admin_deferred_scripts() {
		$options = $this->settings->get_options();
		$entries = isset( $options['script_gate_entries'] ) && is_array( $options['script_gate_entries'] )
			? $options['script_gate_entries']
			: array();
		$scripts = array();

		foreach ( $entries as $index => $entry ) {
			if ( ! is_array( $entry ) || empty( $entry['enabled'] ) ) {
				continue;
			}

			$src    = isset( $entry['src'] ) ? trim( (string) $entry['src'] ) : '';
			$inline = isset( $entry['inline'] ) ? trim( (string) $entry['inline'] ) : '';

			if ( '' === $src && '' === $inline ) {
				continue;
			}

			$category = isset( $entry['category'] ) ? sanitize_key( $entry['category'] ) : 'analytics';
			$label    = isset( $entry['label'] ) ? sanitize_key( $entry['label'] ) : '';

			if ( '' === $label ) {
				$label = 'script';
			}

			$attributes = array();

			if ( ! empty( $entry['async'] ) ) {
				$attributes['async'] = true;
			}

			if ( ! empty( $entry['defer'] ) ) {
				$attributes['defer'] = true;
			}

			$scripts[] = array(
				'id'         => 'ccbo_' . $category . '_' . $label . '_' . absint( $index + 1 ),
				'src'        => $src,
				'inline'     => $inline,
				'attributes' => $attributes,
			);
		}

		return $scripts;
	}

	/**
	 * Return built-in deferred scripts for first-party integrations.
	 *
	 * @return array
	 */
	private function get_built_in_deferred_scripts() {
		$options = $this->settings->get_options();

		if ( empty( $options['ga4_enabled'] ) || '' === $options['ga4_measurement_id'] ) {
			return array();
		}

		$measurement_id = $options['ga4_measurement_id'];

		return array(
			array(
				'id'         => 'ccbo_ga4_library',
				'src'        => 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $measurement_id ),
				'attributes' => array(
					'async' => true,
				),
			),
			array(
				'id'     => 'ccbo_ga4_init',
				'inline' => "window.dataLayer = window.dataLayer || [];\nwindow.gtag = window.gtag || function(){dataLayer.push(arguments);};\ngtag('js', new Date());\ngtag('config', '" . esc_js( $measurement_id ) . "');",
			),
		);
	}

	/**
	 * Normalize deferred script tag attributes.
	 *
	 * @param mixed $attributes Raw attributes.
	 * @return array
	 */
	private function normalize_deferred_script_attributes( $attributes ) {
		$normalized = array();

		if ( ! is_array( $attributes ) ) {
			return $normalized;
		}

		foreach ( $attributes as $name => $value ) {
			$name = strtolower( sanitize_key( (string) $name ) );

			if ( '' === $name ) {
				continue;
			}

			if ( ! in_array( $name, array( 'async', 'defer', 'type', 'crossorigin', 'referrerpolicy', 'nonce' ), true ) && 0 !== strpos( $name, 'data-' ) ) {
				continue;
			}

			if ( is_bool( $value ) ) {
				$normalized[ $name ] = $value;
				continue;
			}

			if ( is_scalar( $value ) ) {
				$normalized[ $name ] = sanitize_text_field( (string) $value );
			}
		}

		return $normalized;
	}
}
