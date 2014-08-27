<?php

namespace FlexPress\Plugins\CMS\Generators;

use FlexPress\Components\Hooks\HookableTrait;

class SiteMap
{

    use HookableTrait;

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
    public function trashedPost()
    {

        $this->generateSitemap();
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
    public function postUpdated($post_id, $post_after, $post_before)
    {

        // only generate a new sitemap if the post is being published
        if (($post_before->post_status != "publish")
            && ($post_after->post_status == 'publish')
        ) {

            $this->generateSitemap();

        }

    }

    /**
     * Generate The Sitemap of the website each time a page is created.
     *
     * @author Adam Bulmer
     */

    private function generateSitemap()
    {

        if ($site_map = $this->generateXml()) {

            if ($this->saveFile($site_map)) {

                $this->submitFile();

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

    private function generateXml()
    {

        $args = array(

            'posts_per_page' => -1,
            'numberposts' => -1,
            'orderby' => 'post_type',
            'order' => 'DESC',
            'post_type' => apply_filters('fpcms_sitemap_post_types', array('post', 'page'))

        );

        if ($results = get_posts($args)) {

            $sitemap = new \SimpleXMLElement($this->getDocStructure());

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

    private function saveFile($sitemap)
    {

        if ($sitemap) {

            if ($path = $this->getFilePath()) {

                return file_put_contents($path, $sitemap);

            }

        }

    }

    /**
     * Submit file to search engines.
     *
     * @author Adam Bulmer
     */

    private function submitFile()
    {

        $encoded_url = urlencode($this->getFileUrl());

        wp_remote_get(self::GOOGLE_WEBMASTER_TOOLS_URL . $encoded_url);
        wp_remote_get(self::BING_WEBMASTER_TOOLS_URL . $encoded_url);

    }

    /**
     * Get the sitemap file path
     *
     * @author Adam Bulmer
     */

    public function getFilePath()
    {

        if (is_writable($_SERVER['DOCUMENT_ROOT'])) {
            return $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $this->getFileName();
        }

    }

    /**
     * Get the location of the sitemap file with URL
     *
     * @return string
     * @author Adam Bulmer
     */

    public function getFileUrl()
    {

        return get_bloginfo('url') . "/" . $this->getFileName();

    }

    /**
     * Get the filename depending on if multisite enabled
     *
     * @return string
     * @author Adam Bulmer
     * @editor Tim Perry
     */

    public function getFileName()
    {

        $fileName = "sitemap";

        if (is_multisite()) {
            $fileName .= "-" . get_current_blog_id();
        }

        $fileName .= ".xml";

        return apply_filters('fpcms_sitemap_name', $fileName);

    }

    /**
     * Gets the base doc structure including comment
     *
     * @return string
     * @author Adam Bulmer
     */

    public function getDocStructure()
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

    public function adminNotices()
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

        if (!$this->getFilePath()) {
            echo '<div class="error"><p><strong>Website Root Folder Not Writable, Cannot create sitemap!</strong></p></div>';
        }

    }

} 