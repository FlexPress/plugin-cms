<?php

namespace FlexPress\CMS\Generators;

class SiteMap
{

    // ==================
    // ! ACTIONS
    // ==================

    const GOOGLE_WEBMASTER_TOOLS_URL = "http://www.google.com/webmasters/tools/ping?sitemap=";
    const BING_WEBMASTER_TOOLS_URL = "http://www.bing.com/webmaster/ping.aspx?siteMap=";

    /**
     * Regenerate sitemap after a post/page has been deleted
     *
     * @author Adam Bulmer
     * @type action
     *
     * @return bool
     */
    public function trashed_post()
    {

        $this->_generate_sitemap();
        return true;

    }

    /**
     * post_updated action hook public method
     *
     * @author Adam Bulmer
     *
     * @param $post_id
     * @param $post_after
     * @param $post_before
     *
     * @type action
     * @params 3
     */
    public function post_updated($post_id, $post_after, $post_before)
    {

        // only generate a new sitemap if the post is being published
        if (($post_before->post_status != "publish") && ($post_after->post_status == 'publish')) {

            $this->_generate_sitemap();

        }

    }

    /**
     * Generate The Sitemap of the website each time a page is created.
     *
     * @return file
     * @author Adam Bulmer
     */

    private function _generate_sitemap()
    {

        if ($site_map = $this->_generate_xml()) {

            if ($this->_save_file($site_map)) {

                $this->_submit_file();

            }

        }

    }

    // ==================
    // ! METHODS
    // ==================

    /**
     * Generate The XML for the sitemap
     *
     * @return string
     * @author Adam Bulmer
     */

    private function _generate_xml()
    {

        $args = array(

            'posts_per_page' => -1,
            'numberposts' => -1,
            'orderby' => 'post_type',
            'order' => 'DESC',
            'post_type' => apply_filters('fpcms_sitemap_post_types', array('post', 'page'))

        );

        if ($results = get_posts($args)) {

            $sitemap = new SimpleXMLElement($this->get_doc_structure());

            foreach ($results as $result) {

                $url = $sitemap->addChild('url');
                $url->addChild('loc', get_permalink($result->ID));

                if (has_post_thumbnail($result->ID)) {

                    if ($image = wp_get_attachment_image_src(get_post_thumbnail_id($result->ID))) {
                        $url->addChild('image:image', $image[0]);
                    }

                }

                $url->addChild('lastmod', mysql2date('Y-m-d', $result->post_date_gmt));

                if ($result->post_type == 'page') {

                    //Pages should display higher than posts for the same keyword
                    $url->addChild('priority', apply_filters('fpcms_sitemap_page_priority', '0.7'));

                } else {

                    $url->addChild('priority', apply_filters('fpcms_sitemap_post_priority', '0.4'));

                }

            }

            return $sitemap->asXML();

        }

    }

    /**
     * Save the Sitemap into the root directory of the website
     *
     * @param $sitemap
     *
     * @return bool
     * @author Adam Bulmer
     */

    private function _save_file($sitemap)
    {

        if ($sitemap) {

            if ($path = $this->get_file_path()) {
                return file_put_contents($path, $sitemap);
            }

        }

    }

    /**
     * Submit file to search engines.
     *
     * @author Adam Bulmer
     */

    private function _submit_file()
    {

        $encoded_url = urlencode($this->get_file_url());

        wp_remote_get(self::GOOGLE_WEBMASTER_TOOLS_URL . $encoded_url);
        wp_remote_get(self::BING_WEBMASTER_TOOLS_URL . $encoded_url);

    }

    /**
     * Get the sitemap file path
     *
     * @author Adam Bulmer
     */

    public function get_file_path()
    {

        if (is_writable(ABSPATH)) {

            return ABSPATH . DIRECTORY_SEPARATOR . $this->get_file_name();

        }

    }

    /**
     * Get the location of the sitemap file with URL
     *
     * @return string
     * @author Adam Bulmer
     */

    public function get_file_url()
    {

        return get_bloginfo('url') . DIRECTORY_SEPARATOR . $this->get_file_name();

    }

    /**
     * Get the filename depending on if multisite enabled
     *
     * @return string
     * @author Adam Bulmer
     * @editor Tim Perry
     */

    public function get_file_name()
    {

        return is_multisite() ? apply_filters('fpcms_sitemap_name', 'sitemap') . '-' . get_current_blog_id(
            ) . '.xml' : apply_filters('fpcms_sitemap_name', 'sitemap') . '.xml';

    }

    /**
     * Gets the base doc structure including comment
     *
     * @return string
     * @author Adam Bulmer
     */

    public function get_doc_structure()
    {

        $xml_string = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
        $xml_string .= '</urlset>';

        return $xml_string;

    }

    /**
     * admin_notice hook method
     *
     * @author Adam Bulmer
     * @type action
     */

    public function admin_notices()
    {

        $this->display_writable_error_message();

    }

    /**
     * Display admin notice if the root directory is not writable.
     *
     * @return string
     * @author Adam Bulmer
     */

    public function display_writable_error_message()
    {

        if (!$this->get_file_path()) {
            echo '<div class="error"><p><strong>Website Root Folder Not Writable, Cannot create sitemap!</strong></p></div>';
        }

    }

} 