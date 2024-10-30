<?php

const chainwire = 'chainwire';
const financewire = 'financewire';
const cybernewswire = 'cybernewswire';
const techwire = 'techwire';
const gamingwire = 'gamingwire';

const domain_chainwire = 'https://app.chainwire.org';
const domain_cybernewswire = 'https://app.cybernewswire.com';
const domain_financewire = 'https://app.financewire.com';
const domain_gamingwire = 'https://app.gamingwire.io';

const author_username_chainwire = 'chainwire';
const author_email_chainwire = 'contact@chainwire.org';
const author_first_name_chainwire = 'Chainwire';
const author_url_chainwire = 'https://chainwire.org/';
const author_avatar_chainwire = '/wp-plugin/author_avatar_chainwire.png';

const author_username_cybernewswire = 'cybernewswire';
const author_email_cybernewswire = 'contact@cybernewswire.com';
const author_first_name_cybernewswire = 'CyberNewsWire';
const author_url_cybernewswire = 'https://cybernewswire.com';
const author_avatar_cybernewswire = '/wp-plugin/author_avatar_cybernewswire.png';

const author_username_financewire = 'financewire';
const author_email_financewire = 'contact@financewire.com';
const author_first_name_financewire = 'FinanceWire';
const author_url_financewire = 'https://financewire.com';
const author_avatar_financewire = '/wp-plugin/author_avatar_financewire.png';

const author_username_gamingwire = 'gamingwire';
const author_email_gamingwire = 'contact@gamingwire.io';
const author_first_name_gamingwire = 'GamingWire';
const author_url_gamingwire = 'https://gamingwire.io';
const author_avatar_gamingwire = '/wp-plugin/author_avatar_gamingwire.png';

const author_last_name = null;
const author_twitter = null;
const author_facebook = null;
const author_google = null;
const author_tumblr = null;
const author_instagram = null;
const author_pinterest = null;

class ChainwireCommon
{
    protected $wires = [];
    protected $plugin_id;

    public function __construct($plugin_id)
    {
        $this->plugin_id = $plugin_id;
        $this->wires = [chainwire, financewire, cybernewswire, gamingwire, techwire];
    }

    public function get_wires()
    {
        return $this->wires;
    }

    /**
     * @param $wire
     * @return object
     */
    public function get_credentials_for_wire($wire = null)
    {
        switch ($wire) {
            case financewire:
                $username = author_username_financewire;
                $email = author_email_financewire;
                $avatar = author_avatar_financewire;
                $domain = domain_financewire;
                break;
            case cybernewswire:
                $username = author_username_cybernewswire;
                $email = author_email_cybernewswire;
                $avatar = author_avatar_cybernewswire;
                $domain = domain_cybernewswire;
                break;
            case gamingwire:
                $username = author_username_gamingwire;
                $email = author_email_gamingwire;
                $avatar = author_avatar_gamingwire;
                $domain = domain_gamingwire;
                break;
            default:
                $wire = chainwire;
                $username = author_username_chainwire;
                $email = author_email_chainwire;
                $avatar = author_avatar_chainwire;
                $domain = domain_chainwire;
                break;
        }
        return (object)[
                'wire' => $wire,
                'username' => $username,
                'email' => $email,
                'avatar' => $avatar,
                'domain' => $domain
        ];
    }

    /**
     * @param $wire
     * @return array
     */
    public function get_user_data($wire)
    {
        switch ($wire) {
            case cybernewswire:
                $first_name = author_first_name_cybernewswire;
                $user_url = author_url_cybernewswire;
                break;
            case financewire:
                $first_name = author_first_name_financewire;
                $user_url = author_url_financewire;
                break;
            case gamingwire:
                $first_name = author_first_name_gamingwire;
                $user_url = author_url_gamingwire;
                break;
            default:
                $first_name = author_first_name_chainwire;
                $user_url = author_url_chainwire;
                break;
        }
        return [
                'first_name' => $first_name,
                'user_url' => $user_url,
                'last_name' => author_last_name,
                'twitter' => author_twitter,
                'facebook' => author_facebook,
                'google' => author_google,
                'tumblr' => author_tumblr,
                'instagram' => author_instagram,
                'pinterest' => author_pinterest,
        ];
    }

    public function get_admin_category_options($categories)
    {
        $category_options = '';
        foreach ($categories as $c) {
            $category_options .= '<option value="' . $c->name . '">' . $c->name . '</option>';
        }
        return $category_options;
    }

    public function get_admin_wire_options($options = [])
    {
        $wires_options = [];
        $plugin_id = $this->plugin_id;

        $label_category = translate('Main Category', $this->plugin_id);
        $label_additional_categories = translate('Additional Categories', $this->plugin_id);

        foreach ($this->wires as $w) {
            $label = null;
            $link = null;
            $desc = null;
            $option_name_category = 'category';
            $option_name_additional_categories = 'additional_categories';
            switch ($w) {
                case chainwire:
                    $label = 'Chainwire';
                    $link = 'https://app.chainwire.org';
                    $desc = 'Chainwire is the leading blockchain and crypto newswire and press release distribution service that maximize crypto news coverage.';
                    break;
                case financewire:
                    $label = 'FinanceWire';
                    $link = 'https://app.financewire.com';
                    $desc = 'Broadcast your news to industry-leading financial media outlets with guaranteed and immediate exposure.';
                    break;
                case cybernewswire:
                    $label = 'CyberNewsWire';
                    $link = 'https://app.cybernewswire.com';
                    $desc = 'CyberNewsWire is the leading cyber newswire and press release distribution service that maximize cyber-security news coverage.';
                    break;
                case gamingwire:
                    $label = 'GamingWire';
                    $link = 'https://app.gamingwire.io';
                    $desc = 'GamingWire is a newswire syndication service for gaming and eSports companies';
                    break;
                case techwire:
                    $label = 'TechWire';
                    $desc = 'Broadcast your news to industry-leading technology media outlets with guaranteed and immediate exposure.';
                    break;
            }

            $option_name_category = $this->get_wire_field($w, $option_name_category);
            $option_name_additional_categories = $this->get_wire_field($w, $option_name_additional_categories);

            $category = $this->get_plugin_internal_option($options, $option_name_category);
            $additional_categories = $this->get_plugin_internal_option($options, $option_name_additional_categories, '');

            $config = [
                    'wire' => $w,
                    'description' => $desc,
                    'link' => $link,
                    'label' => $label,
                    'option_category' => [
                            'field_name' => $option_name_category,
                            'label' => $label_category,
                            'id' => $plugin_id . '-' . $option_name_category,
                            'name' => $plugin_id . '[' . $option_name_category . ']',
                            'value' => $category,
                    ],
                    'option_additional_categories' => [
                            'field_name' => $option_name_additional_categories,
                            'label' => $label_additional_categories,
                            'id' => $plugin_id . '-' . $option_name_additional_categories,
                            'name' => $plugin_id . '[' . $option_name_additional_categories . ']',
                            'value' => !empty($additional_categories) ? $additional_categories : "",
                    ]
            ];
            $wires_options[] = $config;
        }
        return $wires_options;
    }

    public function get_wire_field($wire, $field)
    {
        if ($wire !== chainwire) {
            return $wire . '_' . $field;
        }
        return $field;
    }

    public function get_plugin_internal_option($options, $name, $default_value = null)
    {
        $option_value = isset($options[$name]) ? $options[$name] : null;
        return sanitize_text_field($option_value !== null ? $option_value : $default_value);
    }

}
