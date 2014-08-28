<?php

/*
Plugin Name: CMS Plugin
Plugin URI: https://github.com/FlexPress/plugin-cms
Description: FlexPress cms plugin
Version: 1.0.3
Author: FlexPress
Author URI: https://github.com/FlexPress
*/

use FlexPress\Plugins\CMS\DependencyInjection\DependencyInjectionContainer;

// Include autoloader if installed on it's own.
$autoloadFile = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadFile)) {
    require_once($autoloadFile);
}

// Dependency Injection
$dic = new DependencyInjectionContainer();
$dic->init();

// Run cms
$dic['CMS']->init(__FILE__);
