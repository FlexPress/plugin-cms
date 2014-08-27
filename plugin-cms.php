<?php

/*
Plugin Name: CMS Plugin
Plugin URI: https://github.com/FlexPress/plugin-cms
Description: FlexPress cms plugin
Version: 1.0.0
Author: FlexPress
Author URI: https://github.com/FlexPress
*/

use FlexPress\Plugins\CMS\DependencyInjection\DependencyInjectionContainer;

// Include autoloader if installed on it's own.
if(file_exists('vendor/autoload.php')) {
    require_once('vendor/autoload.php');
}

// Dependency Injection
$dic = new DependencyInjectionContainer();
$dic->init();

// Run cms
$dic['CMS']->init(__DIR__);
