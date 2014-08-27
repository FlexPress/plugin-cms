<?php

namespace FlexPress\Plugins\CMS\UI;

use FlexPress\Components\Hooks\HookableTrait;
use FlexPress\Plugins\CMS\CMS;

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
     * Hook into get text
     *
     * @param $translated_text
     * @param $text
     * @param $domain
     * @return mixed
     * @type filter
     * @params 3
     */
    public function gettext($translated_text, $text, $domain)
    {

        if (strpos($text, "Excerpts are") !== false) {
            return preg_replace('/<a.*<\/a>/', "", $text);
        }

        return $translated_text;
    }

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

//    /**
//     * Hook into the tiny mce init
//     *
//     * @param $init , current CSS classes (Array)
//     *
//     * @author Tim Perry
//     * @type action
//     *
//     */
//    public function tinyMceBeforeInit($init)
//    {
//
//        return $this->_add_custom_classes_and_formats($init);
//
//    }
//
//    /**
//     * Adds custom CSS classes & formats to the TinyMCE editor Style Selector
//     *
//     * @param $init , current CSS classes (Array)
//     *
//     * @author Tim Perry
//     */
//    protected function _add_custom_classes_and_formats($init)
//    {
//
//        if (!function_exists('get_field')) {
//            return $init;
//        }
//
//        if (($classes = get_field('fpt_tiny_mce_classes', 'options'))) {
//
//            $init['theme_advanced_styles'] = '';
//
//            foreach ($classes as $class) {
//
//                $init['theme_advanced_styles'] .= $class['fpt_tiny_mce_class_label_text'] . ' = ' . $class['fpt_tiny_mce_class_name'] . ';';
//
//            }
//
//            $init['theme_advanced_styles'] = rtrim($init['theme_advanced_styles'], ';');
//
//        }
//
//        $init['theme_advanced_blockformats'] = get_field('fpt_tiny_mce_formats', 'options');
//
//        //Always display the advanced editor
//        $init['wordpress_adv_hidden'] = false;
//
//        return $init;
//
//    }

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
     * Hook into user contact methods
     *
     * @param $contactmethods
     *
     * @author Tim Perry
     * @type filter
     * @params 1
     */
    public function userContactmethods($contactmethods)
    {

        unset($contactmethods['aim']);
        unset($contactmethods['jabber']);
        unset($contactmethods['yim']);

        return $contactmethods;
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

    /**
     * customise the media manager
     *
     * @params $strings array of strings used throughout the media manager
     * @author Tim Perry
     * @type filter
     * @params 1
     *
     */
    public function mediaViewStrings($strings)
    {

        $strings = $this->removeFromUrl($strings);
        //$strings = $this->removeGallery($strings);

        return $strings;

    }

    /**
     * Remove the option to link to the attachment URL and force link to file
     *
     * @param $strings
     * @returns $strings updated array of strings
     * @author Tim Perry
     */
    protected function removeFromUrl($strings)
    {

        unset($strings['insertFromUrlTitle']);

        return $strings;

    }

    /**
     * Remove the gallery creation functionaility if not required
     *
     * @param $strings
     * @returns $strings updated array of strings
     * @author Tim Perry
     */
    protected function removeGallery($strings)
    {

//        if (FCMSUtils::acf_available()) {
//
//            $gallery_controls = get_field('fpt_gallery_controls', 'options');
//
//        }
//
//        if ($gallery_controls != 'Yes') {
//
//            unset($strings['createGalleryTitle']);
//            unset($strings['editGalleryTitle']);
//            unset($strings['cancelGalleryTitle']);
//            unset($strings['insertGallery']);
//            unset($strings['updateGallery']);
//            unset($strings['addToGallery']);
//            unset($strings['addToGalleryTitle']);
//            unset($strings['reverseOrder']);
//            unset($strings['createNewGallery']);
//
//        }
//
//        return $strings;

    }

    /**
     * set user default media setttings
     *
     * @params $strings Strings
     * @author Tim Perry
     * @type action
     */
    public function userRegister($user_id)
    {

        $this->createUserSettings($user_id);

    }

    /**
     * Update users default options and force media link as there default link perferance
     *
     * @params $user_id current users ID
     * @returns null
     * @author Tim Perry
     */
    protected function createUserSettings($user_id)
    {

//        global $wpdb;
//
//        if (FCMSUtils::acf_available()) {
//
//            $link = ($link = get_field('fpt_media_link', 'options')) ? $link : 'file';
//            $align = ($align = get_field('fpt_media_alignment', 'options')) ? $align : 'none';
//            $size = ($size = get_field('fpt_media_size', 'options')) ? $size : 'medium';
//
//            $settings = '';
//            $settings .= 'libraryContent=browse';
//            $settings .= '&urlbutton=' . $link;
//            $settings .= '&align=' . $align;
//            $settings .= '&imgsize=' . $size;
//
//            update_user_option($user_id, 'wp_user-settings', $settings, true);
//
//        }

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
     * admin_enqueue_scripts action function
     *
     * @author Tim Perry
     * @type action
     */

    public function adminEnqueueScripts()
    {

        $cms_url = FORECMS_URL;
        echo "<script> var cms_url = '{$cms_url}' </script>\n";

        wp_enqueue_style('FCMSBackendUI', FORECMS_URL . 'assets/css/FCMSBackendUI.css', null, FORECMS_VERSION);

        if (!defined('FCMS_DISABLE_BRANDING')) {

            wp_enqueue_style(
                'FCMSBackendUIBranding',
                FORECMS_URL . 'assets/css/FCMSBackendUIBranding.css',
                null,
                FORECMS_VERSION
            );
            wp_enqueue_script(
                'FCMSBackendUIBranding',
                FORECMS_URL . 'assets/js/FCMSBackendUIBranding.js',
                array('jquery'),
                FORECMS_VERSION,
                false
            );

        }

    }

    /**
     * Login head action function
     *
     * @author Adam Bulmer
     * @type action
     */
    public function loginHead()
    {
        ?>

        <style>
            #login h1 {
                display: none !important;
            }

            #custom-logo {
                width: 100%;
                text-align: center;
                padding: 0 0 10px 0;
                margin-top: 5em;
            }

            #login-wrapper {
                position: absolute;
                width: 100%;
                height: 00%;
                top: 0;
                left: 0;
                overflow: auto;
            }
        </style>
        <script src="<?php echo FORECMS_URL . 'assets/js/jquery-1.8.3.min.js'; ?>"></script>
        <?php if (!defined('FCMS_DISABLE_BRANDING')) { ?>
        <script>
            jQuery(document).ready(function ($) {
                $('#login h1').remove();
                $('#login').prepend('<div id="custom-logo"><a href="http://www.forepoint.co.uk"><img border="0" src="<?php echo FORECMS_URL . '/assets/images/loginLogo.png'; ?>" /></a></div>');

            });
        </script>
    <?php
    }
    }

    /**
     * Admin Head Action Function
     *
     * @author Adam Bulmer
     * @type action
     *
     */
    public function adminHead()
    {
        ?>

        <script src="<?php echo FORECMS_URL; ?>assets/js/modernizr-2.6.2.min.js"></script>

        <!--[if lte IE 8]>
        <script src="<?php echo FORECMS_URL; ?>assets/js/excanvas.js"></script>
        <![endif]-->

        <?php if (!current_user_can('update_core')) { ?>

        <style>

            #dashboard_right_now .versions p, #dashboard_right_now .versions #wp-version-message {
                display: none;
            }

            .update-nag {
                display: none !important;
            }

            #footer-upgrade {
                display: none !important;
            }

            #favorite-actions {
                display: none !important;
            }

        </style>

    <?php
    }

    }

    /**
     * Admin bar Render action function
     *
     * @author Adam Bulmer
     * @type action
     */
    public function wpBeforeAdminBarRender()
    {

        global $wp_admin_bar;

        $wp_admin_bar->remove_menu('wporg', 'wp-logo');
        $wp_admin_bar->remove_menu('documentation', 'wp-logo');
        $wp_admin_bar->remove_menu('support-forums', 'wp-logo');
        $wp_admin_bar->remove_menu('feedback', 'wp-logo');
        $wp_admin_bar->remove_menu('w3tc');
        $wp_admin_bar->remove_menu('updates');
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('appearance');
        $this->removeTopAdminItems();

    }

    /**
     * Remove items from the top admin bar
     *
     * @return void
     * @author Adam Bulmer
     */
    protected function removeTopAdminItems()
    {

        global $wp_admin_bar;

        $wp_admin_bar->remove_menu('wporg', 'wp-logo');
        $wp_admin_bar->remove_menu('documentation', 'wp-logo');
        $wp_admin_bar->remove_menu('support-forums', 'wp-logo');
        $wp_admin_bar->remove_menu('feedback', 'wp-logo');
        $wp_admin_bar->remove_menu('w3tc');
        $wp_admin_bar->remove_menu('updates');
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('appearance');

        if (defined('FCMS_DISABLE_BRANDING')) {
            return;
        }

        global $wp_admin_bar;

        $wp_admin_bar->add_menu(

            array(

                'parent' => 'wp-logo',
                'id' => 'about',
                'title' => 'About ForeCMS',
                'href' => admin_url('about.php')

            )

        );

        $wp_admin_bar->add_menu(

            array(

                'parent' => 'wp-logo',
                'id' => 'forepoint',
                'title' => 'Forepoint',
                'href' => 'http://www.forepoint.co.uk',
                'meta' => array(
                    'target' => '_blank'
                )

            )

        );

        $wp_admin_bar->add_menu(

            array(

                'id' => 'fpt_support_details_url',
                'title' => 'Support Details',
                'href' => network_admin_url('admin.php?page=support-details')

            )

        );

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

        global $current_user;

        remove_menu_page('link-manager.php');
        remove_menu_page('edit-comments.php');

//        if ((FCMSUtils::get_current_user_primary_role(
//                ) == 'content_manager') || (FCMSUtils::get_current_user_primary_role(
//                ) == 'author') || (FCMSUtils::get_current_user_primary_role() == 'editor')
//        ) {
//
//            remove_menu_page('tools.php');
//            remove_menu_page('options-general.php');
//
//        }

    }

    /**
     * Add new pages to the CMS
     * @author Adam Bulmer
     * @since 3.2
     */
    protected function addPages()
    {

        global $current_user;

        //Add a hidden menu item
        add_menu_page(
            'Support Details',
            'Support Details',
            'read',
            'support-details',
            array($this, 'support_details_page')
        );

        add_options_page(
            'CMS',
            'CMS',
            'manage_options',
            'wp-plugin-cms-options',
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

//        if (FCMSUtils::acf_available() && !get_field('fpt_fcms_enable_post_revisions', 'options')) {
//            remove_meta_box('revisionsdiv', 'post', 'normal');
//        }
//
//        if (FCMSUtils::acf_available() && !get_field('fpt_fcms_enable_post_excerpt', 'options')) {
//            remove_meta_box('postexcerpt', 'post', 'normal');
//        }

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

//        if (FCMSUtils::acf_available() && !get_field('fpt_fcms_enable_post_revisions', 'options')) {
//            remove_meta_box('revisionsdiv', 'page', 'normal');
//        }

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
            'fpt_available_shortcodes',
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

        $this->removeDashboardWidgets();
        $this->addDashboardWidgets();

    }

    /**
     * Remove Dashboard Widgets
     *
     * @return void
     * @author Tim Perry
     */

    protected function removeDashboardWidgets()
    {

        unset($GLOBALS['wp_meta_boxes']);
    }

    /**
     * Add Dashboard Widgets
     *
     * @author Adam Bulmer
     */

    protected function addDashboardWidgets()
    {

        global $current_user;
        get_currentuserinfo();

//        if (!defined('FCMS_DISABLE_BRANDING')) {
//            wp_add_dashboard_widget(
//                'dashboard_support',
//                'Contact Support Form',
//                array('FCMSBackendUI', 'dashboard_support')
//            );
//        }
//
//        if (FCMSUtils::acf_available() && get_field('fpt_user_manual', 'options')) {
//
//            wp_add_dashboard_widget('dashboard_manual', 'User Manual', array('FCMSBackendUI', 'dashboard_manual'));
//
//        }

        if (class_exists("FCMSAnalytics") && (in_array('administrator', $current_user->roles) || in_array(
                    'content_manager',
                    $current_user->roles
                ))
        ) {

//            wp_add_dashboard_widget(
//                'dashboard_google_analytics',
//                'Website Analytics',
//                array('FCMSAnalytics', 'dashboard_google_analytics')
//            );

        }

    }

    /**
     * ACF Save Post
     *
     * @author - Tim Perry
     * @type action
     * @priority 20
     * @hook_name acf/save_post
     */
    public function acfSavePost($post_id)
    {

        $this->updateUserSettings($post_id);

    }

    /**
     * Update all users settings
     *
     * @param $post_id
     *
     * @author Tim Perry
     */
    protected function updateUserSettings($post_id)
    {

        if ($post_id == 'options') {

            $args = array(
                'orderby' => 'ID',
                'order' => 'ASC'
            );

            $users = get_users($args);

            foreach ($users as $user) {

                $current_settings = get_user_meta($user->ID, 'wp_user-settings');

                if (!empty ($current_settings)) {

                    $current_settings = explode('&', $current_settings[0]);

                    foreach ($current_settings as $current_setting) {

                        $current_setting = explode('=', $current_setting);

                        switch ($current_setting[0]) {

                            case 'urlbutton':

                                $new_settings[$current_setting[0]] = get_field('fpt_media_link', 'option');

                                break;
                            case 'align':

                                $new_settings[$current_setting[0]] = get_field('fpt_media_alignment', 'option');

                                break;
                            case 'imgsize':

                                $new_settings[$current_setting[0]] = get_field('fpt_media_size', 'option');

                                break;
                            default:

                                $new_settings[$current_setting[0]] = $current_setting[1];

                                break;

                        }

                    }

                    $new_settings_string = '';

                    foreach ($new_settings as $k => $v) {

                        $new_settings_string .= $k . '=' . $v . '&';

                    }

                    $new_settings_string = rtrim($new_settings_string, '&');

                } else {

                    $link = ($link = get_field('fpt_media_link', 'options')) ? $link : 'file';
                    $align = ($align = get_field('fpt_media_alignment', 'options')) ? $align : 'none';
                    $size = ($size = get_field('fpt_media_size', 'options')) ? $size : 'medium';

                    $new_settings_string = '';
                    $new_settings_string .= 'libraryContent=browse';
                    $new_settings_string .= '&urlbutton=' . $link;
                    $new_settings_string .= '&align=' . $align;
                    $new_settings_string .= '&imgsize=' . $size;

                }

                update_user_meta($user->ID, 'wp_user-settings', $new_settings_string);

            }

        }

    }

    /**
     * Manage 'Pages' custom columns
     *
     * @author Tim Perry
     * @type action
     * @params 2
     */
    public function managePagesCustomColumn($column_name, $post_id)
    {

        $this->pagesColumnsOutput($column_name, $post_id);

    }

    /**
     * Output for each of the custom 'Page' columns
     *
     * @author Tim Perry
     */
    protected function pagesColumnsOutput($column_name = null, $post_id = null)
    {

        if ($column_name && $post_id) {

            $post = get_post($post_id);

            switch ($column_name) {

                case 'page_type':

                    the_field('fpt_page_format', $post_id);

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

    /**
     * Customize the edit user profile page :)
     *
     * @author Adam Bulmer
     * @type action
     * @priority 1
     */
    public function editUserProfile($user)
    {

        $this->userSystemInfo($user);

    }

    /**
     * Display the user system info to us :)
     *
     * @author Adam Bulmer
     */
    protected function userSystemInfo($user)
    {

        ?>

        <?php if (current_user_can('manage_options')) { ?>

        <?php if ($system_info = get_user_meta($user->ID, 'fpt_user_system_info', true)) { ?>

            <div class="support-details-profile">

                <h3>Support Details</h3>

                <p><strong>Public IP Address</strong>: <?php echo $system_info['fpt_user_ip']; ?></p>

                <p><strong>Screen Resolution</strong>: <?php echo $system_info['fpt_user_screen_res']; ?></p>

                <p><strong>Operating System</strong>: <?php echo $system_info['fpt_user_os']; ?></p>

                <p><strong>Browser</strong>: <?php echo $system_info['fpt_user_browser']; ?></p>

                <p><strong>User Agent</strong>: <?php echo $system_info['fpt_user_agent']; ?></p>

            </div>

        <?php } ?>

    <?php } ?>

    <?php

    }

    // ==========
    // ! METHODS
    // ==========

    /**
     * Add Dashboard for email support request
     *
     * @author Adam Bulmer
     * @since 3.2
     */

    public function dashboardSupport()
    {

        ?>
        <?php global $current_user; ?>

        <!--        --><?php //$submitted = (isset($_POST['txtMessage'])) ? FCMSUtils::process_post(
//        'btnSupportSubmit',
//        array('FCMSBackendUI', 'process_dashboard_support')
//    ) : false;
        ?>

        <div class="fpt-support-dashboard">

            <?php if ($submitted) { ?>

                <p class="success-message">Thank you, your message has been received, Forepoint will get back to you
                    soon.</p>

            <?php
            } else {
                if ((!$submitted) && (isset($_POST['btnSupportSubmit']))) {
                    ?>

                    <p class="error-message">Please fill out the message box before clicking the summit button.</p>

                <?php
                }
            } ?>

            <form name="fpt-support-form" action="" method="post">

                <div class="control-group">
                    <label class="control-label" for="txtMessage">Message</label>

                    <div class="controls">
                        <textarea class="dashboard-textarea" name="txtMessage" id="txtMessage" rows="12"
                                  placeholder="Please give a brief description of your issue."></textarea>
                    </div>
                </div>

                <!--                <input type="hidden" value="-->
                <?php //echo FCMSUtils::get_user_attr($current_user->ID, 'user_email'); ?><!--"-->
                <!--                       name="txtUserEmail" id="txtUserEmail">-->
                <!--                <input type="hidden" value="-->
                <?php //echo FCMSUtils::get_user_attr($current_user->ID, 'display_name'); ?><!--"-->
                <!--                       name="txtUserName" id="txtUserName">-->

                <input type="submit" value="Submit" name="btnSupportSubmit" class="button-primary">

            </form>

        </div>

    <?php

    }

    /**
     *
     * Processes the dashboard support form
     *
     * @author Adam Bulmer
     *
     */
    public function processDashboardSupport()
    {

        $subject = "ForeCMS Dashboard Support";

        $args = array(

            'to' => array(

                'email' => "webteam@forepoint.co.uk",
                'name' => "Forepoint Web Team"

            ),
            'from' => array(

                'email' => $_POST['txtUserEmail'],
                'name' => $_POST['txtUserName']

            ),
            'subject' => $subject,
            'replacements' => array(

                'TITLE' => $subject,
                'USERNAME' => $_POST['txtUserName'],
                'EMAIL' => $_POST['txtUserEmail'],
                'MESSAGE_BODY' => $_POST['txtMessage'],
                'BROWSER' => $_SERVER['HTTP_USER_AGENT'],
                'WEBSITE_URL' => $_SERVER['HTTP_HOST'],
                'WEBSITE_NAME' => get_bloginfo('name')

            ),
            'template' => 'forecms_dashboard_support'

        );

//        FCMSNotificationCentre::send_email($args);

    }

    /**
     * Add Dashboard for downloading manual
     *
     * @author Adam Bulmer
     */

    public function dashboardManual()
    {

//        if (FCMSUtils::acf_available()) {
//
//            the_field('fpt_user_manual_description', 'options');
//            echo '<a href="' . get_field(
//                    'fpt_user_manual',
//                    'options'
//                ) . '" class="button-primary">Download User Manual</a>';
//
//        }

    }

    /**
     * Add Hidden Page for displaying user system info
     *
     * @access public
     * @author Adam Bulmer
     * @since 3.2
     */
    public function supportDetailsPage()
    {

//        $success = false;
//
//        if (isset($_POST['fpt_user_support_details_submit'])) {
//            $success = FCMSUtils::store_user_system_info();
//        }
//
//        $public_ip = $_SERVER['REMOTE_ADDR'];
//        $user_agent = $_SERVER['HTTP_USER_AGENT'];
//
//        $os = FCMSUtils::format_user_os($user_agent);
//        $browser = FCMSUtils::format_user_browser($user_agent);

        ?>

        <div class="wrap support-details">
            <div id="icon-options-general" class="icon32"><br></div>
            <h2>Support Details</h2>

            <?php if ($success) { ?>

                <div class="message updated"><p>Your system details have been submitted to Forepoint</p></div>

            <?php } ?>

            <form id="support_details" name="fpt_user_support_details" action="" method="post">

                <p><strong>Public IP Address</strong>: <?php echo $public_ip; ?></p>

                <p><strong>Screen Resolution</strong>: <span class="support_details__screen-res"></span></p>

                <p><strong>Operating System</strong>: <?php echo $os; ?></p>

                <p><strong>Browser</strong>: <?php echo $browser; ?></p>

                <input type="hidden" id="fpt_user_screen_res" name="fpt_user_screen_res" value="">
                <input type="hidden" id="fpt_user_ip" name="fpt_user_ip" value="<?php echo $public_ip; ?>">
                <input type="hidden" id="fpt_user_agent" name="fpt_user_agent" value="<?php echo $user_agent; ?>">
                <input type="hidden" id="fpt_user_os" name="fpt_user_os" value="<?php echo $os; ?>">
                <input type="hidden" id="fpt_user_browser" name="fpt_user_browser" value="<?php echo $browser; ?>">

                <input type="submit" value="Submit Information to Forepoint" name="fpt_user_support_details_submit"
                       class="button-primary">

            </form>

        </div>

    <?php

    }

} 