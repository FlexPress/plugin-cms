<?php

namespace FlexPress\Plugins\CMS;

use FlexPress\Components\Hooks\HookableTrait;

class Config
{

    use HookableTrait;

    const OPTIONS_KEY_SETUP_DONE = 'fp_cms_setup';

    /**
     *
     * Used to install some default options and configurations
     *
     * @author Tim Perry
     *
     */
    public function install()
    {

        if (!get_option(self::OPTIONS_KEY_SETUP_DONE)) {

            $this->setupACFOptions();
            $this->setupDefaultOptions();
            $this->setupTinyMCEDefaults();

            update_option(self::OPTIONS_KEY_SETUP_DONE, 1);

        }

    }

    /**
     *
     * Adds the license keys for acf addons
     *
     * @author Tim Perry
     *
     */
    protected function setupACFOptions()
    {

        update_option('acf_repeater_ac', 'QJF7-L4IX-UCNP-RF2W');
        update_option('acf_options_page_ac', 'OPN8-FA4J-Y2LW-81LS');
        update_option('acf_flexible_content_ac', 'FC9O-H6VN-E4CL-LT33');
        update_option('acf_gallery_ac', 'GF72-8ME6-JS15-3PZC');

    }


    /**
     *
     * Sets default options such as the comments status to being closed
     *
     * @author Tim Perry
     *
     */
    protected function setupDefaultOptions()
    {

        update_option('default_comment_status', 'closed');
        update_option('use_trackback', 0);
        update_option('use_smilies', 0);
        update_option('permalink_structure', '/%year%/%monthnum%/%postname%/');
        update_option('blogdescription', '');
        update_option('default_role', 'author');

    }

    /**
     * Setup tiny mce defaults
     * @author - Adam Bulmer
     */

    protected function setupTinyMCEDefaults()
    {

        $args = array(

            'hr',
            'wp_adv',
            'blockquote',
            'bold',
            'italic',
            'strikethrough',
            'underline',
            'bullist',
            'numlist',
            'outdent',
            'indent',
            'justifyleft',
            'justifycenter',
            'justifyright',
            'justifyfull',
            'cut',
            'copy',
            'paste',
            'link',
            'unlink',
            'image',
            'wp_more',
            'wp_page',
            'search',
            'replace',
            'fontselect',
            'fontsizeselect',
            'wp_help',
            'fullscreen',
            'styleselect',
            'formatselect',
            'forecolor',
            'backcolor',
            'pastetext',
            'pasteword',
            'removeformat',
            'cleanup',
            'spellchecker',
            'charmap',
            'print',
            'undo',
            'redo',
            'tablecontrols',
            'cite',
            'ins',
            'del',
            'abbr',
            'acronym',
            'attribs',
            'layer',
            'advhr',
            'code',
            'visualchars',
            'nonbreaking',
            'sub',
            'sup',
            'visualaid',
            'insertdate',
            'inserttime',
            'anchor',
            'styleprops',
            'emotions',
            'media',
            'iespell',
            'separator',
            '|'

        );

        update_option('tadv_allbtns', $args);

        $args = array(

            'bold',
            'italic',
            'strikethrough',
            'blockquote',
            'separator',
            'bullist',
            'numlist',
            'outdent',
            'indent',
            'separator',
            'link',
            'unlink',
            'separator',
            'cut',
            'copy',
            'pastetext',
            'separator',
            'spellchecker',
            'search',
            'separator',
            'fullscreen',
            'attribs'

        );

        update_option('tadv_btns1', $args);

        $args = array(

            'formatselect',
            'styleselect',
            'removeformat',
            'separator',
            'sup',
            'sub',
            'separator',
            'charmap',
            'separator',
            'undo',
            'redo',
            'separator',
            'tablecontrols',
            'delete_table'

        );

        update_option('tadv_btns2', $args);

        $args = array();

        update_option('tadv_btns3', $args);
        update_option('tadv_btns4', $args);

        $args = array(

            'advlink1' => 0,
            'advimage' => 1,
            'editorstyle' => 0,
            'hideclasses' => 0,
            'contextmenu' => 0,
            'no_autop' => 1,
            'advlist' => 0

        );

        update_option('tadv_options', $args);

        $args = array(

            'table',
            'searchreplace',
            'xhtmlxtras',
            'advimage'

        );

        update_option('tadv_plugins', $args);

        $args = array(

            'toolbar_1' => array(

                'bold',
                'italic',
                'strikethrough',
                'separator1',
                'bullist',
                'numlist',
                'outdent',
                'indent',
                'separator2',
                'link',
                'unlink',
                'separator4',
                'cut',
                'copy',
                'pastetext',
                'separator12',
                'spellchecker',
                'search',
                'separator6',
                'fullscreen',
                'attribs'

            ),
            'toolbar_2' => array(

                'formatselect',
                'styleselect',
                'removeformat',
                'separator8',
                'sup',
                'sub',
                'separator9',
                'charmap',
                'separator11',
                'undo',
                'redo',
                'separator10',
                'tablecontrols'

            ),
            'toolbar_3' => array(),
            'toolbar_4' => array()
        );

        update_option('tadv_toolbars', $args);

    }

    /**
     *
     * Init hook
     *
     * @type action
     * @author Tim Perry
     *
     */
    public function init()
    {
        date_default_timezone_set('Europe/London');
    }
}
