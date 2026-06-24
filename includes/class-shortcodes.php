<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Frontend shortcodes for consent controls and attribution.
 */
class CCBO_Cookie_Consent_Shortcodes {

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_shortcode( 'ccbo_cookie_consent_reopen', array( $this, 'render_reopen_shortcode' ) );
		add_shortcode( 'ccbo_cookie_consent_attribution', array( $this, 'render_attribution_shortcode' ) );
	}

	/**
	 * Render a button that reopens the consent banner.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_reopen_shortcode( $atts ) {
		$atts = $this->shortcode_atts(
			array(
				'text'  => __( 'Cookie settings', 'cookie-consent-by-osano' ),
				'class' => '',
			),
			$atts
		);
		$class = trim( 'ccbo-cookie-consent-reopen ' . $this->sanitize_class_list( $atts['class'] ) );

		return sprintf(
			'<button type="button" class="%1$s" data-ccbo-cookie-consent-reopen="1">%2$s</button>',
			esc_attr( $class ),
			esc_html( $atts['text'] )
		);
	}

	/**
	 * Render optional CookieConsent attribution.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render_attribution_shortcode( $atts ) {
		$atts = $this->shortcode_atts(
			array(
				'text'      => __( 'Cookie consent powered by', 'cookie-consent-by-osano' ),
				'link_text' => __( 'Osano CookieConsent', 'cookie-consent-by-osano' ),
				'url'       => 'https://github.com/osano/cookieconsent',
				'class'     => '',
			),
			$atts
		);
		$class = trim( 'ccbo-cookie-consent-attribution ' . $this->sanitize_class_list( $atts['class'] ) );
		$url   = esc_url( $atts['url'] );

		if ( '' === $url ) {
			return sprintf(
				'<span class="%1$s">%2$s %3$s</span>',
				esc_attr( $class ),
				esc_html( $atts['text'] ),
				esc_html( $atts['link_text'] )
			);
		}

		return sprintf(
			'<span class="%1$s">%2$s <a href="%3$s" rel="noopener noreferrer nofollow" target="_blank">%4$s</a></span>',
			esc_attr( $class ),
			esc_html( $atts['text'] ),
			esc_url( $url ),
			esc_html( $atts['link_text'] )
		);
	}

	/**
	 * Merge shortcode attributes without requiring WordPress in lightweight tests.
	 *
	 * @param array $defaults Default values.
	 * @param mixed $atts Shortcode attributes.
	 * @return array
	 */
	private function shortcode_atts( $defaults, $atts ) {
		$atts = is_array( $atts ) ? $atts : array();

		return array_merge( $defaults, array_intersect_key( $atts, $defaults ) );
	}

	/**
	 * Sanitize a whitespace-separated CSS class list.
	 *
	 * @param string $class_list Raw class list.
	 * @return string
	 */
	private function sanitize_class_list( $class_list ) {
		$classes = preg_split( '/\s+/', trim( (string) $class_list ) );
		$classes = array_filter(
			array_map(
				function ( $class ) {
					return preg_replace( '/[^A-Za-z0-9_-]/', '', $class );
				},
				$classes
			)
		);

		return implode( ' ', $classes );
	}
}
