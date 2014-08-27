<?php

namespace FlexPress\Plugins\CMS\Generators;

class Robots
{
    // ==================
    // ! ACTIONS
    // ==================

    /**
     * Regenerate robots.txt after a post/page has been deleted
     *
     * @author Tim Perry
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
     * @author Tim Perry
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

            $this->_generate_robots_txt();

        }

    }

    // ==================
    // ! FILTERS
    // ==================

    /**
     *
     * Reads the robots.txt file for the current site
     * used as a workaround for multisite instead of htaccess rewrites
     *
     * @type filter
     *
     * @return string
     * @author Tim Perry
     *
     */
    public function robots_txt()
    {

        return file_get_contents($this->get_file_path());

    }

    // ==================
    // ! METHODS
    // ==================

    /**
     * Generate the robots.txt file
     *
     * @return string
     * @author Tim Perry
     */

    private function _generate_robots_txt()
    {

        $args = array(

            'posts_per_page' => -1,
            'numberposts' => -1,
            'orderby' => 'post_type',
            'order' => 'DESC',
            'post_type' => 'page',
            'post_status' => 'publish',
            'child_of' => 0,
            'post_parent' => 0

        );

        if ($results = get_posts($args)) {

            $content = "User-Agent: *\r";
            $content .= "Disallow: /\r\r";

            $content .= "User-Agent: googlebot\r";
            $content .= "User-Agent: bingbot\r";
            $content .= "User-Agent: Slurp\r";
            $content .= "Disallow: /\r";
            $content .= "Allow: /wp-content/uploads/*\r";

            if (is_multisite()) {
                $content .= "Allow: /wp-content/blogs.dir/*\r";
            }

            $content .= "Allow: /$\r";

            foreach ($results as $result) {

                if ($permalink = get_permalink($result->ID)) {
                    $path = parse_url($permalink, PHP_URL_PATH) . "*";
                    if ($path != "/*") {
                        $content .= "Allow: " . $path . "\r";
                    }
                }

            }

            $oldest_post = current(get_posts('post_status=publish&post_type=post&order=ASC'));
            $year = date("Y", strtotime($oldest_post->post_date));
            $this_year = date("Y");

            while ($year++ < $this_year) {
                $content .= "Allow: /$year/*\r";
            }

            $this->_save_file($content);

        }

    }

    /**
     * Save the robots file to the root directory of the website
     *
     * @param $content
     *
     * @return bool
     * @author Tim Perry
     */

    private function _save_file($content)
    {

        if ($content) {

            if ($path = $this->get_file_path()) {
                return file_put_contents($path, $content);
            }

        }

        return false;

    }

    /**
     * Get the robots file path
     *
     * @author Tim Perry
     */

    public function get_file_path()
    {

        if (is_writable(ABSPATH)) {
            return ABSPATH . DIRECTORY_SEPARATOR . $this->get_file_name();
        }

        return false;

    }


    /**
     * Get the filename depending on if multisite enabled
     *
     * @return string
     * @author Tim Perry
     * @editor Tim Perry
     */

    public function get_file_name()
    {

        $filename = apply_filters('fpcms_robotstxt_name', 'robots');

        if (is_multisite()) {

            $blog_id = get_current_blog_id();
            $filename .= "-" . $blog_id;

        }

        return $filename . '.txt';

    }

    /**
     * admin_notice hook method
     *
     * @author Tim Perry
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
     * @author Tim Perry
     */

    public function display_writable_error_message()
    {

        if ( !$this->get_file_path()) {
            echo '<div class="error"><p><strong>Website Root Folder Not Writable, Cannot create sitemap!</strong></p></div>';
        }

    }

} 