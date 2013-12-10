<?php
ob_start();
date_default_timezone_set('Asia/Bangkok');
define('MICRO_TIME_NOW', microtime());
define('TIME_NOW', time());

define('DS', DIRECTORY_SEPARATOR);
define('APP_DIR', __DIR__ . DS);
define('ROOT_DIR', substr(APP_DIR, 0, 1 + strrpos(APP_DIR, DS, -2)));

if (isset($_SERVER['HTTP_HOST'])) {
	$_base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
	define('SCHEME', $_base_url);
	$_base_url .= '://' . $_SERVER['HTTP_HOST'];
	$_current_uri = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
	$_base_url .= $_current_uri;
	$_current_uri = str_replace($_current_uri, '', $_SERVER['REQUEST_URI']);
	if ('public/' == substr($_base_url, -7)) $_base_url = substr($_base_url, 0, -7);
} else {
	define('SCHEME', 'http');
	$_base_url = 'http://localhost/';
	$_current_uri = substr($_SERVER["REQUEST_URI"], 1);
}
define('BASE_URL', $_base_url);
define('CURRENT_URI', $_current_uri);

define('PUBLIC_DIR', ROOT_DIR . 'public' . DS);
define('CORE_DIR', APP_DIR . 'core' . DS);
define('LIBRARY_DIR', APP_DIR . 'lib' . DS);
define('CONTROLLER_DIR', APP_DIR . 'controller' . DS);
define('MODEL_DIR', APP_DIR . 'model' . DS);
define('TEMPLATE_DIR', APP_DIR . 'template' . DS);

define('CACHE_DIR', APP_DIR . 'cache' . DS);
define('PHP_CACHE_DIR', CACHE_DIR . 'php' . DS); //need write permission
define('DB_CACHE_DIR', CACHE_DIR . 'db' . DS); //need write permission

define('DEFAULT_CSS_DIR', PUBLIC_DIR . 'css' . DS); //need write permission
define('CSS_CACHE_DIR', DEFAULT_CSS_DIR . 'cache' . DS); //need write permission
define('DEFAULT_JS_DIR', PUBLIC_DIR . 'js' . DS); //need write permission
define('JS_CACHE_DIR', DEFAULT_JS_DIR . 'cache' . DS); //need write permission

/*##########################################################*/

define('ERROR_LOG_FILE_SUFFIX', 'Y-m-d');
define('ERROR_LOG_PASS', 'e10adc3949ba59abbe56e057f20f883e');

/*
 *     Development
 *     Testing
 *     Production
 * */

define('ENVIRONMENT', 'Development');

define('PHP_CACHE', false);

define('ACTION_LIB_LOG', true);
define('ACTION_URL_LOG', true);

define('APP_LOG_DIR', APP_DIR . 'logs' . DS); //need write permission
define('ERROR_LOG_DIR', APP_LOG_DIR . 'errors' . DS); //need write permission
define('ACTION_LOG_DIR', APP_LOG_DIR . 'actions' . DS); //need write permission
define('LIB_LOG_DIR', APP_LOG_DIR . 'libs' . DS); //need write permission

/*##########################################################*/

define('DEFAULT_MODULE', 'site');
define('DEFAULT_CONTROLLER', 'home');
define('DEFAULT_ACTION', 'default');
define('DEFAULT_LAYOUT', 'default');

define('REWRITE_SUFFIX', '.html');
define('ADAPTER_FILE_EXT', '.adt');
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
define('MONGO_DRIVER_NAME', 'Mongo');

define('DB_INSTANCE', '');
define('DB_DRIVER_NAME', MYSQL_DRIVER_NAME);
define('DB_OBJECT_KEY', 'id');

/*##########################################################*/
