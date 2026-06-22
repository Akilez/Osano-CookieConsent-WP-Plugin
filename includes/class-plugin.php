<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once CCBO_COOKIE_CONSENT_PATH . 'includes/class-assets.php';
require_once CCBO_COOKIE_CONSENT_PATH . 'includes/class-settings.php';
require_once CCBO_COOKIE_CONSENT_PATH . 'includes/class-updater.php';

/**
 * Main plugin coordinator.
 */
final class CCBO_Cookie_Consent_Plugin {

	/**
	 * Shared singleton instance.
	 *
	 * @var CCBO_Cookie_Consent_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Asset manager.
	 *
	 * @var CCBO_Cookie_Consent_Assets
	 */
	private $assets;

	/**
	 * Settings manager.
	 *
	 * @var CCBO_Cookie_Consent_Settings
	 */
	private $settings;

	/**
	 * Updater integration.
	 *
	 * @var CCBO_Cookie_Consent_Updater
	 */
	private $updater;

	/**
	 * Return the shared instance.
	 *
	 * @return CCBO_Cookie_Consent_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Construct the plugin.
	 */
	private function __construct() {
		$this->settings = new CCBO_Cookie_Consent_Settings();
		$this->assets   = new CCBO_Cookie_Consent_Assets( $this->settings );
		$this->updater  = new CCBO_Cookie_Consent_Updater();

		$this->updater->init();

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Initialize the plugin after WordPress is ready.
	 *
	 * @return void
	 */
	public function init() {
		$this->assets->register_hooks();
		$this->settings->register_hooks();
	}
}
