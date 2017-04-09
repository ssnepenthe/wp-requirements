<?php
/**
 * Requirement_Interface interface.
 *
 * @package WP_Requirements
 */

namespace WP_Requirements;

/**
 * Defines a requirement interface.
 */
interface Requirement_Interface {
	/**
	 * Get the failure message for this requirement.
	 *
	 * @return string
	 */
	public function get_message();

	/**
	 * Determine if the requirement is met.
	 *
	 * @return boolean
	 */
	public function is_met();
}
