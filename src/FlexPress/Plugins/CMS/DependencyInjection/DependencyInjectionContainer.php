<?php

namespace FlexPress\Plugins\CMS\DependencyInjection;

use FlexPress\Components\Hooks\Hooker;
use FlexPress\Components\MetaBox\Helper as MetaBoxHelper;
use FlexPress\Plugins\CMS\CMS;
use FlexPress\Plugins\CMS\Generators\PDFThumbnail;
use FlexPress\Plugins\CMS\Generators\Robots;
use FlexPress\Plugins\CMS\MetaBoxes\PageType;
use FlexPress\Plugins\CMS\UI\Backend;
use Symfony\Component\HttpFoundation\Request;

class DependencyInjectionContainer extends \Pimple
{
    /**
     *
     * Adds the configs using pimple
     *
     * @author Tim Perry
     *
     */
    public function init()
    {
        $this->addSPLConfigs();
        $this->addHookConfigs();
        $this->addCMSConfigs();
    }

    /**
     *
     * Adds the spl configs such as objectStorage
     *Tim Perry
     *
     */
    protected function addSPLConfigs()
    {
        $this['objectStorage'] = $this->factory(
            function () {
                return new \SplObjectStorage();
            }
        );
    }

    protected function addHookConfigs()
    {

        $this['backendUI'] = function ($c) {
            return new Backend($c);
        };

        $this['pdfThumbnail'] = function () {
            return new PDFThumbnail();
        };

        $this['robots'] = function () {
            return new Robots();
        };

    }

    /**
     *
     * Add cms configs
     *
     * @author Tim Perry
     *
     */
    protected function addCMSConfigs()
    {

        $this["request"] = function () {
            return Request::createFromGlobals();
        };

        $this['hooker'] = function ($c) {
            return new Hooker($c['objectStorage'], array(
//                $c['backendUI'],
                $c['pdfThumbnail'],
                $c['robots']
            ));
        };

        $this["pageTypeMetaBox"] = function ($c) {
            return new PageType($c["request"], $c);
        };

        $this["metaBoxHelper"] = function ($c) {
            return new MetaBoxHelper($c['objectStorage'], array(
                $c['pageTypeMetaBox']
            ));
        };

        $this['CMS'] = function ($c) {
            return new CMS($c['hooker'], $c["metaBoxHelper"]);
        };

    }
}
