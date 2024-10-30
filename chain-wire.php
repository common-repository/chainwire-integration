<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://chainwire.org
 * @since             1.0.1
 * @package           Chainwire
 *
 * @wordpress-plugin
 * Plugin Name:       Chainwire Integration
 * Plugin URI:        https://chainwire.org/chainwire-uri/
 * Description:       Integrate Your Blog With Chainwire Platform
 * Version:           1.0.23
 * Author:            chainwire.org
 * Author URI:        https://chainwire.org/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       chainwire
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('CHAIN_WIRE_INTEGRATION_PLUGIN', '1.0.23');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-chainwire-activator.php
 */
function activate_chainwire_plugin()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-chainwire-activator.php';
    ChainwireActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-chainwire-deactivator.php
 */
function deactivate_chainwire_plugin()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-chainwire-deactivator.php';
    ChainwireDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_chainwire_plugin');
register_deactivation_hook(__FILE__, 'deactivate_chainwire_plugin');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-chainwire.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_chainwire_plugin()
{

    $plugin = new Chainwire();
    $plugin->run();

}

run_chainwire_plugin();
