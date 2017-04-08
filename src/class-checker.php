<?php
/**
 * Checker class.
 *
 * @package WP_Plugin_Requirements
 */

namespace WP_Plugin_Requirements;

use Closure;

/**
 * Defines the checker class.
 *
 * Should work down to PHP 5.3. I have no intention of matching WordPress support
 * down to 5.2 since we rely on Composer which requires 5.3.2.
 *
 * @todo Add support for soft requirements?
 */
class Checker {
	/**
	 * Path to plugin file - used to deactivate the plugin.
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * Plugin name - used for notifiactions.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Array of plugin requirements.
	 *
	 * @var Requirement_Interface[]
	 */
	protected $requirements = array();

	/**
	 * Class constructor.
	 *
	 * @param string $name Plugin name for notifications.
	 * @param string $file Path to plugin file used to deactivate plugin.
	 */
	public function __construct( $name, $file ) {
		$this->name = (string) $name;
		$this->file = realpath( $file );
	}

	/**
	 * Add an arbitrary closure check.
	 *
	 * @param Closure $callback        Function that checks a requirement.
	 * @param string  $failure_message Failure message if check is not met.
	 *
	 * @return self
	 */
	public function add_check( Closure $callback, $failure_message ) {
		return $this->add_requirement(
			new Closure_Requirement( $callback, $failure_message )
		);
	}

	/**
	 * Add a requirement instance.
	 *
	 * @param Requirement_Interface $requirement Requirement instance.
	 *
	 * @return self
	 */
	public function add_requirement( Requirement_Interface $requirement ) {
		$this->requirements[] = $requirement;

		return $this;
	}

	/**
	 * Add a check for the existence of a specific class.
	 *
	 * @param  string $class Class name to check for.
	 *
	 * @return self
	 */
	public function class_exists( $class ) {
		return $this->add_check(
			function() use ( $class ) {
				return class_exists( $class );
			},
			sprintf( 'The %s class is required but missing', $class )
		);
	}

	/**
	 * Deactivate the plugin associated with this checker instance.
	 */
	public function deactivate() {
		if ( $this->requirements_met() ) {
			return;
		}

		// Prevent display of plugin activated notice.
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		deactivate_plugins( $this->file );
	}

	/**
	 * Hook deactivate and notify methods to WordPress.
	 */
	public function deactivate_and_notify() {
		add_action( 'admin_init', array( $this, 'deactivate' ) );
		add_action( 'admin_notices', array( $this, 'notify' ) );
	}

	/**
	 * Add a check for the existence of a specific function.
	 *
	 * @param  string $function Function to check for.
	 *
	 * @return self
	 */
	public function function_exists( $function ) {
		return $this->add_check(
			function() use ( $function ) {
				return function_exists( $function );
			},
			sprintf( 'The %s function is required but missing', $function )
		);
	}

	/**
	 * Print an admin notice for each failed requirement.
	 */
	public function notify() {
		if ( $this->requirements_met() ) {
			return '';
		}

		echo '<div class="notice notice-error">';

		foreach ( $this->requirements as $requirement ) {
			if ( $requirement->is_met() ) {
				continue;
			}

			printf(
				'<p>%s deactivated: %s</p>',
				esc_html( $this->name ),
				esc_html( $requirement->get_message() )
			);
		}

		echo '</div>';
	}

	/**
	 * Verify that a minimum PHP version is met.
	 *
	 * @param  string $version Version string.
	 *
	 * @return self
	 */
	public function php_at_least( $version ) {
		return $this->add_check(
			function() use ( $version ) {
				return version_compare( phpversion(), $version, '>=' );
			},
			sprintf( 'PHP %s or newer is required', $version )
		);
	}

	/**
	 * Check whether a plugin is active.
	 *
	 * @param  string $plugin_file Plugin file to check relative to plugins dir.
	 * @param  string $plugin_name Plugin name for notifications.
	 *
	 * @return self
	 */
	public function plugin_active( $plugin_file, $plugin_name ) {
		return $this->add_check(
			function() use ( $plugin_file ) {
				return in_array(
					$plugin_file,
					(array) get_option( 'active_plugins' ),
					true
				);
			},
			sprintf( '%s must be installed and active', $plugin_name )
		);
	}

	/**
	 * Detemine if all requirements are met.
	 *
	 * @return bool
	 */
	public function requirements_met() {
		$met = true;

		foreach ( $this->requirements as $requirement ) {
			$met = $met && $requirement->is_met();
		}

		return $met;
	}

	/**
	 * Verify that a minimum WP version is met.
	 *
	 * @param  string $version Version string.
	 *
	 * @return self
	 */
	public function wp_at_least( $version ) {
		return $this->add_check(
			function() use ( $version ) {
				return version_compare( get_bloginfo( 'version' ), $version, '>=' );
			},
			sprintf( 'WordPress %s or newer is required', $version )
		);
	}

	/**
	 * Static constructor for chaining purposes.
	 *
	 * @param  string $name Plugin name for notifications.
	 * @param  string $file Path to main plugin file for deactivating plugin.
	 *
	 * @return static
	 */
	public static function make( $name, $file ) {
		return new static( $name, $file );
	}
}
