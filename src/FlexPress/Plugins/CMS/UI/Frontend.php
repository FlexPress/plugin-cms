<?php

namespace FlexPress\Plugins\CMS\UI;

class Frontend
{

    // ==========
    // ! FILTERS
    // ==========

    /**
     * Protected page title format, to remove the Protected:
     *
     * @author - Adam Bulmer
     * @return string
     * @type filter
     *
     */

    public function protectedTitleFormat()
    {

        return '%s';

    }

    /**
     * wp_list_pages Action Function
     *
     * @author - Adam Bulmer
     * @return mixed
     * @type filter
     *
     */
    public function wpListPages($content)
    {

        return $this->_strip_classes($content);

    }

    /**
     * Strip the vast myrid of classes WordPress adds when listing pages or catagroies
     *
     * @param $content
     *
     * @return string
     * @author Shaun Bent
     */

    private function _strip_classes($content)
    {

        $pattern = '# class=(\'|")([-\w ]+)(\'|")#';
        $needle = 'current_page_';
        $cls_rplcmt_val = 'current';

        preg_match_all($pattern, $content, $class_attrs);

        $num_class_attrs = (isset($class_attrs)) ? count($class_attrs[0]) : 0;

        for ($i = 0; $i < $num_class_attrs; $i++) {

            if (strpos($class_attrs[2][$i], $needle) === false) {
                $content = preg_replace("#{$class_attrs[0][$i]}#", '', $content);
            } else {
                $content = preg_replace(
                    "#{$class_attrs[0][$i]}#",
                    " class={$class_attrs[1][$i]}$cls_rplcmt_val{$class_attrs[3][$i]}",
                    $content
                );
            }

        }

        return preg_replace('/title=\"(.*?)\"/', '', $content);

    }

    /**
     * Team Profiles Output Code
     *
     * @author - Shaun Bent
     * @editor - Adam Bulmer
     *
     */

    private function _add_team_profiles($content)
    {

        global $post;

        if ($profiles = get_field('fp_profiles')) {

            $content .= '<div class="team-profiles">';

            foreach ($profiles as $profile) {

                $image = '';

                $image = FCMSUtils::get_image_object($profile['fpt_profile_image']);

                $content .= '<article class="team-profile box">';
                $content .= '<aside class="box__media--left">';

                if (!empty($profile['fpt_profile_image'])) {
                    $content .= FCMSUtils::get_the_image(
                        $image,
                        apply_filters('fcms_team_profile_image_size', 'Profile Picture')
                    );
                }

                $content .= '</aside>';
                $content .= '<div class="box__body">';
                $content .= '<h2 class="team-profile__title">' . $profile['fpt_profile_name'] . '</h2>';
                $content .= $profile['fpt_profile_bio'];
                $content .= '</div>';
                $content .= '</article>';

            }

            $content .= '</div>';

        }

        return $content;

    }

    /**
     * Hook into the archive link output
     *
     * @param $x
     *
     * @return mixed $link_html
     * @author Shaun Bent
     * @type filter
     *
     */
    public function get_archives_link($x)
    {

        return $this->_setup_active_link_on_archive($x);

    }

    /**
     * Modify wp_get_archives to mark the current archive
     *
     * @param $x
     *
     * @return string
     * @author Shaun Bent
     */
    private function _setup_active_link_on_archive($x)
    {

        $url = preg_match('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.-]*(\?\S+)?)?)?)@i', $x, $matches);
        return $matches[4] == $_SERVER['REQUEST_URI'] ? preg_replace('@<li@', '<li class="current"', $x) : $x;

    }

    /**
     * Hook into the html media markup
     *
     * @param $html
     *
     * @return string
     * @author Adam Bulmer
     * @type filter
     */
    public function media_send_to_editor($html)
    {

        return FCMSUtils::setup_responsive_layout($html);

    }

    /**
     * Hook into the image caption shortcode
     *
     * @param $val
     * @param $attr
     * @param null $content
     *
     * @return text HTML content describing embedded figure
     * @author Shaun Bent
     * @type filter
     * @params 3
     */
    public function img_caption_shortcode($val, $attr, $content = null)
    {

        return $this->_make_shortcode_caption_html5_compliant($val, $attr, $content);

    }

    /**
     * Filter to replace the [caption] shortcode text with HTML5 compliant code
     *
     * @param $val
     * @param null $attr
     * @param null $content
     *
     * @return text HTML content describing embedded figure
     * @author Shaun Bent
     */
    private function _make_shortcode_caption_html5_compliant($val, $attr = null, $content = null)
    {

        extract(
            shortcode_atts(
                array(

                    'id' => '',
                    'align' => '',
                    'width' => '',
                    'caption' => ''

                ),
                $attr
            )
        );

        if (empty($caption)) {
            return $val;
        }

        $capid = '';
        if ($id) {

            $id = esc_attr($id);
            $capid = 'id="figcaption_' . $id . '" ';
            $id = 'id="' . $id . '" aria-labelledby="figcaption_' . $id . '" ';

        }

        return '<figure ' . $id . 'class="wp-caption ' . esc_attr(
            $align
        ) . '" style="width: ' . $width . 'px">' . do_shortcode(
            $content
        ) . '<figcaption ' . $capid . 'class="wp-caption-text">' . $caption . '</figcaption></figure>';

    }

    /**
     * Hook into the the posts where sql filter
     *
     * @author Shaun Bent
     * @type filter
     * @return string
     */
    public function posts_orderby($order_by)
    {

        return $this->_group_search_results_by_post_type($order_by);

    }

    /**
     * Group search results by Post Type
     *
     * @returns custom order by SQL
     * @author Shaun Bent
     */
    private function _group_search_results_by_post_type($order_by)
    {

        global $post;
        global $wpdb;

        if (is_search()) {

            return $order_by = " {$wpdb->posts}.post_type ASC, {$wpdb->posts}.post_date DESC";

        }

        return $order_by;

    }

    /**
     * Hook into the image sizes array filter
     *
     * @returns array
     * @author Tim Perry
     * @type filter
     * @return array
     */
    public function intermediate_image_sizes($sizes)
    {

        return $this->always_add_full_image_size($sizes);

    }

    /**
     * Always swallow in the full image size
     *
     * @returns array
     * @author Tim Perry
     */
    private function always_add_full_image_size($sizes)
    {

        if (!in_array('full', $sizes)) {
            $sizes [] = 'full';
        }

        return $sizes;

    }

    // ==================
    // ! ACTIONS
    // ==================

    /**
     *
     * @type action
     *
     * @author Tim Perry
     *
     */
    public function init()
    {

        $this->_remove_wp_head_actions();

    }

    /**
     *
     * Remove some wp_head filters
     *
     * @author Tim Perry
     *
     */
    private function _remove_wp_head_actions()
    {

        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'index_rel_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'start_post_rel_link', 10, 0);
        remove_action('wp_head', 'parent_post_rel_link', 10, 0);
        remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);

    }

    /**
     * wp_enqueue_scripts hook
     *
     * @return void
     * @author Adam Bulmer
     * @type action
     */

    public function wp_enqueue_scripts()
    {

        $this->_enqueue_styles();

    }

    /**
     * Include CSS/JS For Admin Users on the front of the site.
     *
     * @return void
     * @author Adam Bulmer
     */

    private function _enqueue_styles()
    {

        if (is_user_logged_in()) {

            wp_enqueue_style(
                'FCMSBackendUI',
                FORECMS_URL . 'assets/css/FCMSBackendUIBranding.css',
                null,
                FORECMS_VERSION
            );

        }

    }
}
