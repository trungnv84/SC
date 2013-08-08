<?php
define('REWRITE_SUFFIX', '.html');
define('DEFAULT_VIEW_TYPE', 'html');
define('DEFAULT_TEMPLATE', 'site');

$config = new stdClass();

$config->loadPath = [
	ROOT_DIR . DS . 'app' . DS . 'core',
	'Controller' => ROOT_DIR . DS . 'app' . DS . 'controller',
	'View' => ROOT_DIR . DS . 'app' . DS . 'view',
	'Model' => ROOT_DIR . DS . 'app' . DS . 'model',
	ROOT_DIR . DS . 'app' . DS . 'lib'
];

$config->router = [
	['/([^/]+)(/([^/\.]+))(/|\\'. REWRITE_SUFFIX. ')?', ['controller' => 1, 'action' => 3]]
];

$config->defaultController = 'home';

return $config;