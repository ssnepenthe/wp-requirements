<?php
/**
 * Requirement_Interface interface.
 *
 * @package WP_Plugin_Requirements
 */

namespace WP_Plugin_Requirements;

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
