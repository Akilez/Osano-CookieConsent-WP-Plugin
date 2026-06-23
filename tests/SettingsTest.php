<?php

function test_settings_default_options() {
    $settings = new CCBO_Cookie_Consent_Settings();
    $defaults = $settings->get_default_options();

    ccbo_assert_same( 'opt-in', $defaults['consent_mode'], 'Default consent mode should be opt-in.' );
    ccbo_assert_same( true, $defaults['enabled'], 'Banner should default to enabled.' );
    ccbo_assert_same( false, $defaults['enable_location_services'], 'Location services should default to disabled.' );
    ccbo_assert_same( 'https://ipapi.co/json/', $defaults['location_service_url'], 'ipapi should be the default location endpoint.' );
    ccbo_assert_same( false, $defaults['ga4_enabled'], 'GA4 should default to disabled.' );
    ccbo_assert_same( '', $defaults['ga4_measurement_id'], 'GA4 measurement ID should default to blank.' );
    ccbo_assert_same( '#1f2937', $defaults['palette_popup_background'], 'Banner background default changed unexpectedly.' );
}

function test_settings_sanitize_options() {
    $settings = new CCBO_Cookie_Consent_Settings();
    $result   = $settings->sanitize_options(
        array(
            'enabled'                      => '1',
            'consent_mode'                 => 'bogus',
            'position'                     => 'top-right',
            'theme'                        => 'block-light',
            'revokable'                    => '',
            'enable_location_services'     => '1',
            'message'                      => "<b>Hello</b>\n\nWorld",
            'allow_text'                   => ' Allow ',
            'deny_text'                    => '<script>bad</script>Decline',
            'link_text'                    => ' Learn more ',
            'policy_url'                   => '/privacy-policy/',
            'cookie_name'                  => 'My Cookie Name!',
            'cookie_domain'                => ' cookies.example.com ',
            'cookie_path'                  => ' /subdir/ ',
            'expiry_days'                  => '0',
            'location_service_url'         => ' https://ipapi.co/json/ ',
            'location_service_timeout'     => '100',
            'location_service_cache_hours' => '0',
            'ga4_enabled'                  => '1',
            'ga4_measurement_id'           => ' g-Te_st123! ',
            'palette_popup_background'     => '#ABCDEF',
            'palette_popup_text'           => 'bad-color',
            'palette_button_background'    => '#123456',
            'palette_button_text'          => '#ffffff',
            'palette_button_border'        => '#654321',
            'palette_highlight_text'       => '#FEDCBA',
            'custom_css'                   => '<style>.ccbo{color:red;}</style>',
        )
    );

    ccbo_assert_same( 'opt-in', $result['consent_mode'], 'Invalid consent mode should fall back to default.' );
    ccbo_assert_same( 'top-right', $result['position'], 'Valid position should be kept.' );
    ccbo_assert_same( 'block-light', $result['theme'], 'Valid theme should be kept.' );
    ccbo_assert_false( $result['revokable'], 'Unchecked revokable flag should sanitize to false.' );
    ccbo_assert_true( $result['enable_location_services'], 'Location services toggle should sanitize to true.' );
    ccbo_assert_same( "Hello\n\nWorld", $result['message'], 'Message should be sanitized as textarea content.' );
    ccbo_assert_same( 'Allow', $result['allow_text'], 'Allow label should be trimmed.' );
    ccbo_assert_same( 'badDecline', $result['deny_text'], 'Deny label should strip tags.' );
    ccbo_assert_same( '/privacy-policy/', $result['policy_url'], 'Relative policy URL should remain valid.' );
    ccbo_assert_same( 'mycookiename', $result['cookie_name'], 'Cookie name should sanitize via sanitize_key.' );
    ccbo_assert_same( 'cookies.example.com', $result['cookie_domain'], 'Cookie domain should be trimmed.' );
    ccbo_assert_same( '/subdir/', $result['cookie_path'], 'Cookie path should be trimmed.' );
    ccbo_assert_same( 1, $result['expiry_days'], 'Cookie expiry should have a minimum of one day.' );
    ccbo_assert_same( 500, $result['location_service_timeout'], 'Location timeout should have a minimum of 500ms.' );
    ccbo_assert_same( 1, $result['location_service_cache_hours'], 'Location cache should have a minimum of one hour.' );
    ccbo_assert_true( $result['ga4_enabled'], 'GA4 toggle should sanitize to true.' );
    ccbo_assert_same( 'G-TEST123', $result['ga4_measurement_id'], 'GA4 measurement ID should normalize to an uppercase Google Analytics ID.' );
    ccbo_assert_same( '#abcdef', $result['palette_popup_background'], 'Valid hex colors should normalize and persist.' );
    ccbo_assert_same( '#f9fafb', $result['palette_popup_text'], 'Invalid hex colors should fall back to defaults.' );
    ccbo_assert_same( '.ccbo{color:red;}', $result['custom_css'], 'Custom CSS should be stripped down to raw CSS text.' );
}
