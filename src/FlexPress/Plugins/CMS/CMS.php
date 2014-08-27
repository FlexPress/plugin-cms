<?php

namespace FlexPress\Plugins\CMS;

use FlexPress\Components\Hooks\Hooker;
use FlexPress\Components\MetaBox\Helper as MetaBoxHelper;
use FlexPress\Plugins\AbstractPlugin;

class CMS extends AbstractPlugin
{

    /**
     * @var Hooker
     */
    protected $hooker;

    /**
     * @var \FlexPress\Components\MetaBox\Helper
     */
    protected $metaBoxHelper;


    public function __construct(Hooker $hooker, MetaBoxHelper $metaBoxHelper)
    {
        $this->hooker = $hooker;
        $this->metaBoxHelper = $metaBoxHelper;
    }

    /**
     *
     * Fires off the various calls to helpers
     *
     * @author Tim Perry
     *
     */
    public function init($file)
    {
        parent::init($file);

        $this->metaBoxHelper->init();

        add_action('init', array($this, 'initHook'));
    }

    /**
     *
     * Callback for the init action hook
     *
     * @author Tim Perry
     *
     */
    public function initHook()
    {
        $this->hooker->hookUp();
    }

    /**
     *
     * For the given template it provides a absolute path
     * for the views directory of the cms plugin
     *
     * @param $template
     * @return string
     * @author Tim Perry
     *
     */
    public function getViewPath($template)
    {
        return $this->path . '/views/' . $template;
    }
}
