<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://chainwire.org
 * @since      1.0.0
 *
 * @package    Chainwire
 * @subpackage Chainwire/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Chainwire
 * @subpackage Chainwire/admin
 * @author     Konrad Seweryn <konrad@cracsoft.com>
 */
class ChainwireAdmin
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $plugin_id The ID of this plugin.
     */
    private $plugin_id;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_id The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_id, $version)
    {

        $this->plugin_id = $plugin_id;
        $this->version = $version;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in ChainwireLoader as all of the hooks are defined
         * in that particular class.
         *
         * The ChainwireLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_id, plugin_dir_url(__FILE__) . 'css/chainwire-admin.css', array(), $this->version, 'all');
        wp_enqueue_style('select2.min.css', plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in ChainwireLoader as all of the hooks are defined
         * in that particular class.
         *
         * The ChainwireLoader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_script('select2.min.js', plugin_dir_url(__FILE__) . 'js/select2.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script($this->plugin_id, plugin_dir_url(__FILE__) . 'js/chainwire-admin.js', array('select2.min.js'), $this->version, false);

    }

    /**
     * Register the administration menu for this plugin into the WordPress Dashboard menu.
     *
     * @since    1.0.0
     */

    public function add_plugin_admin_menu()
    {

        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         *        Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
        add_options_page('Chainwire', 'Chainwire', 'manage_options', $this->plugin_id, array($this, 'display_plugin_setup_page')
        );
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */

    public function add_action_links($links)
    {
        /*
        *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
        */
        $settings_link = array(
                '<a href="' . admin_url('options-general.php?page=' . $this->plugin_id) . '">' . __('Settings', $this->plugin_id) . '</a>',
        );
        return array_merge($settings_link, $links);

    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */

    public function display_plugin_setup_page()
    {
        include_once('partials/chainwire-admin-display.php');
    }

    protected function get_safe_input_field($input, $field, $default_value = null)
    {
        return sanitize_text_field(isset($input[$field]) ? $input[$field] : $default_value);
    }

    public function validate($input)
    {
        // All checkboxes inputs
        $valid = array();

        //Cleanup
        $valid['token'] = $this->get_safe_input_field($input, 'token');
        $valid['secret'] = $this->get_safe_input_field($input, 'secret');
        $valid['polylang_post_language'] = $this->get_safe_input_field($input, 'polylang_post_language');
        $valid['use_client_image_for_featured_image'] = $this->get_safe_input_field($input, 'use_client_image_for_featured_image', false);
        $valid['add_feature_image_to_post'] = $this->get_safe_input_field($input, 'add_feature_image_to_post', false);
        $valid['add_tags_to_post'] = $this->get_safe_input_field($input, 'add_tags_to_post', false);
        $valid['fill_yoast_seo_tags'] = $this->get_safe_input_field($input, 'fill_yoast_seo_tags', false);
        $valid['post_status'] = $this->get_safe_input_field($input, 'post_status', 'publish');

        $plugin = new ChainwireCommon($this->plugin_id);
        $wires_options = $plugin->get_admin_wire_options();

        foreach ($wires_options as $wire_option) {
            $field = $wire_option['option_category']['field_name'];
            $valid[$field] = htmlspecialchars($this->get_safe_input_field($input, $field));

            $field = $wire_option['option_additional_categories']['field_name'];
            $valid[$field] = $this->get_safe_input_field($input, $field);
        }

        return $valid;
    }


    public function options_update()
    {
        register_setting($this->plugin_id, $this->plugin_id, array($this, 'validate'));
    }

}
