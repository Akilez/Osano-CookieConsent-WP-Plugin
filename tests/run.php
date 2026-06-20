<?php

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/TestCase.php';

$test_files = glob( __DIR__ . '/*Test.php' );
sort( $test_files );

foreach ( $test_files as $test_file ) {
    require $test_file;
}

$tests = array_filter( get_defined_functions()['user'], function ( $function_name ) {
    return 0 === strpos( $function_name, 'test_' );
} );
sort( $tests );

$failures = array();

foreach ( $tests as $test ) {
    ccbo_reset_test_state();

    try {
        $test();
        echo '[PASS] ' . $test . PHP_EOL;
    } catch ( Throwable $exception ) {
        $failures[] = array(
            'test'    => $test,
            'message' => $exception->getMessage(),
        );
        echo '[FAIL] ' . $test . PHP_EOL;
        echo '       ' . str_replace( PHP_EOL, PHP_EOL . '       ', trim( $exception->getMessage() ) ) . PHP_EOL;
    }
}

echo PHP_EOL;
echo 'Ran ' . count( $tests ) . ' tests.' . PHP_EOL;

if ( $failures ) {
    echo count( $failures ) . ' test(s) failed.' . PHP_EOL;
    exit( 1 );
}

echo 'All tests passed.' . PHP_EOL;
