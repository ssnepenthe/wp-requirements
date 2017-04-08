<?php
/**
 * Class Checker_Test
 *
 * @package WP_Plugin_Requirements
 */

namespace WP_Plugin_Requirements_Tests;

use WP_UnitTestCase;
use WP_Plugin_Requirements\Checker;
use WP_Plugin_Requirements\Closure_Requirement;

class Checker_Test extends WP_UnitTestCase {
	/** @test */
	function it_is_instantiable() {
		$c = new Checker( '', '' );

		$this->assertInstanceOf( 'WP_Plugin_Requirements\\Checker', $c );
	}

	/** @test */
	function it_accepts_arbitrary_checks() {
		$c = new Checker( '', '' );
		$c->add_check( function() { return true; }, '' );

		$this->assertTrue( $c->requirements_met() );
	}

	/** @test */
	function it_accepts_arbitrary_requirements() {
		$c = new Checker( '', '' );
		$c->add_requirement(
			new Closure_Requirement( function() { return true; }, '' )
		);

		$this->assertTrue( $c->requirements_met() );
	}

	/** @test */
	function it_provides_a_fluent_interface_for_adding_checks() {
		$c = new Checker( '', '' );

		$this->assertInstanceOf(
			'WP_Plugin_Requirements\\Checker',
			$c->add_check( function() { return true; }, '' )
		);
		$this->assertInstanceOf(
			'WP_Plugin_Requirements\\Checker',
			$c->add_requirement(
				new Closure_Requirement( function() { return true; }, '' )
			)
		);
		$this->assertInstanceOf(
			'WP_Plugin_Requirements\\Checker',
			Checker::make( '', '' )->add_check( function() { return true; }, '' )
		);
	}

	/** @test */
	function it_can_check_class_existence() {
		$c1 = new Checker( 'Some Plugin', '' );
		$c1->class_exists( 'DateTime' );

		$c2 = new Checker( 'Some Plugin', '' );
		$c2->class_exists( 'NotReal' );

		$this->assertTrue( $c1->requirements_met() );
		$this->assertEquals( '', $this->capture_admin_notice( $c1 ) );

		$this->assertFalse( $c2->requirements_met() );
		$this->assertEquals(
			'<div class="notice notice-error"><p>Some Plugin deactivated: The NotReal class is required but missing</p></div>',
			$this->capture_admin_notice( $c2 )
		);
	}

	/** @test */
	function it_can_deactivate_plugin() {
		// @todo !!!
	}

	/** @test */
	function it_can_check_function_existence() {
		$c1 = new Checker( 'Some Plugin', '' );
		$c1->function_exists( 'phpversion' );

		$c2 = new Checker( 'Some Plugin', '' );
		$c2->function_exists( 'not_real' );

		$this->assertTrue( $c1->requirements_met() );
		$this->assertEquals( '', $this->capture_admin_notice( $c1 ) );

		$this->assertFalse( $c2->requirements_met() );
		$this->assertEquals(
			'<div class="notice notice-error"><p>Some Plugin deactivated: The not_real function is required but missing</p></div>',
			$this->capture_admin_notice( $c2 )
		);
	}

	/** @test */
	function it_can_check_php_version() {
		$c1 = new Checker( 'Some Plugin', '' );
		$c1_version = $this->decr_version( phpversion() );
		$c1->php_at_least( $c1_version );

		$c2 = new Checker( 'Some Plugin', '' );
		$c2_version = $this->incr_version( phpversion() );
		$c2->php_at_least( $c2_version );

		$this->assertTrue( $c1->requirements_met() );
		$this->assertEquals( '', $this->capture_admin_notice( $c1 ) );

		$this->assertFalse( $c2->requirements_met() );
		$this->assertEquals(
			sprintf(
				'<div class="notice notice-error"><p>Some Plugin deactivated: PHP %s or newer is required</p></div>',
				$c2_version
			),
			$this->capture_admin_notice( $c2 )
		);
	}

	/** @test */
	function it_can_check_active_plugins() {
		// @todo !!!
	}

	/** @test */
	function it_can_check_wp_version() {
		$c1 = new Checker( 'Some Plugin', '' );
		$c1_version = $this->decr_version( get_bloginfo( 'version' ) );
		$c1->wp_at_least( $c1_version );

		$c2 = new Checker( 'Some Plugin', '' );
		$c2_version = $this->incr_version( get_bloginfo( 'version' ) );
		$c2->wp_at_least( $c2_version );

		$this->assertTrue( $c1->requirements_met() );
		$this->assertEquals( '', $this->capture_admin_notice( $c1 ) );

		$this->assertFalse( $c2->requirements_met() );
		$this->assertEquals(
			sprintf(
				'<div class="notice notice-error"><p>Some Plugin deactivated: WordPress %s or newer is required</p></div>',
				$c2_version
			),
			$this->capture_admin_notice( $c2 )
		);
	}

	protected function capture_admin_notice( $checker ) {
		ob_start();

		$checker->notify();

		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	/**
	 * Not particularly robust but it should do the trick...
	 */
	protected function manipulate_version( $version, $operation = 'incr' ) {
		$operations = array( 'incr', 'decr' );

		if ( ! in_array( $operation, $operations, true ) ) {
			$operation = 'incr';
		}

		// Explode on "." and get the first three parts cast to int.
		$bits = array_map( 'intval', array_slice( explode( '.', $version ), 0, 3 ) );

		for ( $i = count( $bits ) - 1; $i >= 0; $i-- ) {
			if ( $operation === 'incr' ) {
				$bits[ $i ] += 1;
				break;
			}

			if ( 0 === $bits[ $i ] ) {
				continue;
			}

			$bits[ $i ] -= 1;
			break;
		}

		return implode( '.', $bits );
	}

	/**
	 * Input of "4.8-alpha-39357-src" or "4.8" returns "4.9".
	 * Input of "7.0.15-1+deb.sury.org~trusty+1" or "7.0.15" returns "7.0.16".
	 */
	protected function incr_version( $version ) {
		return $this->manipulate_version( $version );
	}

	/**
	 * Input of "4.8-alpha-39357-src" or "4.8" returns "4.7".
	 * Input of "7.0.15-1+deb.sury.org~trusty+1" or "7.0.15" returns "7.0.14".
	 */
	protected function decr_version( $version ) {
		return $this->manipulate_version( $version, 'decr' );
	}
}
