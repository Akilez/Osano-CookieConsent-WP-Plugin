<?php

function test_assets_frontend_config_without_domain() {
    $settings = new CCBO_Cookie_Consent_Settings();
    update_option(
        CCBO_Cookie_Consent_Settings::OPTION_KEY,
        array(
            'cookie_domain'            => '',
            'enable_location_services' => true,
            'policy_url'               => '/privacy-policy/',
        )
    );

    $assets = new CCBO_Cookie_Consent_Assets( $settings );
    $config = ccbo_invoke_private_method( $assets, 'get_frontend_config' );

    ccbo_assert_same( 'opt-in', $config['type'], 'Frontend config should use the saved/default consent mode.' );
    ccbo_assert_same( '/privacy-policy/', $config['content']['href'], 'Policy URL should flow into the frontend config.' );
    ccbo_assert_same( true, $config['ulcLocation']['enabled'], 'Location services flag should flow into the frontend config.' );
    ccbo_assert_false( isset( $config['cookie']['domain'] ), 'Cookie domain should be omitted when blank.' );
    ccbo_assert_same( false, $config['cookie']['secure'], 'Cookie secure flag should reflect is_ssl().' );
}

function test_assets_frontend_config_with_domain_and_filters() {
    $settings = new CCBO_Cookie_Consent_Settings();
    update_option(
        CCBO_Cookie_Consent_Settings::OPTION_KEY,
        array(
            'cookie_domain' => '.example.com',
            'policy_url'    => '/privacy-policy/',
        )
    );

    add_filter(
        'ccbo_cookie_consent_policy_url',
        function ( $url ) {
            return 'https://example.com' . $url;
        }
    );

    $assets = new CCBO_Cookie_Consent_Assets( $settings );
    $config = ccbo_invoke_private_method( $assets, 'get_frontend_config' );

    ccbo_assert_same( '.example.com', $config['cookie']['domain'], 'Cookie domain should be included when present.' );
    ccbo_assert_same( 'https://example.com/privacy-policy/', $config['content']['href'], 'Policy URL filter should be applied.' );
}

function test_assets_custom_css_is_trimmed() {
    $settings = new CCBO_Cookie_Consent_Settings();
    update_option(
        CCBO_Cookie_Consent_Settings::OPTION_KEY,
        array(
            'custom_css' => "\n  .ccbo-cookie-consent { color: red; }  \n",
        )
    );

    $assets = new CCBO_Cookie_Consent_Assets( $settings );
    $css    = ccbo_invoke_private_method( $assets, 'get_custom_css' );

    ccbo_assert_same( '.ccbo-cookie-consent { color: red; }', $css, 'Custom CSS should be trimmed before output.' );
}
