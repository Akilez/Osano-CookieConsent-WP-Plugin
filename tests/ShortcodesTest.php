<?php

function test_shortcodes_register_expected_tags() {
    global $ccbo_test_shortcodes;

    $shortcodes = new CCBO_Cookie_Consent_Shortcodes();
    $shortcodes->register_hooks();

    ccbo_assert_array_has_key( 'ccbo_cookie_consent_reopen', $ccbo_test_shortcodes, 'Reopen shortcode should be registered.' );
    ccbo_assert_array_has_key( 'ccbo_cookie_consent_attribution', $ccbo_test_shortcodes, 'Attribution shortcode should be registered.' );
}

function test_reopen_shortcode_renders_button_trigger() {
    $shortcodes = new CCBO_Cookie_Consent_Shortcodes();
    $html       = $shortcodes->render_reopen_shortcode(
        array(
            'text'  => 'Manage cookies',
            'class' => 'footer-link invalid<script>',
        )
    );

    ccbo_assert_true( false !== strpos( $html, '<button type="button"' ), 'Reopen shortcode should render a button.' );
    ccbo_assert_true( false !== strpos( $html, 'data-ccbo-cookie-consent-reopen="1"' ), 'Reopen button should include the JS trigger attribute.' );
    ccbo_assert_true( false !== strpos( $html, 'Manage cookies' ), 'Reopen button should include custom text.' );
    ccbo_assert_true( false !== strpos( $html, 'footer-link invalidscript' ), 'Reopen button should preserve sanitized custom classes.' );
}

function test_attribution_shortcode_renders_default_credit() {
    $shortcodes = new CCBO_Cookie_Consent_Shortcodes();
    $html       = $shortcodes->render_attribution_shortcode( array() );

    ccbo_assert_true( false !== strpos( $html, 'ccbo-cookie-consent-attribution' ), 'Attribution shortcode should include its base class.' );
    ccbo_assert_true( false !== strpos( $html, 'Cookie consent powered by' ), 'Attribution shortcode should include default text.' );
    ccbo_assert_true( false !== strpos( $html, 'https://github.com/osano/cookieconsent' ), 'Attribution shortcode should link to the upstream project by default.' );
    ccbo_assert_true( false !== strpos( $html, 'Osano CookieConsent' ), 'Attribution shortcode should include default link text.' );
}
