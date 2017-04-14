# wp-requirements
Helper for declaring dependencies in a WordPress plugin.

## Requirements
WordPress, PHP 5.3 or later and Composer.

## Installation
Install using Composer:

```
$ composer require ssnepenthe/wp-requirements
```

## Usage
This package provides a simple method for ensuring that WordPress plugins fail gracefully down to PHP 5.3.

Create a checker instance in your main plugin file (e.g. `my-plugin/my-plugin.php`):

```PHP
use WP_Requirements\Plugin_Checker;

$checker = new Plugin_Checker( 'My Awesome Plugin', __FILE__ );
```

Where the first parameter is the name of your plugin (used for notifications when requirements are not met) and the second parameter is the path to your main plugin file (used to deactivate the plugin).

Then add any number of requirements. All of the following are valid:

```PHP
// Verify that the Debug_Bar class exists - an indirect way of verifying that the Debug_Bar plugin is active.
$checker->class_exists( 'Debug_Bar' );

// Verify that the DOM extension is loaded.
$checker->extension_loaded( 'dom' );

// Verify that the cmb2_bootstrap() function exists - an indirect way of verifying that the CMB2 plugin is active.
$checker->function_exists( 'cmb2_bootstrap' );

// Verify that the server has PHP 5.4 or greater.
$checker->php_at_least( '5.4' );

// Verify that Hello Dolly is active.
// First parameter is plugin path relative to the plugin directory.
// Second parameter is plugin name used for label when requirement is not met.
$checker->plugin_active( 'hello.php', 'Hello Dolly' );

// Verify that the server has WordPress 4.7 or greater.
$checker->wp_at_least( '4.7' );

// Check any arbitrary condition.
// First parameter is a closure that should return true when the requirement is met, false otherwise.
// Second parameter is a message to display when the requirement is not met. Note that it will be prefixed with '{plugin name} deactivated: ' when it is displayed.
$checker->add_check(
    function() {
        return defined( 'SOME_CONSTANT' ) && SOME_CONSTANT;
    },
    'SOME_CONSTANT must be defined and truthy'
);
```

The Plugin_Checker class also provides a fluent interface:

```PHP
use WP_Requirements\Plugin_Checker;

$checker = Plugin_Checker::make( 'My Awesome Plugin', __FILE__ )
    ->function_exists( 'cmb2_bootstrap' )
    ->php_at_least( '5.6' )
    ->wp_at_least( '4.7' );
```

Finally, verify all requirements are met and bootstrap your plugin accordingly.

```PHP
if ( $checker->requirements_met() ) {
    // Whatever logic is required to bootstrap your plugin.
    // This should mostly take place outside of this file to minimize risk of errors when requirements are not met.
    $plugin = new My_Awesome_Plugin_Bootstrap;
    $plugin->init();
} else {
    // This method hooks in to 'admin_notices' to inform the user which requirements weren't met and 'admin_init' to actually deactivate the plugin.
    $checker->deactivate_and_notify();
}
```
