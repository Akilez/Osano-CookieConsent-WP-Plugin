<?php

define( 'ABSPATH', __DIR__ . DIRECTORY_SEPARATOR );
define( 'CCBO_DISABLE_UPDATER', true );

global $ccbo_test_options, $ccbo_test_actions, $ccbo_test_filters, $ccbo_test_hooks;
$ccbo_test_options = array();
$ccbo_test_actions = array();
$ccbo_test_filters = array();
$ccbo_test_hooks   = array(
    'activation'   => array(),
    'deactivation' => array(),
    'uninstall'    => array(),
);

function __( $text, $domain = null ) {
    return $text;
}

function esc_html__( $text, $domain = null ) {
    return $text;
}

function esc_attr__( $text, $domain = null ) {
    return $text;
}

function esc_html( $text ) {
    return (string) $text;
}

function esc_attr( $text ) {
    return (string) $text;
}

function esc_url( $url ) {
    return esc_url_raw( $url );
}

function esc_js( $text ) {
    return addslashes( (string) $text );
}

function esc_textarea( $text ) {
    return (string) $text;
}

function sanitize_text_field( $text ) {
    $text = strip_tags( (string) $text );
    return trim( preg_replace( '/\s+/', ' ', $text ) );
}

function sanitize_textarea_field( $text ) {
    $text = strip_tags( (string) $text );
    $text = str_replace( array( "\r\n", "\r" ), "\n", $text );
    return trim( preg_replace( "/\n{3,}/", "\n\n", $text ) );
}

function sanitize_key( $key ) {
    $key = strtolower( (string) $key );
    return preg_replace( '/[^a-z0-9_\-]/', '', $key );
}

function sanitize_hex_color( $color ) {
    $color = trim( (string) $color );

    if ( '' === $color ) {
        return '';
    }

    return preg_match( '/^#(?:[0-9a-fA-F]{3}){1,2}$/', $color ) ? strtolower( $color ) : null;
}

function wp_strip_all_tags( $text ) {
    return strip_tags( (string) $text );
}

function esc_url_raw( $url ) {
    $url = trim( (string) $url );

    if ( '' === $url ) {
        return '';
    }

    if ( '/' === substr( $url, 0, 1 ) ) {
        return $url;
    }

    return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : '';
}

function absint( $value ) {
    return abs( (int) $value );
}

function apply_filters( $tag, $value ) {
    global $ccbo_test_filters;

    if ( empty( $ccbo_test_filters[ $tag ] ) ) {
        return $value;
    }

    foreach ( $ccbo_test_filters[ $tag ] as $callback ) {
        $value = $callback( $value );
    }

    return $value;
}

function do_action( $tag ) {
    global $ccbo_test_actions;
    $ccbo_test_actions[] = $tag;
}

function add_action( $tag, $callback ) {
    global $ccbo_test_actions;
    $ccbo_test_actions[] = array( $tag, $callback );
}

function add_filter( $tag, $callback ) {
    global $ccbo_test_filters;

    if ( ! isset( $ccbo_test_filters[ $tag ] ) ) {
        $ccbo_test_filters[ $tag ] = array();
    }

    $ccbo_test_filters[ $tag ][] = $callback;
}

function add_shortcode( $tag, $callback ) {
    global $ccbo_test_shortcodes;

    if ( ! isset( $ccbo_test_shortcodes ) ) {
        $ccbo_test_shortcodes = array();
    }

    $ccbo_test_shortcodes[ $tag ] = $callback;
}

function register_setting() {}
function add_settings_section() {}
function add_settings_field() {}
function add_options_page() {}
function wp_enqueue_style() {}
function wp_enqueue_script() {}
function wp_register_style() {}
function wp_register_script() {}
function wp_localize_script() {}
function wp_add_inline_style() {}
function settings_fields() {}
function do_settings_fields() {}
function submit_button() {}
function checked() {}
function selected() {}
function current_user_can() { return true; }
function is_ssl() { return false; }

function plugin_dir_path( $file ) {
    return rtrim( dirname( $file ), '\\/' ) . DIRECTORY_SEPARATOR;
}

function plugin_dir_url( $file ) {
    return 'http://example.test/plugin/';
}

function register_activation_hook( $file, $callback ) {
    global $ccbo_test_hooks;
    $ccbo_test_hooks['activation'][] = $callback;
}

function register_deactivation_hook( $file, $callback ) {
    global $ccbo_test_hooks;
    $ccbo_test_hooks['deactivation'][] = $callback;
}

function register_uninstall_hook( $file, $callback ) {
    global $ccbo_test_hooks;
    $ccbo_test_hooks['uninstall'][] = $callback;
}

function get_option( $key, $default = false ) {
    global $ccbo_test_options;
    return array_key_exists( $key, $ccbo_test_options ) ? $ccbo_test_options[ $key ] : $default;
}

function add_option( $key, $value ) {
    global $ccbo_test_options;
    $ccbo_test_options[ $key ] = $value;
    return true;
}

function update_option( $key, $value ) {
    global $ccbo_test_options;
    $ccbo_test_options[ $key ] = $value;
    return true;
}

function delete_option( $key ) {
    global $ccbo_test_options;
    unset( $ccbo_test_options[ $key ] );
    return true;
}

require_once dirname( __DIR__ ) . '/cookie-consent-by-osano.php';
