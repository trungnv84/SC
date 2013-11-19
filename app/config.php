<?php
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

$config = new stdClass();

$config->autoLoadPath = array(
	APP_DIR . 'core',
	'Controller' => CONTROLLER_DIR,
	/*'View' => APP_DIR . DS . 'view',*/
	'Model' => MODEL_DIR,
	APP_DIR . 'lib'
);

$config->router = array(
	array(
		'^/admin(/|/([^/\.]+))?(/|/([^/\.]+))?(/|\\' . REWRITE_SUFFIX . ')?',
		array('controller' => 2, 'action' => 4)
	),
	array(
		'^/([^/\.]+)(/|/([^/\.]+))?(/|\\' . REWRITE_SUFFIX . ')?',
		array('controller' => 1, 'action' => 3)
	)
);

$config->modules = array('site', 'admin');

$config->modulePaths = array(
	'site' => array(
		'Controller' => $config->autoLoadPath['Controller'] . DS . 'site' //CONTROLLER_DIR . DS . 'site'
	),
	'admin' => array(
		'Controller' => $config->autoLoadPath['Controller'] . DS . 'admin' //CONTROLLER_DIR . DS . 'admin'
	)
);

$config->moduleTemplates = array(
	'site' => 'site',
	'admin' => 'admin'
);

/*##########################################################*/

/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$config->db[MYSQL_DRIVER_NAME] = array(
	'hostname' => '172.16.90.26',
	'username' => 'admin',
	'password' => 'admin',
	'database' => 'tivitz',
	'dbprefix' => 'tbl_',
	'pconnect' => true,
	//'db_debug' => TRUE;
	//'cache_on' => FALSE;
	//'cachedir' => '';
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	//'swap_pre' => '';
	//'autoinit' => TRUE;
	//'stricton' => FALSE;
);

if (DB_INSTANCE) {
	$config->db = array(
		DB_INSTANCE => $config->db
	);

	$config->db['user'] = array(
		MYSQL_DRIVER_NAME => array(
			'hostname' => '172.16.90.26',
			'username' => 'admin',
			'password' => 'admin',
			'database' => 'tivitz',
			'dbprefix' => '',
			'pconnect' => false,
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci'
		)
	);

	$config->db['session'] = array(
		MYSQL_DRIVER_NAME => 'user.sc_session'
	);
}

/*##########################################################*/

$config->dbKeyIgnores = array(
	MYSQL_DRIVER_NAME => array('database', 'dbprefix')
);

/*##########################################################*/

$config->defaultModule = DEFAULT_MODULE;
$config->defaultController = DEFAULT_CONTROLLER;
$config->defaultAction = DEFAULT_ACTION;

return $config;