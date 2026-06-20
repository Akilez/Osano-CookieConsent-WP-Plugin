<?php

function test_activation_adds_default_options_when_missing() {
    ccbo_reset_test_state();

    ccbo_cookie_consent_activate();

    $options = get_option( CCBO_Cookie_Consent_Settings::OPTION_KEY, null );

    ccbo_assert_true( is_array( $options ), 'Activation should create the plugin options array when it is missing.' );
    ccbo_assert_same( 'ipapi', $options['location_service_provider'], 'Activation should seed the default provider.' );
}

function test_activation_merges_existing_options() {
    ccbo_reset_test_state();
    update_option(
        CCBO_Cookie_Consent_Settings::OPTION_KEY,
        array(
            'message' => 'Custom message',
            'enabled' => false,
        )
    );

    ccbo_cookie_consent_activate();

    $options = get_option( CCBO_Cookie_Consent_Settings::OPTION_KEY, array() );

    ccbo_assert_same( 'Custom message', $options['message'], 'Activation should preserve existing saved values.' );
    ccbo_assert_same( false, $options['enabled'], 'Activation should preserve existing booleans.' );
    ccbo_assert_array_has_key( 'theme', $options, 'Activation should backfill newly added default keys.' );
}

function test_uninstall_removes_options() {
    ccbo_reset_test_state();
    update_option( CCBO_Cookie_Consent_Settings::OPTION_KEY, array( 'enabled' => true ) );

    ccbo_cookie_consent_uninstall();

    ccbo_assert_same( 'missing', get_option( CCBO_Cookie_Consent_Settings::OPTION_KEY, 'missing' ), 'Uninstall should delete the plugin option.' );
}
