<?php
/**
 * Class SampleTest
 *
 * @package Wp_Plugin_Requirements
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {
	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

	/**
	 * Not particularly robust but it should do the trick...
	 */
	protected function manipulate_version( $version, $operation = 'simplify' ) {
		$operations = [ 'incr', 'decr', 'simplify' ];

		if ( ! in_array( $operation, $operations, true ) ) {
			$operation = 'simplify';
		}

		// Explode on "." and get the first three parts cast to int.
		$bits = array_map( 'intval', array_slice( explode( '.', $version ), 0, 3 ) );

		if ( 3 === count( $bits ) ) {
			$key = 2;
		} else {
			$key = 1;
		}

		switch ( $operation ) {
			case 'incr':
				$bits[ $key ] += 1;
				break;
			case 'decr':
				$bits[ $key ] -= 1;
				break;
		}

		return implode( '.', $bits );
	}

	/**
	 * Input of "4.8-alpha-39357-src" or "4.8" returns "4.9".
	 * Input of "7.0.15-1+deb.sury.org~trusty+1" or "7.0.15" returns "7.0.16".
	 */
	protected function incr_version( $version ) {
		return $this->manipulate_version( $version, 'incr' );
	}

	/**
	 * Input of "4.8-alpha-39357-src" or "4.8" returns "4.7".
	 * Input of "7.0.15-1+deb.sury.org~trusty+1" or "7.0.15" returns "7.0.14".
	 */
	protected function decr_version( $version ) {
		return $this->manipulate_version( $version, 'decr' );
	}

	/**
	 * Input of "4.8-alpha-39357-src" returns "4.8".
	 * Input of "7.0.15-1+deb.sury.org~trusty+1" returns "7.0.15".
	 */
	protected function simplify_version( $version ) {
		return $this->manipulate_version( $version );
	}
}
