<?php
/**
 * Closure_Requirement class.
 *
 * @package WP_Requirements
 */

namespace WP_Requirements;

use Closure;

/**
 * Defines the closure requirement class.
 */
class Closure_Requirement implements Requirement_Interface {
	/**
	 * Closure used to check requirement.
	 *
	 * @var Closure
	 */
	protected $closure;

	/**
	 * Failure message.
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Class constructor.
	 *
	 * @param Closure $closure         Function used to check requirement.
	 * @param string  $failure_message Message to display if requirement is not met.
	 */
	public function __construct( Closure $closure, $failure_message ) {
		$this->closure = $closure;
		$this->message = (string) $failure_message;
	}

	/**
	 * Get the failure message.
	 *
	 * @return string
	 */
	public function get_message() {
		return $this->message;
	}

	/**
	 * Determine if this requirement is met.
	 *
	 * @return boolean
	 */
	public function is_met() {
		return (bool) call_user_func( $this->closure );
	}
}
