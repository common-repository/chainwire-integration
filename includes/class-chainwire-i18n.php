<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://chainwire.org
 * @since      1.0.0
 *
 * @package    Chainwire
 * @subpackage Chainwire/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Chainwire
 * @subpackage Chainwire/includes
 * @author     Your Name <konrad@cracsoft.com>
 */
class ChainwireI18n
{


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {

        load_plugin_textdomain(
                'chainwire',
                false,
                dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );

    }



}
