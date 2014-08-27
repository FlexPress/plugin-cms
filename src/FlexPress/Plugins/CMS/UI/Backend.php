<?php

namespace FlexPress\Plugins\CMS\UI;

use FlexPress\Components\Hooks\HookableTrait;
use FlexPress\Plugins\CMS\CMS;
use FlexPress\Plugins\CMS\MetaBoxes\PageType;

class Backend
{

    use HookableTrait;

    /**
     * @var CMS
     */
    protected $cms;

    /**
     * @param $dic
     */
    public function __construct($dic)
    {
        $this->dic = $dic;
    }

    // ==========
    // ! FILTERS
    // ==========

    /**
     * There is a bug where if you select a term in a hierarchical taxonomy, it
     * looses its position and jumps to the top of the list regardless of if its a
     * child of another term.
     *
     * @returns array
     * @author Adam Bulmer
     * @type filter
     */

    public function wpTermsChecklistArgs($args)
    {

        $args['checked_ontop'] = false;
        return $args;

    }

    /**
     *
     * Add custom tinymce classes
     *
     * @param $init
     * @return mixed
     * @author Tim Perry
     * @type filter
     *
     */
    public function tinyMceBeforeInit($init)
    {

        // Create array of new styles
        $new_styles = array(
            array(
                'title' => __('CMS Styles', 'flexpress'),
                'items' => apply_filters(
                    'fpcms_tiny_mce_styles',
                    array(
                        array(
                            'title' => __('Intro Text', 'flexpress'),
                            'selector' => 'p',
                            'classes' => 'intro-text'
                        ),
                    )
                ),
            ),
        );

        // Merge old & new styles
        $init['style_formats_merge'] = true;

        // Add new styles
        $init['style_formats'] = json_encode($new_styles);

        return $init;

    }

    /**
     * Update ACF WYSIWYG settings to mirror the WP default stuff
     *
     * @param $toolbars , current toolbar configuration (Array)
     *
     * @return $toolbars, updated toolbar configuration (Array)
     * @author Tim Perry
     * @type filter
     * @hook_name acf/fieldGroups/wysiwyg/toolbars
     */
    public function acfFieldsWysiwygToolbars($toolbars)
    {

        return $this->setupCustomToolbars($toolbars);

    }

    /**
     * Update ACF WYSIWYG settings to mirror the WP default stuff
     *
     * @param $toolbars , current toolbar configuration (Array)
     *
     * @return $toolbars, updated toolbar configuration (Array)
     * @author Tim Perry
     *
     */
    protected function setupCustomToolbars($toolbars)
    {

        //mirror default WP editor settings into ACF full
        for ($i = 1; $i <= 4; $i++) {

            $toolbars['Full'][$i] = get_option('tadv_btns' . $i);

        }

        //tweak the simple editor layout
        $toolbars['Basic'][1] = array(
            'bold',
            'italic',
            'blockquote',
            'styleselect',
            'bullist',
            'numlist',
            'link',
            'unlink',
            'pastetext',
            'undo',
            'redo',
            'fullscreen'
        );

        //create a very simple editor layout
        $toolbars['Very Simple'] = array();
        $toolbars['Very Simple'][1] = array('bold', 'italic', 'link', 'unlink');

        return $toolbars;

    }

    /**
     *  hook into the excerpt length
     *
     * @param $length
     *
     * @return bool
     *
     * @author Tim Perry
     *
     * @type filter
     *
     * @priority 9999
     *
     */
    public function excerptLength($length = 20)
    {

        return apply_filters('fpcms_excerpt_length', $length);

    }

    /**
     * hook into the what mimes can be uploaded
     *
     * @param $mime_types
     *
     * @author Tim Perry
     * @type filter
     * @params 1
     * @priority 1
     */
    public function uploadMimes($mime_types)
    {

        $mime_types['dot'] = 'application/msword';
        $mime_types['pub'] = 'application/x-mspublisher';

        return $mime_types;
    }

    /**
     * Hook into post mime types
     *
     * @param $post_mime_types
     *
     * @return mixed $post_mime_types
     * @author Tim Perry
     * @type filter
     *
     */
    public function postMimeTypes($post_mime_types)
    {

        $post_mime_types['application/pdf'] = array(
            __('PDF Documents'),
            __('Manage PDF Documents'),
            _n_noop('PDF Document (%s)', 'PDF Documents (%s)')
        );

        $post_mime_types['application/msword'] = array(
            __('Word Documents'),
            __('Manage Word Documents'),
            _n_noop('Word Document (%s)', 'Word Documents (%s)')
        );

        $post_mime_types['application/x-shockwave-flash'] = array(
            __('Flash'),
            __('Manage Flash'),
            _n_noop('Flash (%s)', 'Flash (%s)')
        );

        return $post_mime_types;
    }


    /**
     * Remove the comments column from the posts admin table
     *
     * @param $defaults
     *
     * @return the array of columns
     * @author Tim Perry
     * @type filter
     */
    public function managePostsColumns($defaults)
    {

        unset($defaults['comments']);
        return $defaults;
    }

    /**
     * Hook into the pages admin columns
     *
     * @return the array of columns
     * @author Tim Perry
     * @type filter
     */
    public function managePagesColumns()
    {

        return array(

            'cb' => __('<input type="checkbox" />'),
            'title' => __('Title'),
            'page_type' => __('Page Type'),
            'author' => __('Author'),
            'last_updated' => __('Last Updated'),
            'date' => __('Date Created')

        );
    }

    // ==================
    // ! ACTIONS
    // ==================


    /**
     * admin_init hook
     *
     * @author Tim Perry
     * @type action
     */
    public function adminInit()
    {
        add_post_type_support('page', 'excerpt');
    }

    /**
     * Admin Menu Action Function
     *
     * @author Adam Bulmer
     * @type action
     *
     */
    public function adminMenu()
    {

        $this->removePages();
        $this->addPages();

    }

    /**
     * Remove unused pages
     * @author Adam Bulmer
     */
    protected function removePages()
    {

        remove_menu_page('link-manager.php');
        remove_menu_page('edit-comments.php');

    }

    /**
     * Add new pages to the CMS
     * @author Adam Bulmer
     * @since 3.2
     */
    protected function addPages()
    {

        add_options_page(
            'CMS',
            'CMS',
            'manage_options',
            'fp-plugin-cms-options',
            array(
                $this,
                'outputOptionsPage'
            )
        );

    }

    /**
     *
     * Outputs the options page
     *
     * @author Tim Perry
     *
     */
    public function outputOptionsPage()
    {
        \Timber::render($this->dic['CMS']->getViewPath('pages/options.html.twig'));
    }

    /**
     * Add & remove meta boxes
     *
     * @author Tim Perry
     * @type action
     *
     */
    public function addMetaBoxes()
    {

        $this->removePostMetaBoxes();
        $this->removePageMetaBoxes();
        $this->addMiscMetaBoxes();

    }

    /**
     * Remove ununsed post meta boxes
     * @author Tim Perry
     */
    protected function removePostMetaBoxes()
    {

        remove_meta_box('commentsdiv', 'post', 'normal');
        remove_meta_box('formatdiv', 'post', 'normal');
        remove_meta_box('postcustom', 'post', 'normal');
        remove_meta_box('trackbacksdiv', 'post', 'normal');
        remove_meta_box('commentstatusdiv', 'post', 'normal');

    }

    /**
     * Remove ununsed post meta boxes
     * @author Tim Perry
     */
    protected function removePageMetaBoxes()
    {

        remove_meta_box('commentsdiv', 'page', 'normal');
        remove_meta_box('postcustom', 'page', 'normal');
        remove_meta_box('trackbacksdiv', 'page', 'normal');
        remove_meta_box('commentstatusdiv', 'page', 'normal');

    }

    /**
     * Add metaboxes
     * @author Adam Bulmer
     */

    public function addMiscMetaBoxes()
    {

        add_meta_box(
            'fp_available_shortcodes',
            'Available Shortcodes',
            array($this, 'available_shortcodes'),
            'post',
            'side',
            'default'
        );

    }

    /**
     * Display Available Shortcodes
     * @author Adam Bulmer
     */

    public function availableShortcodes()
    {

        global $shortcode_tags;

        ?>

        <ul>

            <?php foreach ($shortcode_tags as $key => $tag) { ?>

                <li><?php echo $key; ?></li>

            <?php } ?>

        </ul>

    <?php

    }

    /**
     * Dashboard Action Function
     * @author Adam Bulmer
     * @type action
     */

    public function wpDashboardSetup()
    {

        unset($GLOBALS['wp_meta_boxes']);

    }

    /**
     * Manage 'Pages' custom columns
     *
     * @author Tim Perry
     * @param $column_name
     * @param $postId
     * @type action
     * @params 2
     */
    public function managePagesCustomColumn($column_name, $postId)
    {

        if ($column_name && $postId) {

            $post = get_post($postId);

            switch ($column_name) {

                case 'page_type':

                    echo ucwords(get_post_meta($postId, PageType::META_NAME_PAGE_TYPE, true));
                    break;

                case 'last_updated':

                    echo '<abbr title="' . date('Y/m/d g:i:s A', strtotime($post->post_modified_gmt)) . '">' . date(
                            'd/m/Y',
                            strtotime($post->post_modified_gmt)
                        ) . ' @ </abbr><br /><span class="update-time">' . date(
                            'g:i:s A',
                            strtotime($post->post_modified_gmt)
                        ) . '</span>';

                    break;

            }

        }
    }

} 