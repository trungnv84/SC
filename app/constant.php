<?php
date_default_timezone_set('Asia/Bangkok');
define('MICRO_TIME_NOW', microtime());
define('TIME_NOW', time());

define('DS', DIRECTORY_SEPARATOR);
define('APP_DIR', __DIR__ . DS);
define('ROOT_DIR', substr(APP_DIR, 0, 1 + strrpos(APP_DIR, DS, -2)));
define('APP_LOG_DIR', APP_DIR . 'logs' . DS);
define('ERROR_LOG_DIR', APP_LOG_DIR . 'errors' . DS);

if (isset($_SERVER['HTTP_HOST'])) {
	$_base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
	define('SCHEME', $_base_url);
	$_base_url .= '://' . $_SERVER['HTTP_HOST'];
	$_base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
} else {
	define('SCHEME', 'http');
	$_base_url = 'http://localhost/';
}
define('BASE_URL', $_base_url);

define('PUBLIC_DIR', ROOT_DIR . 'public' . DS);
define('CONTROLLER_DIR', APP_DIR . 'controller' . DS);
define('MODEL_DIR', APP_DIR . 'model' . DS);
define('TEMPLATE_DIR', APP_DIR . 'template' . DS);

define('CACHE_DIR', APP_DIR . 'cache' . DS);
define('PHP_CACHE_DIR', CACHE_DIR . 'php' . DS);
define('DB_CACHE_DIR', CACHE_DIR . 'db' . DS);

define('DEFAULT_CSS_DIR', PUBLIC_DIR . 'css' . DS);
define('CSS_CACHE_DIR', DEFAULT_CSS_DIR . 'cache' . DS);
define('DEFAULT_JS_DIR', PUBLIC_DIR . 'js' . DS);
define('JS_CACHE_DIR', DEFAULT_JS_DIR . 'cache' . DS);

/*##########################################################*/

define('ENVIRONMENT', 'Development');

define('ERROR_LOG_PASS', '123456');

define('PHP_CACHE', false);

/*##########################################################*/

define('DEFAULT_MODULE', 'site');
define('DEFAULT_CONTROLLER', 'home');
define('DEFAULT_ACTION', 'default');
define('DEFAULT_LAYOUT', 'default');

define('REWRITE_SUFFIX', '.html');
define('DEFAULT_TEMPLATE', 'site');
define('DEFAULT_VIEW_TYPE', 'html');

/*##########################################################*/

define('ASSETS_OPTIMIZATION', 15); //0 || 5 || 15
define('ASSETS_VERSION', '1.0');

/*##########################################################*/

define('DIR_WRITE_MODE', 0755);
define('FILE_WRITE_MODE', 0644);

define('DIR_SAFE_MODE', 0500);
define('FILE_SAFE_MODE', 0400);

/*##########################################################*/

define('MYSQL_DRIVER_NAME', 'MySql');

define('DB_INSTANCE', '');
define('DB_DRIVER', MYSQL_DRIVER_NAME);
define('DB_OBJECT_KEY', 'id');

/*##########################################################*/
