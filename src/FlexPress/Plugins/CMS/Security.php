<?php

namespace FlexPress\Plugins\CMS;

use FlexPress\Components\Hooks\HookableTrait;

class Security
{

    use HookableTrait;

    /**
     *
     * Disable xmlrpc
     *
     * @type filter
     * @return bool
     * @author Tim Perry
     *
     */
    public function xmlrpcEnabled()
    {
        return false;
    }

    /**
     *
     * Forces the session to be destroyed on logout
     *
     * @author Tim Perry
     *
     */
    public function wpLogout()
    {
        session_regenerate_id(true);
    }

    /**
     *
     * Replaces login errors with a generic error messsage
     *
     * @param $errors
     *
     * @internal param $message
     * @return mixed
     * @author Tim Perry
     */
    public function wpLoginErrors( $errors ) {

        return "Something went wrong, please try again.";

    }

    /**
     *
     * Shortens the login session expiration
     *
     * @return int
     * @author Tim Perry
     *
     */
    public function authCookieExpiration()
    {
        return 15 * MINUTE_IN_SECONDS;
    }

    /**
     *
     * Init hook
     *
     * @author Tim Perry
     *
     */
    public function init()
    {
        $this->disablePasswordResets();
    }

    /**
     *
     * If the options is selected, password resets will be disabled
     *
     * @author Tim Perry
     *
     */
    protected function disablePasswordResets()
    {
        // todo line 370 on FCMSSEcurioty
    }

} 