<?php

function ccbo_fail( $message ) {
    throw new RuntimeException( $message );
}

function ccbo_assert_true( $condition, $message ) {
    if ( ! $condition ) {
        ccbo_fail( $message );
    }
}

function ccbo_assert_false( $condition, $message ) {
    if ( $condition ) {
        ccbo_fail( $message );
    }
}

function ccbo_assert_same( $expected, $actual, $message ) {
    if ( $expected !== $actual ) {
        ccbo_fail( $message . "\nExpected: " . var_export( $expected, true ) . "\nActual: " . var_export( $actual, true ) );
    }
}

function ccbo_assert_array_has_key( $key, $array, $message ) {
    if ( ! is_array( $array ) || ! array_key_exists( $key, $array ) ) {
        ccbo_fail( $message );
    }
}

function ccbo_invoke_private_method( $object, $method, ...$args ) {
    $reflection = new ReflectionObject( $object );
    $method_ref = $reflection->getMethod( $method );
    $method_ref->setAccessible( true );

    return $method_ref->invokeArgs( $object, $args );
}

function ccbo_reset_test_state() {
    global $ccbo_test_options, $ccbo_test_actions, $ccbo_test_filters;

    $ccbo_test_options = array();
    $ccbo_test_actions = array();
    $ccbo_test_filters = array();
}
