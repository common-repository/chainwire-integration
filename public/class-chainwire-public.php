<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://chainwire.org
 * @since      1.0.0
 *
 * @package    Chainwire
 * @subpackage Chainwire/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Chainwire
 * @subpackage Chainwire/public
 * @author     Konrad Seweryn <konrad@cracsoft.com>
 */
class ChainwirePublic
{

    const StatusError = 'ERROR';
    const StatusSuccess = 'SUCCESS';
    const PressReleaseBranded = 0;
    const PressReleaseUnbranded = 1;
    const PostDuplicatedExpirationTime = 259200; // 3 days
    const DateTimeFormat = 'Y-m-d H:i:s';
    const WpPostStatusDraft = 'draft';

    /**
     * Enable iframes with videos of post
     *
     * @param $allowedposttags
     * @return mixed
     */
    public function esw_author_cap_filter($allowedposttags)
    {
        $allowedposttags['iframe'] = array(
            'align' => true,
            'width' => true,
            'height' => true,
            'frameborder' => true,
            'name' => true,
            'src' => true,
            'id' => true,
            'class' => true,
            'style' => true,
            'scrolling' => true,
            'marginwidth' => true,
            'marginheight' => true,
            'allowfullscreen' => true,
            'mozallowfullscreen' => true,
            'webkitallowfullscreen' => true,
        );
        return $allowedposttags;
    }

    private $ssl_verify = true;
    private $verify_post = true;

    private $unbranded_author_username = 'PressRelease';
    private $unbranded_author_email = null;
    private $unbranded_author_first_name = '';
    private $unbranded_author_url = null;
    private $unbranded_author_avatar = '/wp-plugin/unbranded_author_avatar.png';

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

    private $common;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_id The name of the plugin.
     * @param string $version The version of this plugin.
     * @since    1.0.0
     */
    public function __construct($plugin_id, $version)
    {

        $this->plugin_id = $plugin_id;
        $this->version = $version;
        $this->common = new ChainwireCommon($plugin_id);

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
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

        wp_enqueue_style($this->plugin_id, plugin_dir_url(__FILE__) . 'css/chainwire-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
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

//        wp_enqueue_script($this->plugin_id, plugin_dir_url(__FILE__) . 'js/chainwire-public.js', array('jquery'), $this->version, false);

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function custom_rest_api_init()
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

        register_rest_route('chain-wire-plugin/v1', '/posts', array(
            'methods' => 'POST',
            'callback' => array($this, 'add_new_post'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('chain-wire-plugin/v1', '/verification', array(
            'methods' => 'POST',
            'callback' => array($this, 'verify_installation'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('chain-wire-plugin/v1', '/plugin-details', array(
            'methods' => 'POST',
            'callback' => array($this, 'check_plugin_details'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('chain-wire-plugin/v1', '/post-details', array(
            'methods' => 'POST',
            'callback' => array($this, 'check_post_details'),
            'permission_callback' => '__return_true'
        ));

    }

    /**
     * @param $wire
     * @return mixed
     */
    protected function verify_wire($wire)
    {
        $data = $this->common->get_credentials_for_wire($wire);
        return $data->wire;
    }

    /**
     * @param $domain
     * @param $plugin_token
     * @param $plugin_secret
     * @param $article_id
     * @param $press_release_html
     * @param null $verification_token
     * @return bool
     */
    protected function verify_article($domain, $plugin_token, $plugin_secret, $article_id, $press_release_html, $verification_token = null)
    {
        $basic_content = preg_replace("/[^A-Za-z0-9]/", '', $press_release_html);
        $content_hash = md5($basic_content);
        $response = $this->send_post_request($domain, [
            'form' => [
                'article_id' => $article_id,
                'press_release_html_hash' => $content_hash,
                'verification_token' => $verification_token
            ]
        ]);
        if (!is_wp_error($response)) {
            if ($response['response']['code'] == 200) {
                $data = json_decode($response['body']);
                if ($data->data->hash === crc32($content_hash . $plugin_token . $plugin_secret)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $url
     * @return bool|mixed
     */
    protected function download_file($url)
    {
        if (!class_exists('WP_Http')) {
            include_once(ABSPATH . WPINC . '/class-http.php');
        }
        $http = new WP_Http();
        $response = $http->request($url);
        if (is_wp_error($response) || $response['response']['code'] !== 200) {
            return false;
        }
        $upload = wp_upload_bits(basename($url), null, $response['body']);
        if (!empty($upload['error'])) {
            return false;
        }
        return $upload;
    }

    /**
     * @param $domain
     * @param $url
     * @return array
     */
    protected function get_fixed_url($domain, $url)
    {
        $src = $url;
        $attachment_id = null;
        if (!$this->is_absolute($url)) {
            if ($url && is_string($url)) {
                if ($url[0] !== '/') {
                    $url = '/' . $url;
                }
            }
            $src = $domain . $url;
        }
        $upload = $this->download_file($src);
        if ($upload && $upload['url']) {
            $src = $upload['url'];
        }
        if ($upload && $upload['file']) {
            $attachment_id = $this->set_attachment($upload['file']);
        }
        return [
            'url' => $src,
            'attachment_id' => $attachment_id
        ];
    }

    /**
     * @param $file
     * @param null $parent_post_id
     * @return int|WP_Error
     */
    protected function set_attachment($file, $parent_post_id = null)
    {
        $filename = basename($file);
        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_parent' => $parent_post_id,
            'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attachment_id = wp_insert_attachment($attachment, $file, $parent_post_id);
        if (!is_wp_error($attachment_id)) {
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $file);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
        }
        return $attachment_id;
    }

    /**
     * @param $url
     * @return false|int
     */
    protected static function is_absolute($url)
    {
        return preg_match('/^https?:\/\/(.*)/', $url);
    }

    /**
     * @param null $press_release_kind
     * @param bool $update_avatar
     * @return false|int|WP_Error
     * @throws Exception
     */
    protected function get_author_id($wire, $press_release_kind = null, $update_avatar = false)
    {
        $unbranded = $press_release_kind === self::PressReleaseUnbranded;
        $user_email = null;
        $avatar = null;
        if ($unbranded) {
            $username = $this->unbranded_author_username;
            $user_id = username_exists($username);
            $credentials = $this->common->get_credentials_for_wire();
            $domain = $credentials->domain;
        } else {
            $credentials = $this->common->get_credentials_for_wire($wire);
            $username = $credentials->username;
            $user_email = $credentials->email;
            $avatar = $credentials->avatar;
            $domain = $credentials->domain;
            $user_id = username_exists($username);
        }

        if (!$user_id) {
            if ($unbranded) {
                $user_email = $this->unbranded_author_email;
            }
            if ($user_email && email_exists($user_email)) {
                throw new Exception('User already exists.');
            }
            $random_password = wp_generate_password(20);
            $user_id = wp_create_user($username, $random_password, $user_email);

            if ($user_id <= 0) {
                throw new Exception("User wasn't created");
            }

            $user = new WP_User($user_id);
            $user->set_role('author');

            $arr = $this->common->get_user_data($username);
            $arr['ID'] = $user_id;

            if ($unbranded) {
                $arr['first_name'] = $this->unbranded_author_first_name;
                $arr['user_url'] = $this->unbranded_author_url;
            }

            foreach ($arr as $k => $v) {
                if ($v === null) {
                    delete_user_meta($user_id, $k);
                }
            }
            wp_update_user($arr);
            $update_avatar = true;
        }

        if ($update_avatar) {
            if ($unbranded) {
                $avatar = $this->unbranded_author_avatar;
            }
            if ($avatar) {
                $this->set_avatar_for_user($domain . $avatar, $user_id);
            }
        }

        return $user_id;
    }

    /**
     * @param $avatar_url
     * @param $user_id
     */
    protected function set_avatar_for_user($avatar_url, $user_id)
    {
        global $wpdb;
        $upload = $this->download_file($avatar_url);
        if ($upload['file']) {
            $attachment_id = $this->set_attachment($upload['file']);
            if ($attachment_id) {
                update_user_meta($user_id, $wpdb->get_blog_prefix() . 'user_avatar', $attachment_id);
            }
        }
    }

    /**
     * @param $categories
     * @return array
     */
    protected function get_categories($categories, $create_parent = false)
    {
        if (!$categories || !is_array($categories)) {
            $categories = [];
        }

        $connected = [];

        if ($create_parent) {
            $parent_category_name = 'Chainwire';
            $parent_category_id = get_cat_ID($parent_category_name);

            if (!$parent_category_id) {
                $parent = wp_insert_term($parent_category_name, 'category');
                if (!is_wp_error($parent)) {
                    $parent_category_id = $parent['term_id'];
                }
            }

            if ($parent_category_id) {
                foreach ($categories as $category) {
                    $c = get_cat_ID($category);
                    if (!$c) {
                        $c = wp_insert_term($category, 'category', ['parent' => $parent_category_id]);
                        if (!is_wp_error($c)) {
                            $connected[] = $c['term_id'];
                        }
                    } else {
                        $connected[] = $c;
                    }
                }
                if (count($categories) === 0) {
                    $connected = [$parent_category_id];
                }
            }
        } else {
            foreach ($categories as $category) {
                $c = get_cat_ID($category);
                if (!$c) {
                    $c = wp_insert_term($category, 'category');
                    if (!is_wp_error($c)) {
                        $connected[] = $c['term_id'];
                    }
                } else {
                    $connected[] = $c;
                }
            }
        }
        return $connected;
    }

    protected function add_iframe($initArray)
    {
        $initArray['extended_valid_elements'] = "iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]";
        return $initArray;
    }

    protected function verify_image_url($url)
    {
        if (!$url) {
            return null;
        }
        $supported_images = array(
            'gif',
            'jpg',
            'jpeg',
            'png'
        );
        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION)); // Using strtolower to overcome case sensitive
        return in_array($ext, $supported_images) ? $url : null;
    }


    /**
     * @param $domain
     * @param $html
     * @param array $options
     * @return array
     */
    protected function regenerate_html($domain, $html, $options = [])
    {
        $add_featured_image_to_post = $options['add_featured_image_to_post'];
        $use_client_image_for_featured_image = $options['use_client_image_for_featured_image'];
        $press_release_client_image = $options['press_release_client_image'];
        $press_release_featured_image = $options['press_release_featured_image'];
        $uploaded_featured_image = null;
        $uploaded_client_image = null;

        $press_release_client_image = $this->verify_image_url($press_release_client_image);
        $press_release_featured_image = $this->verify_image_url($press_release_featured_image);

        if ($use_client_image_for_featured_image && $press_release_client_image) {
            $uploaded_client_image = $this->get_fixed_url($domain, $press_release_client_image);
        }

        if ($press_release_featured_image) {
            $uploaded_featured_image = $this->get_fixed_url($domain, $press_release_featured_image);
        }

        $featured_image = $uploaded_client_image ? $uploaded_client_image : $uploaded_featured_image;

        $doc = new DOMDocument();
        if ($add_featured_image_to_post && $featured_image) {
            $src = $featured_image['url'];
            $attachment_id = $featured_image['attachment_id'];
            $src_set = null;
            if ($src) {
                if ($attachment_id) {
                    $src_set = wp_get_attachment_image_srcset($attachment_id);
                }
                $src_set_attribute = $src_set ? ' srcset="' . $src_set . '"' : '';
                $html = '<p><img alt="" src="' . $src . '"' . $src_set_attribute . '></p>' . $html;
            }
        }

        $doc->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $html);
        $tags = $doc->getElementsByTagName('img');

        /** @var DOMNode $tag */
        foreach ($tags as $tag) {
            $img = $tag->getAttribute('src');
            $fixed_url = $this->get_fixed_url($domain, $img);
            $src = $fixed_url['url'];
            $attachment_id = $fixed_url['attachment_id'];
            if ($src) {
                if ($attachment_id) {
                    $src_set = wp_get_attachment_image_srcset($attachment_id);
                    if ($src_set) {
                        $tag->setAttribute('src_set', $src_set);
                    }
                    $src_large = wp_get_attachment_image_url($attachment_id, 'large');
                    $tag->setAttribute('src', $src_large);
                }
            }
        }

        return [
            'html' => $doc->saveHTML(),
            'featured_image' => $featured_image
        ];
    }

    /**
     * @param $domain
     * @param $data
     * @return array|WP_Error
     */
    protected function send_post_request($domain, $data)
    {
        $url = $domain . '/api/wp-plugin/verify-article';
        $response = wp_remote_post($url, array(
                'method' => 'POST',
                'timeout' => 15,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(),
                'body' => $data,
                'sslverify' => $this->ssl_verify,
                'cookies' => array()
            )
        );
        return $response;
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function get_current_date()
    {
        return current_datetime()->format(self::DateTimeFormat);
    }

    /**
     * @return string
     * @throws Exception
     */
    protected function get_fixed_date()
    {
        $date = $this->get_safe_post_field('press_release_date');
        if ($date === "now" || !$date) {
            return $this->get_current_date();
        } else {
            $date = get_date_from_gmt($date);
        }
        return $date;
    }

    public function add_new_post()
    {
        return $this->publish_new_post([
            'post_status' => null
        ]);
    }

    /**
     * @param $field
     * @param null $default_value
     * @return mixed
     */
    protected function get_safe_html_field($field, $default_value = null)
    {
        $field = isset($_POST[$field]) ? $_POST[$field] : $default_value;
        if ($field) {
            return wp_kses($field, array(
                'br' => array(),
                'strong' => array(),
                'bold' => array(),
                'b' => array(),
                'i' => array(),
                'ol' => array(),
                'ul' => array(),
                'li' => array(),
                'a' => array(
                    'href' => array(),
                    'title' => array(),
                    'target' => array(),
                    'rel' => array(),
                ),
                'img' => array(
                    'src' => array(),
                    'alt' => array(),
                ),
                'video' => array(),
                'h1' => array(),
                'h2' => array(),
                'h3' => array(),
                'h4' => array(),
                'h5' => array(),
                'h6' => array(),
                'p' => array(
                    'class' => array(),
                ),
                'blockquote' => array(),
                'u' => array(),
                'del' => array(),
                'span' => array(),
                'iframe' => array(
                    'src' => array()
                ),
            ));
        }
        return $field;
    }

    /**
     * @param $field
     * @param null $default_value
     * @return mixed
     */
    protected function get_safe_post_field($field, $default_value = null)
    {
        $value = isset($_POST[$field]) ? $_POST[$field] : $default_value;
        if (is_string($value)) {
            return sanitize_text_field($value);
        } elseif (is_array($value)) {
            return self::sanitize_array_field($value);
        }
        return sanitize_text_field($value);
    }

    /**
     * Recursive sanitation for an array
     *
     * @param $array
     *
     * @return mixed
     */
    protected function sanitize_array_field($array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                $value = self::sanitize_array_field($value);
            } else {
                $value = sanitize_text_field($value);
            }
        }
        return $array;
    }

    /**
     * @param $field
     * @param null $default_value
     * @return mixed
     */
    protected function get_safe_option($options, $field, $default_value = null)
    {
        return sanitize_text_field(isset($options[$field]) ? $options[$field] : $default_value);
    }

    /**
     * @param $wire
     * @param $options
     * @param $field
     * @param null $default_value
     * @return mixed
     */
    protected function get_safe_wire_option($wire, $options, $field, $default_value = null)
    {
        $field = $this->common->get_wire_field($wire, $field);
        return $this->get_safe_option($options, $field, $default_value);
    }

    /**
     * Check post status
     *
     * @return array
     */
    public function check_post_details()
    {
        $token = $this->get_safe_post_field('token');
        if ($token) {
            $options = get_option($this->plugin_id);
            $plugin_token = $this->get_safe_option($options, 'token');
            if ($plugin_token && $plugin_token === $token) {
                $post_id = $this->get_safe_post_field('post_id');
                if ($post_id) {
                    $current_post = get_post($post_id);
                }
                if (!$current_post) {
                    $title = $this->get_safe_post_field('press_release_title');
                    $current_post = $this->find_post_by_title_full($title);
                }
                if ($current_post) {
                    $current_post_id = $current_post->ID;
                    return $this->get_post_response_success($current_post_id);
                }
                return [
                    'status' => self::StatusError,
                    'errors' => [
                        'form' => 'Post does not exist'
                    ]
                ];
            }
        }
        return [
            'status' => self::StatusError,
            'errors' => [
                'form' => 'Wrong Data - token not set'
            ]
        ];
    }

    /**
     * @param $post_id
     * @return array
     */
    protected function get_post_response_success($post_id)
    {
        return [
            'status' => self::StatusSuccess,
            'data' => [
                'post_id' => $post_id,
                'post_status' => get_post_status($post_id),
                'article_url' => get_permalink($post_id),
                'published_at' => get_post_datetime($post_id)->format(self::DateTimeFormat),
            ]
        ];
    }

    /**
     * Check plugin details
     *
     * @return array
     * @throws Exception
     */
    public function check_plugin_details()
    {
        $token = $this->get_safe_post_field('token');
        if ($token) {
            $options = get_option($this->plugin_id);
            $plugin_token = $this->get_safe_option($options, 'token');
            if ($plugin_token && $plugin_token === $token) {
                $add_featured_image_to_post = $this->get_safe_option($options, 'add_feature_image_to_post');
                $post_status = $this->get_safe_option($options, 'post_status');
                $use_client_image_for_featured_image = $this->get_safe_option($options, 'use_client_image_for_featured_image');

                $wire = $this->get_safe_post_field('wire');
                $wire = $this->verify_wire($wire);

                $categories = $this->get_categories_for_post($wire, $options);
                $plugin_version = $this->version;
                $categories_names = [];
                foreach ($categories as $category) {
                    $categories_names[] = get_the_category_by_ID($category);
                }
                return [
                    'status' => self::StatusSuccess,
                    'data' => [
                        'add_featured_image_to_post' => $add_featured_image_to_post,
                        'use_client_image_for_featured_image' => $use_client_image_for_featured_image,
                        'categories' => $categories_names,
                        'version' => $plugin_version,
                        'post_status' => $post_status,
                        'wp_version' => get_bloginfo('version')
                    ]
                ];
            }
        }
        return [
            'status' => self::StatusError,
            'errors' => [
                'form' => 'Wrong Data - token not set'
            ]
        ];
    }

    public function verify_installation()
    {
        $verification_kind = $this->get_safe_post_field('verification_kind', 'installation');
        $data = $this->publish_new_post([
            'post_status' => self::WpPostStatusDraft
        ]);
        if ($verification_kind === 'installation') {
            if ($data['status'] === self::StatusSuccess) {
                $post_data = $data['data'];
                $post_id = $post_data['post_id'];
                if ($post_id) {
                    wp_delete_post($post_id, true);
                }
            }
        }
        return $data;
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function get_categories_for_post($wire, $options = [])
    {
        $custom_category = $this->get_safe_wire_option($wire, $options, 'category');
        $additional_categories = $this->get_safe_wire_option($wire, $options, 'additional_categories', '');
        $additional_categories = explode(';', (string)$additional_categories);
        $categories = $this->get_safe_post_field('press_release_categories', []);
        $categories = $this->get_categories($custom_category ? [$custom_category] : $categories);
        $categories_additional = [];
        if ($additional_categories) {
            $categories_additional = $this->get_categories($additional_categories);
            if (!$custom_category) {
                $categories = [];
            }
        }
        return array_merge($categories, $categories_additional);
    }

    /**
     * @param $title
     * @return mixed|null
     */
    protected function find_post_by_title_full($title)
    {
        $current_post = $this->find_post_by_title(esc_html($title));
        if (!$current_post) {
            if (strpos($title, '&') !== false) {
                $current_post = $this->find_post_by_title($title);
                if (!$current_post) {
                    $current_post = $this->find_post_by_title(str_replace("&", "and", $title));
                }
            }
        }
        return $current_post;
    }


    /**
     * @param $title
     * @return mixed|null
     */
    protected function find_post_by_title($title)
    {
        $current_posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit'),
            'title' => $title,
        ));
        return isset($current_posts[0]) ? $current_posts[0] : null;
    }

    /**
     * @param $publish_options
     * @return array
     */
    protected function publish_new_post($publish_options = [])
    {
        try {
            $token = $this->get_safe_post_field('token');
            if ($token) {
                $options = get_option($this->plugin_id);
                $plugin_token = $this->get_safe_option($options, 'token');
                $plugin_secret = $this->get_safe_option($options, 'secret');
                $add_featured_image_to_post = $this->get_safe_option($options, 'add_feature_image_to_post');
                $use_client_image_for_featured_image = $this->get_safe_option($options, 'use_client_image_for_featured_image');
                $add_tags_to_post = $this->get_safe_option($options, 'add_tags_to_post');
                $fill_yoast_seo_tags = $this->get_safe_option($options, 'fill_yoast_seo_tags');

                if ($plugin_token && $plugin_secret && $plugin_token === $token) {
                    $title = $this->get_safe_post_field('press_release_title');
                    $date = $this->get_fixed_date();
                    $current_post = $this->find_post_by_title_full($title);
                    if ($current_post) {
                        $current_post_id = $current_post->ID;
                        $current_post_timestamp = get_post_timestamp($current_post_id);
                        $post_date = strtotime($date);
                        if ($post_date && $current_post_timestamp) {
                            if (abs($current_post_timestamp - $post_date) < self::PostDuplicatedExpirationTime) {
                                return $this->get_post_response_success($current_post_id);
                            }
                        }
                    }
                    $article_id = $this->get_safe_post_field('article_id');
                    $press_release_featured_image = $this->get_safe_post_field('press_release_featured_image');
                    $press_release_client_image = $this->get_safe_post_field('press_release_client_image');

                    $html = $this->get_safe_html_field('press_release_html');
                    $excerpt = $this->get_safe_post_field('excerpt');
                    $tags = $this->get_safe_post_field('press_release_tags', []);

                    $verification_token = $this->get_safe_post_field('verification_token');

                    $wire = $this->get_safe_post_field('wire');
                    $data = $this->common->get_credentials_for_wire($wire);

                    $wire = $data->wire;
                    $domain = $data->domain;

                    $verified = $this->verify_post ? $this->verify_article($domain, $plugin_token, $plugin_secret, $article_id, $html, $verification_token) : true;

                    if (!$verified) {
                        throw new Exception('Wrong verification');
                    }

                    $press_release_kind = intval($this->get_safe_post_field('press_release_kind', self::PressReleaseBranded));
                    $update_avatar = $this->get_safe_post_field('update_author_avatar');
                    $author_id = $this->get_author_id($wire, $press_release_kind, $update_avatar);

                    $html_fixed = $this->regenerate_html($domain, $html, [
                        'add_featured_image_to_post' => $add_featured_image_to_post,
                        'use_client_image_for_featured_image' => $use_client_image_for_featured_image,
                        'press_release_featured_image' => $press_release_featured_image,
                        'press_release_client_image' => $press_release_client_image
                    ]);

                    $categories = $this->get_categories_for_post($wire, $options);

                    $post_status = isset($publish_options['post_status']) ? $publish_options['post_status'] : null;
                    $post_status = $post_status ? $post_status : $this->get_safe_option($options, 'post_status');

                    $my_post = array(
                        'post_title' => $title,
                        'post_content' => $html_fixed['html'],
                        'post_date' => $date,
                        'post_status' => self::WpPostStatusDraft,
                        'post_author' => $author_id,
                        'post_category' => $categories
                    );

                    if ($excerpt) {
                        $my_post['post_excerpt'] = $excerpt;
                    }

                    $post_id = wp_insert_post($my_post, true);

                    if ($post_id instanceof WP_Error) {
                        throw new Exception('Incorrect data for post : ' . json_encode($post_id->errors));
                    } else if ($post_id === 0) {
                        throw new Exception('Incorrect data for post : ' . json_encode($my_post));
                    }

                    if ($add_tags_to_post) {
                        wp_set_post_tags($post_id, $tags);
                    }

                    if ($html_fixed['featured_image'] && $html_fixed['featured_image']['attachment_id']) {
                        set_post_thumbnail($post_id, $html_fixed['featured_image']['attachment_id']);
                    }

                    //support for yoast seo plugin
                    if ($fill_yoast_seo_tags && in_array('wordpress-seo/wp-seo.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                        $seo_value = $this->get_safe_post_field('seo_title');
                        if ($seo_value) {
                            update_post_meta($post_id, '_yoast_wpseo_title', $seo_value);
                        }
                        $seo_value = $this->get_safe_post_field('seo_focus_keyword');
                        if ($seo_value) {
                            update_post_meta($post_id, '_yoast_wpseo_title', $seo_value);
                        }
                        $seo_value = $this->get_safe_post_field('seo_meta_description');
                        if ($seo_value) {
                            update_post_meta($post_id, '_yoast_wpseo_metadesc', $seo_value);
                        }
                    }

                    //support for polylang plugin
                    if (function_exists('pll_set_post_language')) {
                        $polylang_post_language = $this->get_safe_option($options, 'polylang_post_language');
                        if ($polylang_post_language) {
                            pll_set_post_language($post_id, $polylang_post_language);
                        }
                    }

                    if ($post_status && $post_status !== self::WpPostStatusDraft) {
                        wp_update_post([
                            'ID' => $post_id,
                            'post_status' => $post_status
                        ]);
                    }
                    return $this->get_post_response_success($post_id);

                } else {
                    throw new Exception('Wrong token');
                }
            }
        } catch (Exception $e) {
            return [
                'status' => self::StatusError,
                'errors' => [
                    'form' => $e->getMessage()
                ]
            ];
        }

        return [
            'status' => self::StatusError,
            'errors' => [
                'form' => 'Wrong Data - token not set'
            ]
        ];
    }

}
