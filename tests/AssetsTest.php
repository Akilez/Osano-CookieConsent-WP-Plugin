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

function test_assets_frontend_config_includes_normalized_deferred_scripts() {
    $settings = new CCBO_Cookie_Consent_Settings();
    update_option(
        CCBO_Cookie_Consent_Settings::OPTION_KEY,
        array(
            'ga4_enabled'        => true,
            'ga4_measurement_id' => 'G-TEST123',
        )
    );

    add_filter(
        'ccbo_cookie_consent_deferred_scripts',
        function () {
            return array(
                array(
                    'id'         => 'Google Analytics',
                    'src'        => ' https://www.googletagmanager.com/gtag/js?id=G-TEST123 ',
                    'attributes' => array(
                        'async'      => true,
                        'defer'      => false,
                        'data-site'  => 'main',
                        'onclick'    => 'alert(1)',
                    ),
                ),
                array(
                    'inline' => 'window.dataLayer = window.dataLayer || [];',
                ),
                array(
                    'id'  => 'invalid',
                    'src' => 'not-a-url',
                ),
            );
        }
    );

    $assets = new CCBO_Cookie_Consent_Assets( $settings );
    $config = ccbo_invoke_private_method( $assets, 'get_frontend_config' );

    ccbo_assert_array_has_key( 'deferredScripts', $config, 'Frontend config should include deferred scripts.' );
    ccbo_assert_same( 4, count( $config['deferredScripts'] ), 'Built-in GA4 scripts and valid custom deferred scripts should be included.' );
    ccbo_assert_same( true, $config['ga4']['enabled'], 'GA4 config should be enabled when the measurement ID is present.' );
    ccbo_assert_same( 'G-TEST123', $config['ga4']['measurementId'], 'GA4 measurement ID should flow into the frontend config.' );
    ccbo_assert_same( 'ccbo_ga4_library', $config['deferredScripts'][0]['id'], 'Built-in GA4 library script should be prepended.' );
    ccbo_assert_same( 'googleanalytics', $config['deferredScripts'][2]['id'], 'Deferred script ids should be sanitized.' );
    ccbo_assert_same( 'https://www.googletagmanager.com/gtag/js?id=G-TEST123', $config['deferredScripts'][2]['src'], 'Deferred script URLs should be trimmed and preserved.' );
    ccbo_assert_same(
        array(
            'async'     => true,
            'defer'     => false,
            'data-site' => 'main',
        ),
        $config['deferredScripts'][2]['attributes'],
        'Deferred script attributes should be normalized and unsafe attributes should be removed.'
    );
    ccbo_assert_same( 'window.dataLayer = window.dataLayer || [];', $config['deferredScripts'][3]['inline'], 'Inline deferred scripts should be preserved.' );
}
