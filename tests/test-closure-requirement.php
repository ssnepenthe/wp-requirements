<?php
/**
 * Class Closure_Requirement_Test
 *
 * @package WP_Plugin_Requirements
 */

namespace WP_Plugin_Requirements_Tests;

use PHPUnit_Framework_TestCase;
use WP_Plugin_Requirements\Closure_Requirement;
use WP_Plugin_Requirements\Requirement_Interface;

class Closure_Requirement_Test extends PHPUnit_Framework_TestCase {
	/** @test */
	function it_is_instantiable() {
		$r = new Closure_Requirement( function() {}, 'message' );

		$this->assertInstanceOf( Requirement_Interface::class, $r );
		$this->assertInstanceOf( Closure_Requirement::class, $r );
	}

	/** @test */
	function it_provides_access_to_message() {
		$r = new Closure_Requirement( function() {}, 'some message' );

		$this->assertEquals( 'some message', $r->get_message() );
	}

	/** @test */
	function it_determines_requirement_status() {
		$r1 = new Closure_Requirement( function() { return true; }, 'message' );
		$r2 = new Closure_Requirement( function() { return false; }, 'message' );

		$this->assertTrue( $r1->is_met() );
		$this->assertFalse( $r2->is_met() );
	}

	/** @test */
	function it_casts_requirement_status_to_bool() {
		$r = new Closure_Requirement( function() { return 'hello'; }, 'message' );

		$this->assertTrue( $r->is_met() );
	}
}
