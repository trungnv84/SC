<?php
defined('ROOT_DIR') || exit;

define('REWRITE_SUFFIX', '.html');
define('DEFAULT_VIEW_TYPE', 'html');
define('DEFAULT_TEMPLATE', 'site');
define('DEFAULT_LAYOUT', 'default');
define('ASSETS_OPTIMIZATION', '5');
define('ASSETS_VERSION', '1.0');

$config = new stdClass();

$config->autoLoadPath = [
	APP_DIR . DS . 'core',
	'Controller' => CONTROLLER_DIR,
	/*'View' => APP_DIR . DS . 'view',*/
	'Model' => MODEL_DIR,
	APP_DIR . DS . 'lib'
];

$config->router = [
	['/([^/]+)(/([^/\.]+))(/|\\'. REWRITE_SUFFIX. ')?', ['controller' => 1, 'action' => 3]]
];

$config->defaultController = 'home';

return $config;