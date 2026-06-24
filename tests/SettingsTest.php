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
    ccbo_assert_same( array(), $defaults['script_gate_entries'], 'Script gate entries should default to an empty list.' );
    ccbo_assert_same( '#1f2937', $defaults['palette_popup_background'], 'Banner background default changed unexpectedly.' );
    ccbo_assert_same( '#ffffff', $defaults['reopen_button_background'], 'Reopen button background default changed unexpectedly.' );
    ccbo_assert_same( '#4b5563', $defaults['attribution_text'], 'Attribution text default changed unexpectedly.' );
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
            'script_gate_entries'          => array(
                array(
                    'enabled'  => '1',
                    'label'    => ' Main Pixel ',
                    'category' => 'marketing',
                    'src'      => ' https://example.com/pixel.js ',
                    'inline'   => '<script>window.pixel = true;</script>',
                    'async'    => '1',
                    'defer'    => '',
                ),
                array(
                    'label'    => '',
                    'category' => 'bad',
                    'src'      => 'not-a-url',
                    'inline'   => ' window.analytics = true; ',
                    'defer'    => '1',
                ),
                array(
                    'enabled' => '1',
                    'label'   => 'Empty',
                    'src'     => '',
                    'inline'  => '',
                ),
            ),
            'palette_popup_background'     => '#ABCDEF',
            'palette_popup_text'           => 'bad-color',
            'palette_button_background'    => '#123456',
            'palette_button_text'          => '#ffffff',
            'palette_button_border'        => '#654321',
            'palette_highlight_text'       => '#FEDCBA',
            'reopen_button_background'     => '#111111',
            'reopen_button_text'           => '#222222',
            'reopen_button_border'         => '#333333',
            'attribution_text'             => '#444444',
            'attribution_link'             => 'not-a-color',
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
    ccbo_assert_same( 2, count( $result['script_gate_entries'] ), 'Script gate should keep only rows with a valid URL or inline script.' );
    ccbo_assert_same( 'Main Pixel', $result['script_gate_entries'][0]['label'], 'Script labels should be sanitized.' );
    ccbo_assert_same( 'marketing', $result['script_gate_entries'][0]['category'], 'Valid script categories should be kept.' );
    ccbo_assert_same( 'https://example.com/pixel.js', $result['script_gate_entries'][0]['src'], 'Script URLs should be sanitized.' );
    ccbo_assert_same( 'window.pixel = true;', $result['script_gate_entries'][0]['inline'], 'Script tag wrappers should be stripped from inline scripts.' );
    ccbo_assert_true( $result['script_gate_entries'][0]['async'], 'Async should sanitize to true when checked.' );
    ccbo_assert_false( $result['script_gate_entries'][0]['defer'], 'Defer should sanitize to false when unchecked.' );
    ccbo_assert_false( $result['script_gate_entries'][1]['enabled'], 'Script gate entries should preserve disabled state.' );
    ccbo_assert_same( 'Script 2', $result['script_gate_entries'][1]['label'], 'Blank script labels should receive a fallback label.' );
    ccbo_assert_same( 'analytics', $result['script_gate_entries'][1]['category'], 'Invalid script categories should fall back to analytics.' );
    ccbo_assert_same( '', $result['script_gate_entries'][1]['src'], 'Invalid script URLs should be discarded.' );
    ccbo_assert_true( $result['script_gate_entries'][1]['defer'], 'Defer should sanitize to true when checked.' );
    ccbo_assert_same( '#abcdef', $result['palette_popup_background'], 'Valid hex colors should normalize and persist.' );
    ccbo_assert_same( '#f9fafb', $result['palette_popup_text'], 'Invalid hex colors should fall back to defaults.' );
    ccbo_assert_same( '#111111', $result['reopen_button_background'], 'Valid reopen button colors should persist.' );
    ccbo_assert_same( '#222222', $result['reopen_button_text'], 'Valid reopen button text colors should persist.' );
    ccbo_assert_same( '#333333', $result['reopen_button_border'], 'Valid reopen button border colors should persist.' );
    ccbo_assert_same( '#444444', $result['attribution_text'], 'Valid attribution text colors should persist.' );
    ccbo_assert_same( '#2563eb', $result['attribution_link'], 'Invalid attribution link colors should fall back to defaults.' );
    ccbo_assert_same( '.ccbo{color:red;}', $result['custom_css'], 'Custom CSS should be stripped down to raw CSS text.' );
}
