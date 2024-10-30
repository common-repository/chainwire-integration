<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://chainwire.org
 * @since      1.0.0
 *
 * @package    Chainwire
 * @subpackage Chainwire/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">

    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

    <form method="post" name="cleanup_options" action="options.php">

        <?php
        //Grab all options
        $options = get_option($this->plugin_id);
        $plugin_id = $this->plugin_id;
        $categories = get_categories(array(
                "hide_empty" => 0,
                "type" => "post",
                'orderby' => 'name',
                'order' => 'ASC'
        ));

        $plugin = new ChainwireCommon($plugin_id);
        $category_options = $plugin->get_admin_category_options($categories);
        $wires_options = $plugin->get_admin_wire_options($options);

        // Cleanup
        $token = $plugin->get_plugin_internal_option($options, 'token');
        $secret = $plugin->get_plugin_internal_option($options, 'secret');
        $polylang_post_language = $plugin->get_plugin_internal_option($options, 'polylang_post_language');
        $post_status = $plugin->get_plugin_internal_option($options, 'post_status', 'publish');
        $use_client_image_for_featured_image = $plugin->get_plugin_internal_option($options, 'use_client_image_for_featured_image', false);
        $add_feature_image_to_post = $plugin->get_plugin_internal_option($options, 'add_feature_image_to_post', false);
        $add_tags_to_post = $plugin->get_plugin_internal_option($options, 'add_tags_to_post', false);
        $fill_yoast_seo_tags = $plugin->get_plugin_internal_option($options, 'fill_yoast_seo_tags', false);

        $wires = $plugin->get_wires();

        $polylang_languages = function_exists('pll_the_languages') ? pll_the_languages(array('raw' => 1)) : [];
        $is_polylang_installed = count($polylang_languages) > 0;
        if ($is_polylang_installed) {
            $polylang_languages_options = '';
            array_push($polylang_languages, (object)[
                    'value' => null,
                    'slug' => 'Not set'
            ]);
            foreach ($polylang_languages as $pl) {
                $pl = (object)($pl);
                $pl->value = isset($pl->value) ? $pl->value : $pl->slug;
                $polylang_languages_options .= '<option value="' . $pl->value . '" ' . ($pl->value === $polylang_post_language ? 'selected="selected"' : '') . '>' . $pl->slug . '</option>';
            }
        }

        $post_status_array = [
                [
                        'name' => 'publish',
                        'label' => 'Publish'
                ],
                [
                        'name' => 'draft',
                        'label' => 'Draft'
                ],
                [
                        'name' => 'pending',
                        'label' => 'Pending'
                ],
                [
                        'name' => 'private',
                        'label' => 'Private'
                ],
        ];

        $post_status_options = '';
        foreach ($post_status_array as $c) {
            $post_status_options .= '<option value="' . $c['name'] . '" ' . ($c['name'] === $post_status ? 'selected="selected"' : '') . '>' . $c['label'] . '</option>';
        }
        settings_fields($this->plugin_id);
        do_settings_sections($this->plugin_id);
        ?>

        <fieldset>
            <p>Default Post Status</p>
            <legend class="screen-reader-text"><span><?php _e('Post Status', $this->plugin_id); ?></span>
            </legend>
            <label for="<?php echo $this->plugin_id; ?>-post_status">
                <select class="regular-text" id="<?php echo $this->plugin_id; ?>-post_status"
                        name="<?php echo $this->plugin_id; ?>[post_status]">
                    <?php echo $post_status_options ?>
                </select>
            </label>
        </fieldset>

        <fieldset>
            <p>Your Key</p>
            <legend class="screen-reader-text"><span><?php _e('Your Token', $this->plugin_id); ?></span>
            </legend>
            <label for="<?php echo $this->plugin_id; ?>-token">
                <input class="regular-text" id="<?php echo $this->plugin_id; ?>-token"
                       name="<?php echo $this->plugin_id; ?>[token]"
                       value="<?php if (!empty($token)) echo $token; ?>"/>
            </label>
        </fieldset>

        <fieldset>
            <p>Your Secret</p>
            <legend class="screen-reader-text"><span><?php _e('Your Secret', $this->plugin_id); ?></span>
            </legend>
            <label for="<?php echo $this->plugin_id; ?>-secret">
                <input class="regular-text" id="<?php echo $this->plugin_id; ?>-secret"
                       name="<?php echo $this->plugin_id; ?>[secret]"
                       value="<?php if (!empty($secret)) echo $secret; ?>"/>
            </label>
        </fieldset>

        <p>Additional Options</p>

        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Use client logo instead of featured image', $this->plugin_id); ?></span>
            </legend>
            <label for="<?php echo $this->plugin_id; ?>-use_client_image_for_featured_image">
                <input class="regular-text"
                       id="<?php echo $this->plugin_id; ?>-use_client_image_for_featured_image"
                       type="checkbox"
                       name="<?php echo $this->plugin_id; ?>[use_client_image_for_featured_image]"
                        <?php if (!empty($use_client_image_for_featured_image)) echo 'checked="checked"'; ?>/>
                <span><?php _e('Use client logo instead of featured image', $this->plugin_id); ?></span>
            </label>
        </fieldset>

        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Add feature image to post content', $this->plugin_id); ?></span>
            </legend>
            <label for="<?php echo $this->plugin_id; ?>-add_feature_image_to_post">
                <input class="regular-text"
                       id="<?php echo $this->plugin_id; ?>-add_feature_image_to_post"
                       type="checkbox"
                       name="<?php echo $this->plugin_id; ?>[add_feature_image_to_post]"
                        <?php if (!empty($add_feature_image_to_post)) echo 'checked="checked"'; ?>/>
                <span><?php _e('Add featured image to the post content - please choose it only if your theme <strong><u>doesn\'t display</u></strong> featured images for posts', $this->plugin_id); ?></span>
            </label>
        </fieldset>

        <fieldset>
            <legend class="screen-reader-text">
                <span><?php _e('Add tags to post', $this->plugin_id); ?></span>
            </legend>
            <label for="<?php echo $this->plugin_id; ?>-add_tags_to_post">
                <input class="regular-text"
                       id="<?php echo $this->plugin_id; ?>-add_tags_to_post"
                       type="checkbox"
                       name="<?php echo $this->plugin_id; ?>[add_tags_to_post]"
                        <?php if (!empty($add_tags_to_post)) echo 'checked="checked"'; ?>/>
                <span><?php _e('Add tags to the post - you can find available tags on <a href="https://app.chainwire.org" target="_blank">app.chainwire.org</a>', $this->plugin_id); ?></span>
            </label>
        </fieldset>

        <?php if ($is_polylang_installed) { ?>
            <fieldset>
                <p>Polylang Language For Post</p>
                <legend class="screen-reader-text"><span><?php _e('Your Secret', $this->plugin_id); ?></span>
                </legend>
                <label for="<?php echo $this->plugin_id; ?>-polylang_post_language">
                    <select class="regular-text" id="<?php echo $this->plugin_id; ?>-polylang_post_language"
                            name="<?php echo $this->plugin_id; ?>[polylang_post_language]">
                        <?php echo $polylang_languages_options ?>
                    </select>
                </label>
            </fieldset>
        <?php } ?>

        <?php if (in_array('wordpress-seo/wp-seo.php', apply_filters('active_plugins', get_option('active_plugins')))) { ?>
            <fieldset>
                <p>Yoast SEO Plugin</p>
                <fieldset>
                    <legend class="screen-reader-text">
                        <span><?php _e('Add tags to post', $this->plugin_id); ?></span>
                    </legend>
                    <label for="<?php echo $this->plugin_id; ?>-fill_yoast_seo_tags">
                        <input class="regular-text"
                               id="<?php echo $this->plugin_id; ?>-fill_yoast_seo_tags"
                               type="checkbox"
                               name="<?php echo $this->plugin_id; ?>[fill_yoast_seo_tags]"
                                <?php if (!empty($fill_yoast_seo_tags)) echo 'checked="checked"'; ?>/>
                        <span><?php _e('Fill SEO meta-tags automatically', $this->plugin_id); ?></span>
                    </label>
                </fieldset>
            </fieldset>

        <?php } ?>

        <?php foreach ($wires_options as $wire) : ?>

            <?php
            $option_category = $wire['option_category'];
            $option_additional_categories = $wire['option_additional_categories'];
            ?>

            <h2 class="title"><?= $wire['label'] ?></h2>
            <p><?= $wire['description'] ?></p>

            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th>
                        <fieldset>
                            <span><?= $option_category['label'] ?></span>
                            <legend class="screen-reader-text"><span><?= $option_category['label'] ?></span>
                            </legend>
                        </fieldset>
                    </th>
                    <td>
                        <label for="<?= $option_category['id'] ?>">
                            <select class="regular-text fill-value" id="<?= $option_category['id'] ?>"
                                    data-value="<?= $option_category['value'] ?>"
                                    name="<?= $option_category['name'] ?>"><?= $category_options ?></select>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>
                        <fieldset>
                            <span><?= $option_additional_categories['label'] ?></span>
                            <legend class="screen-reader-text">
                                <span><?= $option_additional_categories['label'] ?></span>
                            </legend>
                        </fieldset>
                    </th>
                    <td>
                        <fieldset class="additional-categories-wrapper"
                                  style="display: none; max-width: 22rem; width: 100% !important;">
                            <label for="<?= $option_additional_categories['id'] ?>" style="width: 100% !important;">
                                <select
                                        data-value="<?= $option_additional_categories['value'] ?>"
                                        data-placeholder="Click here to select additional categories"
                                        class="multiple-select regular-text"
                                        style="width: 100% !important;" multiple="multiple"
                                        id="<?= $option_additional_categories['id'] ?>"
                                        name="<?= $option_additional_categories['name'] ?>">
                                    <?= $category_options ?>
                                </select>
                            </label>
                            <input type="hidden"
                                   class="input-hidden"
                                   name="<?= $option_additional_categories['name'] ?>"
                                   value="<?= $option_additional_categories['value'] ?>"
                                   id="<?= $option_additional_categories['id'] ?>">
                        </fieldset>
                    </td>
                </tr>
                </tbody>
            </table>


        <?php endforeach ?>

        <!--        Add feature image to post content-->
        <?php submit_button('Save all changes', 'primary', 'submit', TRUE); ?>

    </form>

</div>
