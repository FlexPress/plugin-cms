<?php

/*
Plugin Name: plugin-cms
Plugin URI: https://github.com/FlexPress/component-metabox
Description: FlexPres cms plugin
Version: 1.0.0
Author: FlexPRess
Author URI: https://github.com/FlexPress
*/

use FlexPress\Plugins\CMS\DependencyInjection\DependencyInjectionContainer;

//require_once('vendor/autoload.php');

// Dependency Injection
$dic = new DependencyInjectionContainer();
$dic->init();

// Run cms
$dic['CMS']->init(__DIR__);
