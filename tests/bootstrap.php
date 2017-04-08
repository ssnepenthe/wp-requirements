<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Wp_Plugin_Requirements
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _require_once_if_exists( $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

function _manually_load_plugin() {
	_require_once_if_exists( dirname( __DIR__ ) . '/vendor/autoload.php' );
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
