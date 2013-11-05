<?php
date_default_timezone_set('Asia/Bangkok');
define('MICRO_TIME_NOW', microtime());
define('TIME_NOW', time());
define('DS', DIRECTORY_SEPARATOR);
define('PUBLIC_DIR', __DIR__);
define('ROOT_DIR', substr(PUBLIC_DIR, 0, strrpos(PUBLIC_DIR, DS)));
define('APP_DIR', ROOT_DIR . DS . 'app');
define('CONTROLLER_DIR', APP_DIR . DS . 'controller');
define('MODEL_DIR', APP_DIR . DS . 'model');
define('TEMPLATE_DIR', APP_DIR . DS . 'template');
if (isset($_SERVER['HTTP_HOST'])) {
	$_base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
	$_base_url .= '://' . $_SERVER['HTTP_HOST'];
	$_base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
} else {
	$_base_url = 'http://localhost/';
}
define('BASE_URL', $_base_url);
require(APP_DIR . DS . 'App.php');

App::run();