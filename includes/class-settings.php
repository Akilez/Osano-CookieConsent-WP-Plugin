<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin settings integration.
 */
class CCBO_Cookie_Consent_Settings {

	/**
	 * Plugin option key.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'ccbo_cookie_consent_options';

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	const PAGE_SLUG = 'cookie-consent-by-osano';

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Register plugin settings, sections, and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'ccbo_cookie_consent',
			self::OPTION_KEY,
			array(
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => $this->get_default_options(),
			)
		);

		$sections = array(
			'ccbo_cookie_consent_general'  => array(
				'title'    => __( 'General Settings', 'cookie-consent-by-osano' ),
				'callback' => array( $this, 'render_general_section' ),
			),
			'ccbo_cookie_consent_content'  => array(
				'title'    => __( 'Banner Content', 'cookie-consent-by-osano' ),
				'callback' => array( $this, 'render_content_section' ),
			),
			'ccbo_cookie_consent_cookie'   => array(
				'title'    => __( 'Cookie Settings', 'cookie-consent-by-osano' ),
				'callback' => array( $this, 'render_cookie_section' ),
			),
			'ccbo_cookie_consent_location' => array(
				'title'    => __( 'Location Services', 'cookie-consent-by-osano' ),
				'callback' => array( $this, 'render_location_section' ),
			),
			'ccbo_cookie_consent_styling'  => array(
				'title'    => __( 'Styling', 'cookie-consent-by-osano' ),
				'callback' => array( $this, 'render_styling_section' ),
			),
		);

		foreach ( $sections as $section_id => $section ) {
			add_settings_section(
				$section_id,
				$section['title'],
				$section['callback'],
				self::PAGE_SLUG
			);
		}

		$this->register_general_fields();
		$this->register_content_fields();
		$this->register_cookie_fields();
		$this->register_location_fields();
		$this->register_styling_fields();
	}

	private function register_general_fields() {
		$this->add_field(
			'enabled',
			__( 'Enable banner', 'cookie-consent-by-osano' ),
			'checkbox',
			'ccbo_cookie_consent_general',
			array(
				'label'       => __( 'Display the cookie consent banner on the frontend.', 'cookie-consent-by-osano' ),
				'description' => __( 'Turn the banner on or off site-wide.', 'cookie-consent-by-osano' ),
			)
		);

		$this->add_field(
			'consent_mode',
			__( 'Consent mode', 'cookie-consent-by-osano' ),
			'select',
			'ccbo_cookie_consent_general',
			array(
				'options' => array(
					'opt-in'  => __( 'Opt-in', 'cookie-consent-by-osano' ),
					'opt-out' => __( 'Opt-out', 'cookie-consent-by-osano' ),
					'info'    => __( 'Info', 'cookie-consent-by-osano' ),
				),
			)
		);

		$this->add_field(
			'position',
			__( 'Banner position', 'cookie-consent-by-osano' ),
			'select',
			'ccbo_cookie_consent_general',
			array(
				'options' => array(
					'bottom'       => __( 'Bottom', 'cookie-consent-by-osano' ),
					'top'          => __( 'Top', 'cookie-consent-by-osano' ),
					'bottom-left'  => __( 'Bottom left', 'cookie-consent-by-osano' ),
					'bottom-right' => __( 'Bottom right', 'cookie-consent-by-osano' ),
					'top-left'     => __( 'Top left', 'cookie-consent-by-osano' ),
					'top-right'    => __( 'Top right', 'cookie-consent-by-osano' ),
				),
			)
		);

		$this->add_field(
			'theme',
			__( 'Theme', 'cookie-consent-by-osano' ),
			'select',
			'ccbo_cookie_consent_general',
			array(
				'options' => array(
					'classic'        => __( 'Classic', 'cookie-consent-by-osano' ),
					'edgeless'       => __( 'Edgeless', 'cookie-consent-by-osano' ),
					'block'          => __( 'Block', 'cookie-consent-by-osano' ),
					'classic-dark'   => __( 'Classic dark', 'cookie-consent-by-osano' ),
					'classic-light'  => __( 'Classic light', 'cookie-consent-by-osano' ),
					'edgeless-dark'  => __( 'Edgeless dark', 'cookie-consent-by-osano' ),
					'edgeless-light' => __( 'Edgeless light', 'cookie-consent-by-osano' ),
					'block-dark'     => __( 'Block dark', 'cookie-consent-by-osano' ),
					'block-light'    => __( 'Block light', 'cookie-consent-by-osano' ),
				),
			)
		);

		$this->add_field(
			'revokable',
			__( 'Revokable', 'cookie-consent-by-osano' ),
			'checkbox',
			'ccbo_cookie_consent_general',
			array(
				'label'       => __( 'Allow visitors to reopen the consent UI after making a choice.', 'cookie-consent-by-osano' ),
				'description' => __( 'This controls whether the banner can be reopened later.', 'cookie-consent-by-osano' ),
			)
		);

		$this->add_field(
			'enable_location_services',
			__( 'Location services', 'cookie-consent-by-osano' ),
			'checkbox',
			'ccbo_cookie_consent_general',
			array(
				'label'       => __( 'Only show the banner to visitors detected in EU countries using the configured location service.', 'cookie-consent-by-osano' ),
				'description' => __( 'Turn this on when the plugin should resolve the visitor country before deciding whether to show the banner.', 'cookie-consent-by-osano' ),
			)
		);
	}

	private function register_content_fields() {
		$this->add_field(
			'message',
			__( 'Message text', 'cookie-consent-by-osano' ),
			'textarea',
			'ccbo_cookie_consent_content',
			array( 'rows' => 4 )
		);

		$this->add_field( 'allow_text', __( 'Allow button text', 'cookie-consent-by-osano' ), 'text', 'ccbo_cookie_consent_content' );
		$this->add_field( 'deny_text', __( 'Deny button text', 'cookie-consent-by-osano' ), 'text', 'ccbo_cookie_consent_content' );
		$this->add_field( 'link_text', __( 'Learn more link text', 'cookie-consent-by-osano' ), 'text', 'ccbo_cookie_consent_content' );

		$this->add_field(
			'policy_url',
			__( 'Policy URL', 'cookie-consent-by-osano' ),
			'text',
			'ccbo_cookie_consent_content',
			array(
				'description' => __( 'Relative URLs are allowed for internal policy pages, for example /privacy-policy/.', 'cookie-consent-by-osano' ),
			)
		);
	}

	private function register_cookie_fields() {
		$this->add_field( 'cookie_name', __( 'Cookie name', 'cookie-consent-by-osano' ), 'text', 'ccbo_cookie_consent_cookie' );
		$this->add_field(
			'cookie_domain',
			__( 'Cookie domain', 'cookie-consent-by-osano' ),
			'text',
			'ccbo_cookie_consent_cookie',
			array(
				'description' => __( 'Leave blank to use the browser default domain behavior.', 'cookie-consent-by-osano' ),
			)
		);
		$this->add_field( 'cookie_path', __( 'Cookie path', 'cookie-consent-by-osano' ), 'text', 'ccbo_cookie_consent_cookie' );
		$this->add_field(
			'expiry_days',
			__( 'Cookie expiration (days)', 'cookie-consent-by-osano' ),
			'number',
			'ccbo_cookie_consent_cookie',
			array( 'min' => 1 )
		);
	}

	private function register_location_fields() {
		$this->add_field(
			'location_service_provider',
			__( 'Provider', 'cookie-consent-by-osano' ),
			'readonly',
			'ccbo_cookie_consent_location',
			array(
				'value'       => 'ipapi',
				'description' => __( 'This plugin currently uses ipapi to detect the visitor country before deciding whether to show the banner.', 'cookie-consent-by-osano' ),
			)
		);

		$this->add_field(
			'location_service_url',
			__( 'Endpoint URL', 'cookie-consent-by-osano' ),
			'text',
			'ccbo_cookie_consent_location',
			array(
				'description' => __( 'Default ipapi endpoint for visitor country lookups.', 'cookie-consent-by-osano' ),
			)
		);

		$this->add_field(
			'location_service_timeout',
			__( 'Request timeout (ms)', 'cookie-consent-by-osano' ),
			'number',
			'ccbo_cookie_consent_location',
			array(
				'min'         => 500,
				'description' => __( 'How long the browser waits for the location lookup before falling back.', 'cookie-consent-by-osano' ),
			)
		);

		$this->add_field(
			'location_service_cache_hours',
			__( 'Cache duration (hours)', 'cookie-consent-by-osano' ),
			'number',
			'ccbo_cookie_consent_location',
			array(
				'min'         => 1,
				'description' => __( 'How long the browser should cache the visitor country before checking again.', 'cookie-consent-by-osano' ),
			)
		);
	}

	private function register_styling_fields() {
		$this->add_field( 'palette_popup_background', __( 'Banner background', 'cookie-consent-by-osano' ), 'color', 'ccbo_cookie_consent_styling' );
		$this->add_field( 'palette_popup_text', __( 'Banner text', 'cookie-consent-by-osano' ), 'color', 'ccbo_cookie_consent_styling' );
		$this->add_field( 'palette_button_background', __( 'Primary button background', 'cookie-consent-by-osano' ), 'color', 'ccbo_cookie_consent_styling' );
		$this->add_field( 'palette_button_text', __( 'Primary button text', 'cookie-consent-by-osano' ), 'color', 'ccbo_cookie_consent_styling' );
		$this->add_field( 'palette_button_border', __( 'Primary button border', 'cookie-consent-by-osano' ), 'color', 'ccbo_cookie_consent_styling' );
		$this->add_field( 'palette_highlight_text', __( 'Secondary action text', 'cookie-consent-by-osano' ), 'color', 'ccbo_cookie_consent_styling' );
		$this->add_field(
			'custom_css',
			__( 'Additional CSS', 'cookie-consent-by-osano' ),
			'textarea',
			'ccbo_cookie_consent_styling',
			array(
				'rows'        => 8,
				'description' => __( 'Optional CSS overrides for more custom control over the banner UI.', 'cookie-consent-by-osano' ),
			)
		);
	}

	public function register_admin_page() {
		add_options_page(
			__( 'Cookie Consent by Osano', 'cookie-consent-by-osano' ),
			__( 'Cookie Consent', 'cookie-consent-by-osano' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_admin_page' )
		);
	}

	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style(
			'ccbo-cookie-consent-admin',
			CCBO_COOKIE_CONSENT_URL . 'assets/css/admin-settings.css',
			array( 'wp-color-picker' ),
			CCBO_COOKIE_CONSENT_VERSION
		);
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script(
			'ccbo-cookie-consent-admin',
			CCBO_COOKIE_CONSENT_URL . 'assets/js/admin-settings.js',
			array( 'jquery', 'wp-color-picker' ),
			CCBO_COOKIE_CONSENT_VERSION,
			true
		);
	}

	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tabs = $this->get_tabs();
		?>
		<div class="wrap ccbo-cookie-consent-admin">
			<h1><?php echo esc_html__( 'Cookie Consent by Osano', 'cookie-consent-by-osano' ); ?></h1>
			<p><?php echo esc_html__( 'Configure the default cookie consent banner behavior for this site.', 'cookie-consent-by-osano' ); ?></p>

			<form action="options.php" method="post">
				<?php settings_fields( 'ccbo_cookie_consent' ); ?>

				<nav class="nav-tab-wrapper ccbo-cookie-consent-tabs" aria-label="<?php echo esc_attr__( 'Cookie Consent settings sections', 'cookie-consent-by-osano' ); ?>">
					<?php foreach ( $tabs as $tab_key => $tab ) : ?>
						<button type="button" class="nav-tab<?php echo 'general' === $tab_key ? ' nav-tab-active' : ''; ?>" data-tab-target="<?php echo esc_attr( $tab_key ); ?>">
							<?php echo esc_html( $tab['label'] ); ?>
						</button>
					<?php endforeach; ?>
				</nav>

				<?php foreach ( $tabs as $tab_key => $tab ) : ?>
					<section class="ccbo-cookie-consent-tab-panel<?php echo 'general' === $tab_key ? ' is-active' : ''; ?>" data-tab-panel="<?php echo esc_attr( $tab_key ); ?>">
						<div class="ccbo-settings-layout">
							<div class="ccbo-settings-main">
								<h2><?php echo esc_html( $tab['label'] ); ?></h2>
								<?php call_user_func( $tab['callback'] ); ?>
								<?php if ( 'styling' === $tab_key ) : ?>
									<?php $this->render_styling_fields_grouped(); ?>
								<?php else : ?>
									<table class="form-table" role="presentation">
										<?php do_settings_fields( self::PAGE_SLUG, $tab['section'] ); ?>
									</table>
								<?php endif; ?>
							</div>

							<aside class="ccbo-settings-help">
								<?php $this->render_tab_help_panel( $tab_key ); ?>
							</aside>
						</div>
					</section>
				<?php endforeach; ?>

				<?php submit_button( __( 'Save Settings', 'cookie-consent-by-osano' ) ); ?>
			</form>
		</div>
		<?php
	}

	public function sanitize_options( $input ) {
		$defaults = $this->get_default_options();
		$input    = is_array( $input ) ? $input : array();

		$sanitized = array(
			'enabled'                      => ! empty( $input['enabled'] ),
			'consent_mode'                 => $this->sanitize_choice( $input, 'consent_mode', array( 'opt-in', 'opt-out', 'info' ), $defaults['consent_mode'] ),
			'position'                     => $this->sanitize_choice( $input, 'position', array( 'bottom', 'top', 'bottom-left', 'bottom-right', 'top-left', 'top-right' ), $defaults['position'] ),
			'theme'                        => $this->sanitize_choice( $input, 'theme', array( 'classic', 'edgeless', 'block', 'classic-dark', 'classic-light', 'edgeless-dark', 'edgeless-light', 'block-dark', 'block-light' ), $defaults['theme'] ),
			'revokable'                    => ! empty( $input['revokable'] ),
			'enable_location_services'     => ! empty( $input['enable_location_services'] ),
			'message'                      => isset( $input['message'] ) ? sanitize_textarea_field( $input['message'] ) : $defaults['message'],
			'allow_text'                   => isset( $input['allow_text'] ) ? sanitize_text_field( $input['allow_text'] ) : $defaults['allow_text'],
			'deny_text'                    => isset( $input['deny_text'] ) ? sanitize_text_field( $input['deny_text'] ) : $defaults['deny_text'],
			'link_text'                    => isset( $input['link_text'] ) ? sanitize_text_field( $input['link_text'] ) : $defaults['link_text'],
			'policy_url'                   => isset( $input['policy_url'] ) ? $this->sanitize_policy_url( $input['policy_url'] ) : $defaults['policy_url'],
			'cookie_name'                  => isset( $input['cookie_name'] ) ? sanitize_key( $input['cookie_name'] ) : $defaults['cookie_name'],
			'cookie_domain'                => isset( $input['cookie_domain'] ) ? sanitize_text_field( $input['cookie_domain'] ) : $defaults['cookie_domain'],
			'cookie_path'                  => isset( $input['cookie_path'] ) ? sanitize_text_field( $input['cookie_path'] ) : $defaults['cookie_path'],
			'expiry_days'                  => isset( $input['expiry_days'] ) ? max( 1, absint( $input['expiry_days'] ) ) : $defaults['expiry_days'],
			'location_service_provider'    => 'ipapi',
			'location_service_url'         => isset( $input['location_service_url'] ) ? esc_url_raw( trim( (string) $input['location_service_url'] ) ) : $defaults['location_service_url'],
			'location_service_timeout'     => isset( $input['location_service_timeout'] ) ? max( 500, absint( $input['location_service_timeout'] ) ) : $defaults['location_service_timeout'],
			'location_service_cache_hours' => isset( $input['location_service_cache_hours'] ) ? max( 1, absint( $input['location_service_cache_hours'] ) ) : $defaults['location_service_cache_hours'],
			'palette_popup_background'     => $this->sanitize_color_value( $input, 'palette_popup_background', $defaults['palette_popup_background'] ),
			'palette_popup_text'           => $this->sanitize_color_value( $input, 'palette_popup_text', $defaults['palette_popup_text'] ),
			'palette_button_background'    => $this->sanitize_color_value( $input, 'palette_button_background', $defaults['palette_button_background'] ),
			'palette_button_text'          => $this->sanitize_color_value( $input, 'palette_button_text', $defaults['palette_button_text'] ),
			'palette_button_border'        => $this->sanitize_color_value( $input, 'palette_button_border', $defaults['palette_button_border'] ),
			'palette_highlight_text'       => $this->sanitize_color_value( $input, 'palette_highlight_text', $defaults['palette_highlight_text'] ),
			'custom_css'                   => isset( $input['custom_css'] ) ? wp_strip_all_tags( $input['custom_css'] ) : $defaults['custom_css'],
		);

		return array_merge( $defaults, $sanitized );
	}

	public function get_options() {
		$saved = get_option( self::OPTION_KEY, array() );

		if ( ! is_array( $saved ) ) {
			$saved = array();
		}

		return array_merge( $this->get_default_options(), $saved );
	}

	public function get_default_options() {
		$defaults = array(
			'enabled'                      => true,
			'consent_mode'                 => 'opt-in',
			'position'                     => 'bottom',
			'theme'                        => 'classic',
			'revokable'                    => true,
			'enable_location_services'     => false,
			'message'                      => __( 'This website uses cookies to improve your experience.', 'cookie-consent-by-osano' ),
			'allow_text'                   => __( 'Allow cookies', 'cookie-consent-by-osano' ),
			'deny_text'                    => __( 'Decline', 'cookie-consent-by-osano' ),
			'link_text'                    => __( 'Learn more', 'cookie-consent-by-osano' ),
			'policy_url'                   => '/privacy-policy/',
			'cookie_name'                  => 'ccbo_cookie_consent',
			'cookie_domain'                => '',
			'cookie_path'                  => '/',
			'expiry_days'                  => 365,
			'location_service_provider'    => 'ipapi',
			'location_service_url'         => 'https://ipapi.co/json/',
			'location_service_timeout'     => 3000,
			'location_service_cache_hours' => 24,
			'palette_popup_background'     => '#1f2937',
			'palette_popup_text'           => '#f9fafb',
			'palette_button_background'    => '#2563eb',
			'palette_button_text'          => '#ffffff',
			'palette_button_border'        => '#2563eb',
			'palette_highlight_text'       => '#f9fafb',
			'custom_css'                   => '',
		);

		return apply_filters( 'ccbo_cookie_consent_default_options', $defaults );
	}

	private function get_tabs() {
		return array(
			'general'  => array(
				'label'    => __( 'General Settings', 'cookie-consent-by-osano' ),
				'section'  => 'ccbo_cookie_consent_general',
				'callback' => array( $this, 'render_general_section' ),
			),
			'content'  => array(
				'label'    => __( 'Banner Content', 'cookie-consent-by-osano' ),
				'section'  => 'ccbo_cookie_consent_content',
				'callback' => array( $this, 'render_content_section' ),
			),
			'cookie'   => array(
				'label'    => __( 'Cookie Settings', 'cookie-consent-by-osano' ),
				'section'  => 'ccbo_cookie_consent_cookie',
				'callback' => array( $this, 'render_cookie_section' ),
			),
			'location' => array(
				'label'    => __( 'Location Services', 'cookie-consent-by-osano' ),
				'section'  => 'ccbo_cookie_consent_location',
				'callback' => array( $this, 'render_location_section' ),
			),
			'styling'  => array(
				'label'    => __( 'Styling', 'cookie-consent-by-osano' ),
				'section'  => 'ccbo_cookie_consent_styling',
				'callback' => array( $this, 'render_styling_section' ),
			),
		);
	}

	public function render_general_section() {
		echo '<p>' . esc_html__( 'Set the overall banner behavior and presentation defaults.', 'cookie-consent-by-osano' ) . '</p>';
	}

	public function render_content_section() {
		echo '<p>' . esc_html__( 'Control the banner copy shown to site visitors.', 'cookie-consent-by-osano' ) . '</p>';
	}

	public function render_cookie_section() {
		echo '<p>' . esc_html__( 'Configure how the consent cookie is stored.', 'cookie-consent-by-osano' ) . '</p>';
	}

	public function render_location_section() {
		echo '<p>' . esc_html__( 'Configure how the plugin detects whether a visitor is in the EU before showing the banner.', 'cookie-consent-by-osano' ) . '</p>';
	}

	public function render_styling_section() {
		echo '<p>' . esc_html__( 'Set the default banner colors and add optional CSS overrides.', 'cookie-consent-by-osano' ) . '</p>';
	}

	private function render_styling_fields_grouped() {
		?>
		<div class="ccbo-style-groups">
			<div class="ccbo-style-group">
				<h3><?php echo esc_html__( 'Banner', 'cookie-consent-by-osano' ); ?></h3>
				<div class="ccbo-style-group-fields">
					<?php $this->render_compact_field( 'palette_popup_background', __( 'Background', 'cookie-consent-by-osano' ), 'color' ); ?>
					<?php $this->render_compact_field( 'palette_popup_text', __( 'Text', 'cookie-consent-by-osano' ), 'color' ); ?>
				</div>

                <h3><?php echo esc_html__( 'Primary Button', 'cookie-consent-by-osano' ); ?></h3>
				<div class="ccbo-style-group-fields">
					<?php $this->render_compact_field( 'palette_button_background', __( 'Background', 'cookie-consent-by-osano' ), 'color' ); ?>
					<?php $this->render_compact_field( 'palette_button_text', __( 'Text', 'cookie-consent-by-osano' ), 'color' ); ?>
					<?php $this->render_compact_field( 'palette_button_border', __( 'Border', 'cookie-consent-by-osano' ), 'color' ); ?>
				</div>

                <h3><?php echo esc_html__( 'Secondary Action', 'cookie-consent-by-osano' ); ?></h3>
				<div class="ccbo-style-group-fields">
					<?php $this->render_compact_field( 'palette_highlight_text', __( 'Text', 'cookie-consent-by-osano' ), 'color' ); ?>
				</div>
			</div>

			<div class="ccbo-style-group">
				<h3><?php echo esc_html__( 'Additional CSS', 'cookie-consent-by-osano' ); ?></h3>
				<?php $this->render_styling_css_field(); ?>
			</div>
		</div>
		<?php
	}

	private function render_compact_field( $key, $label, $type ) {
		$options    = $this->get_options();
		$value      = isset( $options[ $key ] ) ? $options[ $key ] : '';
		$field_id   = 'ccbo-cookie-consent-' . $key;
		$field_name = self::OPTION_KEY . '[' . $key . ']';
		?>
		<div class="ccbo-style-field">
			<label class="ccbo-style-field-label" for="<?php echo esc_attr( $field_id ); ?>">
				<?php echo esc_html( $label ); ?>
			</label>
			<input
				id="<?php echo esc_attr( $field_id ); ?>"
				name="<?php echo esc_attr( $field_name ); ?>"
				type="<?php echo esc_attr( $type ); ?>"
				value="<?php echo esc_attr( $value ); ?>"
				class="ccbo-color-field"
			/>
		</div>
		<?php
	}

	private function render_styling_css_field() {
		$options     = $this->get_options();
		$value       = isset( $options['custom_css'] ) ? $options['custom_css'] : '';
		$field_id    = 'ccbo-cookie-consent-custom-css';
		$field_name  = self::OPTION_KEY . '[custom_css]';
		$description = __( 'Optional CSS overrides for more custom control over the banner UI.', 'cookie-consent-by-osano' );
		?>
		<div class="ccbo-style-css-field">
			<textarea id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" rows="8" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
			<p class="description"><?php echo esc_html( $description ); ?></p>
		</div>
		<?php
	}

	private function render_tab_help_panel( $tab_key ) {
		$panels = array(
			'general'  => array(
				'eyebrow' => __( 'Quick Guide', 'cookie-consent-by-osano' ),
				'title'   => __( 'Choose How Consent Works On This Site', 'cookie-consent-by-osano' ),
				'intro'   => __( 'These settings control the overall behavior of the banner before you get into wording, location targeting, or visual styling.', 'cookie-consent-by-osano' ),
				'items'   => array(
					array( 'title' => __( 'Enable banner', 'cookie-consent-by-osano' ), 'body' => __( 'Turn the banner on when the site should ask visitors about cookies. Turn it off if consent is being handled elsewhere.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Consent mode', 'cookie-consent-by-osano' ), 'body' => __( 'Use opt-in when tracking should wait for permission. Use opt-out when tracking may run unless a visitor declines. Use info when the banner is only informational.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Position and theme', 'cookie-consent-by-osano' ), 'body' => __( 'These affect where the banner appears and how much visual weight it carries on the page.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Location services toggle', 'cookie-consent-by-osano' ), 'body' => __( 'Enable this when the banner should only appear for visitors detected in the EU. The actual provider settings live in the Location Services tab.', 'cookie-consent-by-osano' ) ),
				),
			),
			'content'  => array(
				'eyebrow' => __( 'Writing Tips', 'cookie-consent-by-osano' ),
				'title'   => __( 'Keep The Banner Short, Clear, And Direct', 'cookie-consent-by-osano' ),
				'intro'   => __( 'This section controls the text visitors actually read, so clarity matters more than legal-sounding language.', 'cookie-consent-by-osano' ),
				'items'   => array(
					array( 'title' => __( 'Message text', 'cookie-consent-by-osano' ), 'body' => __( 'Briefly explain why cookies are used and what the visitor is agreeing to. Aim for plain language over policy jargon.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Button labels', 'cookie-consent-by-osano' ), 'body' => __( 'Use action words that make the choice obvious, such as Allow, Decline, Accept, or Reject.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Policy URL', 'cookie-consent-by-osano' ), 'body' => __( 'Point this to the page where visitors can read the full cookie or privacy policy. Internal paths like /privacy-policy/ are fine.', 'cookie-consent-by-osano' ) ),
				),
			),
			'cookie'   => array(
				'eyebrow' => __( 'Cookie Basics', 'cookie-consent-by-osano' ),
				'title'   => __( 'Control How The Consent Choice Is Stored', 'cookie-consent-by-osano' ),
				'intro'   => __( 'These values do not change the wording of your policy. They control the browser cookie that remembers a visitor\'s consent choice.', 'cookie-consent-by-osano' ),
				'items'   => array(
					array( 'title' => __( 'Cookie name', 'cookie-consent-by-osano' ), 'body' => __( 'This is the identifier saved in the browser. Keep it unique enough that it will not collide with another plugin or script.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Cookie domain', 'cookie-consent-by-osano' ), 'body' => __( 'Leave this blank for most sites. Set it only when you need the consent choice shared across subdomains.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Cookie path', 'cookie-consent-by-osano' ), 'body' => __( 'A path of / makes the consent cookie available across the full site, which is the normal choice for site-wide banners.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Expiration', 'cookie-consent-by-osano' ), 'body' => __( 'This is how long the browser remembers the visitor\'s choice before asking again. One year is a common starting point, but your policy may require something shorter.', 'cookie-consent-by-osano' ) ),
				),
			),
			'location' => array(
				'eyebrow' => __( 'EU Targeting', 'cookie-consent-by-osano' ),
				'title'   => __( 'Only Show The Banner To Visitors In The EU', 'cookie-consent-by-osano' ),
				'intro'   => __( 'This tab controls the browser-side country lookup used when a site does not already have server-level GeoIP data available.', 'cookie-consent-by-osano' ),
				'items'   => array(
					array( 'title' => __( 'Provider', 'cookie-consent-by-osano' ), 'body' => __( 'The plugin uses ipapi to determine the visitor country in the browser before CookieConsent initializes.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Endpoint URL', 'cookie-consent-by-osano' ), 'body' => __( 'This is the URL the browser calls to retrieve the visitor country. In most cases you should leave it at the default ipapi endpoint.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Request timeout', 'cookie-consent-by-osano' ), 'body' => __( 'If the lookup takes too long, the plugin falls back to showing the banner so EU visitors are not skipped by a network failure.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Cache duration', 'cookie-consent-by-osano' ), 'body' => __( 'The visitor country is cached in the browser to reduce repeat requests and speed up later page loads.', 'cookie-consent-by-osano' ) ),
				),
			),
			'styling'  => array(
				'eyebrow' => __( 'Design Notes', 'cookie-consent-by-osano' ),
				'title'   => __( 'Match The Banner To The Site Without Hiding It', 'cookie-consent-by-osano' ),
				'intro'   => __( 'Use these controls to make the banner feel intentional, while still keeping the consent choices easy to see and understand.', 'cookie-consent-by-osano' ),
				'items'   => array(
					array( 'title' => __( 'Banner colors', 'cookie-consent-by-osano' ), 'body' => __( 'Choose a background and text combination with strong contrast so the message stays readable on every page.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Primary button', 'cookie-consent-by-osano' ), 'body' => __( 'This is usually the most prominent action. Use a color that feels native to the site without overwhelming the content below it.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Secondary action', 'cookie-consent-by-osano' ), 'body' => __( 'Keep the alternate action visible and readable so the visitor can make a clear choice either way.', 'cookie-consent-by-osano' ) ),
					array( 'title' => __( 'Additional CSS', 'cookie-consent-by-osano' ), 'body' => __( 'Use custom CSS only when the built-in settings are not enough. It is best for spacing, typography, and edge-case adjustments.', 'cookie-consent-by-osano' ) ),
				),
			),
		);

		$panel = isset( $panels[ $tab_key ] ) ? $panels[ $tab_key ] : $panels['general'];
		?>
		<div class="ccbo-help-panel">
			<p class="ccbo-help-panel-eyebrow"><?php echo esc_html( $panel['eyebrow'] ); ?></p>
			<h3><?php echo esc_html( $panel['title'] ); ?></h3>
			<p class="ccbo-help-panel-intro"><?php echo esc_html( $panel['intro'] ); ?></p>
			<div class="ccbo-help-panel-items">
				<?php foreach ( $panel['items'] as $item ) : ?>
					<section class="ccbo-help-panel-item">
						<h4><?php echo esc_html( $item['title'] ); ?></h4>
						<p><?php echo esc_html( $item['body'] ); ?></p>
					</section>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	private function add_field( $key, $label, $type, $section, $args = array() ) {
		$args['key']  = $key;
		$args['type'] = $type;

		add_settings_field( $key, $label, array( $this, 'render_field' ), self::PAGE_SLUG, $section, $args );
	}

	public function render_field( $args ) {
		$options     = $this->get_options();
		$key         = $args['key'];
		$type        = $args['type'];
		$value       = isset( $options[ $key ] ) ? $options[ $key ] : '';
		$field_id    = 'ccbo-cookie-consent-' . $key;
		$field_name  = self::OPTION_KEY . '[' . $key . ']';
		$description = isset( $args['description'] ) ? $args['description'] : '';

		if ( in_array( $key, array( 'palette_popup_background', 'palette_popup_text', 'palette_button_background', 'palette_button_text', 'palette_button_border', 'palette_highlight_text', 'custom_css' ), true ) ) {
			return;
		}

		if ( 'checkbox' === $type ) {
			$label = isset( $args['label'] ) ? $args['label'] : '';
			?>
			<label for="<?php echo esc_attr( $field_id ); ?>">
				<input id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" type="checkbox" value="1" <?php checked( ! empty( $value ) ); ?> />
				<?php echo esc_html( $label ); ?>
			</label>
			<?php
		} elseif ( 'select' === $type ) {
			$options_map = isset( $args['options'] ) ? $args['options'] : array();
			?>
			<select id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>">
				<?php foreach ( $options_map as $option_value => $option_label ) : ?>
					<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>><?php echo esc_html( $option_label ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php
		} elseif ( 'textarea' === $type ) {
			$rows = isset( $args['rows'] ) ? absint( $args['rows'] ) : 5;
			?>
			<textarea id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" rows="<?php echo esc_attr( $rows ); ?>" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
			<?php
		} else {
			$input_type = in_array( $type, array( 'text', 'number', 'color', 'readonly' ), true ) ? $type : 'text';
			$min        = isset( $args['min'] ) ? absint( $args['min'] ) : '';
			$class      = 'regular-text';

			if ( 'color' === $type ) {
				$class = 'ccbo-color-field';
			} elseif ( 'readonly' === $type ) {
				$input_type = 'text';
			}
			?>
			<input
				id="<?php echo esc_attr( $field_id ); ?>"
				name="<?php echo esc_attr( $field_name ); ?>"
				type="<?php echo esc_attr( $input_type ); ?>"
				value="<?php echo esc_attr( isset( $args['value'] ) ? $args['value'] : $value ); ?>"
				class="<?php echo esc_attr( $class ); ?>"
				<?php echo '' !== $min ? 'min="' . esc_attr( $min ) . '"' : ''; ?>
				<?php echo 'readonly' === $type ? 'readonly="readonly"' : ''; ?>
			/>
			<?php
		}

		if ( $description ) {
			echo '<p class="description">' . esc_html( $description ) . '</p>';
		}
	}

	private function sanitize_choice( $input, $key, $allowed, $fallback ) {
		$value = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : $fallback;
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	private function sanitize_color_value( $input, $key, $fallback ) {
		$value = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : null;
		return ( null === $value || false === $value ) ? $fallback : $value;
	}

	private function sanitize_policy_url( $url ) {
		$url = trim( (string) $url );
		return '' === $url ? '' : esc_url_raw( $url );
	}
}
