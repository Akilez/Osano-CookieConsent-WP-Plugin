<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once CCBO_COOKIE_CONSENT_PATH . 'vendor/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * GitHub release updater integration.
 */
class CCBO_Cookie_Consent_Updater {

	/**
	 * Update checker instance.
	 *
	 * @var object|null
	 */
	private $update_checker = null;

	/**
	 * Initialize the update checker.
	 *
	 * @return void
	 */
	public function init() {
		if ( defined( 'CCBO_DISABLE_UPDATER' ) && CCBO_DISABLE_UPDATER ) {
			return;
		}

		if ( ! class_exists( PucFactory::class ) ) {
			return;
		}

		$repository_url = $this->normalize_repository_url(
			(string) apply_filters( 'ccbo_cookie_consent_update_repository_url', 'https://github.com/Akilez/Osano-CookieConsent-WP-Plugin' )
		);

		if ( '' === $repository_url ) {
			return;
		}

		$this->update_checker = PucFactory::buildUpdateChecker(
			$repository_url,
			CCBO_COOKIE_CONSENT_FILE,
			'cookie-consent-by-osano'
		);

		$branch = (string) apply_filters( 'ccbo_cookie_consent_update_branch', 'main' );

		if ( '' !== $branch ) {
			$this->update_checker->setBranch( $branch );
		}

		$asset_pattern = (string) apply_filters( 'ccbo_cookie_consent_update_asset_pattern', '/cookie-consent-by-osano\.zip$/i' );
		$this->update_checker->getVcsApi()->enableReleaseAssets( $asset_pattern );

		$token = '';

		if ( defined( 'CCBO_COOKIE_CONSENT_GITHUB_TOKEN' ) ) {
			$token = (string) CCBO_COOKIE_CONSENT_GITHUB_TOKEN;
		}

		$token = (string) apply_filters( 'ccbo_cookie_consent_update_token', $token );

		if ( '' !== $token ) {
			$this->update_checker->setAuthentication( $token );
		}
	}

	/**
	 * Normalize the configured GitHub repository URL for the update checker.
	 *
	 * @param string $repository_url Raw repository URL.
	 * @return string
	 */
	private function normalize_repository_url( $repository_url ) {
		$repository_url = trim( $repository_url );

		if ( '' === $repository_url ) {
			return '';
		}

		$repository_url = preg_replace( '#\.git/?$#i', '', $repository_url );

		return untrailingslashit( $repository_url );
	}
}
