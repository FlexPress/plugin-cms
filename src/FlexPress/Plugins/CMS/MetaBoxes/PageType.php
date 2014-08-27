<?php

namespace FlexPress\Plugins\CMS\MetaBoxes;

use FlexPress\Components\MetaBox\AbstractMetaBox;
use Symfony\Component\HttpFoundation\Request;

class PageType extends AbstractMetaBox
{

    const OPTION_NAME_PAGE_TYPE = 'fp_page_type';

    /**
     * @var Request
     */
    protected $request;


    /**
     * @var \Pimple
     */
    protected $dic;

    public function __construct(Request $request, $dic)
    {

        $this->request = $request;
        $this->dic = $dic;

    }

    /**
     *
     * {$inheritdoc}
     *
     */
    public function getTitle()
    {
        return "Page Type";
    }

    /**
     *
     * {$inheritdoc}
     *
     */
    public function getCallback()
    {

        $context = \Timber::get_context();

        $context['options'] = $this->getOptions();
        $context['field_name'] = self::OPTION_NAME_PAGE_TYPE;
        $context['current_value'] = $this->getCurrentValue();

        \Timber::render($this->dic['CMS']->getViewPath('meta_boxes/page-type.html.twig'), $context);

    }

    /**
     *
     * Returns the current value or 'default' if there is
     * not currently a value saved
     *
     * @return mixed|string
     * @author Tim Perry
     *
     */
    protected function getCurrentValue()
    {

        if (!$value = get_post_meta($GLOBALS['post']->ID, self::OPTION_NAME_PAGE_TYPE, true)) {
            $value = 'default';
        }

        return $value;

    }

    /**
     *
     * Gets the options for the form
     *
     * @return mixed|void
     * @author Tim Perry
     *
     */
    protected function getOptions()
    {
        return apply_filters(
            'fp_page_format_options',
            array(
                array(
                    "label" => "Default",
                    "value" => "default",
                ),
                array(
                    "label" => "Section",
                    "value" => "section",
                ),
                array(
                    "label" => "Non-Menu",
                    "value" => "nonmenu"
                )
            )
        );
    }

    /**
     * {$inheritdoc}
     */
    public function getContext()
    {
        return 'side';
    }

    /**
     * {$inheritdoc}
     */
    public function savePostCallback($postID)
    {
        if (wp_is_post_revision($postID)) {
            return;
        }

        if ($updatedValue = $this->request->get(self::OPTION_NAME_PAGE_TYPE)) {
            update_post_meta($postID, self::OPTION_NAME_PAGE_TYPE, $updatedValue);
        }

    }
}
