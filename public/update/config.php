<?php
date_default_timezone_set('GMT');
define('MICRO_TIME_NOW', microtime());

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
define('CURRENT_URL', $_base_url . $_current_uri);

define('GIT_PATH', (substr(PHP_OS, 0, 3) == 'WIN' ? '"C:\\Program Files (x86)\\Git\bin\\git"' : 'git'));
//define('GIT_PATH', (substr(PHP_OS, 0, 3) == 'WIN' ? '"D:\\Program Files\\Git\\bin\\git"' : 'git'));

define('GIT_MAIN_BRANCH', 'master');
define('GIT_VERSION_PATH', 'public/update/versions/');
define('GIT_REVISION_PATH', 'public/update/revisions/');

define('VERSION_STATUS_AVAILABLE', 1);
define('VERSION_STATUS_UPDATE', 2);
define('VERSION_STATUS_REVERT', 4);
define('VERSION_STATUS_DOING', 8);
define('VERSION_STATUS_COMPLETE', 16);
define('VERSION_STATUS_SCRIPT', 32);
define('VERSION_STATUS_DATABASE', 64);
define('VERSION_STATUS_UN_SCRIPT', 128);
define('VERSION_STATUS_UN_DATABASE', 256);
define('VERSION_STATUS_SCRIPT_DOING', 512);
define('VERSION_STATUS_SCRIPT_COMPLETE', 1024);
define('VERSION_STATUS_DATABASE_DOING', 2048);
define('VERSION_STATUS_DATABASE_COMPLETE', 4096);

/*$_config = array(
	'db' => array(
		'hostname' => 'localhost',
		'username' => 'root',
		'password' => '',
		'database' => 'sc',
		'pconnect' => true,
		'char_set' => 'utf8',
		'dbcollat' => 'utf8_general_ci',
		'back_dir' => 'dbs/'
	)
);*/